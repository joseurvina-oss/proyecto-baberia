<?php
// Detectar si estamos en Railway verificando las variables de entorno
if (getenv('MYSQLHOST')) {
    // Credenciales de la nube (Railway)
    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
    $dbname = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT'); 
    
    $conn = new mysqli($host, $user, $password, $dbname, $port);
} else {
    // Credenciales locales (AppServ)
    $host = "localhost";
    $user = "root";
    $password = "12345678"; // Tu clave de AppServ (déjala vacía si no configuraste una)
    $dbname = "saas_barberias"; // Nombre correcto de tu base de datos local
    
    $conn = new mysqli($host, $user, $password, $dbname);
}

// Comprobar la conexión
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Error de conexión: " . $conn->connect_error]));
}

// Configurar caracteres para evitar problemas con acentos o eñes
$conn->set_charset("utf8");
?>