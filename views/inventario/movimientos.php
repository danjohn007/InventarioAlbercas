<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-arrow-left-right me-2"></i>Movimientos de Inventario</h2>
            <a href="<?php echo BASE_URL; ?>inventario" class="btn btn-secondary">
                <i class="bi bi-box-seam"></i> Ver Inventario
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>inventario/movimientos" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Producto</label>
                <select name="producto" class="form-select">
                    <option value="">Todos los productos</option>
                    <?php foreach ($productos as $prod): ?>
                        <option value="<?php echo $prod['id']; ?>" <?php echo $producto == $prod['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prod['codigo'] . ' - ' . $prod['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="entrada" <?php echo $tipo == 'entrada' ? 'selected' : ''; ?>>Entrada</option>
                    <option value="salida" <?php echo $tipo == 'salida' ? 'selected' : ''; ?>>Salida</option>
                    <option value="ajuste" <?php echo $tipo == 'ajuste' ? 'selected' : ''; ?>>Ajuste</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de movimientos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Historial de Movimientos (<?php echo $totalRecords; ?>)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Stock Anterior</th>
                        <th>Stock Nuevo</th>
                        <th>Costo</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay movimientos registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($mov['fecha_movimiento'])); ?>
                                        <br>
                                        <span class="text-muted"><?php echo date('H:i', strtotime($mov['fecha_movimiento'])); ?></span>
                                    </small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($mov['codigo']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($mov['producto_nombre']); ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $badge_class = 'secondary';
                                    $icon = 'arrow-left-right';
                                    if ($mov['tipo_movimiento'] == 'entrada') {
                                        $badge_class = 'success';
                                        $icon = 'arrow-down-circle';
                                    } elseif ($mov['tipo_movimiento'] == 'salida') {
                                        $badge_class = 'danger';
                                        $icon = 'arrow-up-circle';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                        <i class="bi bi-<?php echo $icon; ?>"></i>
                                        <?php echo ucfirst($mov['tipo_movimiento']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo number_format($mov['cantidad'], 2); ?></strong>
                                </td>
                                <td><?php echo number_format($mov['stock_anterior'], 2); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo number_format($mov['stock_nuevo'], 2); ?>
                                    </span>
                                </td>
                                <td>
                                    $<?php echo number_format($mov['costo_unitario'], 2); ?>
                                    <br><small class="text-muted">Total: $<?php echo number_format($mov['costo_total'], 2); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($mov['motivo']); ?>
                                    <?php if (!empty($mov['observaciones'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($mov['observaciones']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo htmlspecialchars($mov['usuario_nombre']); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&producto=<?php echo $producto; ?>&tipo=<?php echo $tipo; ?>&fecha_desde=<?php echo $fecha_desde; ?>&fecha_hasta=<?php echo $fecha_hasta; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
