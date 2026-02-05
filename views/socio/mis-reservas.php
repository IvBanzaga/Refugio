<?php
    // Mis Reservas - Vista del Socio
    $title       = 'Mis Reservas';
    $showSidebar = true;

    ob_start();

    // Filtrar reservas por estado
    $pendientes = array_filter($mis_reservas ?? [], fn($r) => $r['estado'] === 'pendiente');
    $aprobadas  = array_filter($mis_reservas ?? [], fn($r) => $r['estado'] === 'reservada');
    $canceladas = array_filter($mis_reservas ?? [], fn($r) => $r['estado'] === 'cancelada');
?>

<h2 class="mb-4"><i class="bi bi-list-check"></i> Mis Reservas</h2>

<!-- Reservas Pendientes -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-hourglass-split"></i> Pendientes de Aprobación</h5>
    </div>
    <div class="card-body">
        <?php if (count($pendientes) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nº Camas</th>
                            <th>Fecha Entrada</th>
                            <th>Fecha Salida</th>
                            <th>Solicitado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendientes as $reserva): ?>
                            <tr>
                                <td><?php echo $reserva['numero_camas'] ?> cama(s)</td>
                                <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_creacion'])) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary me-1"
                                        onclick="editarReservaUsuario(<?php echo htmlspecialchars(json_encode($reserva)) ?>)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="cancelar_reserva">
                                        <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Cancelar esta reserva?')">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted py-3">No tienes reservas pendientes</p>
        <?php endif; ?>
    </div>
</div>

<!-- Reservas Aprobadas -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> Reservas Aprobadas</h5>
    </div>
    <div class="card-body">
        <?php if (count($aprobadas) > 0): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Puedes editar o anular tus reservas aprobadas que aún no han comenzado. La anulación no se puede deshacer.
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nº Camas</th>
                            <th>Fecha Entrada</th>
                            <th>Fecha Salida</th>
                            <th>Días</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aprobadas as $reserva):
                                $dias         = (strtotime($reserva['fecha_fin']) - strtotime($reserva['fecha_inicio'])) / 86400;
                                $puede_editar = strtotime($reserva['fecha_inicio']) > strtotime(date('Y-m-d'));
                        ?>
                            <tr>
                                <td><?php echo $reserva['numero_camas'] ?> cama(s)</td>
                                <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                                <td><?php echo $dias ?> día<?php echo $dias > 1 ? 's' : '' ?></td>
                                <td>
                                    <?php if ($puede_editar): ?>
                                        <button type="button" class="btn btn-sm btn-primary me-1"
                                            onclick="editarReservaUsuario(<?php echo htmlspecialchars(json_encode($reserva)) ?>)">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                    <?php endif; ?>
                                    <form method="post" class="d-inline" onsubmit="return confirmarAnulacion()">
                                        <input type="hidden" name="accion" value="cancelar_reserva">
                                        <input type="hidden" name="id" value="<?php echo $reserva['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-x-circle"></i> Anular
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted py-3">No tienes reservas aprobadas</p>
        <?php endif; ?>
    </div>
</div>

<!-- Reservas Canceladas -->
<?php if (count($canceladas) > 0): ?>
    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-x-circle-fill"></i> Reservas Canceladas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nº Camas</th>
                            <th>Fecha Entrada</th>
                            <th>Fecha Salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($canceladas as $reserva): ?>
                            <tr>
                                <td><?php echo $reserva['numero_camas'] ?> cama(s)</td>
                                <td><?php echo formatear_fecha($reserva['fecha_inicio']) ?></td>
                                <td><?php echo formatear_fecha($reserva['fecha_fin']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function editarReservaUsuario(reserva) {
    // Implementar lógica de edición
    console.log('Editar reserva:', reserva);
    // TODO: Abrir modal de edición
}

function confirmarAnulacion() {
    return confirm('⚠️ IMPORTANTE:\n\nEsta acción anulará tu reserva de forma permanente.\n\n¿Estás seguro de que deseas continuar?');
}
</script>

<?php
    $content = ob_get_clean();
    include VIEWS_PATH . '/layouts/app.php';
?>
