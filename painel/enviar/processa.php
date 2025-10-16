<?php
require __DIR__ . '/../../vendor/autoload.php';
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

    function filtrar($a){ //Funçao de filtragem
        $a = filter_var($a, FILTER_SANITIZE_EMAIL);
        return filter_var($a, FILTER_VALIDATE_EMAIL);
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){ //Verificação de envio do formulario
        $envio=$_POST['envio'];
        $senha=$_POST['senha'];
        $des=$_POST['des'];
        $asst=$_POST['asst'];
        $texto=$_POST['texto'];

        if(!filtrar($des) || !filtrar($envio)){
            $_SESSION['enviado']= "Um dos Emails está inválido!";
            header("location: index.php");
            exit;
        }else{
            $debugLog = '';
            $mail= new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
        try { 
            //Configuraçãoes do serrvidor
            $mail->IsSMTP();
            $mail->Host = "smtp.ourodosul.com.br";
            $mail->Port = 587;//587, 465, 25
            $mail->SMTPAuth = true;
            $mail ->Username =$envio;
            $mail ->Password = $senha;
            $mail ->SMTPSecure=false;//tls ou ssl ou false
            $mail->SMTPAutoTLS = false;
            
            //Debug de envio
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER; 
            $mail->Debugoutput = function($str, $level) use (&$debugLog) {
            $debugLog .= "Debug level $level: $str\n";
            };

            //Remetente e destinatario
            $mail ->setFrom($envio, "");
            $mail ->addAddress($des);

            //Corpo do email
            $mail ->Subject=$asst;
            $mail ->isHTML(true);
            if (!empty(trim($texto))) {
                $mail->Body = nl2br(htmlentities($texto));
                $mail->AltBody = $texto;
            } else {
                $mail->Body = "<p>(Sem conteúdo)</p>";
                $mail->AltBody = "(Sem conteúdo)";
            }
            //Anexo
            if(isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == UPLOAD_ERR_OK){
                $mail ->addAttachment($_FILES['arquivo']['tmp_name'], $_FILES['arquivo']['name']);
            }

            $mail ->send();
            $_SESSION['enviado']="Email enviado com sucesso!";  
        } catch (Exception $e){
            $_SESSION['enviado']="Falha no envio do Email!" . $mail -> ErrorInfo;
        }
        }
        //Envia o debug para pasta logs
        $nome_arquivo = 'log_email_' . date('Ymd_His') . '.log';
        file_put_contents(__DIR__ . '/logs/' . $nome_arquivo, $debugLog);

        header("location: index.php");
        exit;
    }
?>
