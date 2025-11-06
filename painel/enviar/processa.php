<?php
require __DIR__ . '/../../vendor/autoload.php';
session_start();

unset($_SESSION['enviado']);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use mysqli;

// Função de filtragem de email
function filtrar($a) {
    $a = filter_var($a, FILTER_SANITIZE_EMAIL);
    return filter_var($a, FILTER_VALIDATE_EMAIL);
}

// Conexão com o banco
require_once __DIR__ . '/../../ANEXOS.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $envio = $_POST['envio'];
    $senha = $_POST['senha'];
    $des = $_POST['des'];
    $asst = $_POST['asst'];
    $texto = $_POST['texto'];

    //Validação de emails
    if (!filtrar($des) || !filtrar($envio)) {
        $_SESSION['enviado'] = "Um dos Emails está inválido!";
        header("location: index.php");
        exit;
    } else {
        $debugLog = '';
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        try {
            //Configuração SMTP
            $mail->isSMTP();
            $mail->Host = "smtp.ourodosul.com.br";
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->Username = $envio;
            $mail->Password = $senha;
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;

            //Debug
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) use (&$debugLog) {
                $debugLog .= "Debug level $level: $str\n";
            };

            //Remetente e destinatário
            $mail->setFrom($envio, "Contato Ouro do Sul");
            $mail->addAddress($des);

            //Assunto
            $mail->Subject = $asst;

            //Texto base do corpo
            if (!empty(trim($texto))) {
                $mail->Body = nl2br(htmlentities($texto));
                $mail->AltBody = $texto;
            } else {
                $mail->Body = "<p>(Sem conteúdo)</p>";
                $mail->AltBody = "(Sem conteúdo)";
            }

            // Upload e geração do link de download
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $arquivoTmp = $_FILES['arquivo']['tmp_name'];
                $nomeO = $_FILES['arquivo']['name'];
                $extensao = pathinfo($nomeO, PATHINFO_EXTENSION);

                $nomeA = uniqid('anexo_', true) . '.' . $extensao;
                $caminho = $uploadDir . $nomeA;

                if (move_uploaded_file($arquivoTmp, $caminho)) {
                    $link = bin2hex(random_bytes(16));

                    //Grava no banco
                    $sql = "INSERT INTO Anexos (link, arquivo, ativo) VALUES (?, ?, 1)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $link, $nomeA);
                    $stmt->execute();

                    //Gera o link
                    $url = "http://$host/painel/enviar/baixar.php?link=" . $link;

                    //Adiciona o link ao corpo do e-mail
                    $mail->Body .= "<br><br><strong>Baixe o anexo aqui:</strong> <a href='$url'>$url</a>";
                    $mail->AltBody .= "\n\nBaixe o anexo aqui: $url";
                } else {
                    $_SESSION['enviado'] = "Erro ao processar o anexo.";
                    header("location: index.php");
                    exit;
                }
            }

            // Envia o e-mail
            $mail->send();
            $_SESSION['enviado'] = "Email enviado com sucesso!";

        } catch (Exception $e) {
            $_SESSION['enviado'] = "Falha no envio do Email! " . $mail->ErrorInfo;
        }
    }

    // Salva log de debug
    $nome_arquivo = 'log_email_' . date('Ymd_His') . '.log';
    file_put_contents(__DIR__ . '/logs/' . $nome_arquivo, $debugLog);

    header("location: index.php");
    exit;
}
?>