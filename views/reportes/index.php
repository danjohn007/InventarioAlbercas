<style>
    .report-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .report-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }
    
    .report-gradient-1 {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .report-gradient-2 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .report-gradient-3 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .report-gradient-4 {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
</style>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="bi bi-file-earmark-bar-graph me-2"></i>Centro de Reportes</h2>
        <p class="text-muted">Genera reportes detallados y análisis del sistema</p>
    </div>
</div>

<div class="row g-4">
    <!-- Reporte de Inventario -->
    <div class="col-md-6 col-lg-3">
        <div class="card report-card h-100">
            <div class="card-body text-center report-gradient-1 text-white">
                <i class="bi bi-box-seam report-icon"></i>
                <h4 class="card-title">Reporte de Inventario</h4>
                <p class="card-text mb-4">Análisis completo del inventario, valores totales y stock</p>
                <a href="<?php echo BASE_URL; ?>reportes/inventario" class="btn btn-light">
                    <i class="bi bi-eye"></i> Ver Reporte
                </a>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="bi bi-check-circle me-1"></i>
                    Productos, categorías, alertas de stock
                </small>
            </div>
        </div>
    </div>
    
    <!-- Reporte de Gastos -->
    <div class="col-md-6 col-lg-3">
        <div class="card report-card h-100">
            <div class="card-body text-center report-gradient-2 text-white">
                <i class="bi bi-cash-stack report-icon"></i>
                <h4 class="card-title">Reporte de Gastos</h4>
                <p class="card-text mb-4">Análisis de gastos por categoría y tendencias</p>
                <a href="<?php echo BASE_URL; ?>reportes/gastos" class="btn btn-light">
                    <i class="bi bi-eye"></i> Ver Reporte
                </a>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="bi bi-check-circle me-1"></i>
                    Por categoría, fecha, forma de pago
                </small>
            </div>
        </div>
    </div>
    
    <!-- Reporte de Servicios -->
    <div class="col-md-6 col-lg-3">
        <div class="card report-card h-100">
            <div class="card-body text-center report-gradient-3 text-white">
                <i class="bi bi-tools report-icon"></i>
                <h4 class="card-title">Reporte de Servicios</h4>
                <p class="card-text mb-4">Estadísticas de servicios y técnicos</p>
                <a href="<?php echo BASE_URL; ?>reportes/servicios" class="btn btn-light">
                    <i class="bi bi-eye"></i> Ver Reporte
                </a>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="bi bi-check-circle me-1"></i>
                    Por estado, técnico, fechas
                </small>
            </div>
        </div>
    </div>
    
    <!-- Reporte General -->
    <div class="col-md-6 col-lg-3">
        <div class="card report-card h-100">
            <div class="card-body text-center report-gradient-4 text-white">
                <i class="bi bi-graph-up report-icon"></i>
                <h4 class="card-title">Dashboard General</h4>
                <p class="card-text mb-4">Vista general del sistema</p>
                <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-light">
                    <i class="bi bi-eye"></i> Ver Dashboard
                </a>
            </div>
            <div class="card-footer bg-white">
                <small class="text-muted">
                    <i class="bi bi-check-circle me-1"></i>
                    Métricas principales del negocio
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Información adicional -->
<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Información sobre Reportes</h5>
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="bi bi-funnel text-primary"></i> Filtros Avanzados</h6>
                        <p class="text-muted small">
                            Todos los reportes incluyen filtros personalizables para obtener la información exacta que necesitas.
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="bi bi-graph-up text-success"></i> Visualizaciones</h6>
                        <p class="text-muted small">
                            Gráficos interactivos que te ayudan a comprender tendencias y patrones en tus datos.
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="bi bi-download text-info"></i> Exportación</h6>
                        <p class="text-muted small">
                            Exporta los reportes a PDF o Excel para compartir o archivar.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
