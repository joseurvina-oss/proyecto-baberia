<?php
// src/controllers/auth_google.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';

if (!isset($_SESSION['barber_id'])) {
    header("Location: /public/login");
    exit;
}

$client = new Google\Client();
$client->setApplicationName('AgendaBarber SaaS');
$client->setScopes(Google\Service\Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Cargar credenciales desde variable de entorno (Railway) o archivo local
$credentialsPath = __DIR__ . '/../config/credenciales.json';

if (!empty(getenv('GOOGLE_CREDENTIALS_JSON'))) {
    $credentialsConfig = json_decode(getenv('GOOGLE_CREDENTIALS_JSON'), true);
    $client->setAuthConfig($credentialsConfig);
} elseif (file_exists($credentialsPath)) {
    $client->setAuthConfig($credentialsPath);
} else {
    die("Error: No se encontraron las credenciales de Google (ni variable GOOGLE_CREDENTIALS_JSON ni archivo local).");
}

// Construir dinámicamente la URL de callback (HTTPS en Railway)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$redirectUri = $protocol . "://" . $host . "/public/oauth_callback";

$client->setRedirectUri($redirectUri);

$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;