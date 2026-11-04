<?php
session_start();

// Verificar si el barbero ha iniciado sesión, si no, redirigir al login
if (!isset($_SESSION['barber_id'])) {
    header("Location: login_barber.php");
    exit();
}

require_once 'db.php';

$barber_id = $_SESSION['barber_id'];
$mensaje = '';

// Procesar actualización de biografía o datos si se envían
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = $conn->real_escape_string($_POST['bio']);
    
    $sql_update = "UPDATE barbers SET bio = '$bio' WHERE id = $barber_id";
    if ($conn->query($sql_update) === TRUE) {
        $mensaje = "¡Perfil actualizado con éxito!";
    } else {
        $mensaje = "Error al actualizar: " . $conn->error;
    }
}

// Obtener los datos actualizados del barbero
$sql_barber = "SELECT * FROM barbers WHERE id = $barber_id LIMIT 1";
$result = $conn->query($sql_barber);
$barber = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Barbero - AgendaBarber</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            display: flex;
            justify-content: center;
            padding: 40px;
            margin: 0;
        }
        .profile-card {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        h2 {
            color: #ff9900;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        textarea, input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #333;
            background-color: #2a2a2a;
            color: #fff;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .btn-submit {
            padding: 12px 20px;
            background-color: #ff9900;
            color: #000;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-submit:hover {
            background-color: #e68a00;
        }
        .logout-btn {
            float: right;
            background-color: #ff4444;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }
        .logout-btn:hover {
            background-color: #cc0000;
        }
        .success-msg {
            background-color: rgba(46, 204, 113, 0.2);
            border: 1px solid #2ecc71;
            color: #2ecc71;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="profile-card">
    <a href="logout.php" class="logout-btn" onclick="return confirm('¿Estás seguro de cerrar sesión?');">Cerrar Sesión</a>
    <h2>💈 Panel de Barbero</h2>
    <p style="color: #aaa;">Bienvenido, <strong><?php echo htmlspecialchars($barber['name']); ?></strong></p>
    <p style="color: #888; font-size: 14px; margin-bottom: 25px;">Correo: <?php echo htmlspecialchars($barber['email']); ?></p>

    <?php if (!empty($mensaje)): ?>
        <div class="success-msg"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="bio">Biografía / Descripción Profesional:</label>
            <textarea id="bio" name="bio" placeholder="Escribe algo sobre tu experiencia, estilos de corte favoritos, etc."><?php echo htmlspecialchars($barber['bio'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn-submit">Guardar Cambios</button>
    </form>
</div>

<div style="margin-top: 25px; padding: 20px; background: #252525; border-radius: 8px; border-left: 4px solid #4285F4;">
    <h3 style="color: #4285F4; margin-top: 0;">📅 Sincronización con Google Calendar</h3>
    <p style="font-size: 14px; color: #aaa;">Conecta tu correo (ej. agendabarber.cristofer@gmail.com) para que las reservas de tus clientes lleguen directo a tu calendario.</p>
    <a href="auth_google.php" style="display: inline-block; background: #4285F4; color: white; padding: 10px 18px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-top: 5px;">
        Conectar Google Calendar
    </a>
</div>

</body>
</html>