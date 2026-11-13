<?php
// views/administracion/admin_barberos.php
require_once __DIR__ . '/../../src/config/db.php';

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $barber_id = intval($_POST['barber_id']);
    $action = $_POST['action'];

    if ($action === 'dar_30_dias') {
        $nueva_fecha = date('Y-m-d H:i:s', strtotime('+30 days'));
        $stmt = $conn->prepare("UPDATE barbers SET subscription_status = 'active', plan_expires_at = ? WHERE id = ?");
        $stmt->bind_param("si", $nueva_fecha, $barber_id);
        
        if ($stmt->execute()) {
            $mensaje = "¡Se han otorgado 30 días de acceso con éxito!";
            $tipoMensaje = "success";
        } else {
            $mensaje = "Error al actualizar la suscripción.";
            $tipoMensaje = "error";
        }
        $stmt->close();
    } elseif ($action === 'desactivar') {
        $stmt = $conn->prepare("UPDATE barbers SET subscription_status = 'inactive' WHERE id = ?");
        $stmt->bind_param("i", $barber_id);
        
        if ($stmt->execute()) {
            $mensaje = "Cuenta desactivada correctamente.";
            $tipoMensaje = "success";
        } else {
            $mensaje = "Error al desactivar la cuenta.";
            $tipoMensaje = "error";
        }
        $stmt->close();
    }
}

$result = $conn->query("SELECT id, name, email, phone, subscription_status, plan_expires_at FROM barbers ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Control de Suscripciones</title>
    <link rel="icon" type="image/png" href="/public/assets/imagenes/logo_favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #09090b; color: #f4f4f5; font-family: sans-serif; padding: 20px; }
        .admin-container { max-width: 1050px; margin: 30px auto; background: #18181b; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); border: 1px solid #27272a; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #27272a; font-size: 14px; }
        th { background: #27272a; color: #fff; font-weight: 600; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-active { background: #065f46; color: #34d399; }
        .badge-trial { background: #1e3a8a; color: #60a5fa; }
        .badge-inactive { background: #7f1d1d; color: #f87171; }
        .btn-action { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; margin-right: 5px; transition: background 0.2s; }
        .btn-success { background: #059669; color: white; }
        .btn-success:hover { background: #047857; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; }
    </style>
</head>
<body>

    <div class="admin-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0; font-size: 22px;"><i class="fa-solid fa-users-gear" style="color: #e11d48;"></i> Panel de Control - Suscripciones</h2>
                <p style="margin: 5px 0 0 0; color: #a1a1aa; font-size: 13px;">Gestiona manualmente el acceso y los meses de prueba de los barberos en tu plataforma.</p>
            </div>
            <a href="/public/login" style="color: #a1a1aa; text-decoration: none; font-size: 13px;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Ir al Login</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre / Alias</th>
                    <th>Contacto</th>
                    <th>Estado</th>
                    <th>Expira el</th>
                    <th>Acciones Manuales</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($row['email']); ?><br>
                        <small style="color: #a1a1aa;"><i class="fa-brands fa-whatsapp"></i> <?php echo htmlspecialchars($row['phone']); ?></small>
                    </td>
                    <td>
                        <?php 
                            $status = $row['subscription_status'] ?? 'inactive';
                            $badgeClass = 'badge-inactive';
                            if ($status === 'active') $badgeClass = 'badge-active';
                            if ($status === 'trial') $badgeClass = 'badge-trial';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span>
                    </td>
                    <td><?php echo $row['plan_expires_at'] ? $row['plan_expires_at'] : 'Sin acceso'; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="barber_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="dar_30_dias">
                            <button type="submit" class="btn-action btn-success" title="Activar y dar 30 días gratis a partir de hoy">
                                <i class="fa-solid fa-calendar-plus"></i> Dar 30 Días
                            </button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="barber_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="desactivar">
                            <button type="submit" class="btn-action btn-danger" title="Inactivar la cuenta">
                                <i class="fa-solid fa-ban"></i> Desactivar
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($mensaje)): ?>
    <script>
        Swal.fire({
            title: '<?php echo $mensaje; ?>',
            icon: '<?php echo $tipoMensaje; ?>',
            background: '#18181b',
            color: '#fff',
            confirmButtonColor: '#e11d48'
        }).then(() => {
            window.location.href = '/public/admin/barberos';
        });
    </script>
    <?php endif; ?>

</body>
</html>