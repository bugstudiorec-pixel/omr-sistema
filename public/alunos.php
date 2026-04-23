<?php
$pageTitle = 'Alunos';
include '_header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome = trim(isset($_POST['nome'])?$_POST['nome']:'');
    $turma = trim(isset($_POST['turma'])?$_POST['turma']:'');
    $ra = trim(isset($_POST['ra'])?$_POST['ra']:'');
    if($nome==='' || $turma===''){ flash_set('error','Preencha nome e turma.'); redirect_to('alunos.php'); }
    execute_sql('INSERT INTO alunos(nome,turma,ra) VALUES(?,?,?)', array($nome,$turma,$ra));
    flash_set('success','Aluno cadastrado com sucesso.'); redirect_to('alunos.php');
}
$alunos = fetch_all('SELECT * FROM alunos ORDER BY id DESC');
?>
<div class="grid">
<div class="card"><h2 style="margin-top:0">Novo aluno</h2>
<form method="post">
<div class="field"><label>Nome</label><input class="input" type="text" name="nome"></div>
<div class="row"><div class="field"><label>Turma</label><input class="input" type="text" name="turma"></div><div class="field"><label>RA / Matrícula</label><input class="input" type="text" name="ra"></div></div>
<button class="btn" type="submit">Salvar aluno</button>
</form></div>
<div class="card"><h2 style="margin-top:0">Alunos cadastrados</h2>
<table class="table"><tr><th>ID</th><th>Nome</th><th>Turma</th><th>RA</th></tr>
<?php foreach($alunos as $a): ?><tr><td><?php echo (int)$a['id']; ?></td><td><?php echo h($a['nome']); ?></td><td><?php echo h($a['turma']); ?></td><td><?php echo h($a['ra']); ?></td></tr><?php endforeach; ?>
</table></div></div>
<?php include '_footer.php'; ?>
