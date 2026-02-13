<div class="row">
    <!-- Tarjetas de estadísticas -->
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Productos</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['productos']); ?></h2>
                    </div>
                    <div class="fs-1 text-primary">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Valor Inventario</h6>
                        <h2 class="mb-0">$<?php echo number_format($stats['valor_inventario'], 2); ?></h2>
                    </div>
                    <div class="fs-1 text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Gastos del Mes</h6>
                        <h2 class="mb-0">$<?php echo number_format($stats['gastos_mes'], 2); ?></h2>
                    </div>
                    <div class="fs-1 text-danger">
                        <i class="bi bi-graph-down-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Servicios Activos</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['servicios_activos']); ?></h2>
                    </div>
                    <div class="fs-1 text-info">
                        <i class="bi bi-tools"></i>
                    </div>
                </div>
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
    
    <!-- Gráfica de tendencia de gastos -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>Tendencia de Gastos (6 meses)
            </div>
            <div class="card-body">
                <canvas id="ventasChart" height="250"></canvas>
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

// Gráfica de tendencia de ventas
const ventasCtx = document.getElementById('ventasChart').getContext('2d');
new Chart(ventasCtx, {
    type: 'line',
    data: {
        labels: $ventasLabels,
        datasets: [{
            label: 'Gastos',
            data: $ventasData,
            borderColor: 'rgba(102, 126, 234, 1)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
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
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
JS;
?>
