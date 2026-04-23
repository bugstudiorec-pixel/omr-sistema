<?php
session_start();
require_once dirname(__DIR__) . '/config.php';
$prova = trim(isset($_GET['prova'])?$_GET['prova']:''); $turma = trim(isset($_GET['turma'])?$_GET['turma']:'');
$where = ' WHERE 1=1 '; $params = array();
if($prova !== ''){ $where .= ' AND prova LIKE ? '; $params[] = '%'.$prova.'%'; }
if($turma !== ''){ $where .= ' AND turma LIKE ? '; $params[] = '%'.$turma.'%'; }
$rows = fetch_all('SELECT * FROM vw_historico '.$where.' ORDER BY id DESC', $params);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="historico_omr.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, array('ID','Data','Aluno','Turma','Prova','Disciplina','Versao','Token','Acertos','Erros','Em Branco','Rasuras','Nota','Percentual','Revisado Manual'), ';');
foreach($rows as $r){ fputcsv($out, array($r['id'],$r['criado_em'],$r['aluno'],$r['turma'],$r['prova'],$r['disciplina'],$r['versao'],$r['token_qr'],$r['acertos'],$r['erros'],$r['em_branco'],$r['rasuras'],$r['nota'],$r['percentual'],$r['revisado_manual']), ';'); }
fclose($out); exit;
