<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>servicios">Servicios</a></li>
                <li class="breadcrumb-item active">Crear Servicio</li>
            </ol>
        </nav>
        <h2><i class="bi bi-tools me-2"></i>Crear Servicio</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>servicios/guardar">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select name="cliente_id" class="form-select" required>
                                <option value="">Seleccione un cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>">
                                        <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Servicio <span class="text-danger">*</span></label>
                            <select name="tipo_servicio" class="form-select" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="reparacion">Reparación</option>
                                <option value="instalacion">Instalación</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" class="form-control" required 
                               placeholder="Ej: Limpieza y mantenimiento de alberca">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" 
                                  placeholder="Detalles del servicio a realizar"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección del Servicio</label>
                        <textarea name="direccion_servicio" class="form-control" rows="2" 
                                  placeholder="Dirección donde se realizará el servicio"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Programada <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_programada" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Técnico Asignado <span class="text-danger">*</span></label>
                            <select name="tecnico_id" class="form-select" required>
                                <option value="">Seleccione un técnico</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?php echo $tecnico['id']; ?>">
                                        <?php echo htmlspecialchars($tecnico['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="pendiente" selected>Pendiente</option>
                                <option value="en_proceso">En Proceso</option>
                                <option value="completado">Completado</option>
                                <option value="cancelado">Cancelado</option>
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
                                       step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Otros Gastos</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="otros_gastos" class="form-control" 
                                       step="0.01" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>servicios" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Servicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Información</h5>
                <p class="mb-2"><small>Los campos marcados con <span class="text-danger">*</span> son obligatorios.</small></p>
                <hr>
                <h6>Sobre los Materiales:</h6>
                <p class="small">Podrá asignar materiales al servicio después de crearlo, desde la vista de detalle del servicio.</p>
                <hr>
                <h6>Cálculo de Total:</h6>
                <p class="small">El costo total se calculará automáticamente sumando:</p>
                <ul class="small">
                    <li>Costo de mano de obra</li>
                    <li>Costo de materiales (asignados)</li>
                    <li>Otros gastos</li>
                </ul>
            </div>
        </div>
    </div>
</div>
