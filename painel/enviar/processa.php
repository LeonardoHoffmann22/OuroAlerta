<?php
require __DIR__ . '/../../vendor/autoload.php';
session_start();

// Reset da mensagem de envio antes de processar
unset($_SESSION['enviado']);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função de filtragem de email
function filtrar($a) {
    $a = filter_var($a, FILTER_SANITIZE_EMAIL);
    return filter_var($a, FILTER_VALIDATE_EMAIL);
}

function converterParaPDF($arquivoTmp, $nomeOriginal) {
    $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
    $saidaPDF = sys_get_temp_dir() . '/' . pathinfo($nomeOriginal, PATHINFO_FILENAME) . '_' . uniqid() . '.pdf';

    if ($ext === 'pdf') { // Já é PDF
        return $arquivoTmp;
    }

    $caminho = '"C:\Program Files\LibreOffice\program\soffice.exe"'; // Caminho completo LibreOffice

    if (!file_exists($arquivoTmp)) {
        return false;
    }

    $converte = $caminho . ' --headless --nologo --convert-to pdf --outdir ' 
             . escapeshellarg(sys_get_temp_dir()) . ' ' 
             . escapeshellarg($arquivoTmp);

    exec($converte . ' 2>&1', $captura, $retorno);

    if ($retorno === 0) {
        $arquivos = glob(sys_get_temp_dir() . '/*.pdf');
        return $arquivos 
            ? array_reduce($arquivos, fn($maisRecente, $atual) => 
                filemtime($atual) > filemtime($maisRecente) ? $atual : $maisRecente
            ) 
            : false;
    }

    return false;
}

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
            if (!empty(trim($texto))) {
                $mail->Body = nl2br(htmlentities($texto));
                $mail->AltBody = $texto;
            } else {
                $mail->Body = "<p>(Sem conteúdo)</p>";
                $mail->AltBody = "(Sem conteúdo)";
            }

            // Anexo — conversão obrigatória para PDF
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                $pdfConvertido = converterParaPDF($_FILES['arquivo']['tmp_name'], $_FILES['arquivo']['name']);
                if (!$pdfConvertido || !file_exists($pdfConvertido)) {
                    $_SESSION['enviado'] = "Erro ao converter o arquivo em PDF. O envio foi cancelado.";
                    header("location: index.php");
                    exit;
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