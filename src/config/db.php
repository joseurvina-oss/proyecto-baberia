<?php
// src/config/db.php

// Detectar credenciales de Railway o entorno local
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: '';
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306;

if (!empty($host)) {
    // Conexión para Railway (Nube)
    $conn = new mysqli($host, $user, $password, $dbname, (int)$port);
} else {
    // Conexión para entorno local (AppServ)
    $host_local = "localhost";
    $user_local = "root";
    $password_local = "12345678"; // Tu clave local si aplica
    $dbname_local = "saas_barberias";
    
    $conn = new mysqli($host_local, $user_local, $password_local, $dbname_local);
}

// Comprobar la conexión
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Error de conexión: " . $conn->connect_error]));
}

// Configurar caracteres
$conn->set_charset("utf8mb4");
?>