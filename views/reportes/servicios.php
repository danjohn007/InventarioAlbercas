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
                <h2><i class="bi bi-tools me-2"></i>Reporte de Servicios</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>reportes">Reportes</a></li>
                        <li class="breadcrumb-item active">Servicios</li>
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
        <form method="GET" action="<?php echo BASE_URL; ?>reportes/servicios" class="row g-3">
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
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="pendiente" <?php echo $estado == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="en_proceso" <?php echo $estado == 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                    <option value="completado" <?php echo $estado == 'completado' ? 'selected' : ''; ?>>Completado</option>
                    <option value="cancelado" <?php echo $estado == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Técnico</label>
                <select name="tecnico_id" class="form-select">
                    <option value="0">Todos los Técnicos</option>
                    <?php foreach ($tecnicos as $tec): ?>
                        <option value="<?php echo $tec['id']; ?>" 
                                <?php echo $tecnico_id == $tec['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tec['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
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
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Servicios</h6>
                        <h3 class="card-title mb-0"><?php echo number_format($stats['total_servicios']); ?></h3>
                    </div>
                    <i class="bi bi-tools stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Ingresos Totales</h6>
                        <h3 class="card-title mb-0">$<?php echo number_format($stats['total_ingresos'], 2); ?></h3>
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
                        <h6 class="card-subtitle mb-2">Costo Promedio</h6>
                        <h3 class="card-title mb-0">$<?php echo number_format($stats['promedio_costo'], 2); ?></h3>
                    </div>
                    <i class="bi bi-calculator stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">En Proceso</h6>
                        <h3 class="card-title mb-0"><?php echo number_format($stats['servicios_en_proceso']); ?></h3>
                    </div>
                    <i class="bi bi-hourglass-split stat-card-icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estados de Servicios -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="bi bi-clock-history text-secondary" style="font-size: 2rem;"></i>
                            <h4 class="mt-2"><?php echo number_format($stats['servicios_pendientes']); ?></h4>
                            <p class="text-muted mb-0">Pendientes</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="bi bi-gear-fill text-warning" style="font-size: 2rem;"></i>
                            <h4 class="mt-2"><?php echo number_format($stats['servicios_en_proceso']); ?></h4>
                            <p class="text-muted mb-0">En Proceso</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                            <h4 class="mt-2"><?php echo number_format($stats['servicios_completados']); ?></h4>
                            <p class="text-muted mb-0">Completados</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 2rem;"></i>
                            <h4 class="mt-2"><?php echo number_format($stats['servicios_cancelados']); ?></h4>
                            <p class="text-muted mb-0">Cancelados</p>
                        </div>
                    </div>
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
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Servicios por Estado</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="estadoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Desglose de Costos</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="costosChart"></canvas>
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
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Tendencia Mensual de Servicios e Ingresos</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="mensualChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rendimiento por Técnico -->
<?php if (!empty($servicios_por_tecnico)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Rendimiento por Técnico</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Técnico</th>
                                <th>Total Servicios</th>
                                <th>Servicios Completados</th>
                                <th>Tasa de Éxito</th>
                                <th>Ingresos Generados</th>
                                <th>Promedio por Servicio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios_por_tecnico as $tecnico): ?>
                            <?php 
                                $tasa_exito = $tecnico['total_servicios'] > 0 
                                    ? ($tecnico['servicios_completados'] / $tecnico['total_servicios']) * 100 
                                    : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($tecnico['nombre']); ?></strong></td>
                                <td><?php echo number_format($tecnico['total_servicios']); ?></td>
                                <td><?php echo number_format($tecnico['servicios_completados']); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                            <div class="progress-bar <?php echo $tasa_exito >= 80 ? 'bg-success' : ($tasa_exito >= 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $tasa_exito; ?>%;" 
                                                 aria-valuenow="<?php echo $tasa_exito; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span><?php echo number_format($tasa_exito, 1); ?>%</span>
                                    </div>
                                </td>
                                <td class="text-success"><strong>$<?php echo number_format($tecnico['total_ingresos'], 2); ?></strong></td>
                                <td>$<?php echo number_format($tecnico['promedio_costo'], 2); ?></td>
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

<!-- Desglose de Costos -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Desglose de Costos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <i class="bi bi-wrench text-primary" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Mano de Obra</h5>
                            <h3 class="text-primary">$<?php echo number_format($costos_breakdown['total_mano_obra'], 2); ?></h3>
                            <small class="text-muted">
                                <?php 
                                    $porcentaje_mo = $costos_breakdown['total_general'] > 0 
                                        ? ($costos_breakdown['total_mano_obra'] / $costos_breakdown['total_general']) * 100 
                                        : 0;
                                    echo number_format($porcentaje_mo, 1); 
                                ?>% del total
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <i class="bi bi-box-seam text-info" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Materiales</h5>
                            <h3 class="text-info">$<?php echo number_format($costos_breakdown['total_materiales'], 2); ?></h3>
                            <small class="text-muted">
                                <?php 
                                    $porcentaje_mat = $costos_breakdown['total_general'] > 0 
                                        ? ($costos_breakdown['total_materiales'] / $costos_breakdown['total_general']) * 100 
                                        : 0;
                                    echo number_format($porcentaje_mat, 1); 
                                ?>% del total
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded">
                            <i class="bi bi-truck text-warning" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Desplazamiento</h5>
                            <h3 class="text-warning">$<?php echo number_format($costos_breakdown['total_desplazamiento'], 2); ?></h3>
                            <small class="text-muted">
                                <?php 
                                    $porcentaje_desp = $costos_breakdown['total_general'] > 0 
                                        ? ($costos_breakdown['total_desplazamiento'] / $costos_breakdown['total_general']) * 100 
                                        : 0;
                                    echo number_format($porcentaje_desp, 1); 
                                ?>% del total
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border rounded bg-light">
                            <i class="bi bi-receipt text-success" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Total General</h5>
                            <h3 class="text-success">$<?php echo number_format($costos_breakdown['total_general'], 2); ?></h3>
                            <small class="text-muted">100% del total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Clientes -->
<?php if (!empty($top_clientes)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top 10 Clientes por Servicios</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Total Servicios</th>
                                <th>Total Gastado</th>
                                <th>Promedio por Servicio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 1; ?>
                            <?php foreach ($top_clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <?php if ($index <= 3): ?>
                                        <i class="bi bi-trophy-fill text-warning"></i>
                                    <?php endif; ?>
                                    <strong><?php echo $index++; ?></strong>
                                </td>
                                <td><strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                                <td><?php echo number_format($cliente['total_servicios']); ?></td>
                                <td class="text-success"><strong>$<?php echo number_format($cliente['total_gastado'], 2); ?></strong></td>
                                <td>$<?php echo number_format($cliente['total_gastado'] / $cliente['total_servicios'], 2); ?></td>
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
// Gráfico de Estados
const estadoData = {
    labels: <?php echo json_encode(array_map(function($e) { 
        $nombres = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado'
        ];
        return $nombres[$e['estado']] ?? $e['estado'];
    }, $servicios_por_estado)); ?>,
    datasets: [{
        data: <?php echo json_encode(array_map(function($e) { return $e['cantidad']; }, $servicios_por_estado)); ?>,
        backgroundColor: [
            'rgba(108, 117, 125, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(25, 135, 84, 0.8)',
            'rgba(220, 53, 69, 0.8)'
        ],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

const estadoChart = new Chart(document.getElementById('estadoChart'), {
    type: 'doughnut',
    data: estadoData,
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

// Gráfico de Desglose de Costos
const costosData = {
    labels: ['Mano de Obra', 'Materiales', 'Desplazamiento'],
    datasets: [{
        data: [
            <?php echo $costos_breakdown['total_mano_obra']; ?>,
            <?php echo $costos_breakdown['total_materiales']; ?>,
            <?php echo $costos_breakdown['total_desplazamiento']; ?>
        ],
        backgroundColor: [
            'rgba(102, 126, 234, 0.8)',
            'rgba(13, 202, 240, 0.8)',
            'rgba(255, 193, 7, 0.8)'
        ],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

const costosChart = new Chart(document.getElementById('costosChart'), {
    type: 'pie',
    data: costosData,
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
    }, $servicios_mensuales)); ?>,
    datasets: [
        {
            label: 'Cantidad de Servicios',
            data: <?php echo json_encode(array_map(function($m) { return $m['cantidad']; }, $servicios_mensuales)); ?>,
            backgroundColor: 'rgba(102, 126, 234, 0.2)',
            borderColor: 'rgba(102, 126, 234, 1)',
            borderWidth: 3,
            yAxisID: 'y',
            tension: 0.4
        },
        {
            label: 'Ingresos ($)',
            data: <?php echo json_encode(array_map(function($m) { return $m['total_ingresos']; }, $servicios_mensuales)); ?>,
            backgroundColor: 'rgba(25, 135, 84, 0.2)',
            borderColor: 'rgba(25, 135, 84, 1)',
            borderWidth: 3,
            yAxisID: 'y1',
            tension: 0.4
        }
    ]
};

const mensualChart = new Chart(document.getElementById('mensualChart'), {
    type: 'line',
    data: mensualData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Cantidad'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Ingresos ($)'
                },
                grid: {
                    drawOnChartArea: false,
                },
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
