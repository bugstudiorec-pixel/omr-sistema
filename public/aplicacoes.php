<?php
$pageTitle = 'Aplicações e QR';
include '_header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $aluno_id = (int)(isset($_POST['aluno_id'])?$_POST['aluno_id']:0);
    $prova_id = (int)(isset($_POST['prova_id'])?$_POST['prova_id']:0);
    $versao = strtoupper(trim(isset($_POST['versao'])?$_POST['versao']:'A'));
    if($aluno_id < 1 || $prova_id < 1){ flash_set('error','Selecione aluno e prova.'); redirect_to('aplicacoes.php'); }
    $token = generate_token();
    $qr_rel = 'uploads/qr/' . $token . '.png';
    $qr_abs = dirname(__DIR__) . '/' . $qr_rel;
    $payload = 'TOKEN=' . $token . ';VERSAO=' . $versao;
    create_qr_image($payload, $qr_abs);
    execute_sql('INSERT INTO aplicacoes(aluno_id,prova_id,versao,token_qr,qr_path) VALUES(?,?,?,?,?)', array($aluno_id,$prova_id,$versao,$token,$qr_rel));
    flash_set('success','Aplicação criada. Token: '.$token); redirect_to('aplicacoes.php');
}
$alunos = fetch_all('SELECT * FROM alunos ORDER BY nome');
$provas = fetch_all('SELECT * FROM provas ORDER BY nome');
$apps = fetch_all('SELECT a.*, al.nome AS aluno, al.turma, p.nome AS prova FROM aplicacoes a JOIN alunos al ON al.id=a.aluno_id JOIN provas p ON p.id=a.prova_id ORDER BY a.id DESC LIMIT 50');
?>
<div class="grid">
<div class="card"><h2 style="margin-top:0">Nova aplicação</h2>
<form method="post">
<div class="field"><label>Aluno</label><select class="input" name="aluno_id"><?php foreach($alunos as $a): ?><option value="<?php echo (int)$a['id']; ?>"><?php echo h($a['nome'].' - '.$a['turma']); ?></option><?php endforeach; ?></select></div>
<div class="field"><label>Prova</label><select class="input" name="prova_id"><?php foreach($provas as $p): ?><option value="<?php echo (int)$p['id']; ?>"><?php echo h($p['nome'].' - '.$p['disciplina']); ?></option><?php endforeach; ?></select></div>
<div class="field"><label>Versão</label><input class="input" type="text" name="versao" value="A"></div>
<button class="btn" type="submit">Criar aplicação e QR</button>
</form></div>
<div class="card"><h2 style="margin-top:0">Aplicações recentes</h2>
<table class="table"><tr><th>ID</th><th>Aluno</th><th>Prova</th><th>Versão</th><th>Token</th><th>QR</th><th>Gabarito</th></tr>
<?php foreach($apps as $ap): ?><tr><td><?php echo (int)$ap['id']; ?></td><td><?php echo h($ap['aluno']); ?><div class="small muted"><?php echo h($ap['turma']); ?></div></td><td><?php echo h($ap['prova']); ?></td><td><?php echo h($ap['versao']); ?></td><td class="mono"><?php echo h($ap['token_qr']); ?></td><td><?php if($ap['qr_path'] && file_exists(dirname(__DIR__) . '/' . $ap['qr_path'])): ?><a class="btn light" target="_blank" href="../<?php echo h($ap['qr_path']); ?>">Abrir QR</a><?php else: ?><span class="muted">QR não gerado</span><?php endif; ?></td><td><a class="btn" target="_blank" href="gabarito_imprimir.php?id=<?php echo (int)$ap['id']; ?>">🖨️ Imprimir</a></td></tr><?php endforeach; ?>
</table></div></div>
<?php include '_footer.php'; ?>
