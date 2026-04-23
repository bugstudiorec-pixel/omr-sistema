<?php
$pageTitle = 'Resultado';
include '_header.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = fetch_one('SELECT r.*, a.versao, a.token_qr, al.nome AS aluno, al.turma, p.nome AS prova, p.disciplina FROM resultados r LEFT JOIN aplicacoes a ON a.id=r.aplicacao_id LEFT JOIN alunos al ON al.id=a.aluno_id LEFT JOIN provas p ON p.id=a.prova_id WHERE r.id=?', array($id));
if(!$res){ echo '<div class="card">Resultado não encontrado.</div>'; include '_footer.php'; exit; }
$itens = fetch_all('SELECT * FROM resultado_questoes WHERE resultado_id=? ORDER BY numero_questao', array($id));
$leitura = json_decode($res['leitura_json'], true);
?>
<div class="hero"><h1 style="margin-top:0">Resultado da correção</h1>
<div class="pill">Aluno: <?php echo h($res['aluno']); ?></div><div class="pill">Turma: <?php echo h($res['turma']); ?></div><div class="pill">Prova: <?php echo h($res['prova']); ?></div><div class="pill">Versão: <?php echo h($res['versao']); ?></div><div class="pill">Token: <?php echo h($res['token_qr']); ?></div><div class="pill">Nota: <?php echo h(number_format($res['nota'],2,',','.')); ?></div><div class="pill">Aproveitamento: <?php echo h(number_format($res['percentual'],1,',','.')); ?>%</div>
<div class="top-actions"><a class="btn secondary" href="historico.php">Ir para histórico</a></div></div>
<div class="grid">
<div class="card"><h2 style="margin-top:0">Questões</h2>
<form method="post" action="salvar_manual.php">
<input type="hidden" name="resultado_id" value="<?php echo (int)$id; ?>">
<div class="question-grid">
<?php foreach($itens as $item): ?>
<div class="qbox">
<div><strong>Questão <?php echo (int)$item['numero_questao']; ?></strong></div>
<div class="small muted">Lida: <?php echo h($item['resposta_lida']); ?> | Correta: <?php echo h($item['resposta_correta']); ?></div>
<div class="small muted">Status atual: <?php echo h($item['status']); ?></div>
<div class="field" style="margin-top:8px"><label class="small">Resposta final</label><input class="input" type="text" name="q[<?php echo (int)$item['numero_questao']; ?>]" value="<?php echo h($item['resposta_final']); ?>"></div>
</div>
<?php endforeach; ?>
</div>
<button class="btn" type="submit" style="margin-top:16px">Salvar correção manual</button>
</form></div>
<div class="card"><h2 style="margin-top:0">Leitura OMR bruta</h2>
<?php if(!empty($res['imagem_original']) && file_exists(dirname(__DIR__) . '/' . $res['imagem_original'])): ?><div><p class="small">Imagem original</p><img class="img-preview" src="../<?php echo h($res['imagem_original']); ?>"></div><?php endif; ?>
<?php if(!empty($leitura['aligned_image']) && file_exists(dirname(__DIR__) . '/' . $leitura['aligned_image'])): ?><div><p class="small">Imagem alinhada</p><img class="img-preview" src="../<?php echo h($leitura['aligned_image']); ?>"></div><?php endif; ?>
<pre class="code"><?php echo h(json_encode($leitura, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
</div></div>
<?php include '_footer.php'; ?>
