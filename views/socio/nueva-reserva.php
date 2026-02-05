<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Reserva - Refugio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏔️</text></svg>">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-bg: #ecf0f1;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .reservation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }

        .card-header-custom {
            border-bottom: 3px solid var(--secondary-color);
            margin-bottom: 30px;
            padding-bottom: 15px;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }

        .availability-info {
            background: #e8f5e9;
            border-left: 4px solid var(--success-color);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .availability-info.warning {
            background: #fff3e0;
            border-left-color: var(--warning-color);
        }

        .availability-info.danger {
            background: #ffebee;
            border-left-color: var(--danger-color);
        }

        .acompanantes-section {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }

        .info-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reservation-card">
            <!-- Header -->
            <div class="card-header-custom">
                <h2 class="mb-0">
                    <i class="bi bi-calendar-plus me-2"></i>
                    Nueva Reserva
                </h2>
                <p class="text-muted mb-0 mt-2">Complete el formulario para solicitar una reserva en el refugio</p>
            </div>

            <!-- Info Badge -->
            <div class="info-badge">
                <i class="bi bi-info-circle me-2"></i>
                Las reservas quedan pendientes de aprobación por el administrador
            </div>

            <!-- Availability Alert -->
            <div id="availabilityAlert" class="availability-info">
                <i class="bi bi-check-circle me-2"></i>
                <span id="availabilityMessage"></span>
            </div>

            <!-- Reservation Form -->
            <form id="reservationForm" method="POST" action="viewSocioMVC.php">
                <input type="hidden" name="accion" value="crear_reserva">

                <!-- Date Range -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="fecha_entrada" class="form-label required-field">Fecha de Entrada</label>
                        <input type="text" class="form-control" id="fecha_entrada" name="fecha_entrada"
                               placeholder="Seleccione fecha" required readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_salida" class="form-label required-field">Fecha de Salida</label>
                        <input type="text" class="form-control" id="fecha_salida" name="fecha_salida"
                               placeholder="Seleccione fecha" required readonly>
                    </div>
                </div>

                <!-- Number of Beds -->
                <div class="mb-4">
                    <label for="numero_camas" class="form-label required-field">Número de Camas</label>
                    <select class="form-select" id="numero_camas" name="numero_camas" required>
                        <option value="">Seleccione cantidad</option>
                        <option value="1">1 cama</option>
                        <option value="2">2 camas</option>
                        <option value="3">3 camas</option>
                        <option value="4">4 camas</option>
                    </select>
                    <div class="form-text">Máximo 4 camas por reserva</div>
                </div>

                <!-- Activity Description -->
                <div class="mb-4">
                    <label for="actividad" class="form-label required-field">Actividad a Realizar</label>
                    <textarea class="form-control" id="actividad" name="actividad" rows="4"
                              placeholder="Describa la actividad que realizará (senderismo, escalada, etc.)" required></textarea>
                    <div class="form-text">Mínimo 10 caracteres</div>
                </div>

                <!-- Acompañantes Section (Hidden by default) -->
                <div id="acompanantesSection" class="acompanantes-section">
                    <h5 class="mb-3">
                        <i class="bi bi-people me-2"></i>
                        Datos de Acompañantes
                    </h5>
                    <p class="text-muted small">Complete los datos de las personas que le acompañarán</p>

                    <div id="acompanantesContainer">
                        <!-- Acompañante fields will be added dynamically -->
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label for="notas" class="form-label">Notas Adicionales (Opcional)</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"
                              placeholder="Información adicional que desee comunicar"></textarea>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between align-items-center">
                    <a href="viewSocioMVC.php?accion=calendario" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver
                    </a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="bi bi-check-circle me-2"></i>
                        Solicitar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        // Initialize date pickers
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const entradaPicker = flatpickr("#fecha_entrada", {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: today,
            onChange: function(selectedDates, dateStr) {
                salidaPicker.set('minDate', new Date(dateStr).fp_incr(1));
                checkAvailability();
            }
        });

        const salidaPicker = flatpickr("#fecha_salida", {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: today,
            onChange: function() {
                checkAvailability();
            }
        });

        // Handle bed number selection
        document.getElementById('numero_camas').addEventListener('change', function() {
            const numCamas = parseInt(this.value);
            const acompanantesSection = document.getElementById('acompanantesSection');
            const acompanantesContainer = document.getElementById('acompanantesContainer');

            if (numCamas > 1) {
                // Show acompañantes section
                acompanantesSection.style.display = 'block';

                // Clear existing fields
                acompanantesContainer.innerHTML = '';

                // Add fields for each acompañante (numCamas - 1 because the socio is already counted)
                for (let i = 1; i < numCamas; i++) {
                    const acompananteHtml = `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Acompañante ${i}</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Nombre</label>
                                        <input type="text" class="form-control" name="acompanante_nombre[]" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">Apellidos</label>
                                        <input type="text" class="form-control" name="acompanante_apellidos[]" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required-field">DNI/NIE</label>
                                        <input type="text" class="form-control" name="acompanante_dni[]"
                                               pattern="[0-9]{8}[A-Za-z]|[XYZ][0-9]{7}[A-Za-z]" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" name="acompanante_telefono[]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    acompanantesContainer.insertAdjacentHTML('beforeend', acompananteHtml);
                }
            } else {
                acompanantesSection.style.display = 'none';
                acompanantesContainer.innerHTML = '';
            }

            checkAvailability();
        });

        // Check availability function
        function checkAvailability() {
            const fechaEntrada = document.getElementById('fecha_entrada').value;
            const fechaSalida = document.getElementById('fecha_salida').value;
            const numeroCamas = document.getElementById('numero_camas').value;
            const submitBtn = document.getElementById('submitBtn');
            const availabilityAlert = document.getElementById('availabilityAlert');
            const availabilityMessage = document.getElementById('availabilityMessage');

            if (fechaEntrada && fechaSalida && numeroCamas) {
                // Simulate availability check (in production, this would be an AJAX call)
                fetch(`../../api/check_availability.php?fecha_entrada=${fechaEntrada}&fecha_salida=${fechaSalida}&numero_camas=${numeroCamas}`)
                    .then(response => response.json())
                    .then(data => {
                        availabilityAlert.style.display = 'block';

                        if (data.available) {
                            availabilityAlert.className = 'availability-info';
                            availabilityMessage.innerHTML = `<strong>¡Disponible!</strong> Hay ${data.beds_available} camas disponibles para las fechas seleccionadas.`;
                            submitBtn.disabled = false;
                        } else {
                            availabilityAlert.className = 'availability-info danger';
                            availabilityMessage.innerHTML = `<strong>No disponible.</strong> Solo hay ${data.beds_available} camas disponibles (necesita ${numeroCamas}).`;
                            submitBtn.disabled = true;
                        }
                    })
                    .catch(error => {
                        // If check fails, allow submission anyway (will be validated server-side)
                        availabilityAlert.style.display = 'block';
                        availabilityAlert.className = 'availability-info warning';
                        availabilityMessage.innerHTML = '<strong>Verificando disponibilidad...</strong> La reserva será validada por el administrador.';
                        submitBtn.disabled = false;
                    });
            } else {
                submitBtn.disabled = true;
                availabilityAlert.style.display = 'none';
            }
        }

        // Form validation
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const actividad = document.getElementById('actividad').value;

            if (actividad.length < 10) {
                e.preventDefault();
                alert('La descripción de la actividad debe tener al menos 10 caracteres.');
                return false;
            }

            return true;
        });
    </script>
</body>
</html>
