<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar Producto</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>inventario/actualizar">
                    <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="codigo" name="codigo" 
                                   value="<?php echo htmlspecialchars($producto['codigo']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>" 
                                            <?php echo $producto['categoria_id'] == $categoria['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="unidad_medida" class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                            <select class="form-select" id="unidad_medida" name="unidad_medida" required>
                                <option value="">Seleccionar</option>
                                <option value="pieza" <?php echo $producto['unidad_medida'] == 'pieza' ? 'selected' : ''; ?>>Pieza</option>
                                <option value="kg" <?php echo $producto['unidad_medida'] == 'kg' ? 'selected' : ''; ?>>Kilogramo (kg)</option>
                                <option value="litro" <?php echo $producto['unidad_medida'] == 'litro' ? 'selected' : ''; ?>>Litro</option>
                                <option value="metro" <?php echo $producto['unidad_medida'] == 'metro' ? 'selected' : ''; ?>>Metro</option>
                                <option value="caja" <?php echo $producto['unidad_medida'] == 'caja' ? 'selected' : ''; ?>>Caja</option>
                                <option value="paquete" <?php echo $producto['unidad_medida'] == 'paquete' ? 'selected' : ''; ?>>Paquete</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="costo_unitario" class="form-label">Costo Unitario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="costo_unitario" name="costo_unitario" 
                                       step="0.01" min="0.01" value="<?php echo $producto['costo_unitario']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="precio_venta" class="form-label">Precio Venta</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="precio_venta" name="precio_venta" 
                                       step="0.01" min="0" value="<?php echo $producto['precio_venta']; ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                                   step="0.01" min="0" value="<?php echo $producto['stock_minimo']; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="proveedor_id" class="form-label">Proveedor</label>
                            <select class="form-select" id="proveedor_id" name="proveedor_id">
                                <option value="">Sin proveedor</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?php echo $proveedor['id']; ?>" 
                                            <?php echo $producto['proveedor_id'] == $proveedor['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($proveedor['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="activo" class="form-label">Estado</label>
                            <select class="form-select" id="activo" name="activo">
                                <option value="1" <?php echo $producto['activo'] == 1 ? 'selected' : ''; ?>>Activo</option>
                                <option value="0" <?php echo $producto['activo'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Stock actual:</strong> <?php echo number_format($producto['stock_actual'], 2); ?> <?php echo $producto['unidad_medida']; ?>
                        <br>Para modificar el stock, use la opción "Registrar Movimiento".
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>inventario" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Actualizar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
