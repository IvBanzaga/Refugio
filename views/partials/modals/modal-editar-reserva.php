<!-- Modal para Editar Reserva -->
<div class="modal fade" id="modalEditarReserva" tabindex="-1" aria-labelledby="modalEditarReservaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalEditarReservaLabel">
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar Reserva
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarReserva" method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar_reserva">
                    <input type="hidden" name="id_reserva" id="editarReservaId" value="">

                    <!-- Información del Usuario (Solo Lectura) -->
                    <div class="alert alert-light border">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Usuario:</strong>
                                <span id="editarReservaUsuarioNombre"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Estado Actual:</strong>
                                <span id="editarReservaEstadoActual" class="badge"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editarReservaFechaEntrada" class="form-label">Fecha Entrada *</label>
                            <input type="date" class="form-control" id="editarReservaFechaEntrada"
                                   name="fecha_entrada" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editarReservaFechaSalida" class="form-label">Fecha Salida *</label>
                            <input type="date" class="form-control" id="editarReservaFechaSalida"
                                   name="fecha_salida" required>
                        </div>
                    </div>

                    <!-- Número de Camas -->
                    <div class="mb-3">
                        <label for="editarReservaNumeroCamas" class="form-label">Número de Camas *</label>
                        <select class="form-select" id="editarReservaNumeroCamas" name="numero_camas" required>
                            <option value="">Seleccione cantidad</option>
                            <option value="1">1 cama</option>
                            <option value="2">2 camas</option>
                            <option value="3">3 camas</option>
                            <option value="4">4 camas</option>
                            <option value="5">5 camas</option>
                            <option value="6">6 camas</option>
                            <option value="7">7 camas</option>
                            <option value="8">8 camas</option>
                            <option value="9">9 camas</option>
                            <option value="10">10 camas</option>
                        </select>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Se reasignarán las camas automáticamente si es necesario
                        </div>
                    </div>

                    <!-- Actividad -->
                    <div class="mb-3">
                        <label for="editarReservaActividad" class="form-label">Actividad *</label>
                        <textarea class="form-control" id="editarReservaActividad" name="actividad"
                                  rows="3" required minlength="10"></textarea>
                        <div class="form-text">Mínimo 10 caracteres</div>
                    </div>

                    <!-- Estado (Solo Admin) -->
                    <div class="mb-3" id="editarReservaEstadoGroup">
                        <label for="editarReservaEstado" class="form-label">Estado *</label>
                        <select class="form-select" id="editarReservaEstado" name="estado">
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobada">Aprobada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                        <div class="form-text">
                            <i class="bi bi-shield-lock me-1"></i>
                            Solo administradores pueden cambiar el estado
                        </div>
                    </div>

                    <!-- Habitación Asignada (Solo Lectura) -->
                    <div class="mb-3" id="editarReservaHabitacionGroup">
                        <label class="form-label">Habitación Asignada</label>
                        <input type="text" class="form-control" id="editarReservaHabitacion" readonly disabled>
                        <div class="form-text">La habitación se asignará automáticamente según disponibilidad</div>
                    </div>

                    <!-- Camas Asignadas (Solo Lectura) -->
                    <div class="mb-3" id="editarReservaCamasGroup">
                        <label class="form-label">Camas Asignadas</label>
                        <input type="text" class="form-control" id="editarReservaCamas" readonly disabled>
                    </div>

                    <!-- Notas -->
                    <div class="mb-3">
                        <label for="editarReservaNotas" class="form-label">Notas</label>
                        <textarea class="form-control" id="editarReservaNotas" name="notas"
                                  rows="2"></textarea>
                    </div>

                    <!-- Advertencia sobre disponibilidad -->
                    <div class="alert alert-warning" id="editarReservaAdvertencia" style="display: none;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Atención:</strong> Asegúrese de que hay disponibilidad para las nuevas fechas
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="bi bi-check-circle me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función para abrir el modal de edición
function abrirModalEditarReserva(reserva, esAdmin = false) {
    // Establecer action según el rol
    const form = document.getElementById('formEditarReserva');
    form.action = esAdmin ? 'viewAdminMVC.php' : 'viewSocioMVC.php';

    // Cargar datos de la reserva
    document.getElementById('editarReservaId').value = reserva.id;
    document.getElementById('editarReservaUsuarioNombre').textContent =
        reserva.usuario_nombre || 'Usuario';

    // Estado actual con badge
    const estadoBadge = document.getElementById('editarReservaEstadoActual');
    estadoBadge.textContent = reserva.estado;
    estadoBadge.className = 'badge ';
    switch(reserva.estado) {
        case 'aprobada':
            estadoBadge.classList.add('bg-success');
            break;
        case 'pendiente':
            estadoBadge.classList.add('bg-warning', 'text-dark');
            break;
        case 'cancelada':
            estadoBadge.classList.add('bg-secondary');
            break;
    }

    // Fechas
    document.getElementById('editarReservaFechaEntrada').value = reserva.fecha_entrada;
    document.getElementById('editarReservaFechaSalida').value = reserva.fecha_salida;

    // Camas
    document.getElementById('editarReservaNumeroCamas').value = reserva.numero_camas;

    // Actividad
    document.getElementById('editarReservaActividad').value = reserva.actividad || '';

    // Estado (solo visible para admin)
    const estadoGroup = document.getElementById('editarReservaEstadoGroup');
    if (esAdmin) {
        estadoGroup.style.display = 'block';
        document.getElementById('editarReservaEstado').value = reserva.estado;
    } else {
        estadoGroup.style.display = 'none';
    }

    // Habitación y camas asignadas
    if (reserva.habitacion_numero) {
        document.getElementById('editarReservaHabitacion').value =
            'Habitación ' + reserva.habitacion_numero;
        document.getElementById('editarReservaHabitacionGroup').style.display = 'block';
    } else {
        document.getElementById('editarReservaHabitacionGroup').style.display = 'none';
    }

    if (reserva.camas_asignadas) {
        document.getElementById('editarReservaCamas').value = reserva.camas_asignadas;
        document.getElementById('editarReservaCamasGroup').style.display = 'block';
    } else {
        document.getElementById('editarReservaCamasGroup').style.display = 'none';
    }

    // Notas
    document.getElementById('editarReservaNotas').value = reserva.notas || '';

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalEditarReserva'));
    modal.show();
}

// Validación de fechas
document.getElementById('editarReservaFechaEntrada').addEventListener('change', function() {
    const fechaSalida = document.getElementById('editarReservaFechaSalida');
    fechaSalida.min = this.value;

    if (fechaSalida.value && fechaSalida.value <= this.value) {
        document.getElementById('editarReservaAdvertencia').style.display = 'block';
    }
});

document.getElementById('editarReservaFechaSalida').addEventListener('change', function() {
    const fechaEntrada = document.getElementById('editarReservaFechaEntrada');

    if (this.value && this.value <= fechaEntrada.value) {
        document.getElementById('editarReservaAdvertencia').style.display = 'block';
    } else {
        document.getElementById('editarReservaAdvertencia').style.display = 'none';
    }
});

// Validación del formulario
document.getElementById('formEditarReserva').addEventListener('submit', function(e) {
    const actividad = document.getElementById('editarReservaActividad').value;
    const fechaEntrada = document.getElementById('editarReservaFechaEntrada').value;
    const fechaSalida = document.getElementById('editarReservaFechaSalida').value;

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
</script>
