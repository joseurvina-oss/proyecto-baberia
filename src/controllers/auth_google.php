<?php
// src/controllers/auth_google.php - Inicia la autenticación OAuth 2.0 con Google Calendar
session_start();
require_once __DIR__ . '/../config/db.php';

// Verificar que el barbero haya iniciado sesión
if (!isset($_SESSION['barber_id'])) {
    // Redirigimos a la ruta del router público, no al archivo físico
    header("Location: /saas-barberia/public/login");
    exit();
}

// Apuntar correctamente a la carpeta config
$jsonFile = __DIR__ . '/../config/credenciales.json';

if (!file_exists($jsonFile)) {
    die("Error: No se encuentra el archivo credenciales.json en la ruta: " . $jsonFile);
}

$credentialData = json_decode(file_get_contents($jsonFile), true);

$clientId = $credentialData['web']['client_id'] ?? $credentialData['installed']['client_id'] ?? '';

if (empty($clientId)) {
    die("Error: No se pudo leer el client ID del archivo JSON.");
}

// URI de redirección fija y exacta apuntando al archivo en public/
$redirectUri = 'http://localhost/saas-barberia/public/oauth_callback.php';

$scope = 'https://www.googleapis.com/auth/calendar';

// Armar la URL de autorización de Google
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'offline', // Necesario para obtener el refresh_token
    'prompt' => 'consent'       // Fuerza a mostrar la pantalla de permisos
]);

header('Location: ' . $authUrl);
exit();