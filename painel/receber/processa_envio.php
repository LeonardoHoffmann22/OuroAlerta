<?php
$arquivoProgresso = __DIR__ . "/progresso.json";

if (file_exists($arquivoProgresso)) {
    header("Content-Type: application/json");
    echo file_get_contents($arquivoProgresso);
} else {
    echo json_encode([
        "total" => 0,
        "enviados" => 0,
        "porcentagem" => 0,
        "concluido" => false
    ]);
}
