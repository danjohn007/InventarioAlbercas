<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>usuarios">Usuarios</a></li>
            <li class="breadcrumb-item active">Crear Usuario</li>
        </ol>
    </nav>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-plus"></i> Crear Nuevo Usuario
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>usuarios/guardar" id="formCrearUsuario">
                    
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
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
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
                                   value="<?php echo isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : ''; ?>">
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
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="telefono" 
                                   name="telefono" 
                                   maxlength="20"
                                   placeholder="Ej: 123-456-7890"
                                   value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
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
                                   value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
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
                                        <?php echo (isset($_POST['rol_id']) && $_POST['rol_id'] == $rol['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rol['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required
                                       minlength="6">
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-icon"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Mínimo 6 caracteres</small>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirm" class="form-label">
                                Confirmar Contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       required
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
                    <div class="mb-3">
                        <div class="progress" style="height: 5px;">
                            <div id="password-strength" 
                                 class="progress-bar" 
                                 role="progressbar" 
                                 style="width: 0%"></div>
                        </div>
                        <small id="password-strength-text" class="form-text"></small>
                    </div>
                    
                    <!-- Botones -->
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Usuario
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
document.getElementById('formCrearUsuario').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    const passwordConfirmInput = document.getElementById('password_confirm');
    const passwordConfirmGroup = passwordConfirmInput.closest('.col-md-6');
    
    if (password !== passwordConfirm) {
        e.preventDefault();
        passwordConfirmInput.classList.add('is-invalid');
        
        // Crear o actualizar mensaje de error
        let errorDiv = passwordConfirmGroup.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            passwordConfirmGroup.appendChild(errorDiv);
        }
        errorDiv.textContent = 'Las contraseñas no coinciden';
        passwordConfirmInput.focus();
        return false;
    } else {
        passwordConfirmInput.classList.remove('is-invalid');
    }
});

// Indicador de fortaleza de contraseña
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('password-strength');
    const strengthText = document.getElementById('password-strength-text');
    
    let strength = 0;
    let text = '';
    let colorClass = '';
    
    if (password.length >= 6) strength += 25;
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 12.5;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 12.5;
    
    if (strength === 0) {
        text = '';
        colorClass = '';
    } else if (strength <= 25) {
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
