<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>servicios">Servicios</a></li>
                <li class="breadcrumb-item active">Detalle del Servicio</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-tools me-2"></i><?php echo htmlspecialchars($servicio['titulo']); ?></h2>
            <?php if (Auth::can('servicios', 'actualizar')): ?>
                <a href="<?php echo BASE_URL; ?>servicios/editar/<?php echo $servicio['id']; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Información General -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Tipo de Servicio:</strong><br>
                        <?php
                        $tipos = [
                            'mantenimiento' => 'Mantenimiento',
                            'reparacion' => 'Reparación',
                            'instalacion' => 'Instalación',
                            'otro' => 'Otro'
                        ];
                        echo $tipos[$servicio['tipo_servicio']];
                        ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Estado:</strong><br>
                        <?php
                        $estados = [
                            'pendiente' => ['class' => 'warning', 'label' => 'Pendiente'],
                            'en_proceso' => ['class' => 'info', 'label' => 'En Proceso'],
                            'completado' => ['class' => 'success', 'label' => 'Completado'],
                            'cancelado' => ['class' => 'secondary', 'label' => 'Cancelado']
                        ];
                        $est = $estados[$servicio['estado']];
                        ?>
                        <span class="badge bg-<?php echo $est['class']; ?> fs-6"><?php echo $est['label']; ?></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Fecha Programada:</strong><br>
                        <?php echo date('d/m/Y', strtotime($servicio['fecha_programada'])); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Técnico Asignado:</strong><br>
                        <?php echo htmlspecialchars($servicio['tecnico_nombre']); ?>
                    </div>
                </div>
                <?php if ($servicio['fecha_inicio']): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Fecha Inicio:</strong><br>
                            <?php echo date('d/m/Y H:i', strtotime($servicio['fecha_inicio'])); ?>
                        </div>
                        <?php if ($servicio['fecha_fin']): ?>
                            <div class="col-md-6 mb-3">
                                <strong>Fecha Fin:</strong><br>
                                <?php echo date('d/m/Y H:i', strtotime($servicio['fecha_fin'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($servicio['descripcion']): ?>
                    <div class="mb-3">
                        <strong>Descripción:</strong><br>
                        <?php echo nl2br(htmlspecialchars($servicio['descripcion'])); ?>
                    </div>
                <?php endif; ?>
                <?php if ($servicio['direccion_servicio']): ?>
                    <div class="mb-3">
                        <strong>Dirección del Servicio:</strong><br>
                        <?php echo nl2br(htmlspecialchars($servicio['direccion_servicio'])); ?>
                    </div>
                <?php endif; ?>
                <?php if ($servicio['observaciones']): ?>
                    <div class="mb-3">
                        <strong>Observaciones:</strong><br>
                        <?php echo nl2br(htmlspecialchars($servicio['observaciones'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Materiales Utilizados -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Materiales Utilizados</h5>
                <?php if (Auth::can('servicios', 'actualizar')): ?>
                    <a href="<?php echo BASE_URL; ?>servicios/asignar-material/<?php echo $servicio['id']; ?>" class="btn btn-sm btn-light">
                        <i class="bi bi-plus-lg"></i> Asignar Material
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($materiales)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> No se han asignado materiales a este servicio.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unit.</th>
                                    <th>Costo Total</th>
                                    <?php if (Auth::can('servicios', 'actualizar')): ?>
                                        <th>Acción</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materiales as $material): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($material['codigo']); ?></td>
                                        <td><?php echo htmlspecialchars($material['producto_nombre']); ?></td>
                                        <td><?php echo number_format($material['cantidad'], 2); ?> <?php echo htmlspecialchars($material['unidad_medida']); ?></td>
                                        <td>$<?php echo number_format($material['costo_unitario'], 2); ?></td>
                                        <td><strong>$<?php echo number_format($material['costo_total'], 2); ?></strong></td>
                                        <?php if (Auth::can('servicios', 'actualizar')): ?>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>servicios/eliminar-material/<?php echo $material['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('¿Está seguro de eliminar este material? Se devolverá al inventario.');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial del Cliente -->
        <?php if (!empty($historial)): ?>
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Servicios del Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial as $h): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($h['fecha_programada'])); ?></td>
                                        <td><?php echo htmlspecialchars($h['titulo']); ?></td>
                                        <td>
                                            <?php
                                            $tipos = [
                                                'mantenimiento' => 'Mantenimiento',
                                                'reparacion' => 'Reparación',
                                                'instalacion' => 'Instalación',
                                                'otro' => 'Otro'
                                            ];
                                            echo $tipos[$h['tipo_servicio']];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $estados = [
                                                'pendiente' => ['class' => 'warning', 'label' => 'Pendiente'],
                                                'en_proceso' => ['class' => 'info', 'label' => 'En Proceso'],
                                                'completado' => ['class' => 'success', 'label' => 'Completado'],
                                                'cancelado' => ['class' => 'secondary', 'label' => 'Cancelado']
                                            ];
                                            $est = $estados[$h['estado']];
                                            ?>
                                            <span class="badge bg-<?php echo $est['class']; ?>"><?php echo $est['label']; ?></span>
                                        </td>
                                        <td>$<?php echo number_format($h['total'], 2); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>servicios/ver/<?php echo $h['id']; ?>" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <!-- Información del Cliente -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Cliente</h5>
            </div>
            <div class="card-body">
                <h6><?php echo htmlspecialchars($servicio['cliente_nombre']); ?></h6>
                <?php if ($servicio['cliente_telefono']): ?>
                    <p class="mb-2">
                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($servicio['cliente_telefono']); ?>
                    </p>
                <?php endif; ?>
                <?php if ($servicio['cliente_email']): ?>
                    <p class="mb-2">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($servicio['cliente_email']); ?>
                    </p>
                <?php endif; ?>
                <?php if ($servicio['cliente_direccion']): ?>
                    <p class="mb-0">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($servicio['cliente_direccion']); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen de Costos -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Resumen de Costos</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Mano de Obra:</span>
                    <strong>$<?php echo number_format($servicio['costo_mano_obra'], 2); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Materiales:</span>
                    <strong>$<?php echo number_format($servicio['costo_materiales'], 2); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Otros Gastos:</span>
                    <strong>$<?php echo number_format($servicio['otros_gastos'], 2); ?></strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <h5>Total:</h5>
                    <h5><strong class="text-success">$<?php echo number_format($servicio['total'], 2); ?></strong></h5>
                </div>
            </div>
        </div>

        <!-- Información de Registro -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información de Registro</h6>
            </div>
            <div class="card-body">
                <p class="mb-2 small">
                    <strong>Registrado por:</strong><br>
                    <?php echo htmlspecialchars($servicio['usuario_registro_nombre']); ?>
                </p>
                <p class="mb-2 small">
                    <strong>Fecha de creación:</strong><br>
                    <?php echo date('d/m/Y H:i', strtotime($servicio['fecha_creacion'])); ?>
                </p>
                <?php if ($servicio['fecha_actualizacion']): ?>
                    <p class="mb-0 small">
                        <strong>Última actualización:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($servicio['fecha_actualizacion'])); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
