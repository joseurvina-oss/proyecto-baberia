<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
$date     = isset($_GET['date']) ? trim($_GET['date']) : '';

$bookedTimes = [];

if ($staff_id > 0 && !empty($date)) {
    $stmt = $conn->prepare("SELECT appointment_time FROM bookings WHERE staff_id = ? AND appointment_date = ?");
    $stmt->bind_param("is", $staff_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $bookedTimes[] = $row['appointment_time'];
    }
}

echo json_encode(['booked_times' => $bookedTimes]);
exit();
?>