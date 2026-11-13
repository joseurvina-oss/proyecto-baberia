<?php
// views/autenticacion/registro.php
// IMPORTANTE: Ya no necesitamos session_start() aquí porque public/index.php ya lo inicia.

// 1. RUTA DE BASE DE DATOS ACTUALIZADA
require_once __DIR__ . '/../../src/config/db.php';

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);

    $stmtCheck = $conn->prepare("SELECT id FROM barbers WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $mensaje = "El correo electrónico ya está registrado.";
        $tipoMensaje = "error";
        $stmtCheck->close();
    } else {
        $stmtCheck->close();
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $subscription_status = 'inactive'; 
        $plan_expires_at = NULL;

        $stmtIns = $conn->prepare("INSERT INTO barbers (name, email, password, phone, subscription_status, plan_expires_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtIns->bind_param("ssssss", $name, $email, $password_hash, $phone, $subscription_status, $plan_expires_at);
        
        if ($stmtIns->execute()) {
            $mensaje = "¡Registro exitoso! Tu cuenta está pendiente de activación por el administrador.";
            $tipoMensaje = "success";
        } else {
            $mensaje = "Error al registrar la cuenta.";
            $tipoMensaje = "error";
        }
        $stmtIns->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Barbero - AgendaBarber</title>
    <!-- 2. RUTAS DE ASSETS ACTUALIZADAS -->
    <link rel="icon" type="image/png" href="/saas-barberia/public/assets/imagenes/logo_favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/saas-barberia/public/assets/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="booking-card" style="max-width: 420px; margin: 6vh auto;">
        <div style="text-align: center; margin-bottom: 20px;">
        <div style="margin-bottom: 12px;">
        <!-- 3. RUTA DEL LOGO ACTUALIZADA -->
        <img src="/saas-barberia/public/assets/imagenes/logo.png" alt="Logo" style="width: 75px; height: 75px; object-fit: cover; border-radius: 50%; border: 2px solid #e11d48; box-shadow: 0 4px 15px rgba(225,29,72,0.4);">
    </div>
            <h2 style="font-size: 24px; margin-bottom: 5px;">Crear Cuenta de <span>Barbero</span></h2>
            <p style="font-size: 13px; color: #a1a1aa;">Únete a AgendaBarber y obtén tu propio sistema de reservas.</p>
        </div>

        <!-- 4. ACTION DEL FORMULARIO ACTUALIZADO AL ROUTER -->
        <form action="/saas-barberia/public/registro" method="POST">
            <div class="form-group">
                <label for="name"><i class="fa-solid fa-user"></i> Tu Nombre o Alias:</label>
                <input type="text" id="name" name="name" placeholder="Ej: Cristofer Barber" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Correo Electrónico:</label>
                <input type="email" id="email" name="email" placeholder="correo@barber.com" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fa-brands fa-whatsapp"></i> WhatsApp de Contacto:</label>
                <input type="text" id="phone" name="phone" placeholder="Ej: 569927861737" required>
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 10px;">
                <i class="fa-solid fa-user-plus"></i> Registrarse
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <!-- 5. ENLACE DE LOGIN ACTUALIZADO -->
            <a href="/saas-barberia/public/login" style="color: #a1a1aa; text-decoration: none; font-size: 13px;">
                ¿Ya tienes una cuenta? <span style="color: #e11d48; font-weight: 600;">Inicia Sesión</span>
            </a>
        </div>
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
            <?php if ($tipoMensaje === 'success'): ?>
            // 6. REDIRECCIÓN JAVASCRIPT ACTUALIZADA AL ROUTER
            window.location.href = '/saas-barberia/public/login';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>

</body>
</html>