<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>usuarios">Usuarios</a></li>
            <li class="breadcrumb-item active">Editar Usuario</li>
        </ol>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Editar Usuario
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>usuarios/actualizar" id="formEditarUsuario">
                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                    
                    <!-- Información Personal -->
                    <h6 class="text-muted mb-3 border-bottom pb-2">
                        <i class="bi bi-person"></i> Información Personal
                    </h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nombre" 
                                   name="nombre" 
                                   required
                                   maxlength="100"
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">
                                Apellidos <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="apellidos" 
                                   name="apellidos" 
                                   required
                                   maxlength="100"
                                   value="<?php echo htmlspecialchars($usuario['apellidos']); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   required
                                   maxlength="100"
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="telefono" 
                                   name="telefono" 
                                   maxlength="20"
                                   placeholder="Ej: 123-456-7890"
                                   value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Credenciales -->
                    <h6 class="text-muted mb-3 border-bottom pb-2 mt-4">
                        <i class="bi bi-key"></i> Credenciales de Acceso
                    </h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="usuario" class="form-label">
                                Usuario <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="usuario" 
                                   name="usuario" 
                                   required
                                   minlength="4"
                                   maxlength="50"
                                   value="<?php echo htmlspecialchars($usuario['usuario']); ?>">
                            <small class="form-text text-muted">Mínimo 4 caracteres</small>
                        </div>
                        <div class="col-md-6">
                            <label for="rol_id" class="form-label">
                                Rol <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <option value="">Seleccione un rol...</option>
                                <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['id']; ?>" 
                                        <?php echo ($usuario['rol_id'] == $rol['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rol['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Nota:</strong> Si no desea cambiar la contraseña, deje los campos en blanco.
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Nueva Contraseña
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       minlength="6">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Dejar en blanco para mantener la actual</small>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">
                                Confirmar Nueva Contraseña
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       minlength="6">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('password_confirm')">
                                    <i class="bi bi-eye" id="password_confirm-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Indicador de fortaleza de contraseña -->
                    <div class="mb-3" id="password-strength-container" style="display: none;">
                        <div class="progress" style="height: 5px;">
                            <div id="password-strength" 
                                 class="progress-bar" 
                                 role="progressbar" 
                                 style="width: 0%"></div>
                        </div>
                        <small id="password-strength-text" class="form-text"></small>
                    </div>
                    
                    <!-- Estado -->
                    <h6 class="text-muted mb-3 border-bottom pb-2 mt-4">
                        <i class="bi bi-toggle-on"></i> Estado
                    </h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activo" 
                                   name="activo" 
                                   value="1"
                                   <?php echo $usuario['activo'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">
                                Usuario Activo
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Los usuarios inactivos no podrán acceder al sistema
                        </small>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="alert alert-secondary">
                        <div class="row">
                            <div class="col-md-6">
                                <small>
                                    <strong>Creado:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small>
                                    <strong>Última actualización:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion'])); ?>
                                </small>
                            </div>
                            <?php if ($usuario['ultimo_acceso']): ?>
                            <div class="col-md-6 mt-2">
                                <small>
                                    <strong>Último acceso:</strong> 
                                    <?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-secondary">
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
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Validar que las contraseñas coincidan
document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    const passwordConfirmField = document.getElementById('password_confirm');
    
    // Solo validar si se está cambiando la contraseña
    if (password || passwordConfirm) {
        if (password !== passwordConfirm) {
            e.preventDefault();
            passwordConfirmField.classList.add('is-invalid');
            
            // Crear o actualizar mensaje de error
            let errorDiv = passwordConfirmField.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                passwordConfirmField.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = 'Las contraseñas no coinciden';
            passwordConfirmField.focus();
            return false;
        } else {
            passwordConfirmField.classList.remove('is-invalid');
        }
    }
});

// Indicador de fortaleza de contraseña
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthContainer = document.getElementById('password-strength-container');
    const strengthBar = document.getElementById('password-strength');
    const strengthText = document.getElementById('password-strength-text');
    
    if (!password) {
        strengthContainer.style.display = 'none';
        return;
    }
    
    strengthContainer.style.display = 'block';
    
    let strength = 0;
    let text = '';
    let colorClass = '';
    
    if (password.length >= 6) strength += 25;
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 12.5;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 12.5;
    
    if (strength <= 25) {
        text = 'Débil';
        colorClass = 'bg-danger';
    } else if (strength <= 50) {
        text = 'Regular';
        colorClass = 'bg-warning';
    } else if (strength <= 75) {
        text = 'Buena';
        colorClass = 'bg-info';
    } else {
        text = 'Excelente';
        colorClass = 'bg-success';
    }
    
    strengthBar.style.width = strength + '%';
    strengthBar.className = 'progress-bar ' + colorClass;
    strengthText.textContent = text;
    strengthText.className = 'form-text ' + colorClass.replace('bg-', 'text-');
});
</script>
