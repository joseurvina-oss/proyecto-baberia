<?php
// src/controllers/api_get_booked_times.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';

$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
$date     = isset($_GET['date']) ? trim($_GET['date']) : '';

$bookedTimes = [];

// Validación de seguridad extra: Confirmar que la fecha tenga formato YYYY-MM-DD
if ($staff_id > 0 && !empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    
    $stmt = $conn->prepare("SELECT appointment_time FROM bookings WHERE staff_id = ? AND appointment_date = ? AND status = 'confirmed'");
    $stmt->bind_param("is", $staff_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Podrías formatear la hora aquí si lo necesitas (ej. quitar los segundos)
        // $hora_limpia = date('H:i', strtotime($row['appointment_time']));
        $bookedTimes[] = $row['appointment_time'];
    }
    
    $stmt->close();
}

// Cerramos conexión
$conn->close();

echo json_encode(['booked_times' => $bookedTimes]);
exit();
?>