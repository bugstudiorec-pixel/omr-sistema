<?php
$pageTitle = 'Dashboard';
include '_header.php';
$a = fetch_one('SELECT COUNT(*) total FROM alunos');
$p = fetch_one('SELECT COUNT(*) total FROM provas');
$ap = fetch_one('SELECT COUNT(*) total FROM aplicacoes');
$r = fetch_one('SELECT COUNT(*) total FROM resultados');
?>
<div class="hero">
  <h1 style="margin-top:0">Sistema de correção OMR com PHP + Python/OpenCV</h1>
  <p class="muted">Fluxo completo: cadastro de aluno e prova, geração do QR/token da aplicação, upload da folha, leitura óptica via Python, correção automática, revisão manual e histórico.</p>
  <div class="top-actions">
    <a class="btn" href="alunos.php">Cadastrar aluno</a>
    <a class="btn" href="provas.php">Cadastrar prova</a>
    <a class="btn" href="aplicacoes.php">Gerar aplicação + QR</a>
    <a class="btn" href="corrigir.php">Enviar folha</a>
  </div>
</div>
<div class="grid-3">
  <div class="stat"><div class="label">Alunos</div><div class="value"><?php echo (int)$a['total']; ?></div></div>
  <div class="stat"><div class="label">Provas</div><div class="value"><?php echo (int)$p['total']; ?></div></div>
  <div class="stat"><div class="label">Aplicações</div><div class="value"><?php echo (int)$ap['total']; ?></div></div>
  <div class="stat"><div class="label">Correções</div><div class="value"><?php echo (int)$r['total']; ?></div></div>
  <div class="stat"><div class="label">Motor OMR</div><div class="value small">Python + OpenCV</div><div class="muted">Leitura por contornos, warp de perspectiva, QRCodeDetector e análise de preenchimento.</div></div>
  <div class="stat"><div class="label">Dependências</div><div class="value small">PHP + Python 3</div><div class="muted">Necessário: exec habilitado no PHP, python, opencv-python, numpy, pillow; opcional: qrcode.</div></div>
</div>
<div class="card" style="margin-top:18px">
<h3 style="margin-top:0">Como usar</h3>
<ol>
<li>Cadastre aluno.</li>
<li>Cadastre a prova e o gabarito da versão A/B/C.</li>
<li>Crie a aplicação para um aluno e gere o QR do token.</li>
<li>Imprima a folha com o QR e as marcas de sincronismo.</li>
<li>Envie a foto/scan na página de correção.</li>
<li>Revise manualmente qualquer questão, se necessário.</li>
</ol>
</div>
<?php include '_footer.php'; ?>
