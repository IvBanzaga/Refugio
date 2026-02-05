<!-- Modal para Reserva de No Socio -->
<div class="modal fade" id="modalReservaNoSocio" tabindex="-1" aria-labelledby="modalReservaNoSocioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalReservaNoSocioLabel">
                    <i class="bi bi-person-plus me-2"></i>
                    Nueva Reserva - No Socio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReservaNoSocio" method="POST" action="viewAdminMVC.php">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear_reserva_no_socio">

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Los datos de no socios se guardan en el campo de notas en formato estructurado
                    </div>

                    <!-- Datos del No Socio -->
                    <h6 class="border-bottom pb-2 mb-3">Datos del Solicitante</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="noSocioNombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="noSocioNombre"
                                   name="no_socio_nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label for="noSocioApellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="noSocioApellidos"
                                   name="no_socio_apellidos" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="noSocioDNI" class="form-label">DNI/NIE *</label>
                            <input type="text" class="form-control" id="noSocioDNI"
                                   name="no_socio_dni" required
                                   pattern="[0-9]{8}[A-Za-z]|[XYZ][0-9]{7}[A-Za-z]">
                            <div class="form-text">Formato: 12345678A o X1234567A</div>
                        </div>
                        <div class="col-md-6">
                            <label for="noSocioEmail" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="noSocioEmail"
                                   name="no_socio_email" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="noSocioTelefono" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="noSocioTelefono"
                                   name="no_socio_telefono" required pattern="[0-9]{9}">
                            <div class="form-text">9 dígitos</div>
                        </div>
                        <div class="col-md-6">
                            <label for="noSocioClub" class="form-label">Club/Federación</label>
                            <input type="text" class="form-control" id="noSocioClub"
                                   name="no_socio_club"
                                   placeholder="Opcional">
                        </div>
                    </div>

                    <!-- Datos de la Reserva -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Datos de la Reserva</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="noSocioFechaEntrada" class="form-label">Fecha Entrada *</label>
                            <input type="date" class="form-control" id="noSocioFechaEntrada"
                                   name="fecha_entrada" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="noSocioFechaSalida" class="form-label">Fecha Salida *</label>
                            <input type="date" class="form-control" id="noSocioFechaSalida"
                                   name="fecha_salida" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="noSocioNumeroCamas" class="form-label">Número de Camas *</label>
                            <select class="form-select" id="noSocioNumeroCamas" name="numero_camas" required>
                                <option value="">Seleccione cantidad</option>
                                <option value="1">1 cama</option>
                                <option value="2">2 camas</option>
                                <option value="3">3 camas</option>
                                <option value="4">4 camas</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="noSocioEstado" class="form-label">Estado *</label>
                            <select class="form-select" id="noSocioEstado" name="estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobada">Aprobada</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="noSocioActividad" class="form-label">Actividad *</label>
                        <textarea class="form-control" id="noSocioActividad" name="actividad"
                                  rows="3" required minlength="10"
                                  placeholder="Describa la actividad (senderismo, escalada, etc.)"></textarea>
                        <div class="form-text">Mínimo 10 caracteres</div>
                    </div>

                    <div class="mb-3">
                        <label for="noSocioNotas" class="form-label">Notas Adicionales</label>
                        <textarea class="form-control" id="noSocioNotas" name="notas_adicionales"
                                  rows="2" placeholder="Información extra (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-2"></i>Crear Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validación de fechas para no socio
document.getElementById('noSocioFechaEntrada').addEventListener('change', function() {
    const fechaSalida = document.getElementById('noSocioFechaSalida');
    fechaSalida.min = this.value;

    if (fechaSalida.value && fechaSalida.value <= this.value) {
        fechaSalida.value = '';
    }
});

// Validación del formulario
document.getElementById('formReservaNoSocio').addEventListener('submit', function(e) {
    const actividad = document.getElementById('noSocioActividad').value;
    const fechaEntrada = document.getElementById('noSocioFechaEntrada').value;
    const fechaSalida = document.getElementById('noSocioFechaSalida').value;
    const telefono = document.getElementById('noSocioTelefono').value;

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

    if (!/^[0-9]{9}$/.test(telefono)) {
        e.preventDefault();
        alert('El teléfono debe tener 9 dígitos.');
        return false;
    }
});

function abrirModalReservaNoSocio() {
    document.getElementById('formReservaNoSocio').reset();
    const modal = new bootstrap.Modal(document.getElementById('modalReservaNoSocio'));
    modal.show();
}
</script>
