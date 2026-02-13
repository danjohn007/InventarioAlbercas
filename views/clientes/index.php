<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-people-fill me-2"></i>Gestión de Clientes</h2>
            <?php if (Auth::can('clientes', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>clientes/crear" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Crear Cliente
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>clientes" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Nombre, apellidos, teléfono, email o ciudad..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>clientes" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de clientes -->
<div class="card">
    <div class="card-body">
        <?php if (empty($clientes)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No se encontraron clientes.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Ciudad</th>
                            <th>Servicios</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>
                                    <?php if (!empty($cliente['apellidos'])): ?>
                                        <?php echo htmlspecialchars($cliente['apellidos']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cliente['telefono'])): ?>
                                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($cliente['telefono']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cliente['email'])): ?>
                                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($cliente['email']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo !empty($cliente['ciudad']) ? htmlspecialchars($cliente['ciudad']) : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $cliente['total_servicios']; ?> servicios</span>
                                </td>
                                <td>
                                    <?php if ($cliente['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (Auth::can('clientes', 'actualizar')): ?>
                                        <a href="<?php echo BASE_URL; ?>clientes/editar/<?php echo $cliente['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>">
                                    Anterior
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>">
                                    Siguiente
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
