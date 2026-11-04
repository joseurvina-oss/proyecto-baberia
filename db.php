<?php
$host = "localhost";
$user = "root";
$password = "12345678";
$database = "saas_barberias";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Error de conexión: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");
?>