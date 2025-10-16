<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/../../vendor/autoload.php';

$pastaLogs = __DIR__ . "/logs";
if (!is_dir($pastaLogs)) mkdir($pastaLogs, 0777, true);

$arquivoLog = $pastaLogs . "/envio_" . date("Y-m-d_H-i-s") . ".log";

function registrarLog($mensagem, $arquivoLog) {
    $linha = "[" . date("d/m/Y H:i:s") . "] " . $mensagem . "\n";
    file_put_contents($arquivoLog, $linha, FILE_APPEND);
}

registrarLog("INÍCIO DO ENVIO", $arquivoLog);

$arquivoPlanilha   = __DIR__ . "/../ArmazenaP/sobras.xlsx";
$arquivoTxt        = __DIR__ . "/../ArmazenaP/emai-cap.txt";
$arquivoProgresso  = __DIR__ . "/progresso.json";

file_put_contents($arquivoProgresso, json_encode([
    "total" => 0,
    "enviados" => 0,
    "concluido" => false
]));

if (!file_exists($arquivoPlanilha)) {
    registrarLog("Planilha não encontrada!", $arquivoLog);
    die("Planilha não encontrada.");
}
if (!file_exists($arquivoTxt)) {
    registrarLog("Modelo TXT não encontrado!", $arquivoLog);
    die("Modelo TXT não encontrado.");
}

$spreadsheet = IOFactory::load($arquivoPlanilha);
$sheet = $spreadsheet->getActiveSheet();
$template = file_get_contents($arquivoTxt);

$encoding = mb_detect_encoding($template, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

if ($encoding !== 'UTF-8') {
    $template = mb_convert_encoding($template, 'UTF-8', $encoding);
}

registrarLog("Planilha e template carregados com sucesso.", $arquivoLog);

$totalClientes = 0;
foreach ($sheet->getRowIterator(2) as $row) {
    $email = $row->getCellIterator('M','M')->current()->getValue();
    if (!empty($email)) $totalClientes++;
}

file_put_contents($arquivoProgresso, json_encode([
    "total" => $totalClientes,
    "enviados" => 0,
    "concluido" => false
]));

$enviados = 0;

foreach ($sheet->getRowIterator(2) as $row) {
    $cell = $row->getCellIterator();
    $cell->setIterateOnlyExistingCells(false);

    $dados = [];
    foreach ($cell as $c) $dados[] = $c->getValue();

    $nome  = $dados[1];
    $email = $dados[12];

    registrarLog(str_repeat("=", 50), $arquivoLog);
    registrarLog("Início do envio para: $nome <$email>", $arquivoLog);

    if (empty($email)) {
        registrarLog("Usuário não possui email. Ignorado.", $arquivoLog);
        registrarLog(str_repeat("=", 50) . "\n", $arquivoLog);
        continue;
    }

    $valor1 = is_numeric($dados[9])  ? number_format($dados[9], 2, ',', '.') : $dados[9];
    $valor2 = is_numeric($dados[10]) ? number_format($dados[10], 2, ',', '.') : $dados[10];
    $valor3 = is_numeric($dados[11]) ? number_format($dados[11], 2, ',', '.') : $dados[11];

   $mensagem = str_replace(
    ["<nome>", "<codigo>", "R$##.###.###,#1", "R$ ###.###,#2", "R$ ###.###,#3"],
    [$nome, $dados[0], "R$ " . $valor1, "R$ " . $valor2, "R$ " . $valor3],
    $template
    );

    $mail = new PHPMailer(true);
    try {
        $mail->IsSMTP();
        $mail->Host = "smtp.ourodosul.com.br";
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = "ourodosul@ourodosul.com.br";
        $mail->Password = "Email#1935";
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;

        $mail->setFrom('ourodosul@ourodosul.com.br', 'Ouro do Sul');
        $mail->addAddress($email, $nome);

        $mail->isHTML(false);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Distribuição de Sobras - Ouro do Sul";
        $mail->Body    = $mensagem;

        $mail->SMTPDebug = 1;
        $mail->Debugoutput = function($str, $level) use ($arquivoLog, $nome, $email) {
            if (stripos($str, 'error') !== false || stripos($str, 'fail') !== false) {
                registrarLog("[SMTP Debug Level $level] $str (Destinatário: $nome <$email>)", $arquivoLog);
            }
        };

        $mail->send();
        registrarLog("Envio realizado com sucesso.", $arquivoLog);

    } catch (Exception $e) {
        registrarLog("Erro no envio: " . $e->getMessage(), $arquivoLog);
    }

    $enviados++;

    file_put_contents($arquivoProgresso, json_encode([
        "total" => $totalClientes,
        "enviados" => $enviados,
        "concluido" => ($enviados == $totalClientes)
    ]));

    registrarLog("Fim do envio para: $nome <$email>", $arquivoLog);
    registrarLog(str_repeat("=", 50) . "\n", $arquivoLog);
}

registrarLog("FIM DO ENVIO", $arquivoLog);
registrarLog("Total clientes: $totalClientes | Enviados: $enviados", $arquivoLog);