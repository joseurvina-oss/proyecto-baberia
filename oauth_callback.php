<?php
// oauth_callback.php - Procesa el código de Google y guarda los tokens en la BD
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php';
require_once 'vendor/autoload.php';

// Verificar que el barbero esté logueado
if (!isset($_SESSION['barber_id'])) {
    header("Location: login_barbero.php");
    exit();
}

$client = new Google_Client();
$client->setAuthConfig('credenciales.json');
$client->setRedirectUri('http://localhost/saas-barberia/oauth_callback.php');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $barber_id = $_SESSION['barber_id'];
        $tokenJson = json_encode($token);
        
        // Extraer refresh token si viene incluido (Google solo lo manda la primera vez o al forzar consentimiento)
        $refreshToken = isset($token['refresh_token']) ? $token['refresh_token'] : null;

        $sql = "UPDATE barbers SET google_access_token = ?";
        if ($refreshToken) {
            $sql .= ", google_refresh_token = ?";
        }
        $sql .= " WHERE id = ?";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            die("Error en prepare(): " . $conn->error);
        }

        if ($refreshToken) {
            $stmt->bind_param("ssi", $tokenJson, $refreshToken, $barber_id);
        } else {
            $stmt->bind_param("si", $tokenJson, $barber_id);
        }
        
        if ($stmt->execute()) {
            // Redirige al panel del barbero con un aviso de éxito opcional
            header("Location: dashboard_barbero.php?google=success");
            exit();
        } else {
            echo "Error al guardar el token en la base de datos: " . $stmt->error;
        }
    } else {
        echo "Error en la autenticación con Google: " . htmlspecialchars($token['error_description'] ?? $token['error']);
    }
} else {
    echo "Código de autorización no encontrado.";
}
?>