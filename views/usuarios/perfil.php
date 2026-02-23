<?php
$fotoPerfil = !empty($usuario['foto_perfil'])
    ? BASE_URL . 'public/uploads/' . htmlspecialchars($usuario['foto_perfil'], ENT_QUOTES, 'UTF-8')
    : null;
$nombreCompleto = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos'], ENT_QUOTES, 'UTF-8');
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-person-circle me-2"></i>Mi Perfil</h2>
        <p class="text-muted mb-0">Administra tu información personal y preferencias de cuenta</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left column: Photo + summary -->
    <div class="col-lg-4">
        <!-- Profile photo card -->
        <div class="card mb-4">
            <div class="card-body text-center py-4">
                <div class="mb-3 position-relative d-inline-block">
                    <?php if ($fotoPerfil): ?>
                        <img src="<?php echo $fotoPerfil; ?>" alt="Foto de perfil"
                             class="rounded-circle border border-3 border-primary"
                             style="width:120px;height:120px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center border border-3 border-primary"
                             style="width:120px;height:120px;background:linear-gradient(135deg,#667eea,#764ba2);">
                            <i class="bi bi-person-fill text-white" style="font-size:3rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold mb-1"><?php echo $nombreCompleto; ?></h5>
                <span class="badge bg-secondary mb-3"><?php echo htmlspecialchars($usuario['rol_nombre'], ENT_QUOTES, 'UTF-8'); ?></span>

                <!-- Upload photo form -->
                <form action="<?php echo BASE_URL; ?>perfil/subir-foto" method="POST" enctype="multipart/form-data" id="fotoForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-2">
                        <label for="foto_perfil" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-camera me-1"></i>Cambiar Foto
                        </label>
                        <input type="file" id="foto_perfil" name="foto_perfil" class="d-none"
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               onchange="document.getElementById('fotoForm').submit();">
                    </div>
                    <small class="text-muted d-block">JPG, PNG, GIF o WebP &middot; Máx. 2 MB</small>
                </form>
            </div>
        </div>

        <!-- Account info summary -->
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Información de Cuenta</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <small class="text-muted">Usuario</small>
                    <span class="fw-semibold"><?php echo htmlspecialchars($usuario['usuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <small class="text-muted">Email</small>
                    <span class="fw-semibold"><?php echo htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <small class="text-muted">Teléfono</small>
                    <span class="fw-semibold"><?php echo htmlspecialchars($usuario['telefono'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <small class="text-muted">Último acceso</small>
                    <span class="fw-semibold"><?php echo $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : '—'; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <small class="text-muted">Miembro desde</small>
                    <span class="fw-semibold"><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Right column: Edit forms -->
    <div class="col-lg-8">
        <!-- Edit personal data -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Datos Personales</div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>perfil/actualizar" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre"
                                   value="<?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="apellidos"
                                   value="<?php echo htmlspecialchars($usuario['apellidos'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email"
                                   value="<?php echo htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono"
                                   value="<?php echo htmlspecialchars($usuario['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change password -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña</div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>perfil/cambiar-password" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Contraseña Actual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password_actual" id="pwdActual" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdActual',this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password_nuevo" id="pwdNuevo"
                                       minlength="6" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdNuevo',this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password_confirm" id="pwdConfirm"
                                       minlength="6" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdConfirm',this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key me-1"></i>Cambiar Contraseña
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent activity -->
        <?php if (!empty($actividad)): ?>
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Actividad Reciente</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Acción</th>
                                <th>Módulo</th>
                                <th>Fecha</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividad as $log): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($log['accion'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($log['tabla'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($log['fecha_creacion'])); ?></td>
                                <td><small><?php echo htmlspecialchars($log['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePwd(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
