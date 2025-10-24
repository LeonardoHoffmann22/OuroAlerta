<?php
require __DIR__ . '/../../vendor/autoload.php'; // Carrega PHPMailer + FPDF via Composer
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função de filtragem de email
function filtrar($a) {
    $a = filter_var($a, FILTER_SANITIZE_EMAIL);
    return filter_var($a, FILTER_VALIDATE_EMAIL);
}

// Função para gerar PDF obrigatório a partir de arquivo de texto
function gerarPDFObrigatorio($arquivoTmp, $nomeOriginal) {
    $pdfDestino = sys_get_temp_dir() . '/' . pathinfo($nomeOriginal, PATHINFO_FILENAME) . '_' . uniqid() . '.pdf';
    try {
        $conteudo = file_get_contents($arquivoTmp);
        if ($conteudo === false) {
            return false;
        }
//teste git
        // Criação do PDF usando FPDF via Composer
        $pdf = new \FPDF(); // Observe a barra invertida
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 8, utf8_decode($conteudo));
        $pdf->Output('F', $pdfDestino);

        return $pdfDestino;
    } catch (Exception $e) {
        return false;
    }
}

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $envio = $_POST['envio'];
    $senha = $_POST['senha'];
    $des = $_POST['des'];
    $asst = $_POST['asst'];
    $texto = $_POST['texto'];

    // Validação de emails
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
            // Configuração SMTP
            $mail->isSMTP();
            $mail->Host = "smtp.ourodosul.com.br";
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->Username = $envio;
            $mail->Password = $senha;
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;

            // Debug
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) use (&$debugLog) {
                $debugLog .= "Debug level $level: $str\n";
            };

            // Remetente e destinatário
            $mail->setFrom($envio, "");
            $mail->addAddress($des);

            // Corpo do email
            $mail->Subject = $asst;
            $mail->isHTML(true);
            if (!empty(trim($texto))) {
                $mail->Body = nl2br(htmlentities($texto));
                $mail->AltBody = $texto;
            } else {
                $mail->Body = "<p>(Sem conteúdo)</p>";
                $mail->AltBody = "(Sem conteúdo)";
            }

            // Anexo — converte para PDF obrigatoriamente
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                $pdfConvertido = gerarPDFObrigatorio($_FILES['arquivo']['tmp_name'], $_FILES['arquivo']['name']);
                if (!$pdfConvertido) {
                    $_SESSION['enviado'] = "Erro ao converter o arquivo para PDF. O envio foi cancelado.";
                    header("location: index.php");
                    exit; // encerra o script
                }
                $mail->addAttachment($pdfConvertido, basename($pdfConvertido));
            }

            // Envia o email
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