<?php
    // Gestión de Usuarios - Vista del Administrador
    $title       = 'Gestión de Usuarios';
    $showSidebar = true;

    ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people-fill"></i> Gestión de Usuarios</h2>
    <div>
        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalImportarCSV">
            <i class="bi bi-file-earmark-arrow-up"></i> Importar CSV
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
            <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <!-- Controles de búsqueda, ordenación y exportación -->
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="get" class="input-group">
                    <input type="hidden" name="accion" value="usuarios">
                    <input type="text"
                           class="form-control"
                           name="search"
                           placeholder="Buscar por nombre, email, DNI..."
                           value="<?php echo htmlspecialchars($search_usuarios ?? '') ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-4">
                <select class="form-select"
                        onchange="window.location.href='?accion=usuarios&search=<?php echo urlencode($search_usuarios ?? '') ?>&sort=' + this.value + '&dir=<?php echo $order_dir_usuarios ?? 'ASC' ?>'">
                    <option value="num_socio" <?php echo($sort_usuarios ?? 'num_socio') === 'num_socio' ? 'selected' : '' ?>>
                        Ordenar por Nº Socio
                    </option>
                    <option value="nombre" <?php echo($sort_usuarios ?? '') === 'nombre' ? 'selected' : '' ?>>
                        Ordenar por Nombre
                    </option>
                    <option value="email" <?php echo($sort_usuarios ?? '') === 'email' ? 'selected' : '' ?>>
                        Ordenar por Email
                    </option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <a href="?accion=export_usuarios_csv&search=<?php echo urlencode($search_usuarios ?? '') ?>&sort=<?php echo urlencode($sort_usuarios ?? 'num_socio') ?>&dir=<?php echo urlencode($order_dir_usuarios ?? 'ASC') ?>"
                   class="btn btn-success me-2">
                    <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                </a>
                <a href="?accion=export_usuarios_pdf&search=<?php echo urlencode($search_usuarios ?? '') ?>&sort=<?php echo urlencode($sort_usuarios ?? 'num_socio') ?>&dir=<?php echo urlencode($order_dir_usuarios ?? 'ASC') ?>"
                   class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nº Socio</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios ?? [] as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['num_socio']) ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido1']) ?></td>
                            <td><?php echo htmlspecialchars($usuario['dni']) ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']) ?></td>
                            <td><?php echo htmlspecialchars($usuario['telf'] ?? '') ?></td>
                            <td>
                                <span class="badge bg-<?php echo $usuario['rol'] === 'admin' ? 'danger' : 'primary' ?>">
                                    <?php echo strtoupper($usuario['rol']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($usuario['email'] !== 'admin@hostel.com'): ?>
                                    <a href="?accion=editar_usuario&id=<?php echo $usuario['id'] ?>"
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="accion" value="eliminar_usuario">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar este usuario?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-shield-lock-fill"></i> Protegido
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if (($paginas_usuarios ?? 1) > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if (($page_usuarios ?? 1) > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?accion=usuarios&page=<?php echo $page_usuarios - 1 ?>&search=<?php echo urlencode($search_usuarios ?? '') ?>&sort=<?php echo urlencode($sort_usuarios ?? 'num_socio') ?>&dir=<?php echo urlencode($order_dir_usuarios ?? 'ASC') ?>">Anterior</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= ($paginas_usuarios ?? 1); $i++): ?>
                        <li class="page-item <?php echo $i === ($page_usuarios ?? 1) ? 'active' : '' ?>">
                            <a class="page-link" href="?accion=usuarios&page=<?php echo $i ?>&search=<?php echo urlencode($search_usuarios ?? '') ?>&sort=<?php echo urlencode($sort_usuarios ?? 'num_socio') ?>&dir=<?php echo urlencode($order_dir_usuarios ?? 'ASC') ?>"><?php echo $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if (($page_usuarios ?? 1) < ($paginas_usuarios ?? 1)): ?>
                        <li class="page-item">
                            <a class="page-link" href="?accion=usuarios&page=<?php echo $page_usuarios + 1 ?>&search=<?php echo urlencode($search_usuarios ?? '') ?>&sort=<?php echo urlencode($sort_usuarios ?? 'num_socio') ?>&dir=<?php echo urlencode($order_dir_usuarios ?? 'ASC') ?>">Siguiente</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Crear/Editar Usuario -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?php echo isset($usuario_editar) ? 'Editar Usuario' : 'Nuevo Usuario' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="<?php echo isset($usuario_editar) ? 'actualizar_usuario' : 'crear_usuario' ?>">
                    <?php if (isset($usuario_editar)): ?>
                        <input type="hidden" name="id" value="<?php echo $usuario_editar['id'] ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nº Socio *</label>
                            <input type="text"
                                   name="num_socio"
                                   class="form-control"
                                   value="<?php echo isset($usuario_editar) ? htmlspecialchars($usuario_editar['num_socio']) : '' ?>"
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">DNI *</label>
                            <input type="text"
                                   name="dni"
                                   class="form-control"
                                   value="<?php echo isset($usuario_editar) ? htmlspecialchars($usuario_editar['dni']) : '' ?>"
                                   required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text"
                                   name="nombre"
                                   class="form-control"
                                   value="<?php echo isset($usuario_editar) ? htmlspecialchars($usuario_editar['nombre']) : '' ?>"
                                   required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Apellido 1 *</label>
                            <input type="text"
                                   name="apellido1"
                                   class="form-control"
                                   value="<?php echo isset($usuario_editar) ? htmlspecialchars($usuario_editar['apellido1']) : '' ?>"
                                   required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Apellido 2</label>
                            <input type="text"
                                   name="apellido2"
                                   class="form-control"
                                   value="<?php echo isset($usuario_editar) ? htmlspecialchars($usuario_editar['apellido2'] ?? '') : '' ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="<?php echo isset($usuario_editar) ? htmlspecialchars($usuario_editar['email']) : '' ?>"
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text"
                                   name="telf"
                                   class="form-control"
                                   value="<?php echo isset($usuario_editar) ? htmlspecialchars($usuario_editar['telf'] ?? '') : '' ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Contraseña <?php echo isset($usuario_editar) ? '' : '*' ?>
                            </label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   <?php echo isset($usuario_editar) ? '' : 'required' ?>>
                            <?php if (isset($usuario_editar)): ?>
                                <small class="text-muted">Dejar en blanco para mantener la actual</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol *</label>
                            <select name="rol" class="form-select" required>
                                <option value="user" <?php echo(isset($usuario_editar) && $usuario_editar['rol'] === 'user') ? 'selected' : '' ?>>
                                    User
                                </option>
                                <option value="admin" <?php echo(isset($usuario_editar) && $usuario_editar['rol'] === 'admin') ? 'selected' : '' ?>>
                                    Admin
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo isset($usuario_editar) ? 'Actualizar' : 'Crear' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($usuario_editar)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('modalCrearUsuario')).show();
        });
    </script>
<?php endif; ?>

<?php include VIEWS_PATH . '/partials/modals/modal-importar-csv.php'; ?>

<?php
    $content = ob_get_clean();
    include VIEWS_PATH . '/layouts/app.php';
?>
