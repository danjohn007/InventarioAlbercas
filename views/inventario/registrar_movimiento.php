<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Info del producto -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                        <p class="text-muted mb-1">
                            <strong>Código:</strong> <?php echo htmlspecialchars($producto['codigo']); ?>
                        </p>
                        <?php if (!empty($producto['descripcion'])): ?>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-end">
                        <h2 class="mb-0">
                            <span class="badge <?php echo $producto['stock_actual'] <= $producto['stock_minimo'] ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo number_format($producto['stock_actual'], 2); ?>
                            </span>
                        </h2>
                        <small class="text-muted"><?php echo htmlspecialchars($producto['unidad_medida']); ?> disponibles</small>
                        <br>
                        <small class="text-muted">Mínimo: <?php echo number_format($producto['stock_minimo'], 2); ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulario de movimiento -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Registrar Movimiento</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>inventario/guardar-movimiento" id="formMovimiento">
                    <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="tipo_movimiento" class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipo_movimiento" name="tipo_movimiento" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="entrada">Entrada - Agregar al inventario</option>
                            <option value="salida">Salida - Retirar del inventario</option>
                            <option value="ajuste">Ajuste - Establecer cantidad exacta</option>
                        </select>
                        <div id="tipoHelp" class="form-text"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                       step="0.01" min="0.01" required>
                                <span class="input-group-text"><?php echo htmlspecialchars($producto['unidad_medida']); ?></span>
                            </div>
                            <div id="cantidadHelp" class="form-text"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="costo_unitario" class="form-label">Costo Unitario</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="costo_unitario" name="costo_unitario" 
                                       step="0.01" min="0" value="<?php echo $producto['costo_unitario']; ?>">
                            </div>
                            <small class="form-text text-muted">Dejar en 0 para usar costo actual</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="motivo" name="motivo" 
                               placeholder="Ej: Compra a proveedor, Uso en servicio #123, Ajuste por inventario físico" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                  placeholder="Detalles adicionales (opcional)"></textarea>
                    </div>
                    
                    <div id="alertaStock" class="alert alert-warning d-none">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <span id="alertaStockText"></span>
                    </div>
                    
                    <div id="resultadoPreview" class="alert alert-info d-none">
                        <strong>Vista previa:</strong>
                        <br>Stock actual: <strong><?php echo number_format($producto['stock_actual'], 2); ?></strong>
                        <br>Stock después del movimiento: <strong id="stockNuevo">-</strong>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>inventario" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="bi bi-save"></i> Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo_movimiento');
    const cantidadInput = document.getElementById('cantidad');
    const stockActual = <?php echo $producto['stock_actual']; ?>;
    const btnGuardar = document.getElementById('btnGuardar');
    const alertaStock = document.getElementById('alertaStock');
    const alertaStockText = document.getElementById('alertaStockText');
    const resultadoPreview = document.getElementById('resultadoPreview');
    const stockNuevo = document.getElementById('stockNuevo');
    const tipoHelp = document.getElementById('tipoHelp');
    const cantidadHelp = document.getElementById('cantidadHelp');
    
    function actualizarPreview() {
        const tipo = tipoSelect.value;
        const cantidad = parseFloat(cantidadInput.value) || 0;
        
        tipoHelp.textContent = '';
        cantidadHelp.textContent = '';
        alertaStock.classList.add('d-none');
        resultadoPreview.classList.add('d-none');
        btnGuardar.disabled = false;
        
        if (!tipo || cantidad <= 0) return;
        
        let nuevoStock = stockActual;
        
        if (tipo === 'entrada') {
            nuevoStock = stockActual + cantidad;
            tipoHelp.textContent = 'Se agregará stock al inventario';
            tipoHelp.className = 'form-text text-success';
        } else if (tipo === 'salida') {
            nuevoStock = stockActual - cantidad;
            tipoHelp.textContent = 'Se restará stock del inventario';
            tipoHelp.className = 'form-text text-danger';
            
            if (nuevoStock < 0) {
                alertaStock.classList.remove('d-none');
                alertaStockText.textContent = 'ERROR: No hay suficiente stock. Stock disponible: ' + stockActual.toFixed(2);
                btnGuardar.disabled = true;
                return;
            }
        } else if (tipo === 'ajuste') {
            nuevoStock = cantidad;
            tipoHelp.textContent = 'Se establecerá el stock exacto a esta cantidad';
            tipoHelp.className = 'form-text text-info';
            cantidadHelp.textContent = 'Ingrese la cantidad TOTAL que debe quedar en inventario';
        }
        
        resultadoPreview.classList.remove('d-none');
        stockNuevo.textContent = nuevoStock.toFixed(2);
        
        if (nuevoStock <= <?php echo $producto['stock_minimo']; ?>) {
            stockNuevo.classList.add('text-danger');
            alertaStock.classList.remove('d-none');
            alertaStockText.textContent = 'ADVERTENCIA: El stock quedará por debajo del mínimo (' + <?php echo $producto['stock_minimo']; ?> + ')';
        } else {
            stockNuevo.classList.remove('text-danger');
        }
    }
    
    tipoSelect.addEventListener('change', actualizarPreview);
    cantidadInput.addEventListener('input', actualizarPreview);
    
    document.getElementById('formMovimiento').addEventListener('submit', function(e) {
        const tipo = tipoSelect.value;
        const cantidad = parseFloat(cantidadInput.value) || 0;
        
        if (tipo === 'salida' && (stockActual - cantidad) < 0) {
            e.preventDefault();
            alert('No hay suficiente stock disponible');
            return false;
        }
        
        return true;
    });
});
</script>
