<?php
// src/controllers/api_booking.php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

$tenant_id = intval($data['tenant_id'] ?? 1);
$staff_id = intval($data['staff_id'] ?? 0);
$client_name = htmlspecialchars(trim($data['clientName'] ?? ''), ENT_QUOTES, 'UTF-8');
$client_phone = htmlspecialchars(trim($data['clientPhone'] ?? ''), ENT_QUOTES, 'UTF-8');
$service = htmlspecialchars(trim($data['service'] ?? ''), ENT_QUOTES, 'UTF-8');
$appointment_date = trim($data['date'] ?? '');
$appointment_time = trim($data['time'] ?? '');

if ($staff_id <= 0 || empty($client_name) || empty($appointment_date) || empty($appointment_time)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
    exit;
}

// INSERT limpio sin service_details
$stmt = $conn->prepare("INSERT INTO bookings (tenant_id, staff_id, client_name, client_phone, service, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')");
$stmt->bind_param("iisssss", $tenant_id, $staff_id, $client_name, $client_phone, $service, $appointment_date, $appointment_time);

if ($stmt->execute()) {
    $stmt->close();

    // Sincronización con Google Calendar
    $stmtBarber = $conn->prepare("SELECT google_access_token, google_refresh_token FROM barbers WHERE id = ?");
    $stmtBarber->bind_param("i", $staff_id);
    $stmtBarber->execute();
    $barberTokens = $stmtBarber->get_result()->fetch_assoc();
    $stmtBarber->close();

    if ($barberTokens && !empty($barberTokens['google_access_token'])) {
        try {
            $client = new Google_Client();
            $client->setAuthConfig(__DIR__ . '/../config/credenciales.json');
            $client->addScope(Google_Service_Calendar::CALENDAR);

            $accessToken = json_decode($barberTokens['google_access_token'], true);
            if (!$accessToken) {
                $accessToken = $barberTokens['google_access_token'];
            }
            
            $tokenArray = [
                'access_token' => is_array($accessToken) ? $accessToken['access_token'] : $barberTokens['google_access_token'],
                'refresh_token' => $barberTokens['google_refresh_token'] ?? '',
                'created' => time()
            ];

            $client->setAccessToken($tokenArray);

            if ($client->isAccessTokenExpired() && !empty($barberTokens['google_refresh_token'])) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            }

            $serviceCalendar = new Google_Service_Calendar($client);

            $event = new Google_Service_Calendar_Event([
                'summary' => 'Corte: ' . $service . ' - ' . $client_name,
                'description' => 'Cliente: ' . $client_name . ' | Teléfono: ' . $client_phone,
                'start' => [
                    'dateTime' => $appointment_date . 'T' . $appointment_time . ':00',
                    'timeZone' => 'America/Santiago',
                ],
                'end' => [
                    'dateTime' => $appointment_date . 'T' . date('H:i:s', strtotime($appointment_time . ' + 45 minutes')),
                    'timeZone' => 'America/Santiago',
                ],
            ]);

            $calendarId = 'primary';
            $serviceCalendar->events->insert($calendarId, $event);

        } catch (Exception $e) {
            error_log("Error sincronizando Google Calendar para barbero ID $staff_id: " . $e->getMessage());
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Cita reservada con éxito.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error SQL: ' . $stmt->error]);
}
$conn->close();