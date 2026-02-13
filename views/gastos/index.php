<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-cash-stack me-2"></i>Gestión de Gastos</h2>
            <?php if (Auth::can('gastos', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>gastos/crear" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Registrar Gasto
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>gastos" class="row g-3">
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
                <a href="<?php echo BASE_URL; ?>gastos" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tarjeta de Total -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Total de Gastos</h5>
                        <small class="opacity-75">
                            <?php 
                            if (!empty($fecha_desde) || !empty($fecha_hasta) || $categoria > 0 || !empty($forma_pago)) {
                                echo 'Filtrado';
                            } else {
                                echo 'Todos los gastos';
                            }
                            ?>
                        </small>
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0">$<?php echo number_format($totalMonto, 2); ?></h2>
                        <small class="opacity-75"><?php echo $totalRecords; ?> registro(s)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de gastos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Listado de Gastos</h5>
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
                        <th>Cliente/Proveedor</th>
                        <th>Forma de Pago</th>
                        <th>Monto</th>
                        <th>Registrado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($gastos)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay gastos registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($gastos as $gasto): ?>
                            <tr>
                                <td>
                                    <span class="d-block"><?php echo date('d/m/Y', strtotime($gasto['fecha_gasto'])); ?></span>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($gasto['fecha_creacion'])); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($gasto['categoria_nombre']); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($gasto['concepto']); ?></strong>
                                    <?php if (!empty($gasto['descripcion'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($gasto['descripcion'], 0, 50)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($gasto['servicio_titulo'])): ?>
                                        <small><?php echo htmlspecialchars(substr($gasto['servicio_titulo'], 0, 30)); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($gasto['cliente_nombre'])): ?>
                                        <small><i class="bi bi-person"></i> <?php echo htmlspecialchars($gasto['cliente_nombre']); ?></small>
                                    <?php elseif (!empty($gasto['proveedor_nombre'])): ?>
                                        <small><i class="bi bi-building"></i> <?php echo htmlspecialchars($gasto['proveedor_nombre']); ?></small>
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
                                    $class = $badgeClass[$gasto['forma_pago']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $class; ?>">
                                        <?php echo ucfirst($gasto['forma_pago']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-danger">$<?php echo number_format($gasto['monto'], 2); ?></strong>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($gasto['usuario_nombre']); ?></small>
                                </td>
                                <td>
                                    <?php if (Auth::can('gastos', 'actualizar')): ?>
                                        <a href="<?php echo BASE_URL; ?>gastos/editar/<?php echo $gasto['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($gasto['comprobante'])): ?>
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
                            <a class="page-link" href="<?php echo BASE_URL; ?>gastos?<?php echo $queryString; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
