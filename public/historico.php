<?php
$pageTitle = 'Histórico';
include '_header.php';
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1; $per = 10; $off = ($page-1)*$per;
$prova = trim(isset($_GET['prova'])?$_GET['prova']:''); $turma = trim(isset($_GET['turma'])?$_GET['turma']:'');
$where = ' WHERE 1=1 '; $params = array();
if($prova !== ''){ $where .= ' AND prova LIKE ? '; $params[] = '%'.$prova.'%'; }
if($turma !== ''){ $where .= ' AND turma LIKE ? '; $params[] = '%'.$turma.'%'; }
$total = fetch_one('SELECT COUNT(*) total FROM vw_historico '.$where, $params); $pages = max(1, ceil($total['total']/$per));
$params2 = $params; $params2[] = $per; $params2[] = $off;
$rows = fetch_all('SELECT * FROM vw_historico '.$where.' ORDER BY id DESC LIMIT ? OFFSET ?', $params2);
?>
<div class="card"><h2 style="margin-top:0">Filtros</h2>
<form method="get"><div class="row"><div class="field"><label>Prova</label><input class="input" type="text" name="prova" value="<?php echo h($prova); ?>"></div><div class="field"><label>Turma</label><input class="input" type="text" name="turma" value="<?php echo h($turma); ?>"></div></div><button class="btn" type="submit">Filtrar</button> <a class="btn light" href="exportar_csv.php?prova=<?php echo urlencode($prova); ?>&turma=<?php echo urlencode($turma); ?>">Exportar CSV</a></form></div>
<div class="card" style="margin-top:18px"><h2 style="margin-top:0">Histórico</h2>
<table class="table"><tr><th>ID</th><th>Aluno</th><th>Turma</th><th>Prova</th><th>Versão</th><th>Nota</th><th>%</th><th>Rev.</th><th>Ações</th></tr>
<?php foreach($rows as $r): ?><tr><td><?php echo (int)$r['id']; ?></td><td><?php echo h($r['aluno']); ?></td><td><?php echo h($r['turma']); ?></td><td><?php echo h($r['prova']); ?></td><td><?php echo h($r['versao']); ?></td><td><?php echo h(number_format($r['nota'],2,',','.')); ?></td><td><?php echo h(number_format($r['percentual'],1,',','.')); ?>%</td><td><?php echo ((int)$r['revisado_manual']) ? 'Sim':'Não'; ?></td><td><a class="btn light" href="resultado.php?id=<?php echo (int)$r['id']; ?>">Abrir</a></td></tr><?php endforeach; ?></table>
<div class="pagination" style="margin-top:16px">
<?php for($i=1;$i<=$pages;$i++): if($i==$page): ?><span class="active"><?php echo $i; ?></span><?php else: ?><a href="?page=<?php echo $i; ?>&prova=<?php echo urlencode($prova); ?>&turma=<?php echo urlencode($turma); ?>"><?php echo $i; ?></a><?php endif; endfor; ?>
</div></div>
<?php include '_footer.php'; ?>
