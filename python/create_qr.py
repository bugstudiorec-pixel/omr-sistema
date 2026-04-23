import os, sys

try:
    import qrcode
except Exception as e:
    sys.stderr.write('Biblioteca qrcode não instalada: %s\n' % e)
    sys.exit(2)

if len(sys.argv) < 3:
    sys.stderr.write('Uso: create_qr.py <texto> <saida.png>\n')
    sys.exit(1)

text = sys.argv[1]
out = sys.argv[2]
os.makedirs(os.path.dirname(out), exist_ok=True)
img = qrcode.make(text)
img.save(out)
print(out)
