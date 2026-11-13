<?php
// views/barbero/editar_perfil.php
require_once __DIR__ . '/../../src/config/db.php';

if (!isset($_SESSION['barber_id'])) {
    header("Location: /public/login");
    exit;
}

$barber_id = $_SESSION['barber_id'];
$mensaje = "";
$tipoMensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Actualizar perfil personal y foto
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        
        $stmtFoto = $conn->prepare("SELECT profile_photo FROM barbers WHERE id = ?");
        $stmtFoto->bind_param("i", $barber_id);
        $stmtFoto->execute();
        $profile_photo = $stmtFoto->get_result()->fetch_assoc()['profile_photo'];
        $stmtFoto->close();

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array(strtolower($ext), $allowed)) {
                $dir = __DIR__ . '/../../public/uploads/';

                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                $nombre_archivo_limpio = 'barber_' . $barber_id . '_' . time() . '.' . $ext;
                $rutaFisica = $dir . $nombre_archivo_limpio;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $rutaFisica)) {
                    $profile_photo = 'public/uploads/' . $nombre_archivo_limpio;
                }
            }
        }

        $updateStmt = $conn->prepare("UPDATE barbers SET name = ?, phone = ?, profile_photo = ? WHERE id = ?");
        $updateStmt->bind_param("sssi", $name, $phone, $profile_photo, $barber_id);
        if ($updateStmt->execute()) {
            $_SESSION['barber_name'] = $name;
            $mensaje = "¡Perfil actualizado con éxito!";
            $tipoMensaje = "success";
        }
        $updateStmt->close();
    }

    // 2. Añadir servicio (Directo a la tabla barber_services)
    if (isset($_POST['add_service'])) {
        $service_name = trim($_POST['service_name']);
        $price = intval($_POST['price']);

        if (!empty($service_name) && $price > 0) {
            $stmtServ = $conn->prepare("INSERT INTO barber_services (staff_id, service_name, price) VALUES (?, ?, ?)");
            $stmtServ->bind_param("isi", $barber_id, $service_name, $price);
            if ($stmtServ->execute()) {
                $mensaje = "¡Servicio añadido con éxito!";
                $tipoMensaje = "success";
            }
            $stmtServ->close();
        }
    }

    // 3. Eliminar servicio
    if (isset($_POST['delete_service_id'])) {
        $serv_id = intval($_POST['delete_service_id']);
        $stmtDel = $conn->prepare("DELETE FROM barber_services WHERE id = ? AND staff_id = ?");
        $stmtDel->bind_param("ii", $serv_id, $barber_id);
        $stmtDel->execute();
        $stmtDel->close();
        $mensaje = "Servicio eliminado.";
        $tipoMensaje = "success";
    }

    // 4. Añadir horario disponible
    if (isset($_POST['add_hour'])) {
        $available_time = trim($_POST['available_time']);
        if (!empty($available_time)) {
            $stmtH = $conn->prepare("INSERT INTO barber_hours (staff_id, available_time) VALUES (?, ?)");
            $stmtH->bind_param("is", $barber_id, $available_time);
            $stmtH->execute();
            $stmtH->close();
            $mensaje = "¡Horario añadido!";
            $tipoMensaje = "success";
        }
    }

    // 5. Eliminar horario
    if (isset($_POST['delete_hour_id'])) {
        $hour_id = intval($_POST['delete_hour_id']);
        $stmtDelH = $conn->prepare("DELETE FROM barber_hours WHERE id = ? AND staff_id = ?");
        $stmtDelH->bind_param("ii", $hour_id, $barber_id);
        $stmtDelH->execute();
        $stmtDelH->close();
        $mensaje = "Horario eliminado.";
        $tipoMensaje = "success";
    }
}

// Consultas para la vista
$stmt = $conn->prepare("SELECT name, phone, profile_photo FROM barbers WHERE id = ?");
$stmt->bind_param("i", $barber_id);
$stmt->execute();
$barber = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmtServicios = $conn->prepare("SELECT * FROM barber_services WHERE staff_id = ?");
$stmtServicios->bind_param("i", $barber_id);
$stmtServicios->execute();
$serviciosList = $stmtServicios->get_result();
$stmtServicios->close();

$stmtHorarios = $conn->prepare("SELECT * FROM barber_hours WHERE staff_id = ? ORDER BY available_time ASC");
$stmtHorarios->bind_param("i", $barber_id);
$stmtHorarios->execute();
$horariosList = $stmtHorarios->get_result();
$stmtHorarios->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurar Perfil, Precios y Horarios - AgendaBarber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .edit-container { max-width: 650px; margin: 30px auto; z-index: 2; position: relative; }
        .card-box { background: rgba(15, 15, 18, 0.88); backdrop-filter: blur(25px); padding: 30px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 20px 40px rgba(0,0,0,0.8); margin-bottom: 25px; }
        .item-row { display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); padding: 10px 16px; border-radius: 10px; margin-bottom: 8px; font-size: 14px; }
        .btn-delete { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); padding: 5px 10px; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-size: 12px; }
        .btn-delete:hover { background: #ef4444; color: #fff; }
    </style>
</head>
<body>

<div class="edit-container">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
        <h2 style="font-size: 22px; margin: 0;"><i class="fa-solid fa-sliders" style="color: #e11d48;"></i> Configurar <span>Perfil, Precios y Horarios</span></h2>
        <a href="/public/dashboard" style="color: #a1a1aa; text-decoration: none; font-size: 13px;"><i class="fa-solid fa-arrow-left"></i> Volver al Panel</a>
    </div>

    <!-- 1. Datos Personales -->
    <div class="card-box">
        <h3 style="font-size: 16px; color: #fff; margin-bottom: 15px;"><i class="fa-solid fa-user-pen" style="color: #e11d48;"></i> Información Personal</h3>
        <form action="/public/editar_perfil" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_profile" value="1">
            
            <div class="form-group">
                <label for="name"><i class="fa-solid fa-user"></i> Tu Nombre:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($barber['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fa-brands fa-whatsapp"></i> WhatsApp de Contacto:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($barber['phone'] ?? ''); ?>" placeholder="Ej: 569927861737" required>
            </div>

            <div class="form-group">
                <label for="photo"><i class="fa-solid fa-camera"></i> Actualizar Foto de Perfil:</label>
                <input type="file" id="photo" name="photo" accept="image/*" style="padding: 8px;">
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 10px;">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios Personales
            </button>
        </form>
    </div>

    <!-- 2. Gestión de Servicios y Precios -->
    <div class="card-box">
        <h3 style="font-size: 16px; color: #fff; margin-bottom: 15px;"><i class="fa-solid fa-tags" style="color: #e11d48;"></i> Tus Servicios y Precios</h3>
        <div style="margin-bottom: 20px;">
            <?php if ($serviciosList && $serviciosList->num_rows > 0): ?>
                <?php while($serv = $serviciosList->fetch_assoc()): ?>
                    <div class="item-row">
                        <div>
                            <strong style="color: #fff;"><?php echo htmlspecialchars($serv['service_name']); ?></strong>
                            <span style="color: #22c55e; margin-left: 10px; font-weight: 600;">$<?php echo number_format($serv['price'], 0, ',', '.'); ?></span>
                        </div>
                        <form action="/saas-barberia/public/editar_perfil" method="POST" style="margin: 0;">
                            <input type="hidden" name="delete_service_id" value="<?php echo $serv['id']; ?>">
                            <button type="submit" class="btn-delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #71717a; font-size: 13px; text-align: center; padding: 10px;">No tienes servicios registrados.</p>
            <?php endif; ?>
        </div>

        <form action="/saas-barberia/public/editar_perfil" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="hidden" name="add_service" value="1">
            <div style="flex: 2; min-width: 150px;" class="form-group"><input type="text" name="service_name" placeholder="Ej: Corte + Barba" required style="margin: 0;"></div>
            <div style="flex: 1; min-width: 100px;" class="form-group"><input type="number" name="price" placeholder="Precio ($)" required style="margin: 0;"></div>
            <button type="submit" class="btn-submit" style="flex: 1; min-width: 120px; margin: 0; height: 48px;"><i class="fa-solid fa-plus"></i> Añadir</button>
        </form>
    </div>

    <!-- 3. Gestión de Horarios de Atención -->
    <div class="card-box">
        <h3 style="font-size: 16px; color: #fff; margin-bottom: 15px;"><i class="fa-solid fa-clock" style="color: #e11d48;"></i> Tus Horarios Disponibles</h3>
        <p style="font-size: 13px; color: #a1a1aa; margin-bottom: 15px;">Define los bloques de horas en los que atiendes para que tus clientes los elijan.</p>
        
        <div style="margin-bottom: 20px;">
            <?php if ($horariosList && $horariosList->num_rows > 0): ?>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php while($hor = $horariosList->fetch_assoc()): ?>
                        <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 8px 14px; border-radius: 8px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
                            <span><i class="fa-regular fa-clock" style="color: #e11d48;"></i> <?php echo htmlspecialchars($hor['available_time']); ?></span>
                            <form action="/saas-barberia/public/editar_perfil" method="POST" style="margin: 0;">
                                <input type="hidden" name="delete_hour_id" value="<?php echo $hor['id']; ?>">
                                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 12px;"><i class="fa-solid xmark fa-x"></i></button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="color: #71717a; font-size: 13px; text-align: center; padding: 10px;">No has configurado horarios aún.</p>
            <?php endif; ?>
        </div>

        <form action="/public/editar_perfil" method="POST" style="display: flex; gap: 10px;">
            <input type="hidden" name="add_hour" value="1">
            <div style="flex: 2;" class="form-group"><input type="time" name="available_time" required style="margin: 0;"></div>
            <button type="submit" class="btn-submit" style="flex: 1; margin: 0; height: 48px;"><i class="fa-solid fa-plus"></i> Agregar Hora</button>
        </form>
    </div>

</div>

<?php if (!empty($mensaje)): ?>
<script>
    Swal.fire({
        title: '<?php echo $mensaje; ?>',
        icon: '<?php echo $tipoMensaje; ?>',
        background: '#18181b',
        color: '#fff',
        timer: 2000,
        showConfirmButton: false
    });
</script>
<?php endif; ?>

</body>
</html>