<?php
require_once __DIR__ . '/vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/credenciales.json');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setRedirectUri('http://localhost/saas-barberia/generar_token.php');
$client->setAccessType('offline');
$client->setPrompt('consent select_account'); // Esto obliga a Google a mostrarte el selector de cuentas

if (!isset($_GET['code'])) {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit();
} else {
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    file_put_contents(__DIR__ . '/token.json', json_encode($client->getAccessToken()));
    echo "¡Token generado y guardado exitosamente como token.json! Ya puedes cerrar esta pestaña y probar hacer una reserva.";
}
?>