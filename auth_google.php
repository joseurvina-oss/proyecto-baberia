<?php
// auth_google.php - Inicia la autenticación OAuth 2.0 con Google Calendar

// 1. Leer las credenciales desde tu archivo credenciales.json
$jsonFile = 'credenciales.json';

if (!file_exists($jsonFile)) {
    die("Error: No se encuentra el archivo credenciales.json en la carpeta del proyecto.");
}

$credentialData = json_decode(file_get_contents($jsonFile), true);

// Extraer los datos según el formato estándar del JSON de Google Web Client
$clientId = $credentialData['web']['client_id'] ?? $credentialData['installed']['client_id'] ?? '';
$redirectUri = $credentialData['web']['redirect_uris'][0] ?? 'http://localhost/saas-barberia/oauth_callback.php';

if (empty($clientId)) {
    die("Error: No se pudo leer el Client ID del archivo JSON.");
}

// 2. Definir el alcance (Scope) para Google Calendar
$scope = 'https://www.googleapis.com/auth/calendar';

// 3. Armar la URL de autorización de Google
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'offline', // Necesario para obtener el refresh_token
    'prompt' => 'consent'       // Fuerza a mostrar la pantalla de permisos
]);

// 4. Redirigir automáticamente al usuario a Google
header('Location: ' . $authUrl);
exit;
?>