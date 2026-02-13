<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>servicios">Servicios</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>servicios/ver/<?php echo $servicio['id']; ?>">Detalle</a></li>
                <li class="breadcrumb-item active">Asignar Material</li>
            </ol>
        </nav>
        <h2><i class="bi bi-box-seam me-2"></i>Asignar Material al Servicio</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Información del Servicio</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Título:</strong> <?php echo htmlspecialchars($servicio['titulo']); ?></p>
                <p class="mb-0"><strong>Fecha Programada:</strong> <?php echo date('d/m/Y', strtotime($servicio['fecha_programada'])); ?></p>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>servicios/guardar-material" id="formAsignarMaterial">
                    <input type="hidden" name="servicio_id" value="<?php echo $servicio['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label">Producto <span class="text-danger">*</span></label>
                        <select name="producto_id" id="producto_id" class="form-select" required>
                            <option value="">Seleccione un producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>" 
                                        data-stock="<?php echo $producto['stock_actual']; ?>"
                                        data-costo="<?php echo $producto['costo_unitario']; ?>"
                                        data-unidad="<?php echo htmlspecialchars($producto['unidad_medida']); ?>">
                                    <?php echo htmlspecialchars($producto['codigo'] . ' - ' . $producto['nombre']); ?> 
                                    (Stock: <?php echo number_format($producto['stock_actual'], 2); ?> <?php echo htmlspecialchars($producto['unidad_medida']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="cantidad" id="cantidad" class="form-control" 
                                       step="0.01" min="0.01" required>
                                <span class="input-group-text" id="unidadMedida">-</span>
                            </div>
                            <small class="form-text text-muted">Stock disponible: <span id="stockDisponible">-</span></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Costo Unitario</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="costoUnitario" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Costo Total Estimado</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control" id="costoTotal" readonly value="0.00">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>servicios/ver/<?php echo $servicio['id']; ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Asignar Material
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
                <h6>Importante:</h6>
                <ul class="small">
                    <li>Al asignar un material, se descontará automáticamente del inventario</li>
                    <li>Verifique el stock disponible antes de asignar</li>
                    <li>El costo del material se sumará al total del servicio</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productoSelect = document.getElementById('producto_id');
    const cantidadInput = document.getElementById('cantidad');
    const stockDisponible = document.getElementById('stockDisponible');
    const unidadMedida = document.getElementById('unidadMedida');
    const costoUnitario = document.getElementById('costoUnitario');
    const costoTotal = document.getElementById('costoTotal');
    
    productoSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            const stock = parseFloat(option.dataset.stock);
            const costo = parseFloat(option.dataset.costo);
            const unidad = option.dataset.unidad;
            
            stockDisponible.textContent = stock.toFixed(2) + ' ' + unidad;
            unidadMedida.textContent = unidad;
            costoUnitario.value = costo.toFixed(2);
            cantidadInput.max = stock;
            cantidadInput.value = '';
            costoTotal.value = '0.00';
        } else {
            stockDisponible.textContent = '-';
            unidadMedida.textContent = '-';
            costoUnitario.value = '';
            cantidadInput.value = '';
            cantidadInput.removeAttribute('max');
            costoTotal.value = '0.00';
        }
    });
    
    cantidadInput.addEventListener('input', function() {
        const cantidad = parseFloat(this.value) || 0;
        const costo = parseFloat(costoUnitario.value) || 0;
        const total = cantidad * costo;
        costoTotal.value = total.toFixed(2);
    });
    
    document.getElementById('formAsignarMaterial').addEventListener('submit', function(e) {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const stock = parseFloat(cantidadInput.max) || 0;
        
        if (cantidad > stock) {
            e.preventDefault();
            alert('La cantidad solicitada excede el stock disponible');
            return false;
        }
    });
});
</script>
