<?php
require_once __DIR__ . '/config.php';
if(file_exists(DB_FILE)){ unlink(DB_FILE); }
db();
header('Content-Type: text/plain; charset=utf-8');
echo "Banco recriado com sucesso em: " . DB_FILE . "\n";
echo "Agora abra /public/index.php\n";
