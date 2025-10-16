<?php
$arquivo = '../ArmazenaP/emai-cap.txt';

if (!file_exists($arquivo)) {
    echo "Arquivo não encontrado!";
    exit;
}

$conteudo = file_get_contents($arquivo);

$conteudoSeguro = htmlspecialchars($conteudo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Modelo TXT</title>
    <style>
        body { font-family: monospace; white-space: pre-wrap; padding: 1rem; }
        .container { max-width: 900px; margin: auto; }
    </style>
</head>
<body>
<div class="container">
<pre><?= $conteudoSeguro ?></pre>
</div>
</body>
</html>