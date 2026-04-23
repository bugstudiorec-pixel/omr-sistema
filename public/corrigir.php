<?php
$pageTitle = 'Corrigir OMR';
include '_header.php';
?>
<div class="grid">
<div class="card"><h2 style="margin-top:0">Enviar folha para leitura</h2>
<form method="post" action="processar_omr.php" enctype="multipart/form-data">
<div class="field"><label>Imagem da folha</label><input class="input" type="file" name="scan" accept="image/png,image/jpeg,image/webp" required></div>
<div class="field"><label>Token manual (opcional)</label><input class="input" type="text" name="token_manual" placeholder="Use se o QR não for lido."></div>
<button class="btn" type="submit">Processar leitura OMR</button>
</form>
<p class="muted">O Python tenta localizar a folha, corrigir perspectiva, ler o QR e identificar as bolhas. Se o QR falhar, você pode informar o token manualmente.</p></div>
<div class="card"><h2 style="margin-top:0">Boas práticas</h2>
<ul>
<li>Foto inteira da folha, sem cortar cantos.</li>
<li>Boa iluminação e pouco reflexo.</li>
<li>Preferir scan ou foto o mais reta possível.</li>
<li>Usar marcas de sincronismo pretas nos 4 cantos.</li>
<li>Imprimir o QR no canto superior.</li>
</ul>
<img class="img-preview" src="assets/img/template_14x4.png" alt="Template base">
</div></div>
<?php include '_footer.php'; ?>
