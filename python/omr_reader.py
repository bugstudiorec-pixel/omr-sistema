import cv2
import json
import math
import os
import re
import sys
import numpy as np


def load_layout(path):
    with open(path, 'r', encoding='utf-8') as f:
        return json.load(f)


def order_points(pts):
    pts = np.array(pts, dtype='float32')
    s = pts.sum(axis=1)
    diff = np.diff(pts, axis=1)
    rect = np.zeros((4, 2), dtype='float32')
    rect[0] = pts[np.argmin(s)]
    rect[2] = pts[np.argmax(s)]
    rect[1] = pts[np.argmin(diff)]
    rect[3] = pts[np.argmax(diff)]
    return rect


def find_page_contour(image):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    blur = cv2.GaussianBlur(gray, (5, 5), 0)
    edged = cv2.Canny(blur, 75, 200)
    cnts, _ = cv2.findContours(edged, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)
    cnts = sorted(cnts, key=cv2.contourArea, reverse=True)[:10]
    for c in cnts:
        peri = cv2.arcLength(c, True)
        approx = cv2.approxPolyDP(c, 0.02 * peri, True)
        if len(approx) == 4:
            return approx.reshape(4, 2)
    return None


def four_point_transform(image, pts, width, height):
    rect = order_points(pts)
    dst = np.array([[0, 0], [width - 1, 0], [width - 1, height - 1], [0, height - 1]], dtype='float32')
    M = cv2.getPerspectiveTransform(rect, dst)
    warped = cv2.warpPerspective(image, M, (width, height))
    return warped


def parse_qr_text(text):
    payload = {'raw': text or ''}
    if not text:
        return payload
    pairs = re.split(r'[;\n]+', text)
    for pair in pairs:
        if '=' in pair:
            k, v = pair.split('=', 1)
            payload[k.strip().lower()] = v.strip()
    if 'token' not in payload and 'raw' in payload:
        m = re.search(r'([A-F0-9]{8,32})', payload['raw'].upper())
        if m:
            payload['token'] = m.group(1)
    return payload


def decode_qr(image):
    det = cv2.QRCodeDetector()
    data, points, _ = det.detectAndDecode(image)
    if data:
        return parse_qr_text(data)
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    data, points, _ = det.detectAndDecode(gray)
    if data:
        return parse_qr_text(data)
    return {'raw': ''}


def darkness_score(gray, cx, cy, r):
    h, w = gray.shape[:2]
    yy, xx = np.ogrid[:h, :w]
    inner = (xx - cx) ** 2 + (yy - cy) ** 2 <= r ** 2
    outer = (xx - cx) ** 2 + (yy - cy) ** 2 <= (r * 1.6) ** 2
    ring = np.logical_and(outer, np.logical_not(inner))
    inner_vals = gray[inner]
    ring_vals = gray[ring]
    if inner_vals.size == 0 or ring_vals.size == 0:
        return 0.0
    inner_dark = np.mean(255 - inner_vals) / 255.0
    ring_dark = np.mean(255 - ring_vals) / 255.0
    return max(0.0, inner_dark - ring_dark * 0.35)


def marker_darkness(gray, box):
    h, w = gray.shape[:2]
    x, y, bw, bh = box
    x1, y1 = int(x * w), int(y * h)
    x2, y2 = min(w, int((x + bw) * w)), min(h, int((y + bh) * h))
    roi = gray[y1:y2, x1:x2]
    if roi.size == 0:
        return 0.0
    return float(np.mean(255 - roi) / 255.0)


def read_answers(warped, layout):
    gray = cv2.cvtColor(warped, cv2.COLOR_BGR2GRAY)
    gray = cv2.GaussianBlur(gray, (3, 3), 0)
    questions = int(layout['questions'])
    alts = layout['alternatives']
    xs = layout['grid']['x_ratios']
    start_y = layout['grid']['start_y_ratio']
    pitch_y = layout['grid']['pitch_y_ratio']
    radius = layout['grid']['bubble_radius_ratio']
    filled_min = layout.get('thresholds', {}).get('filled_min', 0.12)
    multiple_min = layout.get('thresholds', {}).get('multiple_min', 0.10)
    difference_min = layout.get('thresholds', {}).get('difference_min', 0.03)
    h, w = gray.shape[:2]
    answers = {}
    scores = {}
    for q in range(1, questions + 1):
        cy = int((start_y + (q - 1) * pitch_y) * h)
        row_scores = {}
        for i, alt in enumerate(alts):
            cx = int(xs[i] * w)
            sc = darkness_score(gray, cx, cy, int(radius * min(w, h)))
            row_scores[alt] = round(float(sc), 4)
        ordered = sorted(row_scores.items(), key=lambda kv: kv[1], reverse=True)
        best_alt, best_score = ordered[0]
        second_score = ordered[1][1] if len(ordered) > 1 else 0.0
        if best_score < filled_min:
            ans = '?'
        elif second_score >= multiple_min and (best_score - second_score) < difference_min:
            ans = 'RASURA'
        else:
            ans = best_alt
        answers[str(q)] = ans
        scores[str(q)] = row_scores
    return answers, scores


def debug_draw(warped, layout, answers):
    out = warped.copy()
    h, w = out.shape[:2]
    xs = layout['grid']['x_ratios']
    start_y = layout['grid']['start_y_ratio']
    pitch_y = layout['grid']['pitch_y_ratio']
    radius = int(layout['grid']['bubble_radius_ratio'] * min(w, h))
    alts = layout['alternatives']
    for q in range(1, int(layout['questions']) + 1):
        cy = int((start_y + (q - 1) * pitch_y) * h)
        cv2.putText(out, str(q), (8, cy + 4), cv2.FONT_HERSHEY_SIMPLEX, 0.4, (0, 0, 255), 1, cv2.LINE_AA)
        for i, alt in enumerate(alts):
            cx = int(xs[i] * w)
            color = (180, 180, 180)
            if answers.get(str(q)) == alt:
                color = (0, 180, 0)
            elif answers.get(str(q)) == 'RASURA':
                color = (0, 0, 255)
            cv2.circle(out, (cx, cy), radius, color, 1)
            cv2.putText(out, alt, (cx - 5, cy - radius - 4), cv2.FONT_HERSHEY_SIMPLEX, 0.35, color, 1, cv2.LINE_AA)
    return out


def main():
    if len(sys.argv) < 4:
        print(json.dumps({'success': False, 'error': 'Uso: omr_reader.py <imagem> <saida_json> <layout_json>'}))
        return 1
    image_path, output_json, layout_path = sys.argv[1], sys.argv[2], sys.argv[3]
    try:
        layout = load_layout(layout_path)
        img = cv2.imread(image_path)
        if img is None:
            raise Exception('Não foi possível abrir a imagem.')
        qr_raw = decode_qr(img)
        width, height = int(layout['width']), int(layout['height'])
        contour = find_page_contour(img)
        if contour is not None:
            warped = four_point_transform(img, contour, width, height)
        else:
            warped = cv2.resize(img, (width, height))
        qr_warped = decode_qr(warped)
        qr = qr_warped if qr_warped.get('raw') else qr_raw
        gray_warped = cv2.cvtColor(warped, cv2.COLOR_BGR2GRAY)
        marker_info = {}
        for name, box in layout.get('markers', {}).items():
            marker_info[name] = round(marker_darkness(gray_warped, box), 4)
        answers, scores = read_answers(warped, layout)
        debug = debug_draw(warped, layout, answers)
        aligned_rel = os.path.join('temp', 'aligned_' + os.path.basename(image_path))
        aligned_abs = os.path.join(os.path.dirname(os.path.dirname(__file__)), aligned_rel)
        os.makedirs(os.path.dirname(aligned_abs), exist_ok=True)
        cv2.imwrite(aligned_abs, debug)
        result = {
            'success': True,
            'image': image_path,
            'layout': layout.get('name', os.path.basename(layout_path)),
            'qr': qr,
            'markers': marker_info,
            'answers': answers,
            'scores': scores,
            'aligned_image': aligned_rel.replace('\\', '/'),
            'page_detected': contour is not None
        }
    except Exception as e:
        result = {'success': False, 'error': str(e)}
    with open(output_json, 'w', encoding='utf-8') as f:
        json.dump(result, f, ensure_ascii=False, indent=2)
    print(json.dumps(result, ensure_ascii=False))
    return 0 if result.get('success') else 2


if __name__ == '__main__':
    raise SystemExit(main())
