<?php
// api_booking.php
header('Content-Type: application/json');
require_once 'db.php';

// Cargar librerías de Google si usas Composer
require_once 'vendor/autoload.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit;
}

$tenant_id = intval($data['tenant_id'] ?? 1);
$staff_id = intval($data['staff_id'] ?? 0);
$client_name = trim($data['clientName'] ?? '');
$client_phone = trim($data['clientPhone'] ?? '');
$service = trim($data['service'] ?? '');
$appointment_date = trim($data['date'] ?? '');
$appointment_time = trim($data['time'] ?? '');

if ($staff_id <= 0 || empty($client_name) || empty($appointment_date) || empty($appointment_time)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
    exit;
}

// 1. Guardar la cita en la base de datos local (tabla bookings)
$stmt = $conn->prepare("INSERT INTO bookings (tenant_id, staff_id, client_name, client_phone, service, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')");
$stmt->bind_param("iisssss", $tenant_id, $staff_id, $client_name, $client_phone, $service, $appointment_date, $appointment_time);

if ($stmt->execute()) {
    $stmt->close();

    // 2. Intentar sincronizar con Google Calendar de ESTE barbero específico
    $stmtBarber = $conn->prepare("SELECT google_access_token, google_refresh_token FROM barbers WHERE id = ?");
    $stmtBarber->bind_param("i", $staff_id);
    $stmtBarber->execute();
    $barberTokens = $stmtBarber->get_result()->fetch_assoc();
    $stmtBarber->close();

    // Verificar si el barbero tiene tokens configurados
    if ($barberTokens && !empty($barberTokens['google_access_token'])) {
        try {
            $client = new Google_Client();
            // Asegúrate de apuntar a tu archivo de credenciales de Google Cloud
            $client->setAuthConfig('credenciales.json');
            $client->addScope(Google_Service_Calendar::CALENDAR);

            // Reconstruir las credenciales con los tokens de este barbero en particular
            $accessToken = json_decode($barberTokens['google_access_token'], true);
            if (!$accessToken) {
                // Si está guardado como texto plano o JSON directo
                $accessToken = $barberTokens['google_access_token'];
            }
            
            // Si hay refresh token guardado, lo agregamos
            $tokenArray = [
                'access_token' => is_array($accessToken) ? $accessToken['access_token'] : $barberTokens['google_access_token'],
                'refresh_token' => $barberTokens['google_refresh_token'] ?? '',
                'created' => time()
            ];

            $client->setAccessToken($tokenArray);

            // Si el token expiró y hay refresh token, renovarlo automáticamente
            if ($client->isAccessTokenExpired() && !empty($barberTokens['google_refresh_token'])) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            }

            $serviceCalendar = new Google_Service_Calendar($client);

            // Crear el evento en el Google Calendar del barbero
            $event = new Google_Service_Calendar_Event([
                'summary' => 'Corte: ' . $service . ' - ' . $client_name,
                'description' => 'Cliente: ' . $client_name . ' | Teléfono: ' . $client_phone,
                'start' => [
                    'dateTime' => $appointment_date . 'T' . $appointment_time . ':00',
                    'timeZone' => 'America/Santiago', // Ajusta tu zona horaria si es necesario
                ],
                'end' => [
                    'dateTime' => $appointment_date . 'T' . date('H:i:s', strtotime($appointment_time . ' + 45 minutes')),
                    'timeZone' => 'America/Santiago',
                ],
            ]);

            $calendarId = 'primary';
            $serviceCalendar->events->insert($calendarId, $event);

        } catch (Exception $e) {
            // Si falla Google Calendar por token vencido o mal configurado, la cita igual se guardó localmente en la BD
            error_log("Error sincronizando Google Calendar para barbero ID $staff_id: " . $e->getMessage());
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Cita reservada con éxito.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar la cita en la base de datos.']);
}
$conn->close();
