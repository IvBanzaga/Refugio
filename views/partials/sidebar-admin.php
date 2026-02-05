<div class="position-sticky pt-3">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo (! isset($_GET['accion']) || $_GET['accion'] === 'dashboard') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewAdmin.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['accion']) && $_GET['accion'] === 'reservas') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewAdmin.php?accion=reservas">
                <i class="bi bi-calendar-check"></i> Reservas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['accion']) && $_GET['accion'] === 'usuarios') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewAdmin.php?accion=usuarios">
                <i class="bi bi-people"></i> Usuarios
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['accion']) && $_GET['accion'] === 'calendario') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewAdmin.php?accion=calendario">
                <i class="bi bi-calendar3"></i> Calendario
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['accion']) && $_GET['accion'] === 'estadisticas') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewAdmin.php?accion=estadisticas">
                <i class="bi bi-graph-up"></i> Estadísticas
            </a>
        </li>
    </ul>

    <hr>

    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
        Acciones Rápidas
    </h6>
    <ul class="nav flex-column mb-2">
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalReservaSocio">
                <i class="bi bi-plus-circle"></i> Nueva Reserva Socio
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalReservaEspecial">
                <i class="bi bi-calendar-event"></i> Reserva Especial
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                <i class="bi bi-person-plus"></i> Nuevo Usuario
            </a>
        </li>
    </ul>
</div>
