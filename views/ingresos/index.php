<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-currency-dollar me-2"></i>Gestión de Ingresos</h2>
            <?php if (Auth::can('ingresos', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>ingresos/crear" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Registrar Ingreso
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>ingresos" class="row g-3">
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
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Forma de Pago</label>
                <select name="forma_pago" class="form-select">
                    <option value="">Todas</option>
                    <option value="efectivo" <?php echo $forma_pago == 'efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                    <option value="tarjeta" <?php echo $forma_pago == 'tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                    <option value="transferencia" <?php echo $forma_pago == 'transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                    <option value="cheque" <?php echo $forma_pago == 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                </select>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>ingresos" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total de Ingresos</h6>
                        <small class="opacity-75">
                            <?php 
                            if (!empty($fecha_desde) || !empty($fecha_hasta) || $categoria > 0 || !empty($forma_pago)) {
                                echo 'Filtrado';
                            } else {
                                echo 'Todos los ingresos';
                            }
                            ?>
                        </small>
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0">$<?php echo number_format($totalMonto, 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Número de Registros</h6>
                        <small class="opacity-75">Total de ingresos</small>
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0"><?php echo $totalRecords; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Promedio</h6>
                        <small class="opacity-75">Por ingreso</small>
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0">$<?php echo $totalRecords > 0 ? number_format($totalMonto / $totalRecords, 2) : '0.00'; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de ingresos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Listado de Ingresos</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Concepto</th>
                        <th>Servicio</th>
                        <th>Cliente</th>
                        <th>Forma de Pago</th>
                        <th>Monto</th>
                        <th>Registrado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ingresos)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay ingresos registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ingresos as $ingreso): ?>
                            <tr>
                                <td>
                                    <span class="d-block"><?php echo date('d/m/Y', strtotime($ingreso['fecha_ingreso'])); ?></span>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($ingreso['fecha_creacion'])); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($ingreso['categoria_nombre']); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ingreso['concepto']); ?></strong>
                                    <?php if (!empty($ingreso['descripcion'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($ingreso['descripcion'], 0, 50)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($ingreso['servicio_titulo'])): ?>
                                        <small><?php echo htmlspecialchars(substr($ingreso['servicio_titulo'], 0, 30)); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($ingreso['cliente_nombre'])): ?>
                                        <small><i class="bi bi-person"></i> <?php echo htmlspecialchars($ingreso['cliente_nombre']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = [
                                        'efectivo' => 'bg-success',
                                        'tarjeta' => 'bg-primary',
                                        'transferencia' => 'bg-info',
                                        'cheque' => 'bg-warning'
                                    ];
                                    $class = $badgeClass[$ingreso['forma_pago']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $class; ?>">
                                        <?php echo ucfirst($ingreso['forma_pago']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success">$<?php echo number_format($ingreso['monto'], 2); ?></strong>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($ingreso['usuario_nombre']); ?></small>
                                </td>
                                <td>
                                    <?php if (Auth::can('ingresos', 'actualizar')): ?>
                                        <a href="<?php echo BASE_URL; ?>ingresos/editar/<?php echo $ingreso['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($ingreso['comprobante'])): ?>
                                        <button class="btn btn-sm btn-outline-info" title="Ver comprobante">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center mb-0">
                    <?php
                    $queryParams = $_GET;
                    for ($i = 1; $i <= $totalPages; $i++):
                        $queryParams['page'] = $i;
                        $queryString = http_build_query($queryParams);
                    ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>ingresos?<?php echo $queryString; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
