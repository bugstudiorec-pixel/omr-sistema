<?php
require_once dirname(__DIR__) . '/config.php';

$id = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
if ($id < 1) { echo 'Aplicação inválida.'; exit; }

$ap = get_aplicacao($id);
if (!$ap) { echo 'Aplicação não encontrada.'; exit; }

$total_questoes = (int)$ap['total_questoes'];
$total_alt      = (int)$ap['total_alternativas'];
$alternativas   = array_slice(['A','B','C','D','E'], 0, $total_alt);

// Payload do QR (mesmo gerado pelo create_qr.py)
$qr_payload = 'TOKEN=' . $ap['token_qr'] . ';VERSAO=' . $ap['versao'];

// Tentar usar imagem salva em disco (fallback: gera via JS)
$qr_path_abs = dirname(__DIR__) . '/' . $ap['qr_path'];
$qr_base64   = '';
if (!empty($ap['qr_path']) && file_exists($qr_path_abs)) {
    $bytes = file_get_contents($qr_path_abs);
    if ($bytes !== false && strlen($bytes) > 0) {
        $qr_base64 = 'data:image/png;base64,' . base64_encode($bytes);
    }
}

// Distribuição: máx 20 questões por bloco/coluna
$por_grupo = 20;
$grupos    = array_chunk(range(1, $total_questoes), $por_grupo);
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Gabarito – <?php echo h($ap['prova_nome']); ?></title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;background:#e8edf3;color:#000;font-size:13px}

.no-print{
    display:flex;gap:10px;align-items:center;
    padding:11px 20px;background:#132033;color:#fff;
    position:sticky;top:0;z-index:10;
}
.no-print button{background:#2563eb;color:#fff;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:bold}
.no-print button:hover{background:#1d4ed8}
.no-print a{color:#93c5fd;font-size:13px}

.sheet{
    width:210mm;
    min-height:297mm;
    margin:16px auto;
    padding:13mm 14mm 12mm 14mm;
    border:1px solid #bbb;
    background:#fff;
    box-shadow:0 4px 28px rgba(0,0,0,.14);
}

/* Cabeçalho */
.header-row{
    display:flex;align-items:flex-start;justify-content:space-between;
    gap:14px;border-bottom:2.5px solid #000;padding-bottom:10px;margin-bottom:11px;
}
.header-info{flex:1;min-width:0}
.prova-nome{font-size:21px;font-weight:bold;line-height:1.2;margin-bottom:3px}
.prova-sub{font-size:11px;color:#444;margin-bottom:9px}
.aluno-fields{display:grid;grid-template-columns:1fr 1fr;gap:7px 14px}
.field-label{font-size:9px;font-weight:bold;color:#555;text-transform:uppercase;letter-spacing:.4px}
.field-line{border-bottom:1.3px solid #000;height:19px;margin-top:3px}

/* QR */
.qr-block{flex-shrink:0;text-align:center;width:110px}
.qr-wrap{
    width:104px;height:104px;
    border:2.5px solid #000;
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 5px;background:#fff;overflow:hidden;
}
.qr-wrap img{width:100px;height:100px;display:block}
.qr-wrap canvas,.qr-wrap>div>img{width:100px!important;height:100px!important}
.qr-label{font-size:9px;font-weight:bold;color:#111;line-height:1.3;word-break:break-all}
.token-code{font-size:7.5px;font-family:Consolas,monospace;color:#666;margin-top:2px;word-break:break-all}

/* Instruções */
.instrucoes{
    background:#f6f6f6;border:1px solid #ccc;border-radius:5px;
    padding:7px 10px;font-size:10px;margin-bottom:12px;line-height:1.55;
}

/* Título grade */
.grid-title{
    font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:.6px;
    border-bottom:2px solid #000;padding-bottom:4px;margin-bottom:9px;
}

/* Wrapper dos blocos lado a lado */
.blocos-wrap{display:flex;gap:8px;align-items:flex-start;justify-content:flex-start}

/* Cada bloco = uma "tabela coluna" */
.bloco{flex:0 0 auto;border:1.2px solid #aaa;}
.bloco table{width:auto;border-collapse:collapse}
.bloco table tr{border-bottom:1px solid #d0d0d0}
.bloco table tr:last-child{border-bottom:none}
.bloco table td{padding:2px 5px;vertical-align:middle;line-height:1}
.q-num{
    font-size:11px;font-weight:bold;width:22px;
    text-align:right;white-space:nowrap;padding-right:2px!important;
}
.bubbles{display:flex;gap:4px;align-items:center}
.bubble{
    width:19px;height:19px;border-radius:50%;
    border:1.5px solid #000;
    display:flex;align-items:center;justify-content:center;
    font-size:8.5px;font-weight:bold;flex-shrink:0;background:#fff;
}

/* Rodapé */
.footer-strip{
    margin-top:14px;border-top:1px solid #bbb;padding-top:7px;
    font-size:9px;color:#555;display:flex;justify-content:space-between;
}

@media print{
    .no-print{display:none!important}
    .sheet{margin:0;border:none;width:100%;min-height:unset;box-shadow:none;padding:10mm 12mm}
    body{background:#fff}
}
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨️ Imprimir / Salvar PDF</button>
    <a href="aplicacoes.php">← Voltar</a>
    <span style="margin-left:auto;font-size:12px;opacity:.6"><?php echo h($ap['prova_nome']); ?> · <?php echo h($ap['aluno_nome']); ?></span>
</div>

<div class="sheet">

    <!-- Cabeçalho -->
    <div class="header-row">
        <div class="header-info">
            <div class="prova-nome"><?php echo h($ap['prova_nome']); ?></div>
            <div class="prova-sub">
                <?php echo h($ap['disciplina']); ?>
                &nbsp;·&nbsp; Versão <strong><?php echo h($ap['versao']); ?></strong>
                &nbsp;·&nbsp; <?php echo $total_questoes; ?> questões
            </div>
            <div class="aluno-fields">
                <div>
                    <div class="field-label">Nome do Aluno</div>
                    <div class="field-line"></div>
                </div>
                <div>
                    <div class="field-label">Turma / Série</div>
                    <div class="field-line"></div>
                </div>
                <div>
                    <div class="field-label">Data</div>
                    <div class="field-line"></div>
                </div>
                <div>
                    <div class="field-label">Nº / Matrícula</div>
                    <div class="field-line"></div>
                </div>
            </div>
        </div>

        <!-- QR Code: usa base64 do disco se disponível, senão gera via JS -->
        <div class="qr-block">
            <div class="qr-wrap">
                <?php if ($qr_base64): ?>
                    <img src="<?php echo $qr_base64; ?>" alt="QR Code">
                <?php else: ?>
                    <div id="qr-canvas"></div>
                <?php endif; ?>
            </div>
            <div class="qr-label"><?php echo h($ap['prova_nome']); ?></div>
            <div class="token-code"><?php echo h($ap['token_qr']); ?></div>
        </div>
    </div>

    <!-- Instruções -->
    <div class="instrucoes">
        <strong>⚠ Instruções:</strong> Use somente caneta <strong>preta ou azul</strong>.
        Preencha completamente a bolha <strong>(●)</strong> da alternativa escolhida.
        Não rasure — em caso de erro, solicite outra folha ao professor.
    </div>

    <!-- Grade de questões -->
    <div class="grid-title">Gabarito de Respostas</div>

    <div class="blocos-wrap">
        <?php foreach ($grupos as $idx => $grupo): ?>
        <div class="bloco">
            <table>
                <?php foreach ($grupo as $q): ?>
                <tr>
                    <td class="q-num"><?php echo $q; ?></td>
                    <td>
                        <div class="bubbles">
                            <?php foreach ($alternativas as $alt): ?>
                            <div class="bubble"><?php echo $alt; ?></div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Rodapé -->
    <div class="footer-strip">
        <span>Aluno: <strong><?php echo h($ap['aluno_nome']); ?></strong> · Turma: <?php echo h($ap['aluno_turma']); ?></span>
        <span>Token: <strong><?php echo h($ap['token_qr']); ?></strong></span>
    </div>

</div>

<script>
(function(){
    var el = document.getElementById('qr-canvas');
    if (!el) return; // imagem do disco já está no lugar
    var payload = <?php echo json_encode($qr_payload); ?>;
    if (typeof QRCode === 'undefined') {
        el.innerHTML = '<span style="font-size:8px;color:#c00;padding:4px">QRCode.js<br>não carregou</span>';
        return;
    }
    new QRCode(el, {
        text: payload,
        width: 100,
        height: 100,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
})();
</script>
</body>
</html>
