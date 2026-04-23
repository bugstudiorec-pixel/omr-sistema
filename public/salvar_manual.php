<?php
session_start();
require_once dirname(__DIR__) . '/config.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST') redirect_to('historico.php');
$resultado_id = (int)(isset($_POST['resultado_id']) ? $_POST['resultado_id'] : 0);
$res = fetch_one('SELECT r.*, a.prova_id, a.versao FROM resultados r LEFT JOIN aplicacoes a ON a.id=r.aplicacao_id WHERE r.id=?', array($resultado_id));
if(!$res){ flash_set('error', 'Resultado não encontrado.'); redirect_to('historico.php'); }
$gabarito = get_gabarito_map($res['prova_id'], $res['versao']);
$q = isset($_POST['q']) ? $_POST['q'] : array();
$acertos=0; $erros=0; $branco=0; $rasuras=0;
foreach($q as $num=>$valor){
    $num=(int)$num; $valor=strtoupper(trim($valor)); if($valor==='') $valor='?';
    $correta = isset($gabarito[$num]) ? $gabarito[$num] : '?';
    $status='errada';
    if($valor==='RASURA'){ $rasuras++; $status='rasura'; }
    elseif($valor==='?' || $valor===''){ $branco++; $status='branco'; }
    elseif($correta==='?'){ $acertos++; $status='anulada'; }
    elseif($valor===$correta){ $acertos++; $status='correta'; }
    else { $erros++; $status='errada'; }
    execute_sql('UPDATE resultado_questoes SET resposta_final=?, status=? WHERE resultado_id=? AND numero_questao=?', array($valor,$status,$resultado_id,$num));
}
$total = max(1, count($q));
$nota = round(($acertos / $total) * 10, 2);
$percentual = round(($acertos / $total) * 100, 1);
execute_sql('UPDATE resultados SET acertos=?, erros=?, em_branco=?, rasuras=?, nota=?, percentual=?, revisado_manual=1 WHERE id=?', array($acertos,$erros,$branco,$rasuras,$nota,$percentual,$resultado_id));
flash_set('success','Correção manual salva.'); redirect_to('resultado.php?id='.$resultado_id);
