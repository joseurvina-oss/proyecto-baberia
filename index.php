<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - AgendaBarber</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .portal-card {
            background-color: #1e1e1e;
            padding: 40px 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.6);
        }
        h1 {
            color: #ff9900;
            margin-bottom: 10px;
            font-size: 28px;
        }
        p.subtitle {
            color: #aaa;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .options-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .role-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            transition: transform 0.2s, background-color 0.2s;
        }
        .role-btn:hover {
            transform: translateY(-2px);
        }
        .btn-client {
            background-color: #ff9900;
            color: #000;
        }
        .btn-client:hover {
            background-color: #e68a00;
        }
        .btn-barber {
            background-color: #2a2a2a;
            color: #ffffff;
            border: 1px solid #444;
        }
        .btn-barber:hover {
            background-color: #333333;
            border-color: #ff9900;
        }
    </style>
</head>
<body>

<div class="portal-card">
    <h1>💈 AgendaBarber</h1>
    <p class="subtitle">Selecciona tu perfil para ingresar</p>

<!-- En tu index.php, cambia el enlace del botón cliente por este: -->
<a href="cliente_barberos.php" class="role-btn btn-client">
    🙋‍♂️ Soy Cliente (Ver Barberos y Agendar)
</a>

        <!-- Redirige al Inicio de Sesión de Barberos -->
        <a href="login_barbero.php" class="role-btn btn-barber">
            ✂️ Soy Barbero (Iniciar Sesión)
        </a>
    </div>
</div>

</body>
</html>