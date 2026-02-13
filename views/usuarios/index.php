<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="bi bi-people"></i> Usuarios
    </h1>
    <?php if (Auth::can('usuarios', 'crear')): ?>
    <a href="<?php echo BASE_URL; ?>usuarios/crear" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Usuario
    </a>
    <?php endif; ?>
</div>

<!-- Formulario de búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>usuarios" class="row g-3">
            <div class="col-md-10">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Buscar por nombre, apellidos, email o usuario..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
            <?php if (!empty($search)): ?>
            <div class="col-12">
                <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar búsqueda
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i> Listado de Usuarios
    </div>
    <div class="card-body">
        <?php if (empty($usuarios)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No se encontraron usuarios.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th width="150">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($usuario['usuario']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="mailto:<?php echo htmlspecialchars($usuario['email']); ?>">
                                <?php echo htmlspecialchars($usuario['email']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['telefono'] ?? '-'); ?></td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo htmlspecialchars($usuario['rol_nombre']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($usuario['activo']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Activo
                            </span>
                            <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Inactivo
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if ($usuario['ultimo_acceso']) {
                                echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']));
                            } else {
                                echo '<span class="text-muted">Nunca</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <?php if (Auth::can('usuarios', 'actualizar')): ?>
                                <a href="<?php echo BASE_URL; ?>usuarios/editar/<?php echo $usuario['id']; ?>" 
                                   class="btn btn-outline-primary" 
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if (Auth::can('usuarios', 'eliminar') && $usuario['id'] != Auth::user()['id']): ?>
                                <button type="button" 
                                        class="btn btn-outline-danger" 
                                        title="Desactivar"
                                        onclick="confirmarEliminar(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos'], ENT_QUOTES); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Paginación" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>usuarios?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                        <i class="bi bi-chevron-left"></i> Anterior
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>usuarios?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo BASE_URL; ?>usuarios?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                        Siguiente <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        
        <!-- Información de registros -->
        <div class="text-center text-muted mt-2">
            <small>
                Mostrando 
                <?php echo (($page - 1) * $perPage) + 1; ?> - 
                <?php echo min($page * $perPage, $totalRecords); ?> 
                de <?php echo $totalRecords; ?> registro(s)
            </small>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmarEliminar(id, nombre) {
    const mensaje = '¿Está seguro que desea desactivar al usuario "' + nombre + '"?\n\nEl usuario no podrá acceder al sistema.';
    if (confirm(mensaje)) {
        window.location.href = '<?php echo BASE_URL; ?>usuarios/eliminar/' + id;
    }
}
</script>
