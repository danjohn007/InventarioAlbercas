<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-tools me-2"></i>Gestión de Servicios</h2>
            <?php if (Auth::can('servicios', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>servicios/crear" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Crear Servicio
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>servicios" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" <?php echo $estado == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="en_proceso" <?php echo $estado == 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                    <option value="completado" <?php echo $estado == 'completado' ? 'selected' : ''; ?>>Completado</option>
                    <option value="cancelado" <?php echo $estado == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Técnico</label>
                <select name="tecnico_id" class="form-select">
                    <option value="">Todos los técnicos</option>
                    <?php foreach ($tecnicos as $tec): ?>
                        <option value="<?php echo $tec['id']; ?>" <?php echo $tecnico_id == $tec['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tec['nombre_completo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fecha_desde" class="form-control" 
                       value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" 
                       value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>servicios" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de servicios -->
<div class="card">
    <div class="card-body">
        <?php if (empty($servicios)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No se encontraron servicios.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Título</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Técnico</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicios as $servicio): ?>
                            <tr>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($servicio['fecha_programada'])); ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($servicio['titulo']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($servicio['cliente_nombre']); ?>
                                    <?php if (!empty($servicio['cliente_telefono'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($servicio['cliente_telefono']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $tipos = [
                                        'mantenimiento' => ['label' => 'Mantenimiento', 'icon' => 'wrench'],
                                        'reparacion' => ['label' => 'Reparación', 'icon' => 'hammer'],
                                        'instalacion' => ['label' => 'Instalación', 'icon' => 'gear-fill'],
                                        'otro' => ['label' => 'Otro', 'icon' => 'three-dots']
                                    ];
                                    $tipo = $tipos[$servicio['tipo_servicio']];
                                    ?>
                                    <i class="bi bi-<?php echo $tipo['icon']; ?>"></i> <?php echo $tipo['label']; ?>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($servicio['tecnico_nombre']); ?></small>
                                </td>
                                <td>
                                    <?php
                                    $estados = [
                                        'pendiente' => ['class' => 'warning', 'label' => 'Pendiente'],
                                        'en_proceso' => ['class' => 'info', 'label' => 'En Proceso'],
                                        'completado' => ['class' => 'success', 'label' => 'Completado'],
                                        'cancelado' => ['class' => 'secondary', 'label' => 'Cancelado']
                                    ];
                                    $est = $estados[$servicio['estado']];
                                    ?>
                                    <span class="badge bg-<?php echo $est['class']; ?>"><?php echo $est['label']; ?></span>
                                </td>
                                <td>
                                    <strong>$<?php echo number_format($servicio['total'], 2); ?></strong>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo BASE_URL; ?>servicios/ver/<?php echo $servicio['id']; ?>" 
                                           class="btn btn-outline-info" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (Auth::can('servicios', 'actualizar')): ?>
                                            <a href="<?php echo BASE_URL; ?>servicios/editar/<?php echo $servicio['id']; ?>" 
                                               class="btn btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
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
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?>&estado=<?php echo urlencode($estado); ?>&tecnico_id=<?php echo $tecnico_id; ?>&fecha_desde=<?php echo urlencode($fecha_desde); ?>&fecha_hasta=<?php echo urlencode($fecha_hasta); ?>">
                                    Anterior
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&estado=<?php echo urlencode($estado); ?>&tecnico_id=<?php echo $tecnico_id; ?>&fecha_desde=<?php echo urlencode($fecha_desde); ?>&fecha_hasta=<?php echo urlencode($fecha_hasta); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?>&estado=<?php echo urlencode($estado); ?>&tecnico_id=<?php echo $tecnico_id; ?>&fecha_desde=<?php echo urlencode($fecha_desde); ?>&fecha_hasta=<?php echo urlencode($fecha_hasta); ?>">
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
