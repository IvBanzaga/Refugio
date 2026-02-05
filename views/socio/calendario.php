<?php
    // Calendario de Disponibilidad - Vista del Socio
    $title       = 'Calendario de Disponibilidad';
    $showSidebar = true;

    ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar3"></i> Calendario de Disponibilidad</h2>
    <a href="?accion=nueva_reserva" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Nueva Reserva
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-center">
            <a href="?accion=calendario&mes=<?php echo $mes_anterior ?? (date('n') - 1) ?>&anio=<?php echo $anio_anterior ?? date('Y') ?>"
               class="btn btn-sm btn-light">
                <i class="bi bi-chevron-left"></i>
            </a>
            <h5 class="mb-0">
                <?php echo mes_espanol($mes_actual ?? date('n')) ?> <?php echo $anio_actual ?? date('Y') ?>
            </h5>
            <a href="?accion=calendario&mes=<?php echo $mes_siguiente ?? (date('n') + 1) ?>&anio=<?php echo $anio_siguiente ?? date('Y') ?>"
               class="btn btn-sm btn-light">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Leyenda -->
        <div class="mb-3 d-flex gap-3 flex-wrap">
            <span><span class="badge" style="background:#22c55e">●</span> Muchas camas disponibles</span>
            <span><span class="badge bg-warning">●</span> Pocas camas disponibles</span>
            <span><span class="badge bg-danger">●</span> Sin camas disponibles</span>
            <span><span class="badge bg-primary">●</span> Mi reserva aprobada</span>
            <span><span class="badge bg-info">●</span> Mi reserva pendiente</span>
            <span><span class="badge bg-secondary">●</span> Día pasado</span>
        </div>

        <!-- Calendario -->
        <div class="calendario">
            <div class="dia-semana">Lun</div>
            <div class="dia-semana">Mar</div>
            <div class="dia-semana">Mié</div>
            <div class="dia-semana">Jue</div>
            <div class="dia-semana">Vie</div>
            <div class="dia-semana">Sáb</div>
            <div class="dia-semana">Dom</div>

            <?php
                // Espacios vacíos antes del primer día
            for ($i = 1; $i < ($dia_semana_inicio ?? 1); $i++): ?>
                <div class="dia-calendario vacio"></div>
            <?php endfor; ?>

            <?php
                // Días del mes
                $hoy = date('Y-m-d');
                for ($dia = 1; $dia <= ($dias_en_mes ?? cal_days_in_month(CAL_GREGORIAN, $mes_actual ?? date('n'), $anio_actual ?? date('Y'))); $dia++):
                    $fecha     = sprintf('%04d-%02d-%02d', $anio_actual ?? date('Y'), $mes_actual ?? date('n'), $dia);
                    $es_pasado = $fecha < $hoy;

                    // Verificar si el usuario tiene reserva en esta fecha
                    $stmt_mis_reservas = $conexionPDO->prepare("
	                    SELECT r.id, r.estado, h.numero as habitacion,
	                           GROUP_CONCAT(c.numero ORDER BY c.numero SEPARATOR ', ') as camas
	                    FROM reservas r
	                    JOIN habitaciones h ON r.id_habitacion = h.id
	                    LEFT JOIN reservas_camas rc ON r.id = rc.id_reserva
	                    LEFT JOIN camas c ON rc.id_cama = c.id
	                    WHERE r.id_usuario = :id_usuario
	                    AND :fecha BETWEEN r.fecha_inicio AND r.fecha_fin
	                    AND r.estado IN ('pendiente', 'reservada')
	                    GROUP BY r.id, r.estado, h.numero
	                ");
                    $stmt_mis_reservas->bindParam(':id_usuario', $_SESSION['userId'], PDO::PARAM_INT);
                    $stmt_mis_reservas->bindParam(':fecha', $fecha);
                    $stmt_mis_reservas->execute();
                    $mi_reserva = $stmt_mis_reservas->fetch(PDO::FETCH_ASSOC);

                    // Contar total de reservas aprobadas en esta fecha
                    $stmt_total_reservas = $conexionPDO->prepare("
	                    SELECT COUNT(*) as total
	                    FROM reservas
	                    WHERE :fecha BETWEEN fecha_inicio AND fecha_fin
	                    AND estado = 'reservada'
	                ");
                    $stmt_total_reservas->bindParam(':fecha', $fecha);
                    $stmt_total_reservas->execute();
                    $total_reservas_aprobadas = $stmt_total_reservas->fetchColumn();

                    // Obtener camas disponibles
                    $camas_libres = contar_camas_libres_por_fecha($conexionPDO, $fecha);
                    $total_camas  = MAX_CAMAS_HABITACION;

                    // Determinar clase CSS
                    $clase      = 'dia-calendario';
                    $info_extra = '';

                    if ($es_pasado) {
                        $clase .= ' pasado';
                    } elseif ($mi_reserva) {
                    // Usuario tiene reserva en esta fecha
                    if ($mi_reserva['estado'] === 'reservada') {
                        $clase      .= ' mi-reserva-aprobada';
                        $info_extra  = "Hab. {$mi_reserva['habitacion']}, Camas {$mi_reserva['camas']}";
                    } else {
                        $clase      .= ' mi-reserva-pendiente';
                        $info_extra  = "Pendiente - Hab. {$mi_reserva['habitacion']}, Camas {$mi_reserva['camas']}";
                    }
                } elseif ($camas_libres === 0) {
                    $clase .= ' lleno';
                } elseif ($camas_libres < 5) {
                    $clase .= ' pocas-camas';
                }
            ?>
                <div class="<?php echo $clase ?>" data-fecha="<?php echo $fecha ?>"
                    <?php if ($mi_reserva): ?>
                        title="<?php echo $info_extra ?>"
                    <?php elseif (! $es_pasado && $camas_libres > 0): ?>
                        onclick="irAReserva('<?php echo $fecha ?>')"
                        style="cursor: pointer;"
                        title="Click para reservar este día"
                    <?php endif; ?>>
                    <div class="numero-dia"><?php echo $dia ?></div>
                    <?php if (! $es_pasado): ?>
                        <?php if ($mi_reserva): ?>
                            <div class="camas-disponibles">
                                <small><strong><?php echo $info_extra ?></strong></small>
                            </div>
                        <?php else: ?>
                            <div class="camas-disponibles">
                                <span class="texto-libres"><?php echo $camas_libres ?>/<?php echo $total_camas ?></span>
                                <small class="d-block">libres</small>
                                <?php if ($total_reservas_aprobadas > 0): ?>
                                    <small class="d-block text-muted">
                                        <?php echo $total_reservas_aprobadas ?> reserva<?php echo $total_reservas_aprobadas > 1 ? 's' : '' ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<script>
function irAReserva(fecha) {
    window.location.href = '?accion=nueva_reserva&fecha=' + fecha;
}
</script>

<?php
    $content  = ob_get_clean();
    include VIEWS_PATH . '/layouts/app.php';
?>
