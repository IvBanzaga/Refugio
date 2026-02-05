<?php
    // Gestión de Reservas - Vista del Administrador
    $title       = 'Gestión de Reservas';
    $showSidebar = true;

    ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-check"></i> Gestión de Reservas</h2>
    <div>
        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalReservaSocio">
            <i class="bi bi-person-plus"></i> Nueva Reserva Socio
        </button>
        <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#modalReservaNoSocio">
            <i class="bi bi-person"></i> Nueva Reserva No Socio
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalReservaEspecial">
            <i class="bi bi-calendar-event"></i> Nueva Reserva Especial
        </button>
    </div>
</div>

<!-- Tabs de Navegación -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo($tab ?? 'pendientes') === 'pendientes' ? 'active' : '' ?>"
           href="?accion=reservas&tab=pendientes">
            Pendientes <span class="badge bg-warning text-dark"><?php echo $total_pendientes ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo($tab ?? '') === 'aprobadas' ? 'active' : '' ?>"
           href="?accion=reservas&tab=aprobadas">
            Aprobadas <span class="badge bg-success"><?php echo $total_aprobadas ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo($tab ?? '') === 'canceladas' ? 'active' : '' ?>"
           href="?accion=reservas&tab=canceladas">
            Canceladas <span class="badge bg-danger"><?php echo $total_canceladas ?? 0 ?></span>
        </a>
    </li>
</ul>

<?php if (($tab ?? 'pendientes') === 'pendientes'): ?>
    <!-- Reservas Pendientes -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Reservas Pendientes</h5>
            <div class="d-flex gap-2">
                <form class="d-flex gap-2" method="get">
                    <input type="hidden" name="accion" value="reservas">
                    <input type="hidden" name="tab" value="pendientes">
                    <input type="hidden" name="dir" value="ASC">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Buscar..." value="<?php echo htmlspecialchars($search ?? '') ?>">
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="fecha_inicio" <?php echo($sort ?? 'fecha_inicio') === 'fecha_inicio' ? 'selected' : '' ?>>
                            Fecha Entrada
                        </option>
                        <option value="fecha_creacion" <?php echo($sort ?? '') === 'fecha_creacion' ? 'selected' : '' ?>>
                            Fecha Solicitud
                        </option>
                        <option value="nombre" <?php echo($sort ?? '') === 'nombre' ? 'selected' : '' ?>>
                            Nombre
                        </option>
                    </select>
                    <button type="submit" class="btn btn-light btn-sm">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <form method="post" class="d-inline">
                    <input type="hidden" name="accion" value="export_csv">
                    <input type="hidden" name="tipo_reserva" value="pendiente">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? '') ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort ?? 'fecha_inicio') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                    </button>
                </form>
                <form method="post" class="d-inline">
                    <input type="hidden" name="accion" value="export_pdf">
                    <input type="hidden" name="tipo_reserva" value="pendiente">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? '') ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort ?? 'fecha_inicio') ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($reservas_pendientes ?? []) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?accion=reservas&tab=pendientes&sort=nombre&dir=<?php echo(($sort ?? '') === 'nombre' && ($order_dir ?? 'ASC') === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search ?? '') ?>"
                                       class="text-decoration-none text-dark">
                                        Usuario <?php if (($sort ?? '') === 'nombre') {
                                                        echo(($order_dir ?? 'ASC') === 'ASC' ? '▲' : '▼');
                                                    }
                                                ?>
                                    </a>
                                </th>
                                <th>Camas</th>
                                <th>
                                    <a href="?accion=reservas&tab=pendientes&sort=fecha_inicio&dir=<?php echo(($sort ?? 'fecha_inicio') === 'fecha_inicio' && ($order_dir ?? 'ASC') === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search ?? '') ?>"
                                       class="text-decoration-none text-dark">
                                        Entrada <?php if (($sort ?? 'fecha_inicio') === 'fecha_inicio') {
                                                        echo(($order_dir ?? 'ASC') === 'ASC' ? '▲' : '▼');
                                                    }
                                                ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?accion=reservas&tab=pendientes&sort=fecha_fin&dir=<?php echo(($sort ?? '') === 'fecha_fin' && ($order_dir ?? 'ASC') === 'ASC') ? 'DESC' : 'ASC' ?>&search=<?php echo urlencode($search ?? '') ?>"
                                       class="text-decoration-none text-dark">
                                        Salida <?php if (($sort ?? '') === 'fecha_fin') {
                                                       echo(($order_dir ?? 'ASC') === 'ASC' ? '▲' : '▼');
                                                   }
                                               ?>
                                    </a>
                                </th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas_pendientes as $reserva): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido1']) ?></strong><br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($reserva['email']) ?> |
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($reserva['telf'] ?? '') ?>
                                        </small>
                                    </td>
                                    <td><?php echo $reserva['numero_camas'] ?></td>
                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="accion" value="aprobar_reserva">
                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Aprobar">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="accion" value="rechazar_reserva">
                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Rechazar"
                                                    onclick="return confirm('¿Rechazar reserva?')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if (($paginas_pendientes ?? 1) > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $paginas_pendientes; $i++): ?>
                                <li class="page-item <?php echo($page ?? 1) === $i ? 'active' : '' ?>">
                                    <a class="page-link" href="?accion=reservas&tab=pendientes&page=<?php echo $i ?>&search=<?php echo urlencode($search ?? '') ?>&sort=<?php echo urlencode($sort ?? 'fecha_inicio') ?>&dir=ASC">
                                        <?php echo $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center text-muted py-4">No hay reservas pendientes</p>
            <?php endif; ?>
        </div>
    </div>

<?php elseif (($tab ?? '') === 'aprobadas'): ?>
    <!-- Reservas Aprobadas -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Reservas Aprobadas</h5>
            <div class="d-flex gap-2">
                <form class="d-flex gap-2" method="get">
                    <input type="hidden" name="accion" value="reservas">
                    <input type="hidden" name="tab" value="aprobadas">
                    <input type="hidden" name="dir" value="ASC">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Buscar..." value="<?php echo htmlspecialchars($search ?? '') ?>">
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="fecha_inicio" <?php echo($sort ?? 'fecha_inicio') === 'fecha_inicio' ? 'selected' : '' ?>>
                            Fecha Entrada
                        </option>
                        <option value="fecha_creacion" <?php echo($sort ?? '') === 'fecha_creacion' ? 'selected' : '' ?>>
                            Fecha Solicitud
                        </option>
                        <option value="nombre" <?php echo($sort ?? '') === 'nombre' ? 'selected' : '' ?>>
                            Nombre
                        </option>
                    </select>
                    <button type="submit" class="btn btn-light btn-sm">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <form method="post" class="d-inline">
                    <input type="hidden" name="accion" value="export_csv">
                    <input type="hidden" name="tipo_reserva" value="reservada">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? '') ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort ?? 'fecha_inicio') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                    </button>
                </form>
                <form method="post" class="d-inline">
                    <input type="hidden" name="accion" value="export_pdf">
                    <input type="hidden" name="tipo_reserva" value="reservada">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? '') ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort ?? 'fecha_inicio') ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <?php if (count($reservas_aprobadas ?? []) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Camas</th>
                                <th>Actividad</th>
                                <th>Montañero</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas_aprobadas as $reserva):
                                    $usuario_info = mostrar_usuario_reserva($reserva);
                            ?>
                                <tr>
                                    <td><strong><?php echo $usuario_info['display'] ?></strong></td>
                                    <td><?php echo $reserva['numero_camas'] ?></td>
                                    <td><?php echo $usuario_info['actividad'] ?></td>
                                    <td><?php echo $usuario_info['montanero'] ?></td>
                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning"
                                                onclick='editarReserva(<?php echo json_encode($reserva) ?>)'
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="accion" value="cancelar_reserva_admin">
                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Cancelar"
                                                    onclick="return confirm('¿Cancelar esta reserva?')">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if (($paginas_aprobadas ?? 1) > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $paginas_aprobadas; $i++): ?>
                                <li class="page-item <?php echo($page ?? 1) === $i ? 'active' : '' ?>">
                                    <a class="page-link" href="?accion=reservas&tab=aprobadas&page=<?php echo $i ?>&search=<?php echo urlencode($search ?? '') ?>&sort=<?php echo urlencode($sort ?? 'fecha_inicio') ?>&dir=ASC">
                                        <?php echo $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center text-muted py-4">No hay reservas aprobadas</p>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Reservas Canceladas -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Reservas Canceladas</h5>
        </div>
        <div class="card-body">
            <?php if (count($reservas_canceladas ?? []) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Camas</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Fecha Cancelación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas_canceladas as $reserva): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellido1']) ?></strong>
                                    </td>
                                    <td><?php echo $reserva['numero_camas'] ?></td>
                                    <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                    <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                    <td><?php echo formatear_fecha($reserva['fecha_cancelacion'] ?? $reserva['updated_at']) ?></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="accion" value="eliminar_reserva">
                                            <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('¿Eliminar permanentemente?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted py-4">No hay reservas canceladas</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
    $content = ob_get_clean();
    include VIEWS_PATH . '/layouts/app.php';
?>
