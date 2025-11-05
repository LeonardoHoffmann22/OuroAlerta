<?php
require __DIR__ . '/../../vendor/autoload.php';
session_start();

//Reset da mensagem de envio
unset($_SESSION['enviado']);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função de filtragem de email
function filtrar($a) {
    $a = filter_var($a, FILTER_SANITIZE_EMAIL);
    return filter_var($a, FILTER_VALIDATE_EMAIL);
}

// Função para converter qualquer arquivo em PDF com tratamento de tipos não suportados
function converterParaPDF($arquivoTmp, $nomeOriginal) {
    $ext = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
    $saidaDir = sys_get_temp_dir();
    $saidaPDF = $saidaDir . '/anexo_ourodosul.pdf';
    $logConversao = __DIR__ . '/logs/conversao_' . date('Ymd_His') . '.log';

    // Lista de extensões suportadas pelo LibreOffice
    $formatosSuportados = [
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'odt', 'ods', 'odp', 'txt', 'csv', 'rtf', 'pdf'
    ];

    // Garante que o arquivo existe
    if (!file_exists($arquivoTmp)) {
        file_put_contents($logConversao, "ERRO: Arquivo temporário não encontrado.\n");
        return false;
    }

    // Verifica se o formato é suportado
    if (!in_array($ext, $formatosSuportados)) {
        file_put_contents($logConversao, "ERRO: Formato .$ext não suportado para conversão em PDF.\n");
        return false;
    }

    // Se já for PDF, apenas copia
    if ($ext === 'pdf') {
        if (copy($arquivoTmp, $saidaPDF)) {
            file_put_contents($logConversao, "Arquivo já era PDF, copiado para: $saidaPDF\n");
            return $saidaPDF;
        } else {
            file_put_contents($logConversao, "ERRO ao copiar PDF original.\n");
            return false;
        }
    }

    // Caminho do LibreOffice
    $caminho = '"C:\Program Files\LibreOffice\program\soffice.exe"';

    // Comando de conversão
    $comando = $caminho . ' --headless --nologo --convert-to pdf --outdir '
              . escapeshellarg($saidaDir) . ' ' . escapeshellarg($arquivoTmp);

    // Executa e captura resultado
    exec($comando . ' 2>&1', $saidaExec, $retorno);

    $log = "Comando executado:\n$comando\n\nSaída:\n" . implode("\n", $saidaExec) . "\nCódigo de retorno: $retorno\n";
    file_put_contents($logConversao, $log);

    // Sucesso
    if ($retorno === 0) {
        $arquivosPDF = glob($saidaDir . '/*.pdf');
        $maisRecente = $arquivosPDF
            ? array_reduce($arquivosPDF, fn($a, $b) => filemtime($b) > filemtime($a) ? $b : $a)
            : null;

        if ($maisRecente && file_exists($maisRecente)) {
            copy($maisRecente, $saidaPDF);
            file_put_contents($logConversao, "\nConversão concluída. Anexo gerado: $saidaPDF\n", FILE_APPEND);
            return $saidaPDF;
        }

        file_put_contents($logConversao, "\nERRO: Nenhum PDF gerado.\n", FILE_APPEND);
        return false;
    }

    // Falha
    file_put_contents($logConversao, "\nERRO: Conversão falhou (código $retorno)\n", FILE_APPEND);
    return false;
}

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

            //assunto
            $mail->Subject = $asst;
            //Texto
            if (!empty(trim($texto))) {
                $mail->Body = nl2br(htmlentities($texto));
                $mail->AltBody = $texto;
            } else {
                $mail->Body = "<p>(Sem conteúdo)</p>";
                $mail->AltBody = "(Sem conteúdo)";
            }
            
            //Anexo e pdf
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
                $pdfConvertido = converterParaPDF($_FILES['arquivo']['tmp_name'], $_FILES['arquivo']['name']);
                if (!$pdfConvertido || !file_exists($pdfConvertido)) {
                    $_SESSION['enviado'] = "Erro ao converter o arquivo em PDF. O envio foi cancelado.";
                    header("location: index.php");
                    exit;
                }
                $mail->addAttachment($pdfConvertido, 'anexo_ourodosul.pdf', 'base64', 'application/pdf');

            }

            //Envia
            $mail->send();
            $_SESSION['enviado'] = "Email enviado com sucesso!";
        } catch (Exception $e) {
            $_SESSION['enviado'] = "Falha no envio do Email! " . $mail->ErrorInfo;
        }
    }

    //Salva log de debug
    $nome_arquivo = 'log_email_' . date('Ymd_His') . '.log';
    file_put_contents(__DIR__ . '/logs/' . $nome_arquivo, $debugLog);

    header("location: index.php");
    exit;
}
?>