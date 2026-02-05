<!-- Modal para Reserva de Socio -->
<div class="modal fade" id="modalReservaSocio" tabindex="-1" aria-labelledby="modalReservaSocioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalReservaSocioLabel">
                    <i class="bi bi-calendar-plus me-2"></i>
                    Nueva Reserva - Socio
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReservaSocio" method="POST" action="viewAdminMVC.php">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear_reserva_socio">

                    <!-- Socio -->
                    <div class="mb-3">
                        <label for="reservaSocioUsuario" class="form-label">Socio *</label>
                        <select class="form-select" id="reservaSocioUsuario" name="id_usuario" required>
                            <option value="">Seleccione un socio</option>
                            <?php if (isset($usuarios) && is_array($usuarios)): ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <?php if ($u['rol'] === 'user'): ?>
                                        <option value="<?php echo htmlspecialchars($u['id']); ?>">
                                            <?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']); ?>
                                            <?php if (! empty($u['num_socio'])): ?>
                                                - Socio Nº <?php echo htmlspecialchars($u['num_socio']); ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Fechas -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reservaSocioFechaEntrada" class="form-label">Fecha Entrada *</label>
                            <input type="date" class="form-control" id="reservaSocioFechaEntrada"
                                   name="fecha_entrada" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="reservaSocioFechaSalida" class="form-label">Fecha Salida *</label>
                            <input type="date" class="form-control" id="reservaSocioFechaSalida"
                                   name="fecha_salida" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Número de Camas -->
                    <div class="mb-3">
                        <label for="reservaSocioNumeroCamas" class="form-label">Número de Camas *</label>
                        <select class="form-select" id="reservaSocioNumeroCamas" name="numero_camas" required>
                            <option value="">Seleccione cantidad</option>
                            <option value="1">1 cama</option>
                            <option value="2">2 camas</option>
                            <option value="3">3 camas</option>
                            <option value="4">4 camas</option>
                        </select>
                    </div>

                    <!-- Actividad -->
                    <div class="mb-3">
                        <label for="reservaSocioActividad" class="form-label">Actividad *</label>
                        <textarea class="form-control" id="reservaSocioActividad" name="actividad"
                                  rows="3" required minlength="10"
                                  placeholder="Describa la actividad (senderismo, escalada, etc.)"></textarea>
                        <div class="form-text">Mínimo 10 caracteres</div>
                    </div>

                    <!-- Estado (solo admin puede modificar) -->
                    <div class="mb-3">
                        <label for="reservaSocioEstado" class="form-label">Estado *</label>
                        <select class="form-select" id="reservaSocioEstado" name="estado">
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobada">Aprobada</option>
                        </select>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Como admin, puede aprobar directamente la reserva
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-3">
                        <label for="reservaSocioNotas" class="form-label">Notas (opcional)</label>
                        <textarea class="form-control" id="reservaSocioNotas" name="notas"
                                  rows="2" placeholder="Información adicional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Crear Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación de fechas para reserva de socio
document.getElementById('reservaSocioFechaEntrada').addEventListener('change', function() {
    const fechaSalida = document.getElementById('reservaSocioFechaSalida');
    fechaSalida.min = this.value;

    if (fechaSalida.value && fechaSalida.value <= this.value) {
        fechaSalida.value = '';
    }
});

// Validación del formulario
document.getElementById('formReservaSocio').addEventListener('submit', function(e) {
    const actividad = document.getElementById('reservaSocioActividad').value;
    const fechaEntrada = document.getElementById('reservaSocioFechaEntrada').value;
    const fechaSalida = document.getElementById('reservaSocioFechaSalida').value;

    if (actividad.length < 10) {
        e.preventDefault();
        alert('La actividad debe tener al menos 10 caracteres.');
        return false;
    }

    if (fechaSalida <= fechaEntrada) {
        e.preventDefault();
        alert('La fecha de salida debe ser posterior a la fecha de entrada.');
        return false;
    }
});

function abrirModalReservaSocio() {
    document.getElementById('formReservaSocio').reset();
    const modal = new bootstrap.Modal(document.getElementById('modalReservaSocio'));
    modal.show();
}
</script>
