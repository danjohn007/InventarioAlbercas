<style>
    .audit-filters {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .audit-table {
        font-size: 0.9rem;
    }
    
    .audit-table .action-badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .action-crear { background: #d4edda; color: #155724; }
    .action-actualizar { background: #d1ecf1; color: #0c5460; }
    .action-eliminar { background: #f8d7da; color: #721c24; }
    .action-leer { background: #e2e3e5; color: #383d41; }
    .action-exportar { background: #fff3cd; color: #856404; }
    .action-importar { background: #cce5ff; color: #004085; }
    .action-restablecer { background: #ffeaa7; color: #6c5f0b; }
    .action-test_email { background: #d4edff; color: #084298; }
</style>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-clock-history me-2"></i>Historial de Auditoría</h2>
                <p class="text-muted">Registro completo de todas las acciones realizadas en el sistema</p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>configuraciones" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a Configuraciones
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="audit-filters">
    <form method="GET" action="<?php echo BASE_URL; ?>configuraciones/auditoria">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Usuario</label>
                <select name="usuario_id" class="form-select">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario['id']; ?>" 
                                <?php echo (isset($_GET['usuario_id']) && $_GET['usuario_id'] == $usuario['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-bold">Acción</label>
                <select name="accion" class="form-select">
                    <option value="">Todas las acciones</option>
                    <?php foreach ($acciones as $accion): ?>
                        <option value="<?php echo $accion['accion']; ?>"
                                <?php echo (isset($_GET['accion']) && $_GET['accion'] == $accion['accion']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($accion['accion']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-bold">Tabla/Módulo</label>
                <select name="tabla" class="form-select">
                    <option value="">Todas las tablas</option>
                    <?php foreach ($tablas as $tabla): ?>
                        <option value="<?php echo $tabla['tabla']; ?>"
                                <?php echo (isset($_GET['tabla']) && $_GET['tabla'] == $tabla['tabla']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tabla['tabla']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-bold">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" 
                       value="<?php echo $_GET['fecha_desde'] ?? ''; ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label fw-bold">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" 
                       value="<?php echo $_GET['fecha_hasta'] ?? ''; ?>">
            </div>
            
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </div>
        
        <?php if (!empty($_GET['usuario_id']) || !empty($_GET['accion']) || !empty($_GET['tabla']) || 
                  !empty($_GET['fecha_desde']) || !empty($_GET['fecha_hasta'])): ?>
        <div class="mt-2">
            <a href="<?php echo BASE_URL; ?>configuraciones/auditoria" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Limpiar Filtros
            </a>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Estadísticas -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Total de registros:</strong> <?php echo number_format($total); ?> 
            <?php if ($total > $perPage): ?>
                | <strong>Página:</strong> <?php echo $page; ?> de <?php echo $totalPages; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabla de Auditoría -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover audit-table">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th width="15%">Fecha y Hora</th>
                        <th width="15%">Usuario</th>
                        <th width="10%">Acción</th>
                        <th width="10%">Tabla</th>
                        <th width="30%">Detalles</th>
                        <th width="15%">IP / User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auditLogs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                <p class="text-muted mt-2">No se encontraron registros de auditoría</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td><?php echo $log['id']; ?></td>
                                <td>
                                    <small class="d-block"><?php echo date('d/m/Y', strtotime($log['fecha_creacion'])); ?></small>
                                    <small class="text-muted"><?php echo date('H:i:s', strtotime($log['fecha_creacion'])); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['nombre'] . ' ' . $log['apellidos']); ?></strong>
                                    <small class="d-block text-muted">@<?php echo htmlspecialchars($log['usuario']); ?></small>
                                </td>
                                <td>
                                    <span class="action-badge action-<?php echo $log['accion']; ?>">
                                        <?php echo htmlspecialchars($log['accion']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['tabla']): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($log['tabla']); ?></span>
                                        <?php if ($log['registro_id']): ?>
                                            <small class="d-block text-muted">ID: <?php echo $log['registro_id']; ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($log['detalles'] ?? '-'); ?></small>
                                </td>
                                <td>
                                    <small class="d-block"><?php echo htmlspecialchars($log['ip_address']); ?></small>
                                    <small class="text-muted" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                        <?php 
                                        $ua = $log['user_agent'];
                                        if (strlen($ua) > 30) {
                                            echo htmlspecialchars(substr($ua, 0, 30)) . '...';
                                        } else {
                                            echo htmlspecialchars($ua);
                                        }
                                        ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center mt-3">
                    <!-- Primera página -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>configuraciones/auditoria?page=1<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : ''; ?>">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                    </li>
                    
                    <!-- Página anterior -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>configuraciones/auditoria?page=<?php echo $page - 1; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : ''; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    
                    <!-- Páginas -->
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>configuraciones/auditoria?page=<?php echo $i; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Página siguiente -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>configuraciones/auditoria?page=<?php echo $page + 1; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : ''; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    
                    <!-- Última página -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>configuraciones/auditoria?page=<?php echo $totalPages; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : ''; ?>">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
