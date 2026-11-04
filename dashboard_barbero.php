<?php
session_start();
require_once 'db.php';

// Verificar si el barbero ha iniciado sesión
if (!isset($_SESSION['barber_id'])) {
    header("Location: login_barbero.php");
    exit();
}

$barber_id = $_SESSION['barber_id'];
$barber_name = $_SESSION['barber_name'];

// Consultar citas del barbero
$stmt = $conn->prepare("SELECT id, client_name, client_phone, service, appointment_date, appointment_time FROM bookings WHERE staff_id = ? ORDER BY appointment_date DESC, appointment_time DESC");
$stmt->bind_param("i", $barber_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Barbero - AgendaBarber</title>
    <!-- FontAwesome CDN para iconos profesionales -->
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
            padding: 30px 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        header {
            background: rgba(24, 24, 27, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 20px 30px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        h1 {
            color: #f59e0b;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-logout {
            background-color: #ef4444;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s;
        }
        .btn-logout:hover {
            background-color: #dc2626;
        }
        .card {
            background: rgba(24, 24, 27, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        h2 {
            font-size: 18px;
            color: #d4d4d8;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #27272a;
            font-size: 14px;
        }
        th {
            color: #f59e0b;
            font-weight: 600;
        }
        td {
            color: #e4e4e7;
        }
        tr:hover td {
            background-color: rgba(255, 255, 255, 0.02);
        }
        .btn-whatsapp {
            background-color: #22c55e;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s;
        }
        .btn-whatsapp:hover {
            background-color: #16a34a;
        }
        .empty-state {
            text-align: center;
            color: #71717a;
            padding: 40px;
            font-size: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1><i class="fa-solid fa-scissors"></i> Bienvenido, <?php echo htmlspecialchars($barber_name); ?></h1>
        <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a>
    </header>

    <div class="card">
        <h2><i class="fa-regular fa-calendar-days"></i> Mis Próximas Citas Agendadas</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Servicio</th>
                            <th>Fecha y Hora</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><i class="fa-solid fa-user" style="color: #f59e0b; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['client_name']); ?></strong></td>
                                <td><i class="fa-solid fa-phone" style="color: #71717a; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['client_phone']); ?></td>
                                <td><i class="fa-solid fa-cut" style="color: #a1a1aa; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['service']); ?></td>
                                <td><i class="fa-regular fa-clock" style="color: #a1a1aa; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['appointment_date'] . ' ' . $row['appointment_time']); ?> hrs</td>
                                <td>
                                    <?php 
                                        $phoneClean = preg_replace('/[^0-9]/', '', $row['client_phone']);
                                        $msg = "Hola {$row['client_name']}, te escribo desde la barbería para confirmar tu cita de {$row['service']} el día {$row['appointment_date']} a las {$row['appointment_time']} hrs.";
                                    ?>
                                    <a href="https://wa.me/<?php echo $phoneClean; ?>?text=<?php echo urlencode($msg); ?>" target="_blank" class="btn-whatsapp">
                                        <i class="fa-brands fa-whatsapp"></i> Escribir al Cliente
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">No tienes citas agendadas por el momento.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>