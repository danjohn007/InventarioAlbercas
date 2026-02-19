<style>
    .backup-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        transition: box-shadow 0.3s;
    }
    
    .backup-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .backup-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .backup-actions button {
        margin-left: 5px;
    }
</style>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-hdd me-2"></i>Respaldos de Base de Datos</h2>
                <p class="text-muted">Crear y administrar respaldos completos de la base de datos</p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>configuraciones" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a Configuraciones
                </a>
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

<!-- Verificar disponibilidad de herramientas -->
<?php if (!$mysqldumpAvailable || !$mysqlAvailable): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Advertencia:</strong> Las herramientas necesarias no están disponibles:
    <ul class="mb-0 mt-2">
        <?php if (!$mysqldumpAvailable): ?>
            <li>mysqldump no está instalado o no está en el PATH</li>
        <?php endif; ?>
        <?php if (!$mysqlAvailable): ?>
            <li>mysql client no está instalado o no está en el PATH</li>
        <?php endif; ?>
    </ul>
    <p class="mb-0 mt-2">Por favor, instale MySQL client tools para usar esta funcionalidad.</p>
</div>
<?php endif; ?>

<!-- Crear nuevo backup -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Crear Nuevo Respaldo</h5>
    </div>
    <div class="card-body">
        <form id="formCrearBackup">
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Descripción del respaldo (opcional)</label>
                    <input type="text" class="form-control" id="backup_description" 
                           placeholder="Ej: Respaldo antes de actualización...">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100" id="btnCrearBackup" 
                            <?php echo !$mysqldumpAvailable ? 'disabled' : ''; ?>>
                        <i class="bi bi-hdd me-2"></i>Crear Respaldo
                    </button>
                </div>
            </div>
        </form>
        <div id="backup_create_result" class="mt-3"></div>
        
        <div class="alert alert-info mt-3 mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Nota:</strong> El respaldo incluirá todas las tablas y datos de la base de datos. 
            El archivo será comprimido automáticamente para ahorrar espacio.
        </div>
    </div>
</div>

<!-- Lista de respaldos -->
<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="bi bi-archive me-2"></i>Respaldos Disponibles (<?php echo count($backups); ?>)</h5>
    </div>
    <div class="card-body">
        
        <?php if (empty($backups)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No hay respaldos disponibles</p>
                <p class="text-muted">Crea tu primer respaldo usando el formulario de arriba</p>
            </div>
        <?php else: ?>
            
            <div id="backups-list">
                <?php foreach ($backups as $backup): ?>
                    <div class="backup-card" data-filename="<?php echo htmlspecialchars($backup['filename']); ?>">
                        <div class="backup-info">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <i class="bi bi-file-earmark-zip text-primary me-2"></i>
                                    <?php echo htmlspecialchars($backup['filename']); ?>
                                </h6>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i><?php echo $backup['date']; ?>
                                    <span class="ms-3">
                                        <i class="bi bi-hdd me-1"></i><?php echo $backup['size_formatted']; ?>
                                    </span>
                                </small>
                            </div>
                            <div class="backup-actions">
                                <a href="<?php echo BASE_URL; ?>configuraciones/descargarBackup/<?php echo urlencode($backup['filename']); ?>" 
                                   class="btn btn-sm btn-primary" title="Descargar">
                                    <i class="bi bi-download"></i> Descargar
                                </a>
                                <button type="button" class="btn btn-sm btn-warning btn-restore" 
                                        data-filename="<?php echo htmlspecialchars($backup['filename']); ?>"
                                        title="Restaurar" <?php echo !$mysqlAvailable ? 'disabled' : ''; ?>>
                                    <i class="bi bi-arrow-clockwise"></i> Restaurar
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                        data-filename="<?php echo htmlspecialchars($backup['filename']); ?>"
                                        title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<script>
// Crear backup
document.getElementById('formCrearBackup')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnCrearBackup');
    const resultDiv = document.getElementById('backup_create_result');
    const description = document.getElementById('backup_description').value;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando respaldo...';
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Creando respaldo de la base de datos, por favor espere...</div>';
    
    fetch('<?php echo BASE_URL; ?>configuraciones/crearBackup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'description=' + encodeURIComponent(description)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
            document.getElementById('backup_description').value = '';
            
            // Recargar página después de 2 segundos
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Error: ' + error.message + '</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-hdd me-2"></i>Crear Respaldo';
    });
});

// Restaurar backup
document.querySelectorAll('.btn-restore').forEach(btn => {
    btn.addEventListener('click', function() {
        const filename = this.dataset.filename;
        
        if (!confirm('¿Está seguro de que desea restaurar la base de datos desde este respaldo?\n\n' + 
                     'ADVERTENCIA: Esto sobrescribirá TODOS los datos actuales de la base de datos.\n\n' + 
                     'Archivo: ' + filename)) {
            return;
        }
        
        const card = this.closest('.backup-card');
        card.style.opacity = '0.5';
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        fetch('<?php echo BASE_URL; ?>configuraciones/restaurarBackup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'filename=' + encodeURIComponent(filename)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ ' + data.message + '\n\nLa página se recargará para reflejar los cambios.');
                window.location.reload();
            } else {
                alert('✗ Error al restaurar:\n\n' + data.message);
                card.style.opacity = '1';
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Restaurar';
            }
        })
        .catch(error => {
            alert('✗ Error: ' + error.message);
            card.style.opacity = '1';
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Restaurar';
        });
    });
});

// Eliminar backup
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const filename = this.dataset.filename;
        
        if (!confirm('¿Está seguro de que desea eliminar este respaldo?\n\nArchivo: ' + filename)) {
            return;
        }
        
        const card = this.closest('.backup-card');
        
        fetch('<?php echo BASE_URL; ?>configuraciones/eliminarBackup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'filename=' + encodeURIComponent(filename)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                card.style.transition = 'opacity 0.3s';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    
                    // Si no quedan backups, recargar la página
                    if (document.querySelectorAll('.backup-card').length === 0) {
                        window.location.reload();
                    }
                }, 300);
            } else {
                alert('✗ Error al eliminar:\n\n' + data.message);
            }
        })
        .catch(error => {
            alert('✗ Error: ' + error.message);
        });
    });
});
</script>
