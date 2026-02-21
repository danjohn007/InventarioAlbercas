<?php
$errorLogPath = ini_get('error_log');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Registro de Errores</h2>
        <p class="text-muted mb-0">Monitor de errores del sistema en tiempo real</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>configuraciones" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver a Configuraciones
        </a>
        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
        </button>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2 px-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <small class="text-muted">
                    <i class="bi bi-file-text me-1"></i>
                    <strong>Archivo de log:</strong>
                    <?php echo htmlspecialchars($errorLogPath ?: 'No configurado', ENT_QUOTES, 'UTF-8'); ?>
                </small>
            </div>
            <?php if ($errorLogReadable): ?>
            <div class="col-auto ms-auto">
                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Accesible</span>
                <span class="badge bg-secondary ms-1"><?php echo count($errorEntries); ?> entradas</span>
            </div>
            <?php else: ?>
            <div class="col-auto ms-auto">
                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>No accesible</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$errorLogReadable): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Archivo de log no accesible.</strong>
    El archivo de log de PHP no est&aacute; configurado o el servidor web no tiene permisos para leerlo.
    <ul class="mt-2 mb-0 small">
        <li>Verifica la directiva <code>error_log</code> en tu <code>php.ini</code>.</li>
        <li>Aseg&uacute;rate de que el usuario del servidor web tenga permisos de lectura sobre el archivo.</li>
    </ul>
</div>

<?php else: ?>

<!-- Filter controls -->
<div class="card mb-3">
    <div class="card-body py-2 px-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" id="logFilter"
                       placeholder="Filtrar entradas..." oninput="filterLogs()">
            </div>
            <div class="col-auto">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-danger active" data-filter="all" onclick="setFilter('all', this)">Todos</button>
                    <button type="button" class="btn btn-outline-danger" data-filter="PHP Fatal"    onclick="setFilter('PHP Fatal', this)">Fatal</button>
                    <button type="button" class="btn btn-outline-warning" data-filter="PHP Warning" onclick="setFilter('PHP Warning', this)">Warning</button>
                    <button type="button" class="btn btn-outline-info"   data-filter="PHP Notice"  onclick="setFilter('PHP Notice', this)">Notice</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div id="logContainer" style="max-height:600px;overflow-y:auto;padding:12px;">
            <?php if (empty($errorEntries)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                <strong>No se encontraron errores.</strong><br>
                <small>El archivo de log est&aacute; vac&iacute;o o no contiene entradas.</small>
            </div>
            <?php else: ?>
            <?php foreach ($errorEntries as $entry): ?>
            <?php
            $entryClass = 'log-other';
            if (strpos($entry, 'PHP Fatal') !== false || strpos($entry, 'Fatal error') !== false) $entryClass = 'log-error';
            elseif (strpos($entry, 'PHP Warning') !== false) $entryClass = 'log-warning';
            elseif (strpos($entry, 'PHP Notice') !== false || strpos($entry, 'PHP Deprecated') !== false) $entryClass = 'log-notice';
            ?>
            <div class="log-entry <?php echo $entryClass; ?>" data-content="<?php echo htmlspecialchars(strtolower($entry), ENT_QUOTES, 'UTF-8'); ?>">
                <?php /* $entry is already HTML-escaped by the controller (htmlspecialchars applied on read) */ echo $entry; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php endif; ?>

<style>
.log-entry {
    font-family: monospace;
    font-size: 0.78rem;
    padding: 4px 8px;
    border-radius: 4px;
    margin-bottom: 2px;
    word-break: break-all;
}
.log-entry.log-error   { background: #fff5f5; color: #721c24; border-left: 3px solid #dc3545; }
.log-entry.log-warning { background: #fffbf0; color: #856404; border-left: 3px solid #ffc107; }
.log-entry.log-notice  { background: #f0f8ff; color: #084298; border-left: 3px solid #0d6efd; }
.log-entry.log-other   { background: #f8f9fa; color: #495057; border-left: 3px solid #adb5bd; }
.log-entry.hidden-entry { display: none; }
</style>
<script>
var activeFilter = 'all';
function filterLogs() {
    var text = document.getElementById('logFilter').value.toLowerCase();
    document.querySelectorAll('.log-entry').forEach(function (el) {
        var content = el.getAttribute('data-content') || '';
        var matchesText   = text === '' || content.indexOf(text) !== -1;
        var matchesFilter = activeFilter === 'all' || content.indexOf(activeFilter.toLowerCase()) !== -1;
        el.classList.toggle('hidden-entry', !(matchesText && matchesFilter));
    });
}
function setFilter(filter, btn) {
    activeFilter = filter;
    document.querySelectorAll('[data-filter]').forEach(function (b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    filterLogs();
}
</script>
