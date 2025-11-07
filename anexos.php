<?php
$host = "192.168.0.59";//Mudar para o ip da maquina que esta em, uso
$user = 'root'; 
$pass = '';
$db   = 'OuroAlerta';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}
?>