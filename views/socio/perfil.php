<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Refugio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏔️</text></svg>">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --light-bg: #ecf0f1;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .profile-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 30px;
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

        .form-control {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-control:disabled, .form-control[readonly] {
            background-color: var(--light-bg);
            cursor: not-allowed;
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

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .info-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }

        .password-strength {
            height: 5px;
            background: #e0e0e0;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: var(--danger-color);
        }

        .password-strength-bar.medium {
            width: 66%;
            background: var(--warning-color);
        }

        .password-strength-bar.strong {
            width: 100%;
            background: var(--success-color);
        }

        .section-divider {
            margin: 40px 0;
            border-top: 2px dashed #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container profile-container">
        <!-- Personal Information Card -->
        <div class="profile-card">
            <!-- Header -->
            <div class="text-center">
                <div class="profile-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h2><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></h2>
                <div class="info-badge">
                    <i class="bi bi-award-fill me-2"></i>
                    Socio Nº <?php echo htmlspecialchars($usuario['num_socio']); ?>
                </div>
            </div>

            <div class="section-divider"></div>

            <!-- Personal Info Form -->
            <div class="card-header-custom">
                <h4 class="mb-0">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    Información Personal
                </h4>
            </div>

            <form method="POST" action="viewSocioMVC.php" id="profileForm">
                <input type="hidden" name="accion" value="actualizar_perfil">

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label required-field">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="apellidos" class="form-label required-field">Apellidos</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos"
                               value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="email" class="form-label required-field">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        <div class="form-text">Se usará para notificaciones de reservas</div>
                    </div>
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono"
                               value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                               pattern="[0-9]{9}">
                        <div class="form-text">Formato: 9 dígitos</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="dni" class="form-label">DNI/NIE</label>
                        <input type="text" class="form-control" id="dni" name="dni"
                               value="<?php echo htmlspecialchars($usuario['dni'] ?? ''); ?>" readonly disabled>
                        <div class="form-text">No se puede modificar</div>
                    </div>
                    <div class="col-md-6">
                        <label for="num_socio" class="form-label">Número de Socio</label>
                        <input type="text" class="form-control" id="num_socio"
                               value="<?php echo htmlspecialchars($usuario['num_socio']); ?>" readonly disabled>
                        <div class="form-text">No se puede modificar</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="profile-card">
            <div class="card-header-custom">
                <h4 class="mb-0">
                    <i class="bi bi-shield-lock me-2"></i>
                    Cambiar Contraseña
                </h4>
            </div>

            <form method="POST" action="viewSocioMVC.php" id="passwordForm">
                <input type="hidden" name="accion" value="cambiar_contrasena">

                <div class="mb-4">
                    <label for="contrasena_actual" class="form-label required-field">Contraseña Actual</label>
                    <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" required>
                </div>

                <div class="mb-4">
                    <label for="contrasena_nueva" class="form-label required-field">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="contrasena_nueva" name="contrasena_nueva"
                           minlength="6" required>
                    <div class="form-text">Mínimo 6 caracteres</div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <small id="strengthText" class="text-muted"></small>
                </div>

                <div class="mb-4">
                    <label for="contrasena_confirmar" class="form-label required-field">Confirmar Nueva Contraseña</label>
                    <input type="password" class="form-control" id="contrasena_confirmar" name="contrasena_confirmar"
                           minlength="6" required>
                    <div class="invalid-feedback" id="passwordMismatch">
                        Las contraseñas no coinciden
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="viewSocioMVC.php?accion=calendario" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver
                    </a>
                    <button type="submit" class="btn btn-primary" id="passwordSubmitBtn">
                        <i class="bi bi-key me-2"></i>
                        Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        document.getElementById('contrasena_nueva').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            let strength = 0;

            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            strengthBar.className = 'password-strength-bar';

            if (strength <= 2) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Contraseña débil';
                strengthText.style.color = '#e74c3c';
            } else if (strength <= 4) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Contraseña media';
                strengthText.style.color = '#f39c12';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Contraseña fuerte';
                strengthText.style.color = '#27ae60';
            }
        });

        // Password confirmation validation
        const passwordForm = document.getElementById('passwordForm');
        const newPassword = document.getElementById('contrasena_nueva');
        const confirmPassword = document.getElementById('contrasena_confirmar');
        const submitBtn = document.getElementById('passwordSubmitBtn');
        const mismatchFeedback = document.getElementById('passwordMismatch');

        function validatePasswordMatch() {
            if (confirmPassword.value === '') {
                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.remove('is-valid');
                return;
            }

            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                confirmPassword.classList.remove('is-valid');
                submitBtn.disabled = true;
            } else {
                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.add('is-valid');
                submitBtn.disabled = false;
            }
        }

        newPassword.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);

        passwordForm.addEventListener('submit', function(e) {
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                confirmPassword.classList.add('is-invalid');
                return false;
            }

            if (newPassword.value.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres.');
                return false;
            }
        });

        // Profile form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const telefono = document.getElementById('telefono').value;

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor, introduce un email válido.');
                return false;
            }

            // Phone validation (if provided)
            if (telefono && !/^[0-9]{9}$/.test(telefono)) {
                e.preventDefault();
                alert('El teléfono debe tener 9 dígitos.');
                return false;
            }
        });
    </script>
</body>
</html>
