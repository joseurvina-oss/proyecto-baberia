<?php
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '12345678';
$database = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: 3306;

$conn = new mysqli($host, $user, $password, $database, (int)$port);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Error de conexión: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");
?>
