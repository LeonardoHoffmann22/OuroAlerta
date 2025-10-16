<?php
session_start();
if (!isset($_SESSION["logado"]) || $_SESSION["logado"] !== true) {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Inserir Nova Planilha</title>
    <link rel="stylesheet" href="../../style-internas.css">
</head>
<body>
    <div class="topbar">
        <div class="topbar-left">Ouro Alerta - Inserir Novos Arquivos</div>
        <div class="topbar-right">
            <a href="../painel.php" class="btn-topbar">Voltar</a>
        </div>
    </div>

    <div class="container">
        <?php if (!empty($_SESSION['mensagem'])): ?>
            <div class="mensagem">
                <?= $_SESSION['mensagem']; ?>
            </div>
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>

        <div class="card">
            <form action="planilha.php" method="post" enctype="multipart/form-data" class="formulario">
                <h2 style="color: var(--verde-escuro); text-align:center; margin-bottom: 1rem;">Envio de Arquivos</h2>

                <label for="arquivo_planilha">Selecione a Planilha (.xlsx):</label>
                <input type="file" id="arquivo_planilha" name="arquivo_planilha" accept=".xlsx" required>

                <label for="arquivo_txt">Selecione o Modelo de Texto (.txt):</label>
                <input type="file" id="arquivo_txt" name="arquivo_txt" accept=".txt" required>

                <button type="submit" class="btn">Enviar</button>
            </form>
                
                <div class="btn-duplo">
                    <a href="../ArmazenaP/sobras.xlsx" target="_blank" class="btn">Visualizar Planilha</a>
                    <a href="visualizarTXT.php" target="_blank" class="btn">Visualizar Modelo TXT</a>
                </div>
        </div>
    </div>
</body>
</html>