<?php
header('Content-Type: application/json');
require_once 'db.php';

// Obtener los datos JSON enviados por fetch desde el cliente
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Datos de solicitud inválidos.']);
    exit;
}

// Sanitizar y capturar variables
$tenant_id = isset($data['tenant_id']) ? intval($data['tenant_id']) : 1;
$staff_id = isset($data['staff_id']) ? intval($data['staff_id']) : 0;
$clientName = trim($data['clientName'] ?? '');
$clientPhone = trim($data['clientPhone'] ?? '');
$service = trim($data['service'] ?? '');
$date = trim($data['date'] ?? '');
$time = trim($data['time'] ?? '');

// 1. Validar que ningún campo obligatorio esté vacío
if (empty($staff_id) || empty($clientName) || empty($clientPhone) || empty($service) || empty($date) || empty($time)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos del formulario son obligatorios.']);
    exit;
}

// 2. Validar que la fecha y hora seleccionada no se encuentren en el pasado
$appointmentDateTime = strtotime("$date $time");
$currentDateTime = time();

if ($appointmentDateTime < $currentDateTime) {
    echo json_encode(['status' => 'error', 'message' => 'No puedes agendar una cita en una fecha u hora que ya pasó.']);
    exit;
}

// 3. Validar estrictamente en la Base de Datos si el horario ya fue ocupado (Evita doble reserva)
$stmtCheck = $conn->prepare("SELECT id FROM bookings WHERE staff_id = ? AND appointment_date = ? AND appointment_time = ?");
$stmtCheck->bind_param("iss", $staff_id, $date, $time);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Lo sentimos, este horario acaba de ser ocupado por otro cliente. Por favor selecciona otro.']);
    $stmtCheck->close();
    exit;
}
$stmtCheck->close();

// 4. Insertar la cita de forma segura usando Prepared Statements
$stmtInsert = $conn->prepare("INSERT INTO bookings (tenant_id, staff_id, client_name, client_phone, service, appointment_date, appointment_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmtInsert->bind_param("iisssss", $tenant_id, $staff_id, $clientName, $clientPhone, $service, $date, $time);

if ($stmtInsert->execute()) {
    // Respuesta limpia en formato JSON para que SweetAlert2 la procese con éxito
    echo json_encode([
        'status' => 'success', 
        'message' => 'Cita registrada correctamente en el sistema.'
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error al guardar la cita en la base de datos: ' . $conn->error
    ]);
}

$stmtInsert->close();
$conn->close();
?>