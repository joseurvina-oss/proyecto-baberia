<?php
session_start();
require_once 'db.php'; // Tu conexión a MySQL

$mensaje = '';

// Procesar el formulario cuando agregues un barbero
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = 1; // ID de la barbería actual
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password_plana = $_POST['password'];
    
    // Encriptar la contraseña de forma segura
    $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

    $sql = "INSERT INTO barbers (tenant_id, name, email, password) VALUES ('$tenant_id', '$name', '$email', '$password_hash')";
    
    if ($conn->query($sql) === TRUE) {
        $mensaje = "¡Barbero registrado con éxito!";
    } else {
        $mensaje = "Error (quizás el correo ya está registrado): " . $conn->error;
    }
}

// Obtener la lista de todos los barberos registrados
$resultado = $conn->query("SELECT * FROM barbers");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Barberos</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; padding: 30px; }
        .container { max-width: 700px; margin: auto; background: #1e1e1e; padding: 25px; border-radius: 8px; }
        input { width: 100%; padding: 10px; margin: 8px 0; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 4px; }
        button { padding: 10px 20px; background: #f39c12; border: none; color: #000; font-weight: bold; border-radius: 4px; cursor: pointer; }
        button:hover { background: #e67e22; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #333; text-align: left; }
        th { background: #2a2a2a; }
    </style>
</head>
<body>
    <div class="container">
        <h2>👥 Gestión de Barberos</h2>
        <?php if (!empty($mensaje)) echo "<p style='color: #2ecc71;'>$mensaje</p>"; ?>

        <h3>Registrar Nuevo Barbero</h3>
        <form method="POST">
            <label>Nombre del Barbero:</label>
            <input type="text" name="name" required placeholder="Ej: Juan Pérez">

            <label>Correo Electrónico (para su login):</label>
            <input type="email" name="email" required placeholder="barbero@barberia.com">

            <label>Contraseña:</label>
            <input type="password" name="password" required placeholder="Contraseña de acceso">

            <button type="submit">Guardar Barbero</button>
        </form>

        <hr style="border-color: #333; margin: 30px 0;">

        <h3>Barberos Registrados</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
            </tr>
            <?php while($row = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>