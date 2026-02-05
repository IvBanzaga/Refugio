<!-- Modal para Reserva Especial -->
<div class="modal fade" id="modalReservaEspecial" tabindex="-1" aria-labelledby="modalReservaEspecialLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalReservaEspecialLabel">
                    <i class="bi bi-star-fill me-2"></i>
                    Nueva Reserva Especial
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReservaEspecial" method="POST" action="viewAdminMVC.php">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear_reserva_especial">

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Reserva Especial:</strong> Para refugio completo, grupos grandes o GMT
                    </div>

                    <!-- Tipo de Reserva Especial -->
                    <div class="mb-3">
                        <label for="tipoEspecial" class="form-label">Tipo de Reserva *</label>
                        <select class="form-select" id="tipoEspecial" name="tipo_especial" required>
                            <option value="">Seleccione tipo</option>
                            <option value="refugio_completo">Refugio Completo</option>
                            <option value="gmt">Grupo de Montaña (GMT)</option>
                            <option value="grupo_grande">Grupo Grande (&gt;10 personas)</option>
                        </select>
                    </div>

                    <!-- Socio Responsable -->
                    <div class="mb-3">
                        <label for="especialUsuario" class="form-label">Socio Responsable *</label>
                        <select class="form-select" id="especialUsuario" name="id_usuario" required>
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
                        <div class="form-text">Persona responsable del grupo</div>
                    </div>

                    <!-- Fechas -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="especialFechaEntrada" class="form-label">Fecha Entrada *</label>
                            <input type="date" class="form-control" id="especialFechaEntrada"
                                   name="fecha_entrada" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="especialFechaSalida" class="form-label">Fecha Salida *</label>
                            <input type="date" class="form-control" id="especialFechaSalida"
                                   name="fecha_salida" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Número de Personas -->
                    <div class="mb-3">
                        <label for="especialNumeroCamas" class="form-label">Número de Personas *</label>
                        <input type="number" class="form-control" id="especialNumeroCamas"
                               name="numero_camas" min="1" max="50" required>
                        <div class="form-text">Indique el número total de personas del grupo</div>
                    </div>

                    <!-- Nombre del Grupo -->
                    <div class="mb-3">
                        <label for="especialNombreGrupo" class="form-label">Nombre del Grupo/Evento *</label>
                        <input type="text" class="form-control" id="especialNombreGrupo"
                               name="nombre_grupo" required
                               placeholder="Ej: Club Montañero Valladolid, Curso de Alpinismo, etc.">
                    </div>

                    <!-- Actividad -->
                    <div class="mb-3">
                        <label for="especialActividad" class="form-label">Descripción de la Actividad *</label>
                        <textarea class="form-control" id="especialActividad" name="actividad"
                                  rows="3" required minlength="20"
                                  placeholder="Describa detalladamente la actividad planificada"></textarea>
                        <div class="form-text">Mínimo 20 caracteres</div>
                    </div>

                    <!-- Información de Contacto Adicional -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="especialContactoNombre" class="form-label">Contacto Adicional</label>
                            <input type="text" class="form-control" id="especialContactoNombre"
                                   name="contacto_nombre"
                                   placeholder="Nombre del contacto secundario">
                        </div>
                        <div class="col-md-6">
                            <label for="especialContactoTelefono" class="form-label">Teléfono de Contacto</label>
                            <input type="tel" class="form-control" id="especialContactoTelefono"
                                   name="contacto_telefono" pattern="[0-9]{9}"
                                   placeholder="9 dígitos">
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label for="especialEstado" class="form-label">Estado *</label>
                        <select class="form-select" id="especialEstado" name="estado">
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobada">Aprobada</option>
                        </select>
                        <div class="form-text">Las reservas especiales requieren aprobación especial</div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-3">
                        <label for="especialNotas" class="form-label">Notas y Requisitos Especiales</label>
                        <textarea class="form-control" id="especialNotas" name="notas"
                                  rows="3" placeholder="Indique cualquier requisito o información relevante"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle me-2"></i>Crear Reserva Especial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Actualizar placeholder según tipo de reserva especial
document.getElementById('tipoEspecial').addEventListener('change', function() {
    const nombreGrupo = document.getElementById('especialNombreGrupo');
    const numeroCamas = document.getElementById('especialNumeroCamas');

    switch(this.value) {
        case 'refugio_completo':
            nombreGrupo.placeholder = 'Ej: Evento Corporativo XYZ';
            numeroCamas.value = <?php echo $total_camas ?? 20; ?>;
            numeroCamas.readOnly = true;
            break;
        case 'gmt':
            nombreGrupo.placeholder = 'Ej: Club Montañero Valladolid';
            numeroCamas.readOnly = false;
            numeroCamas.value = '';
            break;
        case 'grupo_grande':
            nombreGrupo.placeholder = 'Ej: Curso de Alpinismo 2024';
            numeroCamas.readOnly = false;
            numeroCamas.value = '';
            break;
        default:
            numeroCamas.readOnly = false;
            numeroCamas.value = '';
    }
});

// Validación de fechas
document.getElementById('especialFechaEntrada').addEventListener('change', function() {
    const fechaSalida = document.getElementById('especialFechaSalida');
    fechaSalida.min = this.value;

    if (fechaSalida.value && fechaSalida.value <= this.value) {
        fechaSalida.value = '';
    }
});

// Validación del formulario
document.getElementById('formReservaEspecial').addEventListener('submit', function(e) {
    const actividad = document.getElementById('especialActividad').value;
    const fechaEntrada = document.getElementById('especialFechaEntrada').value;
    const fechaSalida = document.getElementById('especialFechaSalida').value;
    const numeroCamas = parseInt(document.getElementById('especialNumeroCamas').value);

    if (actividad.length < 20) {
        e.preventDefault();
        alert('La descripción debe tener al menos 20 caracteres para reservas especiales.');
        return false;
    }

    if (fechaSalida <= fechaEntrada) {
        e.preventDefault();
        alert('La fecha de salida debe ser posterior a la fecha de entrada.');
        return false;
    }

    if (numeroCamas < 1) {
        e.preventDefault();
        alert('Debe indicar el número de personas.');
        return false;
    }
});

function abrirModalReservaEspecial() {
    document.getElementById('formReservaEspecial').reset();
    const modal = new bootstrap.Modal(document.getElementById('modalReservaEspecial'));
    modal.show();
}
</script>
