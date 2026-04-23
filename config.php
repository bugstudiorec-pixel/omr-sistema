<?php
// Compatível com PHP antigo (ex.: UwAmp) e sem login.
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

if (!defined('APP_NAME')) define('APP_NAME', 'Sistema OMR PHP + Python');
if (!defined('DB_FILE')) define('DB_FILE', __DIR__ . '/data/app.sqlite');
if (!defined('PYTHON_BIN')) define('PYTHON_BIN', 'python');
if (!defined('PYTHON_DIR')) define('PYTHON_DIR', __DIR__ . '/python');
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', __DIR__ . '/uploads');
if (!defined('TEMP_DIR')) define('TEMP_DIR', __DIR__ . '/temp');
if (!defined('DEFAULT_LAYOUT_FILE')) define('DEFAULT_LAYOUT_FILE', __DIR__ . '/python/layouts/layout_14q_4alt.json');

function app_path($path){ return __DIR__ . '/' . ltrim($path, '/'); }
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function ensure_dir($dir){ if(!is_dir($dir)){ mkdir($dir, 0777, true); } }
function flash_set($type, $message){ if(session_status() !== PHP_SESSION_ACTIVE){ session_start(); } $_SESSION['_flash'] = array('type'=>$type,'message'=>$message); }
function flash_get(){ if(session_status() !== PHP_SESSION_ACTIVE){ session_start(); } if(!isset($_SESSION['_flash'])) return null; $f=$_SESSION['_flash']; unset($_SESSION['_flash']); return $f; }
function redirect_to($url){ header('Location: '.$url); exit; }
function is_windows(){ return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'; }

function db(){
    static $pdo = null;
    if($pdo !== null){ return $pdo; }
    ensure_dir(dirname(DB_FILE));
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    init_db($pdo);
    return $pdo;
}

function init_db($pdo){
    $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
}

function fetch_all($sql, $params=array()){
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_one($sql, $params=array()){
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ? $row : null;
}

function execute_sql($sql, $params=array()){
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

function generate_token(){
    if(function_exists('random_bytes')) return strtoupper(bin2hex(random_bytes(8)));
    if(function_exists('openssl_random_pseudo_bytes')){
        $b = openssl_random_pseudo_bytes(8);
        if($b !== false) return strtoupper(bin2hex($b));
    }
    return strtoupper(substr(sha1(uniqid(mt_rand(), true) . microtime(true)), 0, 16));
}

function save_uploaded_file($field, $subdir){
    if(!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK){ return array(false, 'Upload inválido.'); }
    ensure_dir(UPLOAD_DIR . '/' . $subdir);
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES[$field]['name']);
    $file = time() . '_' . $name;
    $dest = UPLOAD_DIR . '/' . $subdir . '/' . $file;
    if(!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)){
        return array(false, 'Não foi possível salvar o arquivo enviado.');
    }
    return array(true, $dest);
}

function build_python_command($script, $args){
    $parts = array();
    $parts[] = escapeshellcmd(PYTHON_BIN);
    $parts[] = escapeshellarg(PYTHON_DIR . '/' . $script);
    foreach($args as $arg){ $parts[] = escapeshellarg($arg); }
    $cmd = implode(' ', $parts) . ' 2>&1';
    return $cmd;
}

function run_python_script($script, $args){
    $cmd = build_python_command($script, $args);
    $output = array();
    $code = 0;
    exec($cmd, $output, $code);
    return array('code'=>$code, 'output'=>implode("\n", $output), 'cmd'=>$cmd);
}

function create_qr_image($text, $outfile){
    ensure_dir(dirname($outfile));
    $res = run_python_script('create_qr.py', array($text, $outfile));
    return file_exists($outfile) ? array(true, $outfile, $res) : array(false, '', $res);
}

function get_prova($id){ return fetch_one('SELECT * FROM provas WHERE id = ?', array($id)); }
function get_aluno($id){ return fetch_one('SELECT * FROM alunos WHERE id = ?', array($id)); }
function get_aplicacao($id){
    return fetch_one('SELECT a.*, al.nome AS aluno_nome, al.turma AS aluno_turma, p.nome AS prova_nome, p.disciplina, p.total_questoes, p.total_alternativas, p.layout_file
                      FROM aplicacoes a
                      JOIN alunos al ON al.id = a.aluno_id
                      JOIN provas p ON p.id = a.prova_id
                      WHERE a.id = ?', array($id));
}
function get_aplicacao_by_token($token){
    return fetch_one('SELECT a.*, al.nome AS aluno_nome, al.turma AS aluno_turma, p.nome AS prova_nome, p.disciplina, p.total_questoes, p.total_alternativas, p.layout_file
                      FROM aplicacoes a
                      JOIN alunos al ON al.id = a.aluno_id
                      JOIN provas p ON p.id = a.prova_id
                      WHERE a.token_qr = ?', array($token));
}
function get_gabarito_map($prova_id, $versao){
    $rows = fetch_all('SELECT numero_questao, resposta_correta FROM gabaritos WHERE prova_id = ? AND versao = ? ORDER BY numero_questao', array($prova_id, $versao));
    $map = array();
    foreach($rows as $r){ $map[(int)$r['numero_questao']] = $r['resposta_correta']; }
    return $map;
}
