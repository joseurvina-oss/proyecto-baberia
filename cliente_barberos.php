<?php
require_once 'db.php';

// Consultar los barberos registrados
$result = $conn->query("SELECT id, name, bio, profile_photo FROM barbers");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestros Barberos - AgendaBarber</title>
    <!-- FontAwesome para Iconos Profesionales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        header {
            text-align: center;
            margin-bottom: 40px;
        }
        h1 {
            color: #f59e0b;
            font-size: 28px;
            margin-bottom: 8px;
        }
        p.subtitle {
            color: #a1a1aa;
            font-size: 15px;
        }
        .barbers-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            width: 100%;
            max-width: 900px;
        }
        .barber-card {
            background: rgba(24, 24, 27, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: transform 0.2s, border-color 0.2s;
        }
        .barber-card:hover {
            transform: translateY(-4px);
            border-color: #f59e0b;
        }
        .avatar-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #000;
            font-weight: bold;
            font-size: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .barber-name {
            font-size: 20px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
        }
        .barber-bio {
            color: #d4d4d8;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.5;
            min-height: 42px;
        }
        .badge-status {
            display: inline-block;
            background-color: rgba(34, 197, 94, 0.15);
            color: #22c55e;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .btn-book {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #f59e0b;
            color: #000;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.2s;
            font-size: 14px;
        }
        .btn-book:hover {
            background-color: #d97706;
        }
        .admin-link {
            margin-top: 40px;
            color: #71717a;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }
        .admin-link:hover {
            color: #f59e0b;
        }
    </style>
</head>
<body>

<header>
    <h1><i class="fa-solid fa-scissors"></i> Selecciona tu Barbero</h1>
    <p class="subtitle">Conoce a nuestros profesionales y agenda tu próxima cita</p>
</header>

<div class="barbers-container">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="barber-card">
                <div class="avatar-placeholder">
                    <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                </div>
                <div class="barber-name"><?php echo htmlspecialchars($row['name']); ?></div>
                <div class="badge-status"><i class="fa-solid fa-circle-check"></i> Disponible hoy</div>
                <div class="barber-bio">
                    <?php echo !empty($row['bio']) ? htmlspecialchars($row['bio']) : 'Especialista en cortes modernos y degradados profesionales.'; ?>
                </div>
                <a href="cliente_reserva.php?staff_id=<?php echo $row['id']; ?>" class="btn-book">
                    <i class="fa-regular fa-calendar-plus"></i> Reservar con <?php echo htmlspecialchars($row['name']); ?>
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: #71717a;">No hay barberos registrados actualmente.</p>
    <?php endif; ?>
</div>

<a href="login_barbero.php" class="admin-link"><i class="fa-solid fa-lock"></i> Acceso para Barberos (Panel de Control)</a>

</body>
</html>