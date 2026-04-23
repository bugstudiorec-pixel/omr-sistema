<?php
session_start();
require_once dirname(__DIR__) . '/config.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ redirect_to('corrigir.php'); }
list($ok, $saved) = save_uploaded_file('scan', 'scans');
if(!$ok){ flash_set('error', $saved); redirect_to('corrigir.php'); }
$token_manual = strtoupper(trim(isset($_POST['token_manual'])?$_POST['token_manual']:''));
$temp_json = TEMP_DIR . '/omr_' . time() . '_' . mt_rand(1000,9999) . '.json';
ensure_dir(TEMP_DIR);
$res = run_python_script('omr_reader.py', array($saved, $temp_json, DEFAULT_LAYOUT_FILE));
if($res['code'] !== 0 || !file_exists($temp_json)){
    @unlink($temp_json);
    flash_set('error', 'Falha ao executar o Python/OpenCV. Saída: ' . $res['output']);
    redirect_to('corrigir.php');
}
$data = json_decode(file_get_contents($temp_json), true);
@unlink($temp_json);
if(!$data || empty($data['success'])){
    flash_set('error', 'O leitor retornou erro: ' . ($data && isset($data['error']) ? $data['error'] : 'JSON inválido'));
    redirect_to('corrigir.php');
}
$token = '';
if(!empty($data['qr']) && !empty($data['qr']['token'])) $token = strtoupper($data['qr']['token']);
if($token === '' && $token_manual !== '') $token = $token_manual;
if($token === ''){ flash_set('error', 'Não foi possível identificar o token da aplicação.'); redirect_to('corrigir.php'); }
$app = get_aplicacao_by_token($token);
if(!$app){ flash_set('error', 'Aplicação não encontrada para o token ' . $token); redirect_to('corrigir.php'); }
$layout_file = $app['layout_file'] ? $app['layout_file'] : DEFAULT_LAYOUT_FILE;
if(basename($layout_file) !== basename(DEFAULT_LAYOUT_FILE)){
    $temp_json = TEMP_DIR . '/omr_' . time() . '_' . mt_rand(1000,9999) . '.json';
    $res2 = run_python_script('omr_reader.py', array($saved, $temp_json, $layout_file));
    if($res2['code'] === 0 && file_exists($temp_json)){
        $data2 = json_decode(file_get_contents($temp_json), true);
        if($data2 && !empty($data2['success'])) $data = $data2;
        @unlink($temp_json);
    }
}
$gabarito = get_gabarito_map($app['prova_id'], $app['versao']);
$comparacao = array(); $acertos=0; $erros=0; $branco=0; $rasuras=0;
$answers = isset($data['answers']) ? $data['answers'] : array();
$scores = isset($data['scores']) ? $data['scores'] : array();
for($q=1; $q <= (int)$app['total_questoes']; $q++){
    $lida = isset($answers[(string)$q]) ? $answers[(string)$q] : (isset($answers[$q]) ? $answers[$q] : '?');
    $correta = isset($gabarito[$q]) ? $gabarito[$q] : '?';
    $status = 'errada';
    if($lida === 'RASURA'){ $rasuras++; $status='rasura'; }
    elseif($lida === '?' || $lida === ''){ $branco++; $status='branco'; }
    elseif($correta === '?'){ $status='anulada'; $acertos++; }
    elseif($lida === $correta){ $acertos++; $status='correta'; }
    else { $erros++; $status='errada'; }
    $comparacao[$q] = array('lida'=>$lida,'final'=>$lida,'correta'=>$correta,'status'=>$status,'scores'=>isset($scores[(string)$q]) ? $scores[(string)$q] : array());
}
$total_validas = max(1, (int)$app['total_questoes']);
$nota = round(($acertos / $total_validas) * 10, 2);
$percentual = round(($acertos / $total_validas) * 100, 1);
execute_sql('INSERT INTO resultados(aplicacao_id,token_qr,imagem_original,imagem_alinhada,leitura_json,acertos,erros,em_branco,rasuras,nota,percentual) VALUES(?,?,?,?,?,?,?,?,?,?,?)', array($app['id'],$token,str_replace(dirname(__DIR__) . '/', '', $saved),isset($data['aligned_image'])?$data['aligned_image']:'',json_encode($data),$acertos,$erros,$branco,$rasuras,$nota,$percentual));
$resultado_id = db()->lastInsertId();
foreach($comparacao as $q=>$item){
    execute_sql('INSERT INTO resultado_questoes(resultado_id,numero_questao,resposta_lida,resposta_final,resposta_correta,status,score_json) VALUES(?,?,?,?,?,?,?)', array($resultado_id,$q,$item['lida'],$item['final'],$item['correta'],$item['status'],json_encode($item['scores'])));
}
redirect_to('resultado.php?id=' . $resultado_id);
