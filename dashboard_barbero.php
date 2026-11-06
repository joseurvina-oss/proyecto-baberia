<?php
// dashboard_barbero.php
session_start();
require_once 'db.php';

if (!isset($_SESSION['barber_id'])) {
    header("Location: login_barbero.php");
    exit;
}

$barber_id = $_SESSION['barber_id'];

$stmtCheckSub = $conn->prepare("SELECT subscription_status, plan_expires_at FROM barbers WHERE id = ?");
$stmtCheckSub->bind_param("i", $barber_id);
$stmtCheckSub->execute();
$subData = $stmtCheckSub->get_result()->fetch_assoc();
$stmtCheckSub->close();

$ahora = date('Y-m-d H:i:s');
$estadoSub = $subData['subscription_status'] ?? 'inactive';
$expiraSub = $subData['plan_expires_at'];

if ($estadoSub === 'inactive' || ($expiraSub && $expiraSub < $ahora)) {
    session_destroy();
    header("Location: login_barbero.php?error=expirado");
    exit;
}

$barberData = [
    'name' => $_SESSION['barber_name'] ?? 'Barbero',
    'phone' => '',
    'instagram_url' => '',
    'profile_photo' => '',
    'bio' => '',
    'google_access_token' => ''
];

$stmtBarber = $conn->prepare("SELECT * FROM barbers WHERE id = ?");
if ($stmtBarber) {
    $stmtBarber->bind_param("i", $barber_id);
    $stmtBarber->execute();
    $resBarber = $stmtBarber->get_result();
    if ($row = $resBarber->fetch_assoc()) {
        $barberData = array_merge($barberData, $row);
    }
    $stmtBarber->close();
}

$citasResult = null;
$stmtCitas = $conn->prepare("SELECT * FROM bookings WHERE staff_id = ? ORDER BY appointment_date DESC, appointment_time DESC");
if ($stmtCitas) {
    $stmtCitas->bind_param("i", $barber_id);
    $stmtCitas->execute();
    $citasResult = $stmtCitas->get_result();
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['PHP_SELF']);
$enlaceReservas = $protocol . "://" . $host . ($scriptPath == '/' ? '' : $scriptPath) . "/cliente_reserva.php?staff_id=" . $barber_id;
$isGoogleConnected = !empty($barberData['google_access_token']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Barbero - AgendaBarber</title>
    <!-- Logo / Favicon actualizado con tu ruta real -->
    <link rel="icon" type="image/png" href="assets/imagenes/logo_favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .dashboard-container { max-width: 900px; width: 100%; z-index: 2; margin: 30px auto; }
        .header-box { background: rgba(15, 15, 18, 0.88); backdrop-filter: blur(25px); padding: 25px 35px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.8); }
        .barber-info { display: flex; align-items: center; gap: 15px; }
        .barber-avatar { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #e11d48; }
        .avatar-placeholder-small { width: 60px; height: 60px; border-radius: 50%; background: #e11d48; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; border: 2px solid #be123c; }
        
        .citas-card { background: rgba(15, 15, 18, 0.88); backdrop-filter: blur(25px); padding: 30px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 20px 40px rgba(0,0,0,0.8); margin-bottom: 25px; }
        .link-box-admin { background: rgba(225, 29, 72, 0.08); border: 1px dashed rgba(225, 29, 72, 0.3); padding: 15px; border-radius: 14px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.08); font-size: 14px; }
        th { color: #e11d48; font-weight: 600; background: rgba(225, 29, 72, 0.05); }
        td { color: #e4e4e7; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }
        .badge-status { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .btn-action { padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-edit { background: rgba(225, 29, 72, 0.15); color: #f43f5e; border: 1px solid rgba(225, 29, 72, 0.3); }
        .btn-edit:hover { background: #e11d48; color: #fff; }
        .btn-logout { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
        .btn-logout:hover { background: #ef4444; color: #fff; }
        .btn-copy { background: #e11d48; color: #fff; border: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-copy:hover { background: #be123c; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Cabecera -->
        <div class="header-box">
            <div class="barber-info">
                <?php if (!empty($barberData['profile_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($barberData['profile_photo']); ?>" alt="Foto" class="barber-avatar">
                <?php else: ?>
                    <div class="avatar-placeholder-small">
                        <?php echo strtoupper(substr($barberData['name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h3 style="color: #fff; font-size: 18px; margin-bottom: 2px;">Hola, <?php echo htmlspecialchars($barberData['name']); ?></h3>
                    <p style="font-size: 12px; color: #a1a1aa;"><i class="fa-solid fa-scissors" style="color: #e11d48;"></i> Panel Profesional - AgendaBarber</p>
                </div>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="editar_perfil.php" class="btn-action btn-edit">
                    <i class="fa-solid fa-user-pen"></i> Editar Perfil, Precios y Horarios
                </a>
                <a href="logout.php" class="btn-action btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Salir
                </a>
            </div>
        </div>

        <!-- Enlace Instagram -->
        <div class="citas-card" style="padding: 20px 30px;">
            <h3 style="font-size: 16px; color: #fff; margin-bottom: 8px;"><i class="fa-solid fa-link" style="color: #e11d48;"></i> Tu Enlace Personalizado para Instagram</h3>
            <p style="font-size: 13px; color: #a1a1aa; margin-bottom: 15px;">Copia este enlace y pégalo en la biografía de tu Instagram.</p>
            
            <div class="link-box-admin">
                <code id="profileLink" style="color: #fff; font-size: 13px; word-break: break-all;"><?php echo $enlaceReservas; ?></code>
                <button type="button" class="btn-copy" onclick="copiarEnlace()">
                    <i class="fa-regular fa-copy"></i> Copiar Enlace
                </button>
            </div>
        </div>

        <!-- Sincronización con Google Calendar -->
        <div class="citas-card" style="padding: 20px 30px;">
            <h3 style="font-size: 16px; color: #fff; margin-bottom: 8px;"><i class="fa-brands fa-google" style="color: #e11d48;"></i> Sincronización con Google Calendar</h3>
            <p style="font-size: 13px; color: #a1a1aa; margin-bottom: 15px;">Conecta tu cuenta personal para que cada reserva de tus clientes se agende automáticamente en tu calendario.</p>
            
            <?php if ($isGoogleConnected): ?>
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;">
                    <i class="fa-solid fa-circle-check"></i> Google Calendar Conectado Exitosamente
                </div>
            <?php else: ?>
                <a href="auth_google.php" style="background: #4285F4; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; transition: all 0.2s;">
                    <i class="fa-brands fa-google"></i> Conectar Google Calendar
                </a>
            <?php endif; ?>
        </div>

        <!-- Listado de Citas -->
        <div class="citas-card">
            <h2 style="text-align: left; font-size: 20px; margin-bottom: 5px;"><i class="fa-solid fa-calendar-days" style="color: #e11d48;"></i> Tus Citas <span>Agendadas</span></h2>
            <p style="margin-bottom: 20px;">Historial y reservas asignadas exclusivamente a tu agenda</p>

            <?php if ($citasResult && $citasResult->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($cita = $citasResult->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cita['client_name']); ?></strong></td>
                                    <td>
                                        <?php 
                                            $phoneVal = $cita['client_phone'];
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneVal);
                                        ?>
                                        <a href="https://wa.me/<?php echo $cleanPhone; ?>" target="_blank" style="color: #22c55e; text-decoration: none; font-weight: 600;">
                                            <i class="fa-brands fa-whatsapp"></i> <?php echo htmlspecialchars($phoneVal); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($cita['service']); ?></td>
                                    <td><?php echo htmlspecialchars($cita['appointment_date']); ?></td>
                                    <td><span style="font-family: monospace; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px;"><?php echo htmlspecialchars($cita['appointment_time']); ?></span></td>
                                    <td><span class="badge-status"><?php echo htmlspecialchars($cita['status'] ?? 'Confirmada'); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #71717a;">
                    <i class="fa-regular fa-calendar-xmark" style="font-size: 40px; margin-bottom: 10px; color: #3f3f46;"></i>
                    <p>No tienes citas agendadas por el momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<script>
    function copiarEnlace() {
        const enlace = document.getElementById('profileLink').innerText;
        navigator.clipboard.writeText(enlace).then(() => {
            Swal.fire({
                title: '¡Enlace copiado!',
                text: 'Ya puedes pegarlo en tu biografía de Instagram.',
                icon: 'success',
                background: '#18181b',
                color: '#fff',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
</script>

<?php if (isset($_GET['google']) && $_GET['google'] === 'success'): ?>
<script>
    Swal.fire({
        title: '¡Google Calendar Conectado!',
        text: 'Tu cuenta ha sido vinculada con éxito.',
        icon: 'success',
        background: '#18181b',
        color: '#fff',
        confirmButtonColor: '#e11d48'
    });
</script>
<?php endif; ?>

</body>
</html>
<?php 
if (isset($stmtCitas)) $stmtCitas->close();
$conn->close(); 
?>