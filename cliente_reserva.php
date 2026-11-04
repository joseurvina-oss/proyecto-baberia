<?php
require_once 'db.php';

$selected_barber_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;

$barber_name = "Barbero Profesional";
$barber_phone = "569927861737";

if ($selected_barber_id > 0) {
    $stmt = $conn->prepare("SELECT name, phone FROM barbers WHERE id = ?");
    $stmt->bind_param("i", $selected_barber_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $barber_name = $row['name'];
        if (!empty($row['phone'])) {
            $barber_phone = preg_replace('/[^0-9]/', '', $row['phone']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva tu Hora - AgendaBarber</title>
    <!-- FontAwesome para Iconos Profesionales -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 para Alertas Modernas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            padding: 30px 20px;
            min-height: 100vh;
        }
        .booking-card {
            background: rgba(24, 24, 27, 0.75);
            backdrop-filter: blur(16px);
            padding: 35px;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        h2 {
            text-align: center;
            color: #f59e0b;
            margin-bottom: 25px;
            font-size: 22px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 14px;
            color: #d4d4d8;
        }
        label i {
            color: #f59e0b;
            margin-right: 6px;
        }
        input, select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #3f3f46;
            background-color: #27272a;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus, select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }
        .barber-badge {
            background-color: #27272a;
            border: 1px solid #3f3f46;
            padding: 12px;
            border-radius: 8px;
            color: #f59e0b;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .time-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 8px;
        }
        .time-btn {
            padding: 10px;
            background-color: #27272a;
            border: 1px solid #3f3f46;
            color: #fff;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            font-size: 14px;
            transition: all 0.2s;
        }
        .time-btn:hover {
            border-color: #f59e0b;
            background-color: #323238;
        }
        .time-btn.selected {
            background-color: #f59e0b;
            color: #000;
            font-weight: bold;
            border-color: #f59e0b;
        }
        /* Estilo para los botones de horas ya ocupadas */
        .time-btn.booked {
            background-color: #18181b !important;
            color: #71717a !important;
            border-color: #27272a !important;
            cursor: not-allowed !important;
            opacity: 0.7;
        }
        .no-slots {
            grid-column: span 3;
            text-align: center;
            color: #a1a1aa;
            font-size: 13px;
            padding: 10px;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #22c55e;
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            margin-top: 20px;
            transition: background-color 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background-color: #16a34a;
        }
        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #a1a1aa;
            text-decoration: none;
            font-size: 13px;
        }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>

<div class="booking-card">
    <h2><i class="fa-solid fa-calendar-check"></i> Reserva tu Cita</h2>

    <form id="bookingForm">
        <input type="hidden" id="staff_id" value="<?php echo $selected_barber_id; ?>">
        <input type="hidden" id="barber_phone" value="<?php echo $barber_phone; ?>">

        <div class="form-group">
            <label>Barbero Seleccionado:</label>
            <div class="barber-badge">
                <i class="fa-solid fa-user-tie"></i> <?php echo htmlspecialchars($barber_name); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="clientName"><i class="fa-solid fa-user"></i> Tu Nombre y Apellido:</label>
            <input type="text" id="clientName" name="clientName" placeholder="Ej: Leo Pérez" required>
        </div>

        <div class="form-group">
            <label for="clientPhone"><i class="fa-solid fa-phone"></i> Tu Teléfono / WhatsApp:</label>
            <input type="tel" id="clientPhone" name="clientPhone" placeholder="Ej: 927861737" required>
        </div>

        <div class="form-group">
            <label for="service"><i class="fa-solid fa-scissors"></i> Servicio:</label>
            <select name="service" id="service" required>
                <option value="Corte de Pelo ($8.000)">Corte de Pelo ($8.000)</option>
                <option value="Corte + Barba ($12.000)">Corte + Barba ($12.000)</option>
                <option value="Perfilado de Barba ($5.000)">Perfilado de Barba ($5.000)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="date"><i class="fa-regular fa-calendar-days"></i> Fecha de la Cita:</label>
            <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fa-regular fa-clock"></i> Selecciona un Horario:</label>
            <div class="time-grid" id="timeGrid">
                <button type="button" class="time-btn" data-time="10:00">10:00</button>
                <button type="button" class="time-btn" data-time="10:45">10:45</button>
                <button type="button" class="time-btn" data-time="11:30">11:30</button>
                <button type="button" class="time-btn" data-time="12:15">12:15</button>
                <button type="button" class="time-btn" data-time="15:00">15:00</button>
                <button type="button" class="time-btn" data-time="15:45">15:45</button>
                <button type="button" class="time-btn" data-time="16:30">16:30</button>
                <button type="button" class="time-btn" data-time="17:15">17:15</button>
                <button type="button" class="time-btn" data-time="18:00">18:00</button>
            </div>
            <input type="hidden" id="selectedTime" name="time" required>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <span id="btnText"><i class="fa-solid fa-check"></i> Confirmar y Reservar</span>
            <div class="spinner" id="btnSpinner"></div>
        </button>
    </form>

    <a href="cliente_barberos.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver a la lista de barberos</a>
</div>

<script>
    const timeGrid = document.getElementById('timeGrid');
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

        // Normalizar formato de fecha si el navegador lo invierte
        if (dateVal.includes('-') && dateVal.split('-')[0].length !== 4) {
            const parts = dateVal.split('-');
            if (parts[2].length === 4) {
                dateVal = `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
        }

        hiddenTimeInput.value = '';
        
        // Restablecer estado de botones
        timeButtons.forEach(btn => {
            btn.classList.remove('selected', 'booked');
            btn.disabled = false;
            const timeVal = btn.getAttribute('data-time');
            btn.innerHTML = timeVal;
        });

        const oldMsg = document.getElementById('noSlotsMsg');
        if (oldMsg) oldMsg.remove();

        fetch(`api_get_booked_times.php?staff_id=${staffId}&date=${dateVal}`)
            .then(res => res.json())
            .then(data => {
                if (data.booked_times) {
                    timeButtons.forEach(btn => {
                        const timeVal = btn.getAttribute('data-time');
                        const isBooked = data.booked_times.some(bookedTime => bookedTime.startsWith(timeVal));
                        
                        if (isBooked) {
                            btn.classList.add('booked');
                            btn.disabled = true;
                            btn.innerHTML = `${timeVal} <span style="font-size: 11px; display: block; color: #ef4444; font-weight: 600;">Ocupado</span>`;
                        }
                    });
                }
            })
            .catch(err => console.error("Error consultando disponibilidad:", err));
    }

    dateInput.addEventListener('change', updateAvailableTimes);
    updateAvailableTimes();

    timeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('booked') || button.disabled) return;
            
            timeButtons.forEach(btn => {
                if(!btn.classList.contains('booked')) {
                    btn.classList.remove('selected');
                }
            });
            button.classList.add('selected');
            hiddenTimeInput.value = button.getAttribute('data-time');
        });
    });

    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const staffId = staffIdInput.value;
        const barberPhone = document.getElementById('barber_phone').value;

        if (!staffId || staffId == "0") {
            Swal.fire({
                title: 'Atención',
                text: 'Debes seleccionar un barbero desde el catálogo primero.',
                icon: 'warning',
                background: '#18181b',
                color: '#fff',
                confirmButtonColor: '#f59e0b'
            }).then(() => {
                window.location.href = 'cliente_barberos.php';
            });
            return;
        }

        if (!hiddenTimeInput.value) {
            Swal.fire({
                title: 'Falta Horario',
                text: 'Por favor selecciona un horario disponible.',
                icon: 'info',
                background: '#18181b',
                color: '#fff',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        submitBtn.disabled = true;
        btnText.innerText = 'Procesando...';
        btnSpinner.style.display = 'block';

        const formData = {
            tenant_id: 1,
            staff_id: staffId,
            clientName: document.getElementById('clientName').value,
            clientPhone: document.getElementById('clientPhone').value,
            service: document.getElementById('service').value,
            date: dateInput.value,
            time: hiddenTimeInput.value
        };

        fetch('api_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.status === 'success' || data.success) {
                    const mensajeWS = `Hola, acabo de agendar una cita para el ${formData.date} a las ${formData.time} hrs para ${formData.service}. Mi nombre es ${formData.clientName}.`;
                    
                    Swal.fire({
                        title: '¡Cita Reservada con Éxito!',
                        text: 'Sincronizada con Google Calendar. Te redirigiremos a WhatsApp...',
                        icon: 'success',
                        background: '#18181b',
                        color: '#fff',
                        confirmButtonColor: '#22c55e',
                        timer: 2500,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = `https://wa.me/${barberPhone}?text=${encodeURIComponent(mensajeWS)}`;
                    });
                } else {
                    Swal.fire({
                        title: 'Aviso',
                        text: data.message || 'Intente nuevamente',
                        icon: 'error',
                        background: '#18181b',
                        color: '#fff',
                        confirmButtonColor: '#f59e0b'
                    });
                    submitBtn.disabled = false;
                    btnText.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar y Reservar';
                    btnSpinner.style.display = 'none';
                    updateAvailableTimes();
                }
            } catch (e) {
                Swal.fire({
                    title: 'Error de Servidor',
                    text: text,
                    icon: 'error',
                    background: '#18181b',
                    color: '#fff',
                    confirmButtonColor: '#f59e0b'
                });
                submitBtn.disabled = false;
                btnText.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar y Reservar';
                btnSpinner.style.display = 'none';
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error de Red',
                text: 'Ocurrió un error al procesar la solicitud.',
                icon: 'error',
                background: '#18181b',
                color: '#fff',
                confirmButtonColor: '#f59e0b'
            });
            submitBtn.disabled = false;
            btnText.innerHTML = '<i class="fa-solid fa-check"></i> Confirmar y Reservar';
            btnSpinner.style.display = 'none';
        });
    });
</script>

</body>
</html>