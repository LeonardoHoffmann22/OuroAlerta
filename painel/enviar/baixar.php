<?php
require __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../bd/MySQL.php'; // ajuste o caminho

// Pasta onde os arquivos serão salvos
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['anexo'])) {
    $arquivoTmp = $_FILES['anexo']['tmp_name'];
    $nomeOriginal = $_FILES['anexo']['name'];
    $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);

    // Nome único do arquivo físico
    $nomeArquivo = uniqid('anexo_', true) . '.' . $extensao;
    $caminhoFinal = $uploadDir . $nomeArquivo;

    if (move_uploaded_file($arquivoTmp, $caminhoFinal)) {
        $link = bin2hex(random_bytes(16)); // gera hash único

        $sql = "INSERT INTO Anexos (link, arquivo, ativo) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $link, $nomeArquivo);
        $stmt->execute();

        // Gerar o link final de download
        $url = "https://seusite.com/download.php?link=" . $link;

        echo "Arquivo enviado com sucesso!<br>";
        echo "Link único de download: <a href='$url'>$url</a>";

        // Aqui você pode integrar o envio do e-mail com PHPMailer
        // $mail->Body = "Baixe o arquivo em: $url";
    } else {
        echo "Erro ao mover o arquivo.";
    }
}
?>