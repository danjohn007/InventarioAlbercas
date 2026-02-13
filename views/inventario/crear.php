<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Crear Nuevo Producto</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>inventario/guardar">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" 
                                   placeholder="Dejar vacío para generar automático">
                            <small class="text-muted">Si se deja vacío, se generará automáticamente</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="unidad_medida" class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                            <select class="form-select" id="unidad_medida" name="unidad_medida" required>
                                <option value="">Seleccionar</option>
                                <option value="pieza">Pieza</option>
                                <option value="kg">Kilogramo (kg)</option>
                                <option value="litro">Litro</option>
                                <option value="metro">Metro</option>
                                <option value="caja">Caja</option>
                                <option value="paquete">Paquete</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="costo_unitario" class="form-label">Costo Unitario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="costo_unitario" name="costo_unitario" 
                                       step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="precio_venta" class="form-label">Precio Venta</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                       step="0.01" min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                                   step="0.01" min="0" value="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="proveedor_id" class="form-label">Proveedor</label>
                        <select class="form-select" id="proveedor_id" name="proveedor_id">
                            <option value="">Sin proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?php echo $proveedor['id']; ?>">
                                    <?php echo htmlspecialchars($proveedor['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Nota:</strong> El stock inicial será 0. Use "Registrar Movimiento" para agregar stock después de crear el producto.
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>inventario" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
