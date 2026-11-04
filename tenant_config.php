<?php
require_once 'db.php';

// Obtener el slug de la barbería desde la URL (ej: index.php?negocio=barberia-style)
// Si no se envía nada, por defecto usaremos el ID 1 (Barbería Style)
$slug = isset($_GET['negocio']) ? $conn->real_escape_string($_GET['negocio']) : 'barberia-style';

$sql = "SELECT * FROM tenants WHERE slug = '$slug' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $tenant = $result->fetch_assoc();
} else {
    // Si no existe, cargamos por defecto el primer tenant para evitar errores
    $sql_default = "SELECT * FROM tenants WHERE id = 1 LIMIT 1";
    $tenant = $conn->query($sql_default)->fetch_assoc();
}

$tenant_id = $tenant['id'];
$tenant_name = $tenant['name'];
$tenant_whatsapp = $tenant['whatsapp_phone'];

// Obtener el personal (staff) correspondiente SOLO a este negocio
$sql_staff = "SELECT * FROM staff WHERE tenant_id = $tenant_id";
$staff_result = $conn->query($sql_staff);

// Obtener los servicios correspondientes SOLO a este negocio
$sql_services = "SELECT * FROM services WHERE tenant_id = $tenant_id";
$services_result = $conn->query($sql_services);
?>