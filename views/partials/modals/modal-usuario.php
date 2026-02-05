<!-- Modal para Crear/Editar Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalUsuarioLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>
                    <span id="modalUsuarioTitulo">Crear Usuario</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUsuario" method="POST" action="viewAdminMVC.php">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="usuarioAccion" value="crear_usuario">
                    <input type="hidden" name="id_usuario" id="usuarioId" value="">

                    <!-- Información Personal -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuarioNombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="usuarioNombre" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label for="usuarioApellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="usuarioApellidos" name="apellidos" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuarioDNI" class="form-label">DNI/NIE</label>
                            <input type="text" class="form-control" id="usuarioDNI" name="dni"
                                   pattern="[0-9]{8}[A-Za-z]|[XYZ][0-9]{7}[A-Za-z]">
                            <div class="form-text">Formato: 12345678A o X1234567A</div>
                        </div>
                        <div class="col-md-6">
                            <label for="usuarioNumSocio" class="form-label">Número de Socio</label>
                            <input type="text" class="form-control" id="usuarioNumSocio" name="num_socio">
                            <div class="form-text">Solo para socios</div>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuarioEmail" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="usuarioEmail" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="usuarioTelefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="usuarioTelefono" name="telefono"
                                   pattern="[0-9]{9}">
                            <div class="form-text">9 dígitos</div>
                        </div>
                    </div>

                    <!-- Rol y Contraseña -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuarioRol" class="form-label">Rol *</label>
                            <select class="form-select" id="usuarioRol" name="rol" required>
                                <option value="">Seleccione rol</option>
                                <option value="user">Socio</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="usuarioPasswordGroup">
                            <label for="usuarioPassword" class="form-label">
                                Contraseña <span id="passwordRequired">*</span>
                            </label>
                            <input type="password" class="form-control" id="usuarioPassword"
                                   name="contrasena" minlength="6">
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                    </div>

                    <!-- Mensaje de ayuda para edición -->
                    <div id="editHelp" class="alert alert-info" style="display: none;">
                        <i class="bi bi-info-circle me-2"></i>
                        Deje la contraseña en blanco para mantener la actual
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>
                        <span id="usuarioSubmitText">Crear Usuario</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Funciones para modal de usuario
function abrirModalCrearUsuario() {
    // Restablecer formulario
    document.getElementById('formUsuario').reset();
    document.getElementById('usuarioId').value = '';
    document.getElementById('usuarioAccion').value = 'crear_usuario';

    // Actualizar textos
    document.getElementById('modalUsuarioTitulo').textContent = 'Crear Usuario';
    document.getElementById('usuarioSubmitText').textContent = 'Crear Usuario';

    // Contraseña requerida para creación
    document.getElementById('usuarioPassword').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('editHelp').style.display = 'none';

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modal.show();
}

function abrirModalEditarUsuario(usuario) {
    // Cargar datos del usuario
    document.getElementById('usuarioId').value = usuario.id;
    document.getElementById('usuarioAccion').value = 'actualizar_usuario';
    document.getElementById('usuarioNombre').value = usuario.nombre;
    document.getElementById('usuarioApellidos').value = usuario.apellidos;
    document.getElementById('usuarioDNI').value = usuario.dni || '';
    document.getElementById('usuarioNumSocio').value = usuario.num_socio || '';
    document.getElementById('usuarioEmail').value = usuario.email;
    document.getElementById('usuarioTelefono').value = usuario.telefono || '';
    document.getElementById('usuarioRol').value = usuario.rol;
    document.getElementById('usuarioPassword').value = '';

    // Actualizar textos
    document.getElementById('modalUsuarioTitulo').textContent = 'Editar Usuario';
    document.getElementById('usuarioSubmitText').textContent = 'Guardar Cambios';

    // Contraseña opcional para edición
    document.getElementById('usuarioPassword').required = false;
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('editHelp').style.display = 'block';

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
    modal.show();
}
</script>
