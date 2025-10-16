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
    <title>Distribuição de Sobras</title>
    <link rel="stylesheet" href="../../style-internas.css">
</head>
<body>
    <div class="topbar">
        <div class="topbar-left">Ouro Alerta - Distribuição de Sobras</div>
        <div class="topbar-right">
            <a href="../painel.php" class="btn-topbar">Voltar</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <button onclick="confirmarEnvio()" class="btn">Enviar Emails</button>

            <div id="statusEnvio" class="status">
                Nenhum envio iniciado ainda.
            </div>
        </div>
    </div>

    <script>
    function confirmarEnvio() {
        if (confirm("Deseja realmente enviar os emails para todos os clientes da planilha?")) {
            iniciarEnvio();
        }
    }

    function iniciarEnvio() {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "envia.php", true);
        xhr.send();

        document.getElementById("statusEnvio").innerText = "Iniciando envio...";

        const interval = setInterval(() => {
            fetch("processa_envio.php")
                .then(res => res.json())
                .then(data => {
                    document.getElementById("statusEnvio").innerText =
                        `Enviados: ${data.enviados} / ${data.total}`;

                    if (data.concluido) {
                        clearInterval(interval);
                        alert("Envio concluído!");
                    }
                });
        }, 2000);
    }
    </script>
</body>
</html>