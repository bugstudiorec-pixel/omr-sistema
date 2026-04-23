<?php
$pageTitle = 'Provas';
include '_header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome = trim(isset($_POST['nome'])?$_POST['nome']:'');
    $disciplina = trim(isset($_POST['disciplina'])?$_POST['disciplina']:'');
    $tq = (int)(isset($_POST['total_questoes'])?$_POST['total_questoes']:0);
    $ta = (int)(isset($_POST['total_alternativas'])?$_POST['total_alternativas']:4);
    $versao = strtoupper(trim(isset($_POST['versao'])?$_POST['versao']:'A'));
    $layout_file = trim(isset($_POST['layout_file'])?$_POST['layout_file']:'');
    $observacoes = trim(isset($_POST['observacoes'])?$_POST['observacoes']:'');
    $gabarito = trim(isset($_POST['gabarito'])?$_POST['gabarito']:'');
    if($nome==='' || $disciplina==='' || $tq < 1 || $ta < 4){ flash_set('error','Preencha os campos obrigatórios da prova.'); redirect_to('provas.php'); }
    if($layout_file==='') $layout_file = DEFAULT_LAYOUT_FILE;
    execute_sql('INSERT INTO provas(nome,disciplina,total_questoes,total_alternativas,layout_file,observacoes) VALUES(?,?,?,?,?,?)', array($nome,$disciplina,$tq,$ta,$layout_file,$observacoes));
    $prova_id = db()->lastInsertId();
    $parts = preg_split('/\s+/', preg_replace('/[^A-E?\s]/i', ' ', strtoupper($gabarito)));
    for($i=1;$i<=$tq;$i++){
        $resp = isset($parts[$i-1]) && $parts[$i-1] !== '' ? $parts[$i-1] : '?';
        execute_sql('INSERT INTO gabaritos(prova_id,versao,numero_questao,resposta_correta) VALUES(?,?,?,?)', array($prova_id,$versao,$i,$resp));
    }
    flash_set('success','Prova e gabarito cadastrados.'); redirect_to('provas.php');
}
$provas = fetch_all('SELECT * FROM provas ORDER BY id DESC');
?>
<div class="grid">
<div class="card"><h2 style="margin-top:0">Nova prova</h2>
<form method="post">
<div class="row"><div class="field"><label>Nome da prova</label><input class="input" type="text" name="nome"></div><div class="field"><label>Disciplina</label><input class="input" type="text" name="disciplina"></div></div>
<div class="row"><div class="field"><label>Total de questões</label><input class="input" type="number" name="total_questoes" value="14"></div><div class="field"><label>Total de alternativas</label><select class="input" name="total_alternativas"><option value="4">4 (A-D)</option><option value="5">5 (A-E)</option></select></div></div>
<div class="row"><div class="field"><label>Versão do gabarito</label><input class="input" type="text" name="versao" value="A"></div><div class="field"><label>Arquivo de layout JSON</label><input class="input" type="text" name="layout_file" value="<?php echo h(DEFAULT_LAYOUT_FILE); ?>"></div></div>
<div class="field"><label>Gabarito (separado por espaço)</label><textarea name="gabarito" rows="5" placeholder="Ex.: B C D C B C D B C A D C A C"></textarea></div>
<div class="field"><label>Observações</label><textarea name="observacoes" rows="3"></textarea></div>
<button class="btn" type="submit">Salvar prova</button>
</form></div>
<div class="card"><h2 style="margin-top:0">Provas cadastradas</h2>
<table class="table"><tr><th>ID</th><th>Prova</th><th>Disciplina</th><th>Questões</th><th>Alternativas</th><th>Layout</th></tr>
<?php foreach($provas as $p): ?><tr><td><?php echo (int)$p['id']; ?></td><td><?php echo h($p['nome']); ?></td><td><?php echo h($p['disciplina']); ?></td><td><?php echo (int)$p['total_questoes']; ?></td><td><?php echo (int)$p['total_alternativas']; ?></td><td class="small mono"><?php echo h(basename($p['layout_file'])); ?></td></tr><?php endforeach; ?>
</table><p class="muted">Para versões B/C, crie mais linhas em <span class="mono">gabaritos</span> ou adapte esta página para cadastrar outras versões da mesma prova.</p></div></div>
<?php include '_footer.php'; ?>
