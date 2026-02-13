<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>clientes">Clientes</a></li>
                <li class="breadcrumb-item active">Editar Cliente</li>
            </ol>
        </nav>
        <h2><i class="bi bi-pencil-square me-2"></i>Editar Cliente</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>clientes/actualizar">
                    <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" 
                                   value="<?php echo htmlspecialchars($cliente['apellidos']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control" 
                                   value="<?php echo htmlspecialchars($cliente['telefono']); ?>" 
                                   placeholder="(000) 000-0000">
                            <small class="form-text text-muted">Formato: números, guiones, paréntesis y espacios</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($cliente['email']); ?>" 
                                   placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" class="form-control" rows="2"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control" 
                                   value="<?php echo htmlspecialchars($cliente['ciudad']); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estado</label>
                            <input type="text" name="estado" class="form-control" 
                                   value="<?php echo htmlspecialchars($cliente['estado']); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Código Postal</label>
                            <input type="text" name="codigo_postal" class="form-control" 
                                   value="<?php echo htmlspecialchars($cliente['codigo_postal']); ?>" maxlength="10">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">RFC</label>
                        <input type="text" name="rfc" class="form-control" 
                               value="<?php echo htmlspecialchars($cliente['rfc']); ?>" maxlength="20">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" class="form-control" rows="3"><?php echo htmlspecialchars($cliente['notas']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="activo" class="form-select">
                            <option value="1" <?php echo $cliente['activo'] ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo !$cliente['activo'] ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>clientes" class="btn btn-secondary">
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
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Información del Cliente</h5>
                <p class="small mb-2">
                    <strong>Fecha de registro:</strong><br>
                    <?php echo date('d/m/Y H:i', strtotime($cliente['fecha_creacion'])); ?>
                </p>
                <?php if ($cliente['fecha_actualizacion']): ?>
                    <p class="small mb-2">
                        <strong>Última actualización:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($cliente['fecha_actualizacion'])); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
