<style>
    .config-section {
        margin-bottom: 2rem;
    }
    
    .config-section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        font-weight: bold;
    }
    
    .config-section-body {
        background: white;
        padding: 20px;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 8px 8px;
    }
    
    .color-preview {
        width: 50px;
        height: 50px;
        border-radius: 4px;
        border: 2px solid #ddd;
        cursor: pointer;
    }
    
    .logo-preview {
        max-width: 200px;
        max-height: 100px;
        border: 2px solid #ddd;
        border-radius: 4px;
        padding: 10px;
    }
</style>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-gear me-2"></i>Configuraciones del Sistema</h2>
                <p class="text-muted">Personaliza la apariencia y comportamiento del sistema</p>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form action="<?php echo BASE_URL; ?>configuraciones/actualizar" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    
    <!-- Configuraciones Generales -->
    <?php if (isset($config_grouped['general'])): ?>
    <div class="config-section">
        <div class="config-section-header">
            <i class="bi bi-info-circle me-2"></i>Configuraciones Generales
        </div>
        <div class="config-section-body">
            <div class="row">
                <?php foreach ($config_grouped['general'] as $config): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold"><?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?></label>
                    <small class="d-block text-muted mb-2"><?php echo $config['descripcion']; ?></small>
                    
                    <?php if ($config['tipo'] === 'booleano'): ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?php echo $config['clave']; ?>" 
                                   value="1" <?php echo $config['valor'] == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label">Activar</label>
                        </div>
                    <?php elseif ($config['tipo'] === 'numero'): ?>
                        <input type="number" class="form-control" name="<?php echo $config['clave']; ?>" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>">
                    <?php else: ?>
                        <input type="text" class="form-control" name="<?php echo $config['clave']; ?>" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Configuraciones de Apariencia -->
    <?php if (isset($config_grouped['apariencia'])): ?>
    <div class="config-section">
        <div class="config-section-header">
            <i class="bi bi-palette me-2"></i>Apariencia y Marca
        </div>
        <div class="config-section-body">
            <div class="row">
                <?php foreach ($config_grouped['apariencia'] as $config): ?>
                
                <?php if ($config['clave'] === 'sitio_logo'): ?>
                <div class="col-md-12 mb-4">
                    <label class="form-label fw-bold">Logotipo del Sistema</label>
                    <small class="d-block text-muted mb-2">Imagen para el encabezado del sistema (formato: PNG, JPG, SVG)</small>
                    
                    <?php if (!empty($config['valor'])): ?>
                    <div class="mb-2">
                        <img src="<?php echo BASE_URL . $config['valor']; ?>" class="logo-preview" alt="Logo actual">
                    </div>
                    <?php endif; ?>
                    
                    <input type="file" class="form-control" name="sitio_logo" accept="image/*">
                    <input type="hidden" name="<?php echo $config['clave']; ?>" value="<?php echo htmlspecialchars($config['valor']); ?>">
                </div>
                
                <?php elseif ($config['clave'] === 'color_primario' || $config['clave'] === 'color_secundario'): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold"><?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?></label>
                    <small class="d-block text-muted mb-2"><?php echo $config['descripcion']; ?></small>
                    
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" class="form-control form-control-color" 
                               name="<?php echo $config['clave']; ?>" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>"
                               style="width: 60px; height: 50px;">
                        <input type="text" class="form-control" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>"
                               readonly>
                    </div>
                </div>
                
                <?php else: ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold"><?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?></label>
                    <small class="d-block text-muted mb-2"><?php echo $config['descripcion']; ?></small>
                    <input type="text" class="form-control" name="<?php echo $config['clave']; ?>" 
                           value="<?php echo htmlspecialchars($config['valor']); ?>">
                </div>
                <?php endif; ?>
                
                <?php endforeach; ?>
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Nota:</strong> Los cambios de color se aplicarán después de recargar la página.
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Configuraciones del Sistema -->
    <?php if (isset($config_grouped['sistema'])): ?>
    <div class="config-section">
        <div class="config-section-header">
            <i class="bi bi-sliders me-2"></i>Configuraciones del Sistema
        </div>
        <div class="config-section-body">
            <div class="row">
                <?php foreach ($config_grouped['sistema'] as $config): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold"><?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?></label>
                    <small class="d-block text-muted mb-2"><?php echo $config['descripcion']; ?></small>
                    
                    <?php if ($config['tipo'] === 'numero'): ?>
                        <input type="number" class="form-control" name="<?php echo $config['clave']; ?>" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>">
                    <?php else: ?>
                        <input type="text" class="form-control" name="<?php echo $config['clave']; ?>" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Configuraciones de Notificaciones y Email -->
    <?php if (isset($config_grouped['notificaciones'])): ?>
    <div class="config-section">
        <div class="config-section-header">
            <i class="bi bi-bell me-2"></i>Notificaciones y Email
        </div>
        <div class="config-section-body">
            <!-- Configuraciones básicas de notificaciones -->
            <div class="row">
                <?php 
                $emailFields = ['smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_password', 
                                'email_from_address', 'email_from_name', 'email_enabled'];
                foreach ($config_grouped['notificaciones'] as $config): 
                    if (in_array($config['clave'], $emailFields)) continue; // Skip email fields for now
                ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold"><?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?></label>
                    <small class="d-block text-muted mb-2"><?php echo $config['descripcion']; ?></small>
                    
                    <?php if ($config['tipo'] === 'booleano'): ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?php echo $config['clave']; ?>" 
                                   value="1" <?php echo $config['valor'] == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label">Activar</label>
                        </div>
                    <?php else: ?>
                        <input type="text" class="form-control" name="<?php echo $config['clave']; ?>" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Configuración de Email/SMTP -->
            <hr class="my-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-envelope me-2"></i>Configuración de Correo Electrónico (SMTP)</h5>
            <div class="row">
                <?php 
                foreach ($config_grouped['notificaciones'] as $config):
                    if (!in_array($config['clave'], $emailFields)) continue;
                ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold"><?php echo ucfirst(str_replace('_', ' ', $config['clave'])); ?></label>
                    <small class="d-block text-muted mb-2"><?php echo $config['descripcion']; ?></small>
                    
                    <?php if ($config['tipo'] === 'booleano'): ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?php echo $config['clave']; ?>" 
                                   id="<?php echo $config['clave']; ?>"
                                   value="1" <?php echo $config['valor'] == '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label">Activar</label>
                        </div>
                    <?php elseif ($config['clave'] === 'smtp_password'): ?>
                        <input type="password" class="form-control" name="<?php echo $config['clave']; ?>" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>"
                               placeholder="Contraseña SMTP">
                    <?php elseif ($config['clave'] === 'smtp_encryption'): ?>
                        <select class="form-select" name="<?php echo $config['clave']; ?>">
                            <option value="none" <?php echo $config['valor'] == 'none' ? 'selected' : ''; ?>>Sin encriptación</option>
                            <option value="tls" <?php echo $config['valor'] == 'tls' ? 'selected' : ''; ?>>TLS (recomendado)</option>
                            <option value="ssl" <?php echo $config['valor'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" name="<?php echo $config['clave']; ?>" 
                               value="<?php echo htmlspecialchars($config['valor']); ?>"
                               placeholder="<?php echo $config['clave'] === 'smtp_host' ? 'smtp.gmail.com' : ''; ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Botón para probar email -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Probar Configuración:</strong> Envía un email de prueba para verificar que la configuración SMTP funciona correctamente.
                    </div>
                    <div class="input-group">
                        <input type="email" class="form-control" id="test_email_address" 
                               placeholder="tu@email.com" required>
                        <button type="button" class="btn btn-primary" id="btnTestEmail">
                            <i class="bi bi-send me-2"></i>Enviar Email de Prueba
                        </button>
                    </div>
                    <div id="email_test_result" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Sección de Backup y Restauración -->
    <div class="config-section">
        <div class="config-section-header">
            <i class="bi bi-database me-2"></i>Backup y Restauración
        </div>
        <div class="config-section-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold mb-3">Exportar Configuración</h6>
                    <p class="text-muted small">Descarga un archivo de respaldo con todas las configuraciones actuales.</p>
                    <a href="<?php echo BASE_URL; ?>configuraciones/exportar" class="btn btn-primary">
                        <i class="bi bi-download"></i> Descargar Backup
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold mb-3">Importar Configuración</h6>
                    <p class="text-muted small">Restaura las configuraciones desde un archivo de respaldo.</p>
                    <form action="<?php echo BASE_URL; ?>configuraciones/importar" method="POST" enctype="multipart/form-data" onsubmit="return confirm('¿Está seguro de que desea restaurar las configuraciones? Se sobrescribirán los valores actuales.');">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="input-group">
                            <input type="file" class="form-control" name="backup_file" accept=".json" required>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload"></i> Restaurar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6 class="fw-bold mb-3"><i class="bi bi-hdd me-2"></i>Respaldos de Base de Datos</h6>
                    <p class="text-muted small">Administra los respaldos completos de la base de datos del sistema.</p>
                    <a href="<?php echo BASE_URL; ?>configuraciones/backups" class="btn btn-primary">
                        <i class="bi bi-hdd"></i> Administrar Respaldos
                    </a>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Auditoría y Logs</h6>
                    <p class="text-muted small">Consulta el historial completo de cambios en las configuraciones del sistema.</p>
                    <a href="<?php echo BASE_URL; ?>configuraciones/auditoria" class="btn btn-info">
                        <i class="bi bi-list-ul"></i> Ver Historial de Auditoría
                    </a>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6 class="fw-bold mb-3">Restablecer Valores por Defecto</h6>
                    <p class="text-muted small">Restaura todas las configuraciones a sus valores originales de fábrica.</p>
                    <form action="<?php echo BASE_URL; ?>configuraciones/restablecer" method="POST" onsubmit="return confirm('¿Está seguro de que desea restablecer todas las configuraciones a sus valores por defecto? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-counterclockwise"></i> Restablecer a Valores por Defecto
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Importante:</strong> Realice copias de seguridad periódicas de sus configuraciones. Se recomienda guardar el archivo de respaldo en un lugar seguro.
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Sincronizar color picker con input de texto
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    colorInput.addEventListener('change', function() {
        this.nextElementSibling.value = this.value;
    });
});

// Preview de imagen antes de subir
document.querySelector('input[name="sitio_logo"]')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.logo-preview');
            if (preview) {
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'logo-preview mb-2';
                document.querySelector('input[name="sitio_logo"]').parentElement.insertBefore(img, document.querySelector('input[name="sitio_logo"]'));
            }
        };
        reader.readAsDataURL(file);
    }
});

// Test email functionality
document.getElementById('btnTestEmail')?.addEventListener('click', function() {
    const testEmail = document.getElementById('test_email_address').value;
    const resultDiv = document.getElementById('email_test_result');
    const btn = this;
    
    if (!testEmail || !testEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Por favor ingrese un email válido</div>';
        return;
    }
    
    // Disable button and show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Enviando email de prueba...</div>';
    
    // Send AJAX request
    fetch('<?php echo BASE_URL; ?>configuraciones/testEmail', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'test_email=' + encodeURIComponent(testEmail)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Error al enviar email: ' + error.message + '</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Enviar Email de Prueba';
    });
});
</script>
