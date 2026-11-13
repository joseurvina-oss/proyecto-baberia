<?php
// views/autenticacion/login_barbero.php
require_once __DIR__ . '/../../src/config/db.php';

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, name, password, subscription_status, plan_expires_at FROM barbers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            $ahora = date('Y-m-d H:i:s');
            $estado = $row['subscription_status'] ?? 'inactive';
            $expira = $row['plan_expires_at'];

            if ($estado === 'inactive' || ($expira && $expira < $ahora)) {
                $mensaje = "Tu cuenta está inactiva o tu periodo de prueba ha expirado. Contacta al administrador.";
                $tipoMensaje = "error";
            } else {
                $_SESSION['barber_id'] = $row['id'];
                $_SESSION['barber_name'] = $row['name'];
                
                // Redirección segura
                header("Location: /public/dashboard");
                exit;
            }
        } else {
            $mensaje = "Contraseña incorrecta.";
            $tipoMensaje = "error";
        }
    } else {
        $mensaje = "El correo no está registrado.";
        $tipoMensaje = "error";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión Barbero - AgendaBarber</title>
    
    <!-- Rutas absolutas exactas según tu árbol de carpetas -->
    <link rel="icon" type="image/png" href="/public/assets/imagenes/logo_favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- ¡Ruta del CSS corregida agregando /assets/! -->
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="booking-card" style="max-width: 400px; margin: 10vh auto;">
        <div style="text-align: center; margin-bottom: 25px;">
            <div style="margin-bottom: 12px;">
                <img src="/public/assets/imagenes/logo.png" alt="Logo" style="width: 75px; height: 75px; object-fit: cover; border-radius: 50%; border: 2px solid #e11d48; box-shadow: 0 4px 15px rgba(225,29,72,0.4);">
            </div>
            <h2 style="font-size: 24px; margin-bottom: 5px;">Portal de <span>Barberos</span></h2>
            <p style="font-size: 13px; color: #a1a1aa;">Inicia sesión para gestionar tus citas y precios.</p>
        </div>

        <form action="/public/login" method="POST">
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Correo Electrónico:</label>
                <input type="email" id="email" name="email" placeholder="correo@barber.com" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 10px;">
                <i class="fa-solid fa-right-to-bracket"></i> Ingresar al Panel
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="/public/registro" style="color: #a1a1aa; text-decoration: none; font-size: 13px;">
                ¿No tienes cuenta? <span style="color: #e11d48; font-weight: 600;">Regístrate aquí</span>
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
        });
    </script>
    <?php endif; ?>

</body>
</html>