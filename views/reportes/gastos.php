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
                <h2><i class="bi bi-cash-stack me-2"></i>Reporte de Gastos</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>reportes">Reportes</a></li>
                        <li class="breadcrumb-item active">Gastos</li>
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
        <form method="GET" action="<?php echo BASE_URL; ?>reportes/gastos" class="row g-3">
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
                    <option value="0">Todas las Categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Forma de Pago</label>
                <select name="forma_pago" class="form-select">
                    <option value="">Todas</option>
                    <option value="efectivo" <?php echo $forma_pago == 'efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                    <option value="tarjeta" <?php echo $forma_pago == 'tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                    <option value="transferencia" <?php echo $forma_pago == 'transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Estadísticas Generales -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Gastos</h6>
                        <h3 class="card-title mb-0"><?php echo number_format($stats['total_gastos']); ?></h3>
                    </div>
                    <i class="bi bi-receipt stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Monto Total</h6>
                        <h3 class="card-title mb-0">$<?php echo number_format($stats['total_monto'], 2); ?></h3>
                    </div>
                    <i class="bi bi-currency-dollar stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Promedio</h6>
                        <h3 class="card-title mb-0">$<?php echo number_format($stats['promedio_monto'], 2); ?></h3>
                    </div>
                    <i class="bi bi-calculator stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Máximo</h6>
                        <h3 class="card-title mb-0">$<?php echo number_format($stats['monto_maximo'], 2); ?></h3>
                    </div>
                    <i class="bi bi-arrow-up-circle stat-card-icon"></i>
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
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Gastos por Categoría</h5>
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
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Gastos por Forma de Pago</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="formaPagoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tendencia Mensual -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Tendencia Mensual de Gastos</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="mensualChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Gastos por Categoría -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i>Resumen por Categoría</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Cantidad de Gastos</th>
                                <th>Total</th>
                                <th>Promedio</th>
                                <th>% del Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gastos_por_categoria as $cat): ?>
                            <?php 
                                $porcentaje = $stats['total_monto'] > 0 
                                    ? ($cat['total_monto'] / $stats['total_monto']) * 100 
                                    : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cat['nombre']); ?></strong></td>
                                <td><?php echo number_format($cat['cantidad_gastos']); ?></td>
                                <td class="text-danger"><strong>$<?php echo number_format($cat['total_monto'], 2); ?></strong></td>
                                <td>$<?php echo number_format($cat['promedio_monto'], 2); ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $porcentaje; ?>%;" 
                                             aria-valuenow="<?php echo $porcentaje; ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?php echo number_format($porcentaje, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th>Total General</th>
                                <th><?php echo number_format($stats['total_gastos']); ?></th>
                                <th class="text-danger">$<?php echo number_format($stats['total_monto'], 2); ?></th>
                                <th>-</th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top 10 Gastos Más Grandes -->
<?php if (!empty($top_gastos)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top 10 Gastos Más Grandes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Forma de Pago</th>
                                <th>Registrado por</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 1; ?>
                            <?php foreach ($top_gastos as $gasto): ?>
                            <tr>
                                <td><strong><?php echo $index++; ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($gasto['fecha_gasto'])); ?></td>
                                <td><?php echo htmlspecialchars($gasto['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($gasto['categoria_nombre']); ?></td>
                                <td>
                                    <?php
                                    $iconos = [
                                        'efectivo' => 'bi-cash',
                                        'tarjeta' => 'bi-credit-card',
                                        'transferencia' => 'bi-bank'
                                    ];
                                    $icon = $iconos[$gasto['forma_pago']] ?? 'bi-currency-dollar';
                                    ?>
                                    <i class="bi <?php echo $icon; ?>"></i>
                                    <?php echo ucfirst($gasto['forma_pago']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($gasto['usuario_nombre']); ?></td>
                                <td class="text-danger"><strong>$<?php echo number_format($gasto['monto'], 2); ?></strong></td>
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

<script>
// Gráfico de Categorías
const categoriaData = {
    labels: <?php echo json_encode(array_map(function($c) { return $c['nombre']; }, $gastos_por_categoria)); ?>,
    datasets: [{
        data: <?php echo json_encode(array_map(function($c) { return $c['total_monto']; }, $gastos_por_categoria)); ?>,
        backgroundColor: [
            'rgba(220, 53, 69, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(13, 202, 240, 0.8)',
            'rgba(102, 126, 234, 0.8)',
            'rgba(118, 75, 162, 0.8)',
            'rgba(237, 100, 166, 0.8)',
            'rgba(255, 154, 158, 0.8)'
        ],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

const categoriaChart = new Chart(document.getElementById('categoriaChart'), {
    type: 'pie',
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

// Gráfico de Forma de Pago
const formaPagoData = {
    labels: <?php echo json_encode(array_map(function($f) { return ucfirst($f['forma_pago']); }, $gastos_por_forma_pago)); ?>,
    datasets: [{
        data: <?php echo json_encode(array_map(function($f) { return $f['total_monto']; }, $gastos_por_forma_pago)); ?>,
        backgroundColor: [
            'rgba(25, 135, 84, 0.8)',
            'rgba(13, 110, 253, 0.8)',
            'rgba(220, 53, 69, 0.8)'
        ],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

const formaPagoChart = new Chart(document.getElementById('formaPagoChart'), {
    type: 'doughnut',
    data: formaPagoData,
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

// Gráfico de Tendencia Mensual
const mensualData = {
    labels: <?php echo json_encode(array_map(function($m) { 
        $fecha = explode('-', $m['mes']);
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return $meses[(int)$fecha[1] - 1] . ' ' . $fecha[0];
    }, $gastos_mensuales)); ?>,
    datasets: [{
        label: 'Gastos Mensuales',
        data: <?php echo json_encode(array_map(function($m) { return $m['total_monto']; }, $gastos_mensuales)); ?>,
        backgroundColor: 'rgba(220, 53, 69, 0.2)',
        borderColor: 'rgba(220, 53, 69, 1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4
    }]
};

const mensualChart = new Chart(document.getElementById('mensualChart'), {
    type: 'line',
    data: mensualData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Total: $' + context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString('es-MX');
                    }
                }
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
