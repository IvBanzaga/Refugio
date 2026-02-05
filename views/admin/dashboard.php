<?php
    // Dashboard - Vista Principal del Administrador
    $title       = 'Dashboard';
    $showSidebar = true;

    ob_start();
?>

<!-- Botones de acción rápida -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
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

<!-- Tarjetas de estadísticas -->
<div class="row mb-4">
    <!-- Reservas Pendientes -->
    <div class="col-md-3">
        <a href="?accion=reservas&tab=pendientes" class="text-decoration-none">
            <div class="card card-stat warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Reservas Pendientes</h6>
                            <h2><?php echo count($reservas_pendientes ?? []) ?></h2>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Reservas Aprobadas -->
    <div class="col-md-3">
        <a href="?accion=reservas&tab=aprobadas" class="text-decoration-none">
            <div class="card card-stat success shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Reservas Aprobadas</h6>
                            <h2><?php echo $reservas_aprobadas_count ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Reservas Canceladas -->
    <div class="col-md-3">
        <a href="?accion=reservas&tab=canceladas" class="text-decoration-none">
            <div class="card card-stat danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted">Reservas Canceladas</h6>
                            <h2><?php echo $reservas_canceladas_count ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-x-circle-fill fs-1 text-danger"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Total Camas -->
    <div class="col-md-3">
        <div class="card card-stat primary shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted">Total Camas</h6>
                        <h2><?php echo MAX_CAMAS_HABITACION ?></h2>
                    </div>
                    <i class="bi bi-grid-3x3-gap-fill fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendario de Reservas -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-calendar3"></i> Calendario de Reservas</h5>
    </div>
    <div class="card-body">
        <!-- Navegación del calendario -->
        <div class="nav-calendario">
            <a href="?accion=dashboard&mes=<?php echo $mes_anterior ?>&anio=<?php echo $anio_anterior ?>"
               class="btn btn-outline-primary">
                <i class="bi bi-chevron-left"></i> Anterior
            </a>
            <h4><?php echo mes_espanol($mes_actual) . ' ' . $anio_actual ?></h4>
            <a href="?accion=dashboard&mes=<?php echo $mes_siguiente ?>&anio=<?php echo $anio_siguiente ?>"
               class="btn btn-outline-primary">
                Siguiente <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <!-- Leyenda -->
        <div class="leyenda-calendario">
            <div class="leyenda-item">
                <div class="leyenda-color" style="background: #fffbeb; border-color: #fbbf24;"></div>
                <span>Con reservas pendientes</span>
            </div>
            <div class="leyenda-item">
                <div class="leyenda-color" style="background: #ecfdf5; border-color: #10b981;"></div>
                <span>Con reservas aprobadas</span>
            </div>
            <div class="leyenda-item">
                <div class="leyenda-color" style="background: linear-gradient(135deg, #fffbeb 50%, #ecfdf5 50%); border-color: #3b82f6;"></div>
                <span>Mixto (pendientes y aprobadas)</span>
            </div>
            <div class="leyenda-item">
                <div class="leyenda-color" style="background: white; border-color: #e5e7eb;"></div>
                <span>Sin reservas</span>
            </div>
        </div>

        <!-- Días de la semana -->
        <div class="calendario mb-2">
            <div class="dia-semana">L</div>
            <div class="dia-semana">M</div>
            <div class="dia-semana">X</div>
            <div class="dia-semana">J</div>
            <div class="dia-semana">V</div>
            <div class="dia-semana">S</div>
            <div class="dia-semana">D</div>
        </div>

        <!-- Días del mes -->
        <div class="calendario">
            <?php
                // Celdas vacías antes del primer día
                for ($i = 1; $i < $dia_semana_inicio; $i++) {
                    echo '<div class="dia-calendario vacio"></div>';
                }

                // Días del mes
                $hoy = date('Y-m-d');
                for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
                    $fecha_actual = sprintf('%04d-%02d-%02d', $anio_actual, $mes_actual, $dia);
                    $es_pasado    = $fecha_actual < $hoy;

                    // Contar reservas para este día
                    $stmt = $conexionPDO->prepare("
                    SELECT estado, COUNT(*) as total
                    FROM reservas
                    WHERE :fecha BETWEEN fecha_inicio AND fecha_fin
                    GROUP BY estado
                ");
                    $stmt->bindParam(':fecha', $fecha_actual);
                    $stmt->execute();
                    $reservas_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $pendientes = 0;
                    $aprobadas  = 0;
                    foreach ($reservas_dia as $r) {
                        if ($r['estado'] === 'pendiente') {
                            $pendientes = $r['total'];
                        }
                        if ($r['estado'] === 'reservada') {
                            $aprobadas = $r['total'];
                        }
                    }

                    // Contar camas libres para este día
                    $camas_libres   = contar_camas_libres_por_fecha($conexionPDO, $fecha_actual);
                    $total_camas    = contar_total_camas($conexionPDO);
                    $camas_ocupadas = $total_camas - $camas_libres;

                    // Determinar clase CSS
                    $clase = 'dia-calendario';
                    if ($es_pasado) {
                        $clase .= ' pasado';
                    } elseif ($pendientes > 0 && $aprobadas > 0) {
                        $clase .= ' mixto';
                    } elseif ($pendientes > 0) {
                        $clase .= ' con-pendientes';
                    } elseif ($aprobadas > 0) {
                        $clase .= ' con-aprobadas';
                    }

                    echo "<div class='$clase'>";
                    echo "<div class='numero-dia'>$dia</div>";

                    if (! $es_pasado) {
                        echo "<div class='info-reservas'>";

                        // Mostrar camas disponibles
                        if ($camas_libres === 0) {
                            echo "<div class='camas-info text-danger mb-1'><strong>Completo</strong></div>";
                        } else {
                            $color_camas = $camas_libres < 5 ? 'text-warning' : 'text-success';
                            echo "<div class='camas-info {$color_camas} mb-1'><i class='bi bi-door-open'></i> <strong>{$camas_libres}/{$total_camas}</strong> libres</div>";
                        }

                        // Mostrar reservas pendientes y aprobadas
                        if ($pendientes > 0) {
                            echo "<span class='badge bg-warning text-dark'>$pendientes pendiente" . ($pendientes > 1 ? 's' : '') . "</span> ";
                        }
                        if ($aprobadas > 0) {
                            echo "<span class='badge bg-success'>$aprobadas aprobada" . ($aprobadas > 1 ? 's' : '') . "</span>";
                        }

                        echo "</div>";
                    }

                    echo '</div>';
                }
            ?>
        </div>
    </div>
</div>

<?php
    $content = ob_get_clean();
    include VIEWS_PATH . '/layouts/app.php';
?>
