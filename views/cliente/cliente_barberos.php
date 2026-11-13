<?php
require_once __DIR__ . '/../config/db.php';
$query = "SELECT * FROM barbers ORDER BY id ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuestros Barberos - AgendaBarber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/saas-barberia/public/assets/css/estilos.css">
    <style>
        .grid-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 25px; max-width: 1100px; width: 100%; z-index: 2; margin-top: 20px; }
        .barber-card { width: 320px; display: flex; flex-direction: column; align-items: center; text-align: center; }
        .profile-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #e11d48; margin-bottom: 15px; box-shadow: 0 0 15px rgba(225, 29, 72, 0.3); }
        .avatar-placeholder { width: 100px; height: 100px; border-radius: 50%; background: #e11d48; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; margin-bottom: 15px; border: 3px solid #be123c; }
        .badge { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); font-size: 12px; padding: 4px 12px; border-radius: 20px; font-weight: bold; margin-bottom: 12px; display: inline-block; }
        .barber-bio { color: #a1a1aa; font-size: 13px; line-height: 1.4; margin-bottom: 20px; }
        .btn-ig { background: linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045); color: white; border-radius: 50px; padding: 12px; text-decoration: none; font-weight: 600; display: block; width: 100%; margin-bottom: 10px; text-align: center; box-shadow: 0 4px 15px rgba(253, 29, 29, 0.3); }
    </style>
</head>
<body>

    <h2 style="z-index: 2; margin-top: 10px;">Conoce a nuestros <span>profesionales</span></h2>
    <p style="z-index: 2; margin-bottom: 30px; text-align: center;">Selecciona a tu barbero preferido para agendar</p>

    <div class="grid-container">
        <?php while($barber = $result->fetch_assoc()): ?>
            <div class="barber-card">
                <?php if (!empty($barber['profile_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($barber['profile_photo']); ?>" alt="Foto" class="profile-img">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($barber['name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>

                <h3><?php echo htmlspecialchars($barber['name']); ?></h3>
                <span class="badge"><i class="fa-solid fa-circle-check"></i> Disponible hoy</span>
                <p class="barber-bio"><?php echo !empty($barber['bio']) ? htmlspecialchars($barber['bio']) : 'Profesional enfocado en brindarte el mejor estilo.'; ?></p>

                <?php if (!empty($barber['instagram_url'])): ?>
                    <a href="<?php echo htmlspecialchars($barber['instagram_url']); ?>" target="_blank" class="btn-ig">
                        <i class="fa-brands fa-instagram"></i> Ver Trabajos en Instagram
                    </a>
                <?php endif; ?>

                <a href="cliente_reserva.php?staff_id=<?php echo $barber['id']; ?>" class="btn-primary">
                    📅 Reservar con <?php echo htmlspecialchars($barber['name']); ?>
                </a>
            </div>
        <?php endwhile; ?>
    </div>

    <a href="index.php" class="back-link" style="margin-top: 40px;"><i class="fa-solid fa-arrow-left"></i> Volver al inicio</a>

</body>
</html>
<?php $conn->close(); ?>