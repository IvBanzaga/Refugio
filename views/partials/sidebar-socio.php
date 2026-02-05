<div class="position-sticky pt-3">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo (! isset($_GET['accion']) || $_GET['accion'] === 'dashboard') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewSocio.php">
                <i class="bi bi-house"></i> Inicio
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['accion']) && $_GET['accion'] === 'mis_reservas') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewSocio.php?accion=mis_reservas">
                <i class="bi bi-calendar-check"></i> Mis Reservas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['accion']) && $_GET['accion'] === 'nueva_reserva') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewSocio.php?accion=nueva_reserva">
                <i class="bi bi-plus-circle"></i> Nueva Reserva
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['accion']) && $_GET['accion'] === 'calendario') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewSocio.php?accion=calendario">
                <i class="bi bi-calendar3"></i> Disponibilidad
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['accion']) && $_GET['accion'] === 'perfil') ? 'active' : '' ?>"
               href="<?php echo BASE_URL ?>/viewSocio.php?accion=perfil">
                <i class="bi bi-person"></i> Mi Perfil
            </a>
        </li>
    </ul>

    <hr>

    <div class="px-3">
        <h6 class="text-muted">Información</h6>
        <ul class="list-unstyled">
            <li><small><i class="bi bi-info-circle"></i> Capacidad: 26 camas</small></li>
            <li><small><i class="bi bi-geo-alt"></i> Ubicación: Montaña</small></li>
        </ul>
    </div>
</div>
