<?php
// src/controllers/oauth_callback.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['barber_id'])) {
    header("Location: /public/login");
    exit;
}

$client = new Google\Client();
$credentialsPath = __DIR__ . '/../config/credenciales.json';

if (!empty(getenv('GOOGLE_CREDENTIALS_JSON'))) {
    $client->setAuthConfig(json_decode(getenv('GOOGLE_CREDENTIALS_JSON'), true));
} elseif (file_exists($credentialsPath)) {
    $client->setAuthConfig($credentialsPath);
} else {
    die("Error: Credenciales de Google no configuradas.");
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : "http";
$redirectUri = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/public/oauth_callback";
$client->setRedirectUri($redirectUri);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $accessToken = json_encode($token);
        $refreshToken = $token['refresh_token'] ?? '';
        $barberId = $_SESSION['barber_id'];

        $stmt = $conn->prepare("UPDATE barbers SET google_access_token = ?, google_refresh_token = IF(? != '', ?, google_refresh_token) WHERE id = ?");
        $stmt->bind_param("sssi", $accessToken, $refreshToken, $refreshToken, $barberId);
        $stmt->execute();
        $stmt->close();

        header("Location: /public/dashboard?google=success");
        exit;
    }
}

header("Location: /public/dashboard?google=error");
exit;