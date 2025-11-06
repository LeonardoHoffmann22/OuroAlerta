<?php
$host = $_SERVER['HTTP_HOST']; 
$user = 'root'; 
$pass = '';
$db   = 'OuroAlerta';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}
?>