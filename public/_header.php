<?php
if(session_status() !== PHP_SESSION_ACTIVE){ session_start(); }
require_once dirname(__DIR__) . '/config.php';
$flash = flash_get();
?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?php echo isset($pageTitle)?h($pageTitle):APP_NAME; ?></title><link rel="stylesheet" href="assets/css/style.css"></head><body>
<div class="nav"><div class="wrap"><div class="brand"><?php echo h(APP_NAME); ?></div><a href="index.php">Dashboard</a><a href="alunos.php">Alunos</a><a href="provas.php">Provas</a><a href="aplicacoes.php">Aplicações + QR</a><a href="corrigir.php">Corrigir OMR</a><a href="historico.php">Histórico</a></div></div>
<div class="wrap">
<?php if($flash): ?><div class="alert <?php echo h($flash['type']); ?>"><?php echo h($flash['message']); ?></div><?php endif; ?>
