<?php
// check_subscription.php
require_once 'db.php';

if (isset($_SESSION['barber_id'])) {
    $barber_id = $_SESSION['barber_id'];
    
    $stmt = $conn->prepare("SELECT subscription_status, plan_expires_at FROM barbers WHERE id = ?");
    $stmt->bind_param("i", $barber_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $hoy = date('Y-m-d');
    
    // Si la suscripción está inactiva o la fecha de expiración ya pasó
    if (!$result || $result['subscription_status'] !== 'active' || $result['plan_expires_at'] < $hoy) {
        // Redirigir a una pasarela de pago o aviso de suscripción vencida
        header("Location: renovar_suscripcion.php");
        exit;
    }
}