<?php
    // Mostrar mensajes flash de sesión
    $mensaje     = $_SESSION['mensaje'] ?? null;
    $tipoMensaje = $_SESSION['tipo_mensaje'] ?? 'info';
    $error       = $_SESSION['error'] ?? null;

    // Limpiar mensajes después de mostrarlos
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje'], $_SESSION['error']);
?>

<?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipoMensaje ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
