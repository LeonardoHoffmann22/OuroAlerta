<?php
session_start();
if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true) {
    header("Location: ../../index.php");
    exit;
}
if(!isset($_SESSION['enviado'])){
    $_SESSION['enviado'] = "Nenhum envio feito!";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envio de Email</title>
    <link rel="stylesheet" href="../../style-internas.css">
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">Ouro Alerta - Envio de Email</div>
        <div class="topbar-right">
            <a href="../painel.php" class="btn-topbar">Voltar</a>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="container">
        <div class="card">
            <form id="form-envio" action="processa.php" method="post" enctype="multipart/form-data" class="formulario">
                
                <label>Email de Envio:</label>
                <input type="email" name="envio" required>

                <label>Senha:</label>
                <input type="password" name="senha" required>

                <label>Destinatário:</label>
                <input type="email" name="des" required>

                <label>Assunto:</label>
                <input type="text" name="asst" maxlength="50" required>

                <label>Texto:</label>
                <textarea name="texto" placeholder="Digite sua mensagem"></textarea>

                <label>Anexo:</label>
                <input type="file" name="arquivo">

                <button type="submit" class="btn">Enviar</button>
                <p class="status"><?php echo $_SESSION['enviado']; ?></p>
            </form>
        </div>
    </div>
    <script>
        window.addEventListener('beforeunload', function () {
            navigator.sendBeacon('resetarEnviado.php');
        });
    </script>
</body>
</html>