<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>clientes">Clientes</a></li>
                <li class="breadcrumb-item active">Crear Cliente</li>
            </ol>
        </nav>
        <h2><i class="bi bi-person-plus-fill me-2"></i>Crear Cliente</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>clientes/guardar">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control" placeholder="(000) 000-0000">
                            <small class="form-text text-muted">Formato: números, guiones, paréntesis y espacios</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estado</label>
                            <input type="text" name="estado" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Código Postal</label>
                            <input type="text" name="codigo_postal" class="form-control" maxlength="10">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">RFC</label>
                        <input type="text" name="rfc" class="form-control" maxlength="20" placeholder="XAXX010101000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>clientes" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cliente
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
                <h6>Validaciones:</h6>
                <ul class="small">
                    <li>El <strong>nombre</strong> es obligatorio</li>
                    <li>El <strong>email</strong> debe tener formato válido (si se proporciona)</li>
                    <li>El <strong>teléfono</strong> debe contener solo números, guiones, paréntesis y espacios (si se proporciona)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
