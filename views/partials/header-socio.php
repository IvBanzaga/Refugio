<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo BASE_URL ?>/viewSocio.php">
            <i class="bi bi-house-heart-fill"></i> <?php echo REFUGIO_NAME ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <?php if (! empty($_SESSION['foto'])): ?>
                            <img src="<?php echo BASE_URL ?>/public/uploads/<?php echo htmlspecialchars($_SESSION['foto']) ?>"
                                 class="rounded-circle" width="32" height="32" alt="Foto">
                        <?php else: ?>
                            <i class="bi bi-person-circle"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?>
                        <small class="text-muted">(<?php echo htmlspecialchars($_SESSION['num_socio'] ?? '') ?>)</small>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL ?>/viewSocio.php?accion=perfil">
                            <i class="bi bi-person"></i> Mi Perfil
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL ?>/viewSocio.php?accion=mis_reservas">
                            <i class="bi bi-calendar-check"></i> Mis Reservas
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL ?>/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
