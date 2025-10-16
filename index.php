<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticação - Ouro Alerta</title>
    <link rel="stylesheet" href="style-login.css">
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <img src="ouro-logo.png" alt="Logo Ouro do Sul">
        </div>

        <h1>Ouro Alerta</h1>

        <?php if(isset($_GET['erro'])): ?>
            <p class="erro">Usuário ou senha inválidos!</p>
        <?php endif; ?>

        <form action="processa.php" method="post">
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="input-group">
                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha" required>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>
</body>
</html>
