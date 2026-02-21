<div class="row">
    <!-- Tarjetas de estadísticas -->
    <div class="col-md-3 mb-3">
        <div class="card stat-card stat-card-blue border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-uppercase mb-1" style="opacity:.85;">Productos</div>
                        <div class="h3 fw-bold mb-0"><?php echo number_format($stats['productos']); ?></div>
                    </div>
                    <div style="font-size:2rem;opacity:.6;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card stat-card-green border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-uppercase mb-1" style="opacity:.85;">Valor Inventario</div>
                        <div class="h3 fw-bold mb-0">$<?php echo number_format($stats['valor_inventario'], 2); ?></div>
                    </div>
                    <div style="font-size:2rem;opacity:.6;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card stat-card-teal border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-uppercase mb-1" style="opacity:.85;">Ingresos del Mes</div>
                        <div class="h3 fw-bold mb-0">$<?php echo number_format($stats['ingresos_mes'], 2); ?></div>
                    </div>
                    <div style="font-size:2rem;opacity:.6;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card stat-card-red border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-semibold text-uppercase mb-1" style="opacity:.85;">Gastos del Mes</div>
                        <div class="h3 fw-bold mb-0">$<?php echo number_format($stats['gastos_mes'], 2); ?></div>
                    </div>
                    <div style="font-size:2rem;opacity:.6;">
                        <i class="bi bi-graph-down-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Balance del mes + Servicios Activos -->
<div class="row mb-3">
    <div class="col-md-3 mb-3">
        <div class="card stat-card-green border-0 shadow-sm h-100">
            <div class="card-body py-3 text-center">
                <i class="bi bi-arrow-up-circle" style="font-size:2rem;opacity:.8;"></i>
                <div class="small fw-semibold text-uppercase mt-1" style="opacity:.85;">Ingresos del Mes</div>
                <div class="h4 fw-bold mb-0 mt-1">$<?php echo number_format($stats['ingresos_mes'], 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card-red border-0 shadow-sm h-100">
            <div class="card-body py-3 text-center">
                <i class="bi bi-arrow-down-circle" style="font-size:2rem;opacity:.8;"></i>
                <div class="small fw-semibold text-uppercase mt-1" style="opacity:.85;">Gastos del Mes</div>
                <div class="h4 fw-bold mb-0 mt-1">$<?php echo number_format($stats['gastos_mes'], 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <?php 
        $balance = $stats['ingresos_mes'] - $stats['gastos_mes'];
        $balance_class = $balance >= 0 ? 'stat-card-green' : 'stat-card-red';
        $balance_icon = $balance >= 0 ? 'bi-check-circle' : 'bi-x-circle';
        ?>
        <div class="card <?php echo $balance_class; ?> border-0 shadow-sm h-100">
            <div class="card-body py-3 text-center">
                <i class="bi <?php echo $balance_icon; ?>" style="font-size:2rem;opacity:.8;"></i>
                <div class="small fw-semibold text-uppercase mt-1" style="opacity:.85;">Balance</div>
                <div class="h4 fw-bold mb-0 mt-1">$<?php echo number_format($balance, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card stat-card-yellow border-0 shadow-sm h-100">
            <div class="card-body py-3 text-center">
                <i class="bi bi-tools" style="font-size:2rem;opacity:.8;"></i>
                <div class="small fw-semibold text-uppercase mt-1" style="opacity:.85;">Servicios Activos</div>
                <div class="h4 fw-bold mb-0 mt-1"><?php echo number_format($stats['servicios_activos']); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfica de gastos por categoría -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2"></i>Gastos por Categoría (Mes Actual)
            </div>
            <div class="card-body">
                <canvas id="gastosChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Gráfica de Ingresos vs Gastos -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Ingresos vs Gastos (6 meses)
            </div>
            <div class="card-body">
                <canvas id="ingresosVsGastosChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfica de tendencia de ingresos -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>Tendencia de Ingresos (6 meses)
            </div>
            <div class="card-body">
                <canvas id="ingresosChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Gráfica de tendencia de gastos -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-down me-2"></i>Tendencia de Gastos (6 meses)
            </div>
            <div class="card-body">
                <canvas id="gastosChart2" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Productos con stock bajo -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle me-2"></i>Productos con Stock Bajo
            </div>
            <div class="card-body">
                <?php if (empty($productosBajoStock)): ?>
                    <p class="text-muted text-center py-3">No hay productos con stock bajo</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Actual</th>
                                    <th>Mínimo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productosBajoStock as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo number_format($producto['stock_actual'], 2); ?></td>
                                    <td><?php echo number_format($producto['stock_minimo'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-circle"></i> Bajo
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Servicios pendientes -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check me-2"></i>Servicios Pendientes
            </div>
            <div class="card-body">
                <?php if (empty($serviciosPendientes)): ?>
                    <p class="text-muted text-center py-3">No hay servicios pendientes</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($serviciosPendientes as $servicio): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($servicio['titulo']); ?></h6>
                                <small>
                                    <?php
                                    $badges = [
                                        'pendiente' => 'bg-warning',
                                        'en_proceso' => 'bg-info',
                                        'completado' => 'bg-success',
                                        'cancelado' => 'bg-danger'
                                    ];
                                    ?>
                                    <span class="badge <?php echo $badges[$servicio['estado']]; ?>">
                                        <?php echo ucfirst($servicio['estado']); ?>
                                    </span>
                                </small>
                            </div>
                            <p class="mb-1 text-muted small">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($servicio['cliente']); ?> |
                                <i class="bi bi-wrench"></i> <?php echo htmlspecialchars($servicio['tecnico']); ?>
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i>
                                <?php echo date('d/m/Y', strtotime($servicio['fecha_programada'])); ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Últimos gastos -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-receipt me-2"></i>Últimos Gastos Registrados
            </div>
            <div class="card-body">
                <?php if (empty($ultimosGastos)): ?>
                    <p class="text-muted text-center py-3">No hay gastos registrados</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Categoría</th>
                                    <th>Forma de Pago</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosGastos as $gasto): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($gasto['fecha_gasto'])); ?></td>
                                    <td><?php echo htmlspecialchars($gasto['concepto']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($gasto['categoria']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst($gasto['forma_pago']); ?></td>
                                    <td class="text-end fw-bold">$<?php echo number_format($gasto['monto'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Preparar datos para gráficas
$gastosLabels = json_encode(array_column($gastosChart, 'nombre'));
$gastosData = json_encode(array_column($gastosChart, 'total'));

$ventasLabels = json_encode(array_column($ventasMes, 'mes'));
$ventasData = json_encode(array_column($ventasMes, 'total'));

$ingresosLabels = json_encode(array_column($ingresosMes, 'mes'));
$ingresosData = json_encode(array_column($ingresosMes, 'total'));

$ingresosVsGastosLabels = json_encode(array_column($ingresosVsGastos, 'mes'));
$ingresosVsGastosIngresos = json_encode(array_column($ingresosVsGastos, 'ingresos'));
$ingresosVsGastosGastos = json_encode(array_column($ingresosVsGastos, 'gastos'));

$extraJs = <<<JS
<script>
// Gráfica de gastos por categoría
const gastosCtx = document.getElementById('gastosChart').getContext('2d');
new Chart(gastosCtx, {
    type: 'doughnut',
    data: {
        labels: $gastosLabels,
        datasets: [{
            data: $gastosData,
            backgroundColor: [
                'rgba(102, 126, 234, 0.8)',
                'rgba(118, 75, 162, 0.8)',
                'rgba(237, 100, 166, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfica de Ingresos vs Gastos
const ingresosVsGastosCtx = document.getElementById('ingresosVsGastosChart').getContext('2d');
new Chart(ingresosVsGastosCtx, {
    type: 'bar',
    data: {
        labels: $ingresosVsGastosLabels,
        datasets: [
            {
                label: 'Ingresos',
                data: $ingresosVsGastosIngresos,
                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 2
            },
            {
                label: 'Gastos',
                data: $ingresosVsGastosGastos,
                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: 2
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2});
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

// Gráfica de tendencia de ingresos
const ingresosCtx = document.getElementById('ingresosChart').getContext('2d');
new Chart(ingresosCtx, {
    type: 'line',
    data: {
        labels: $ingresosLabels,
        datasets: [{
            label: 'Ingresos',
            data: $ingresosData,
            borderColor: 'rgba(25, 135, 84, 1)',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
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

// Gráfica de tendencia de gastos
const gastosCtx2 = document.getElementById('gastosChart2').getContext('2d');
new Chart(gastosCtx2, {
    type: 'line',
    data: {
        labels: $ventasLabels,
        datasets: [{
            label: 'Gastos',
            data: $ventasData,
            borderColor: 'rgba(220, 53, 69, 1)',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
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
</script>
JS;
?>
