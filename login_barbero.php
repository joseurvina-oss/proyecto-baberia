<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($name) && !empty($password)) {
        // Consultar el barbero por nombre (o puedes cambiarlo por email si usas correos)
        $stmt = $conn->prepare("SELECT id, name, password FROM barbers WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Nota: Si tus contraseñas en BD están cifradas usa password_verify, 
            // si están en texto plano puedes cambiarlo por: if ($password === $row['password'])
            if ($password === $row['password'] || password_verify($password, $row['password'])) {
                $_SESSION['barber_id'] = $row['id'];
                $_SESSION['barber_name'] = $row['name'];
                header("Location: dashboard_barbero.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "No se encontró un barbero con ese nombre.";
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - AgendaBarber</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #0b0b0e;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Contenedor adaptado a Modo Oscuro y Estilo SaaS */
        .container {
            max-width: 380px;
            width: 100%;
            background: #18181b;
            background: linear-gradient(0deg, #18181b 0%, #202024 100%);
            border-radius: 24px;
            padding: 35px 30px;
            border: 1px solid #27272a;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
            margin: 20px;
        }

        .heading {
            text-align: center;
            font-weight: 800;
            font-size: 26px;
            color: #f59e0b; /* Tono ámbar característico del SaaS */
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #a1a1aa;
            margin-bottom: 20px;
        }

        .error-msg {
            background-color: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #f87171;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 15px;
        }

        .form {
            margin-top: 10px;
        }

        .form .input {
            width: 100%;
            background: #27272a;
            border: 1px solid #3f3f46;
            padding: 14px 18px;
            border-radius: 12px;
            margin-top: 12px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
        }

        .form .input::placeholder {
            color: #71717a;
        }

        .form .input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }

        .form .forgot-password {
            display: block;
            margin-top: 10px;
            text-align: right;
        }

        .form .forgot-password a {
            font-size: 12px;
            color: #a1a1aa;
            text-decoration: none;
        }

        .form .forgot-password a:hover {
            color: #f59e0b;
        }

        .form .login-button {
            display: block;
            width: 100%;
            font-weight: bold;
            background: linear-gradient(45deg, #f59e0b 0%, #d97706 100%);
            color: #000;
            padding-block: 14px;
            margin: 25px 0 15px 0;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .form .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
        }

        .form .login-button:active {
            transform: scale(0.98);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #71717a;
            text-decoration: none;
            font-size: 13px;
        }

        .back-link:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="heading">✂️ Panel Barberos</div>
    <div class="subtitle">Inicia sesión para ver tus citas</div>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form class="form" method="POST" action="">
        <input type="text" class="input" name="name" placeholder="Tu Nombre de Barbero (Ej: cristofer)" required>
        <input type="password" class="input" name="password" placeholder="Contraseña" required>

        <button type="submit" class="login-button">Iniciar Sesión</button>
    </form>

    <a href="cliente_barberos.php" class="back-link">← Volver al inicio de reservas</a>
</div>

</body>
</html>