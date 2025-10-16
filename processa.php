<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $mail = new PHPMailer(true);

    try {
         $mail->isSMTP();
        $mail->Host = 'smtp.ourodosul.com.br';//servidor de verificaçao
        $mail->SMTPAuth = true;
        $mail->Username = $email;
        $mail->Password = $senha;
        $mail->Port = 587;
        $mail->SMTPAutoTLS = false;
        $mail->SMTPDebug = 0;

        if ($mail->smtpConnect()) {
            $_SESSION["logado"] = true;
            $_SESSION["usuario"] = $email;

            $mail->smtpClose();
            header("Location: /OuroAlerta/painel/painel.php");
            exit;
        } else {
            header("Location: index.php?erro=1");
            exit;
        }

    } catch (Exception $e) {
        header("Location: index.php?erro=1");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
/*Faz a verificação de login(email e senha) de forma externa, contatando o servidor via smtp 
e validando os dados fornecidos pelo usuario, caso logado com sucesso o usuario é direcionado 
para o painel de inicio.
*/
?>
