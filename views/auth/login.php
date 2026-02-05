<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏔️</text></svg>">
    <title>Iniciar Sesión - <?php echo REFUGIO_NAME ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 15px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 2rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .captcha-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header text-center">
                <i class="bi bi-house-heart-fill" style="font-size: 3rem;"></i>
                <h3 class="mt-2 mb-0"><?php echo REFUGIO_NAME ?></h3>
                <small>Sistema de Reservas</small>
            </div>
            <div class="card-body p-4">
                <?php include VIEWS_PATH . '/partials/flash-messages.php'; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Email
                        </label>
                        <input type="email"
                               class="form-control"
                               name="email"
                               required
                               autofocus
                               placeholder="tu@email.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-lock"></i> Contraseña
                        </label>
                        <input type="password"
                               class="form-control"
                               name="password"
                               required
                               placeholder="••••••••">
                    </div>

                    <!-- reCAPTCHA v2 widget -->
                    <div class="captcha-container">
                        <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mt-3">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </button>
                </form>

                <hr class="my-3">

                <div class="text-center text-muted small">
                    <p class="mb-1"><strong>Usuarios de prueba:</strong></p>
                    <p class="mb-0">Admin: admin@hostel.com</p>
                    <p class="mb-0">User: user1@mail.com</p>
                    <p class="mb-0">Contraseña: admin123 / user123</p>
            <small class="text-white">
                &copy; <?php echo date('Y') ?> <?php echo REFUGIO_NAME ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
