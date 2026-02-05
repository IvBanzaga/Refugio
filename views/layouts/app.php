<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Refugio de Montaña' ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL ?>/public/assets/css/styles.css">

    <?php if (isset($extraStyles)): ?>
        <?php echo $extraStyles ?>
    <?php endif; ?>
</head>
<body>
    <?php
        // Incluir header según el rol
        if (isAdmin()):
            include VIEWS_PATH . '/partials/header-admin.php';
        else:
            include VIEWS_PATH . '/partials/header-socio.php';
        endif;
    ?>

    <main class="container-fluid">
        <div class="row">
            <?php if (isset($showSidebar) && $showSidebar): ?>
                <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                    <?php
                        if (isAdmin()):
                            include VIEWS_PATH . '/partials/sidebar-admin.php';
                        else:
                            include VIEWS_PATH . '/partials/sidebar-socio.php';
                        endif;
                    ?>
                </nav>
            <?php endif; ?>

            <main class="<?php echo isset($showSidebar) && $showSidebar ? 'col-md-9 ms-sm-auto col-lg-10' : 'col-12' ?> px-md-4">
                <?php
                    // Mostrar mensajes flash
                    include VIEWS_PATH . '/partials/flash-messages.php';
                ?>

                <?php echo $content ?? '' ?>
            </main>
        </div>
    </main>

    <?php include VIEWS_PATH . '/partials/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <!-- Custom JS -->
    <script src="<?php echo BASE_URL ?>/public/assets/js/app.js"></script>

    <?php if (isset($extraScripts)): ?>
        <?php echo $extraScripts ?>
    <?php endif; ?>
</body>
</html>
