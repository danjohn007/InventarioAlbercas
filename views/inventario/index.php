<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-box-seam me-2"></i>Inventario de Productos</h2>
            <?php if (Auth::can('inventario', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>inventario/crear" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Nuevo Producto
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>inventario" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control" placeholder="Código, nombre o descripción" 
                       value="<?php echo htmlspecialchars($search); ?>">
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
                <label class="form-label">Stock</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="stock_bajo" id="stock_bajo" 
                           <?php echo $stock_bajo ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="stock_bajo">
                        Solo alertas de stock
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de productos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Productos (<?php echo $totalRecords; ?>)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Costo Unit.</th>
                        <th>Precio Venta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay productos registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $producto): ?>
                            <?php $stock_alert = $producto['stock_actual'] <= $producto['stock_minimo']; ?>
                            <tr class="<?php echo $stock_alert ? 'table-warning' : ''; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($producto['codigo']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($producto['nombre']); ?>
                                    <?php if (!empty($producto['descripcion'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                                <td>
                                    <span class="badge <?php echo $stock_alert ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo number_format($producto['stock_actual'], 2); ?> <?php echo htmlspecialchars($producto['unidad_medida']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($producto['stock_minimo'], 2); ?></td>
                                <td>$<?php echo number_format($producto['costo_unitario'], 2); ?></td>
                                <td>$<?php echo number_format($producto['precio_venta'], 2); ?></td>
                                <td>
                                    <?php if ($stock_alert): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle"></i> Stock Bajo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo BASE_URL; ?>inventario/movimiento/<?php echo $producto['id']; ?>" 
                                           class="btn btn-success" title="Registrar movimiento">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </a>
                                        <?php if (Auth::can('inventario', 'actualizar')): ?>
                                            <a href="<?php echo BASE_URL; ?>inventario/editar/<?php echo $producto['id']; ?>" 
                                               class="btn btn-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
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
            <nav>
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&categoria=<?php echo $categoria; ?><?php echo $stock_bajo ? '&stock_bajo=1' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
