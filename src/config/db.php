<?php
// src/config/db.php

// Detectar si estamos en Railway verificando las variables de entorno
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: '';
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: '';
$password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: '';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306;

if (!empty($host)) {
    // Credenciales de la nube (Railway)
    $conn = new mysqli($host, $user, $password, $dbname, (int)$port);
} else {
    // Credenciales locales (AppServ)
    $host_local = "localhost";
    $user_local = "root";
    $password_local = "12345678"; // Tu clave de AppServ (cámbiala si configuraste otra)
    $dbname_local = "saas_barberias"; // Nombre de tu base de datos local
    
    $conn = new mysqli($host_local, $user_local, $password_local, $dbname_local);
}

// Comprobar la conexión
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Error de conexión: " . $conn->connect_error]));
}

// Configurar caracteres para evitar problemas con acentos o eñes
$conn->set_charset("utf8mb4");
?>