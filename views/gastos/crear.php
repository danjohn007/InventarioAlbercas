<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle me-2"></i>Registrar Gasto</h2>
            <a href="<?php echo BASE_URL; ?>gastos" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Gasto</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>gastos/guardar" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Categoría -->
                        <div class="col-md-6 mb-3">
                            <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select name="categoria_id" id="categoria_id" class="form-select" required>
                                <option value="">Seleccione una categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Concepto -->
                        <div class="col-md-6 mb-3">
                            <label for="concepto" class="form-label">Concepto <span class="text-danger">*</span></label>
                            <input type="text" name="concepto" id="concepto" class="form-control" 
                                   placeholder="Ej: Compra de químicos" required maxlength="200">
                        </div>

                        <!-- Descripción -->
                        <div class="col-md-12 mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" 
                                      rows="3" placeholder="Detalles adicionales del gasto..."></textarea>
                        </div>

                        <!-- Monto -->
                        <div class="col-md-4 mb-3">
                            <label for="monto" class="form-label">Monto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto" id="monto" class="form-control" 
                                       step="0.01" min="0.01" placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- Fecha del Gasto -->
                        <div class="col-md-4 mb-3">
                            <label for="fecha_gasto" class="form-label">Fecha del Gasto <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_gasto" id="fecha_gasto" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <!-- Forma de Pago -->
                        <div class="col-md-4 mb-3">
                            <label for="forma_pago" class="form-label">Forma de Pago <span class="text-danger">*</span></label>
                            <select name="forma_pago" id="forma_pago" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <!-- Servicio (Opcional) -->
                        <div class="col-md-6 mb-3">
                            <label for="servicio_id" class="form-label">Servicio Relacionado (Opcional)</label>
                            <select name="servicio_id" id="servicio_id" class="form-select">
                                <option value="">Sin servicio relacionado</option>
                                <?php foreach ($servicios as $servicio): ?>
                                    <option value="<?php echo $servicio['id']; ?>">
                                        <?php echo htmlspecialchars($servicio['titulo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Asocie este gasto a un servicio específico si aplica</small>
                        </div>

                        <!-- Cliente (Opcional) -->
                        <div class="col-md-6 mb-3">
                            <label for="cliente_id" class="form-label">Cliente (Opcional)</label>
                            <select name="cliente_id" id="cliente_id" class="form-select">
                                <option value="">Sin cliente asociado</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>">
                                        <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellidos']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Proveedor (Opcional) -->
                        <div class="col-md-6 mb-3">
                            <label for="proveedor_id" class="form-label">Proveedor (Opcional)</label>
                            <select name="proveedor_id" id="proveedor_id" class="form-select">
                                <option value="">Sin proveedor asociado</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?php echo $proveedor['id']; ?>">
                                        <?php echo htmlspecialchars($proveedor['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Comprobante (Opcional) -->
                        <div class="col-md-6 mb-3">
                            <label for="comprobante" class="form-label">Comprobante (Opcional)</label>
                            <input type="file" name="comprobante" id="comprobante" class="form-control" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">Formatos permitidos: PDF, JPG, PNG. Máximo 5MB</small>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-12 mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" 
                                      rows="3" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Gasto
                            </button>
                            <a href="<?php echo BASE_URL; ?>gastos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>public/js/gastos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php 
    // Create JS mapping of service to cliente
    $servicioClienteMap = [];
    foreach ($servicios as $servicio) {
        if (!empty($servicio['cliente_id'])) {
            $servicioClienteMap[$servicio['id']] = $servicio['cliente_id'];
        }
    }
    ?>
    
    const servicioClienteMap = <?php echo json_encode($servicioClienteMap); ?>;
    initializeGastosForm(servicioClienteMap);
});
</script>
