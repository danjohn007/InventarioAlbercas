<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>servicios">Servicios</a></li>
                <li class="breadcrumb-item active">Editar Servicio</li>
            </ol>
        </nav>
        <h2><i class="bi bi-pencil-square me-2"></i>Editar Servicio</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>servicios/actualizar">
                    <input type="hidden" name="id" value="<?php echo $servicio['id']; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select name="cliente_id" class="form-select" required>
                                <option value="">Seleccione un cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>" 
                                            <?php echo $servicio['cliente_id'] == $cliente['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Servicio <span class="text-danger">*</span></label>
                            <select name="tipo_servicio" class="form-select" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="mantenimiento" <?php echo $servicio['tipo_servicio'] == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                                <option value="reparacion" <?php echo $servicio['tipo_servicio'] == 'reparacion' ? 'selected' : ''; ?>>Reparación</option>
                                <option value="instalacion" <?php echo $servicio['tipo_servicio'] == 'instalacion' ? 'selected' : ''; ?>>Instalación</option>
                                <option value="otro" <?php echo $servicio['tipo_servicio'] == 'otro' ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" required 
                               value="<?php echo htmlspecialchars($servicio['titulo']); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($servicio['descripcion']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección del Servicio</label>
                        <textarea name="direccion_servicio" class="form-control" rows="2"><?php echo htmlspecialchars($servicio['direccion_servicio']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Programada <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_programada" class="form-control" required
                                   value="<?php echo $servicio['fecha_programada']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="datetime-local" name="fecha_inicio" class="form-control"
                                   value="<?php echo $servicio['fecha_inicio'] ? date('Y-m-d\TH:i', strtotime($servicio['fecha_inicio'])) : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="datetime-local" name="fecha_fin" class="form-control"
                                   value="<?php echo $servicio['fecha_fin'] ? date('Y-m-d\TH:i', strtotime($servicio['fecha_fin'])) : ''; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Técnico Asignado <span class="text-danger">*</span></label>
                            <select name="tecnico_id" class="form-select" required>
                                <option value="">Seleccione un técnico</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?php echo $tecnico['id']; ?>"
                                            <?php echo $servicio['tecnico_id'] == $tecnico['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tecnico['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="pendiente" <?php echo $servicio['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="en_proceso" <?php echo $servicio['estado'] == 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                                <option value="completado" <?php echo $servicio['estado'] == 'completado' ? 'selected' : ''; ?>>Completado</option>
                                <option value="cancelado" <?php echo $servicio['estado'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Costos</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Costo Mano de Obra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="costo_mano_obra" class="form-control" 
                                       step="0.01" min="0" value="<?php echo $servicio['costo_mano_obra']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Otros Gastos</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="otros_gastos" class="form-control" 
                                       step="0.01" min="0" value="<?php echo $servicio['otros_gastos']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Costo de Materiales:</strong> 
                        $<?php echo number_format($servicio['costo_materiales'], 2); ?> (calculado automáticamente)
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"><?php echo htmlspecialchars($servicio['observaciones']); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>servicios/ver/<?php echo $servicio['id']; ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Información</h5>
                <p class="small mb-2">
                    <strong>Fecha de creación:</strong><br>
                    <?php echo date('d/m/Y H:i', strtotime($servicio['fecha_creacion'])); ?>
                </p>
                <?php if ($servicio['fecha_actualizacion']): ?>
                    <p class="small mb-2">
                        <strong>Última actualización:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($servicio['fecha_actualizacion'])); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-cash-stack"></i> Total Estimado</h5>
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
                <hr class="border-white">
                <div class="d-flex justify-content-between">
                    <h5>Total:</h5>
                    <h5><strong>$<?php echo number_format($servicio['total'], 2); ?></strong></h5>
                </div>
            </div>
        </div>
    </div>
</div>
