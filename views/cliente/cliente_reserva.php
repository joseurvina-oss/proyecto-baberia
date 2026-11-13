<?php
// views/cliente/cliente_reserva.php
// 1. RUTA DE BASE DE DATOS ACTUALIZADA
require_once __DIR__ . '/../../src/config/db.php';

$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 1;

// Obtener datos del barbero específico
$stmt = $conn->prepare("SELECT id, name, phone, profile_photo FROM barbers WHERE id = ?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$barber = $result->fetch_assoc();
$stmt->close();

if (!$barber) {
    echo "<body style='background:#050507; color:#fff; font-family:sans-serif; text-align:center; padding-top:20vh;'><h2>Barbero no encontrado</h2><a href='/public/' style='color:#e11d48;'>Volver al inicio</a></body>";
    exit;
}

$barber_name = $barber['name'];
$barber_phone = preg_replace('/[^0-9]/', '', $barber['phone'] ?? '569927861737');
$barber_photo = $barber['profile_photo'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar con <?php echo htmlspecialchars($barber_name); ?> - AgendaBarber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/public/assets/css/estilos.css">
    <style>
        .profile-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #e11d48; margin-bottom: 15px; box-shadow: 0 0 20px rgba(225, 29, 72, 0.4); }
        .avatar-placeholder { width: 120px; height: 120px; border-radius: 50%; background: #e11d48; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 50px; font-weight: bold; margin-bottom: 15px; border: 3px solid #be123c; box-shadow: 0 0 20px rgba(225, 29, 72, 0.4); }
        .badge { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); font-size: 12px; padding: 5px 14px; border-radius: 20px; font-weight: bold; margin-bottom: 25px; display: inline-block; }
        .time-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 8px; }
        .time-btn { padding: 10px; background-color: rgba(24, 24, 27, 0.8); border: 1px solid rgba(255, 255, 255, 0.12); color: #fff; border-radius: 10px; cursor: pointer; text-align: center; font-size: 14px; transition: all 0.2s; }
        .time-btn:hover { border-color: #e11d48; background-color: rgba(39, 39, 42, 0.9); }
        .time-btn.selected { background-color: #e11d48; color: #fff; font-weight: bold; border-color: #e11d48; box-shadow: 0 0 15px rgba(225, 29, 72, 0.4); }
        .time-btn.booked { background-color: #121214 !important; color: #52525b !important; border-color: #18181b !important; cursor: not-allowed !important; opacity: 0.6; }
        .spinner { display: none; width: 18px; height: 18px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .booking-section { display: none; }
        .booking-section.active { display: block; }
    </style>
</head>
<body>

<div class="booking-card">

    <!-- SECCIÓN 1: Perfil Esencial (Foto, Nombre y Botón) -->
    <div id="profileSection" class="booking-section active" style="text-align: center; padding-top: 10px;">
        <div style="display: flex; flex-direction: column; align-items: center;">
            <?php if (!empty($barber_photo)): ?>
                <!-- 3. RUTA DE IMAGEN ACTUALIZADA CON BASENAME -->
                <?php $nombreFoto = basename($barber_photo); ?>
                <img src="/public/uploads/<?php echo htmlspecialchars($nombreFoto); ?>" alt="Foto" class="profile-img">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?php echo strtoupper(substr($barber_name, 0, 1)); ?>
                </div>
            <?php endif; ?>

            <h2 style="margin-bottom: 10px; font-size: 24px; color: #fff;"><?php echo htmlspecialchars($barber_name); ?></h2>
            <span class="badge"><i class="fa-solid fa-circle-check"></i> Disponible</span>
        </div>

        <button type="button" class="btn-primary" onclick="mostrarFormulario()" style="display: block; width: 100%; text-decoration: none; padding: 14px; font-size: 15px;">
            <i class="fa-regular fa-calendar-days"></i> Reservar Cita
        </button>
    </div>

    <!-- SECCIÓN 2: Formulario de Reserva y Horarios -->
    <div id="formSection" class="booking-section">
        <div style="display: flex; align-items: center; margin-bottom: 20px; gap: 12px;">
            <button type="button" onclick="mostrarPerfil()" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <h2 style="margin: 0; font-size: 20px; text-align: left;">Selecciona tu <span>Cita</span></h2>
        </div>
        <p style="margin-bottom: 20px; font-size: 13px;">Estás agendando con <strong style="color: #fff;"><?php echo htmlspecialchars($barber_name); ?></strong></p>

        <form id="bookingForm">
            <input type="hidden" id="staff_id" value="<?php echo $staff_id; ?>">
            <input type="hidden" id="barber_phone" value="<?php echo htmlspecialchars($barber_phone); ?>">

            <div class="form-group">
                <label for="clientName"><i class="fa-solid fa-user"></i> Tu Nombre:</label>
                <input type="text" id="clientName" placeholder="Ej: Carlos Soto" required>
            </div>

            <div class="form-group">
                <label for="clientPhone"><i class="fa-solid fa-phone"></i> Tu Teléfono / WhatsApp:</label>
                <input type="tel" id="clientPhone" placeholder="Ej: 927861737" required>
            </div>

            <div class="form-group">
                <label for="service"><i class="fa-solid fa-list-check"></i> Servicio:</label>
                <select id="service" required>
                    <?php
                    $stmtServClient = $conn->prepare("SELECT service_name, price FROM barber_services WHERE staff_id = ?");
                    $stmtServClient->bind_param("i", $staff_id);
                    $stmtServClient->execute();
                    $servClientResult = $stmtServClient->get_result();

                    if ($servClientResult->num_rows > 0) {
                        echo '<option value="">Selecciona un servicio...</option>';
                        while ($sRow = $servClientResult->fetch_assoc()) {
                            $nombreServicio = $sRow['service_name'];
                            $precioServicio = number_format($sRow['price'], 0, ',', '.');
                            $valorOption = $nombreServicio . ' ($' . $precioServicio . ')';
                            echo '<option value="' . htmlspecialchars($valorOption) . '">' . htmlspecialchars($nombreServicio) . ' ($' . $precioServicio . ')</option>';
                        }
                    } else {
                        echo '<option value="">No hay servicios configurados por este barbero</option>';
                    }
                    $stmtServClient->close();
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="date"><i class="fa-regular fa-calendar-days"></i> Fecha:</label>
                <input type="date" id="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label><i class="fa-regular fa-clock"></i> Horarios Disponibles:</label>
                <div class="time-grid" id="timeGrid">
                    <?php
                    $stmtHours = $conn->prepare("SELECT available_time FROM barber_hours WHERE staff_id = ? ORDER BY available_time ASC");
                    $stmtHours->bind_param("i", $staff_id);
                    $stmtHours->execute();
                    $hoursResult = $stmtHours->get_result();

                    if ($hoursResult->num_rows > 0) {
                        while ($hRow = $hoursResult->fetch_assoc()) {
                            $timeFormatted = date('H:i', strtotime($hRow['available_time']));
                            echo '<button type="button" class="time-btn" data-time="' . $timeFormatted . '">' . $timeFormatted . '</button>';
                        }
                    } else {
                        echo '<p style="color: #a1a1aa; font-size: 13px; grid-column: span 3;">No hay horarios configurados por el barbero.</p>';
                    }
                    $stmtHours->close();
                    ?>
                </div>
                <input type="hidden" id="selectedTime" required>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4); margin-top: 20px;">
                <span id="btnText"><i class="fa-solid fa-check"></i> Confirmar y Reservar</span>
                <div class="spinner" id="btnSpinner"></div>
            </button>
        </form>
    </div>

</div>

<script>
    const profileSection = document.getElementById('profileSection');
    const formSection = document.getElementById('formSection');

    function mostrarFormulario() {
        profileSection.classList.remove('active');
        formSection.classList.add('active');
        updateAvailableTimes();
    }

    function mostrarPerfil() {
        formSection.classList.remove('active');
        profileSection.classList.add('active');
    }

    const timeButtons = document.querySelectorAll('.time-btn');
    const hiddenTimeInput = document.getElementById('selectedTime');
    const dateInput = document.getElementById('date');
    const staffIdInput = document.getElementById('staff_id');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    function updateAvailableTimes() {
        const staffId = staffIdInput.value;
        let dateVal = dateInput.value;
        if (!staffId || !dateVal) return;
        
        hiddenTimeInput.value = '';
        timeButtons.forEach(btn => {
            btn.classList.remove('selected', 'booked');
            btn.disabled = false;
            btn.innerHTML = btn.getAttribute('data-time');
        });

        // 4. RUTA API HORARIOS CORREGIDA
        fetch(`/public/api_get_booked_times.php?staff_id=${staffId}&date=${dateVal}`)
            .then(res => res.json())
            .then(data => {
                if (data.booked_times) {
                    timeButtons.forEach(btn => {
                        const timeVal = btn.getAttribute('data-time');
                        if (data.booked_times.some(bt => bt.startsWith(timeVal))) {
                            btn.classList.add('booked');
                            btn.disabled = true;
                            btn.innerHTML = `${timeVal} <span style="font-size: 10px; display: block; color: #ef4444;">Ocupado</span>`;
                        }
                    });
                }
            }).catch(err => console.error(err));
    }

    dateInput.addEventListener('change', updateAvailableTimes);

    timeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('booked') || button.disabled) return;
            timeButtons.forEach(btn => { if(!btn.classList.contains('booked')) btn.classList.remove('selected'); });
            button.classList.add('selected');
            hiddenTimeInput.value = button.getAttribute('data-time');
        });
    });

    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const staffId = staffIdInput.value;
        const barberPhone = document.getElementById('barber_phone').value;

        if (!hiddenTimeInput.value) {
            Swal.fire({ title: 'Falta Horario', text: 'Selecciona un horario disponible.', icon: 'info', background: '#18181b', color: '#fff', confirmButtonColor: '#e11d48' });
            return;
        }

        submitBtn.disabled = true;
        btnText.innerText = 'Procesando...';
        btnSpinner.style.display = 'inline-block';

        const formData = {
            tenant_id: 1,
            staff_id: staffId,
            clientName: document.getElementById('clientName').value,
            clientPhone: document.getElementById('clientPhone').value,
            service: document.getElementById('service').value,
            date: dateInput.value,
            time: hiddenTimeInput.value
        };

        // 5. RUTA API RESERVA CORREGIDA
        fetch('/public/api_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(async res => {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(text || `Error HTTP ${res.status}`);
            }
        })
        .then(data => {
            if (data.status === 'success' || data.success) {
                const mensajeWS = `Hola, acabo de agendar una cita para el ${formData.date} a las ${formData.time} hrs para ${formData.service}. Mi nombre es ${formData.clientName}.`;
                Swal.fire({ title: '¡Cita Reservada!', text: 'Redirigiendo a WhatsApp...', icon: 'success', background: '#18181b', color: '#fff', timer: 2000, showConfirmButton: false })
                .then(() => { window.location.href = `https://wa.me/${barberPhone}?text=${encodeURIComponent(mensajeWS)}`; });
            } else {
                Swal.fire({ title: 'Aviso', text: data.message || 'Intente nuevamente', icon: 'error', background: '#18181b', color: '#fff', confirmButtonColor: '#e11d48' });
                submitBtn.disabled = false;
                btnText.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar y Reservar';
                btnSpinner.style.display = 'none';
            }
        }).catch(err => {
            console.error('Detalle del error:', err);
            Swal.fire({ title: 'Detalle del Error', text: err.message.replace(/<[^>]*>?/gm, '').substring(0, 180), icon: 'error', background: '#18181b', color: '#fff', confirmButtonColor: '#e11d48' });
            submitBtn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar y Reservar';
            btnSpinner.style.display = 'none';
        });
</script>

</body>
</html>