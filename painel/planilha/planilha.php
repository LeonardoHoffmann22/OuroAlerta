<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pasta = __DIR__ . "/../ArmazenaP/";

    if (!isset($_FILES['arquivo_planilha']) || !isset($_FILES['arquivo_txt'])) {
        $_SESSION['mensagem'] ="É obrigatório enviar a planilha (.xlsx) e o modelo TXT juntos.<br>";
        header('location: index.php');
        exit;
    }

    $destinoPlanilha = $pasta . "sobras.xlsx";
    if (file_exists($destinoPlanilha)) {
        unlink($destinoPlanilha);
    }
    $okPlanilha = move_uploaded_file($_FILES['arquivo_planilha']['tmp_name'], $destinoPlanilha);

    $destinoTxt = $pasta . "emai-cap.txt";
    if (file_exists($destinoTxt)) {
        unlink($destinoTxt);
    }
    $okTxt = move_uploaded_file($_FILES['arquivo_txt']['tmp_name'], $destinoTxt);

    // Resultado final
    if ($okPlanilha && $okTxt) {
        $_SESSION['mensagem'] = "Planilha e Modelo TXT salvos com sucesso!<br>";
    } else {
        $_SESSION['mensagem'] ="Erro ao salvar os arquivos!<br>";
    }

        header('location: index.php');
        exit;
}
?>
