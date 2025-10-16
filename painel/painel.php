<?php
session_start();
if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true) {//verificação de login
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Ouro Alerta</title>
    <link rel="stylesheet" href="../style-painel.css">
</head>
<body>
    <header class="topbar">
        <h1>Ouro Alerta - Painel</h1>
        <a href="sair.php" class="btn-logout">Sair</a>
    </header>

    <main class="painel-container">
        <form action="enviar/index.php" method="post">
            <button type="submit" class="btn-action">Enviar Email</button>
        </form>

        <form action="planilha/index.php" method="post">
            <button type="submit" class="btn-action">Inserir Nova Planilha</button>
        </form>

        <form action="receber/index.php" method="post">
            <button type="submit" class="btn-action">Enviar Aviso de Distribuição</button>
        </form>
    </main>
</body>
</html>
