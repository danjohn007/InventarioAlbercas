<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .sidebar, .top-navbar {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
        }
        .card {
            border: 1px solid #ddd !important;
            break-inside: avoid;
        }
    }
    
    .stat-card-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>

<div class="row mb-4 no-print">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-box-seam me-2"></i>Reporte de Inventario</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>reportes">Reportes</a></li>
                        <li class="breadcrumb-item active">Inventario</li>
                    </ol>
                </nav>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <button class="btn btn-success" onclick="exportarExcel()">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
                <button class="btn btn-danger" onclick="exportarPDF()">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>reportes/inventario" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="0">Todas las Categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ordenar por</label>
                <select name="orden" class="form-select">
                    <option value="nombre" <?php echo $orden == 'nombre' ? 'selected' : ''; ?>>Nombre</option>
                    <option value="stock_asc" <?php echo $orden == 'stock_asc' ? 'selected' : ''; ?>>Stock (Menor a Mayor)</option>
                    <option value="stock_desc" <?php echo $orden == 'stock_desc' ? 'selected' : ''; ?>>Stock (Mayor a Menor)</option>
                    <option value="precio_asc" <?php echo $orden == 'precio_asc' ? 'selected' : ''; ?>>Precio (Menor a Mayor)</option>
                    <option value="precio_desc" <?php echo $orden == 'precio_desc' ? 'selected' : ''; ?>>Precio (Mayor a Menor)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Estadísticas Generales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Productos</h6>
                        <h3 class="card-title mb-0"><?php echo number_format($stats['total_productos']); ?></h3>
                    </div>
                    <i class="bi bi-box-seam stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Unidades</h6>
                        <h3 class="card-title mb-0"><?php echo number_format($stats['total_unidades']); ?></h3>
                    </div>
                    <i class="bi bi-stack stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Valor Total</h6>
                        <h3 class="card-title mb-0">$<?php echo number_format($stats['valor_total_inventario'], 2); ?></h3>
                    </div>
                    <i class="bi bi-currency-dollar stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Bajo Stock</h6>
                        <h3 class="card-title mb-0"><?php echo number_format($stats['productos_bajo_stock']); ?></h3>
                    </div>
                    <i class="bi bi-exclamation-triangle stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Valor por Categoría</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoriaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Stock por Categoría</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Productos con Bajo Stock -->
<?php if (!empty($productos_bajo_stock)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Alertas de Stock Bajo</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Déficit</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_bajo_stock as $producto): ?>
                            <?php $deficit = $producto['stock_minimo'] - $producto['stock']; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                <td><strong><?php echo $producto['stock']; ?></strong></td>
                                <td><?php echo $producto['stock_minimo']; ?></td>
                                <td class="text-danger">-<?php echo $deficit; ?></td>
                                <td>
                                    <?php if ($producto['stock'] == 0): ?>
                                        <span class="badge bg-danger">Sin Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Bajo Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabla de Productos -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Detalle de Inventario</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th>Precio Unitario</th>
                                <th>Valor Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($producto['sku']); ?></code></td>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                <td>
                                    <strong><?php echo $producto['stock']; ?></strong>
                                    <?php if ($producto['stock'] <= $producto['stock_minimo']): ?>
                                        <i class="bi bi-exclamation-circle text-warning ms-1"></i>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                <td><strong>$<?php echo number_format($producto['valor_total'], 2); ?></strong></td>
                                <td>
                                    <?php if ($producto['stock'] == 0): ?>
                                        <span class="badge bg-danger">Sin Stock</span>
                                    <?php elseif ($producto['stock'] <= $producto['stock_minimo']): ?>
                                        <span class="badge bg-warning">Bajo Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Normal</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="5" class="text-end">Total Inventario:</th>
                                <th>$<?php echo number_format($stats['valor_total_inventario'], 2); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gráfico de Categorías por Valor
const categoriaData = {
    labels: <?php echo json_encode(array_map(function($c) { return $c['nombre']; }, $categorias_stats)); ?>,
    datasets: [{
        data: <?php echo json_encode(array_map(function($c) { return $c['valor_total'] ?? 0; }, $categorias_stats)); ?>,
        backgroundColor: [
            'rgba(102, 126, 234, 0.8)',
            'rgba(118, 75, 162, 0.8)',
            'rgba(237, 100, 166, 0.8)',
            'rgba(255, 154, 158, 0.8)',
            'rgba(250, 208, 196, 0.8)',
            'rgba(79, 172, 254, 0.8)',
            'rgba(0, 242, 254, 0.8)'
        ],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

const categoriaChart = new Chart(document.getElementById('categoriaChart'), {
    type: 'doughnut',
    data: categoriaData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': $' + context.parsed.toLocaleString('es-MX', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});

// Gráfico de Stock por Categoría
const stockData = {
    labels: <?php echo json_encode(array_map(function($c) { return $c['nombre']; }, $categorias_stats)); ?>,
    datasets: [{
        label: 'Unidades en Stock',
        data: <?php echo json_encode(array_map(function($c) { return $c['total_stock'] ?? 0; }, $categorias_stats)); ?>,
        backgroundColor: 'rgba(102, 126, 234, 0.6)',
        borderColor: 'rgba(102, 126, 234, 1)',
        borderWidth: 2
    }]
};

const stockChart = new Chart(document.getElementById('stockChart'), {
    type: 'bar',
    data: stockData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function exportarPDF() {
    alert('Funcionalidad de exportación a PDF estará disponible próximamente.');
}

function exportarExcel() {
    alert('Funcionalidad de exportación a Excel estará disponible próximamente.');
}
</script>
