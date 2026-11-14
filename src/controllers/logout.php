<?php
// 1. Validar si la sesión ya está activa antes de iniciarla para evitar el Notice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Limpiar todas las variables de sesión
$_SESSION = array();

// 3. Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir la sesión por completo
session_destroy();

// 5. Redirigir al usuario al login o inicio (asegúrate de que no haya espacios antes de <?php)
header("Location: ../index.php"); // Cambia la ruta según la estructura de tu proyecto
exit;