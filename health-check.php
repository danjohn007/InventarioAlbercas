<?php
/**
 * Sistema de Inventario - Health Check
 * Verifica el estado de todos los componentes del sistema
 * 
 * IMPORTANTE: Eliminar este archivo en producción o protegerlo con autenticación
 */

// Deshabilitar salida de errores para este script específico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Array para almacenar resultados de las pruebas
$checks = [];
$allPassed = true;

// Función helper para agregar resultado de check
function addCheck($name, $passed, $message = '', $details = '') {
    global $checks, $allPassed;
    if (!$passed) {
        $allPassed = false;
    }
    $checks[] = [
        'name' => $name,
        'passed' => $passed,
        'message' => $message,
        'details' => $details
    ];
}

// 1. Verificar versión de PHP
$minPhpVersion = '7.4';
$phpVersion = phpversion();
addCheck(
    'Versión de PHP',
    version_compare($phpVersion, $minPhpVersion, '>='),
    "PHP $phpVersion (mínimo requerido: $minPhpVersion)",
    $phpVersion
);

// 2. Verificar extensiones requeridas
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
foreach ($requiredExtensions as $ext) {
    addCheck(
        "Extensión PHP: $ext",
        extension_loaded($ext),
        extension_loaded($ext) ? "Extensión $ext cargada" : "Extensión $ext NO disponible",
        phpversion($ext)
    );
}

// 3. Verificar archivos de configuración
$configFiles = [
    'config/config.php' => 'Configuración general',
    'config/database.php' => 'Configuración de base de datos',
    '.env.example' => 'Ejemplo de variables de entorno',
    '.htaccess' => 'Configuración de Apache',
    '.user.ini' => 'Configuración de PHP-FPM (open_basedir)'
];

foreach ($configFiles as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $readable = $exists ? is_readable(__DIR__ . '/' . $file) : false;
    addCheck(
        "Archivo: $description",
        $exists && $readable,
        $exists ? ($readable ? "Archivo presente y legible" : "Archivo presente pero no legible") : "Archivo no encontrado",
        $file
    );
}

// 4. Verificar permisos de escritura
$writableDirs = [
    'public/uploads' => 'Directorio de uploads',
    '.' => 'Directorio raíz (para logs)'
];

foreach ($writableDirs as $dir => $description) {
    $path = __DIR__ . '/' . $dir;
    $writable = is_writable($path);
    addCheck(
        "Permisos de escritura: $description",
        $writable,
        $writable ? "Directorio escribible" : "Directorio NO escribible",
        $path
    );
}

// 5. Verificar configuración de open_basedir
$openBasedir = ini_get('open_basedir');
$currentPath = __DIR__;
$openBasedirOk = false;

if (empty($openBasedir)) {
    $openBasedirOk = true;
    $openBasedirMsg = "open_basedir no está configurado (acceso sin restricciones)";
} else {
    // Verificar si la ruta actual está en el open_basedir
    $allowedPaths = explode(PATH_SEPARATOR, $openBasedir);
    foreach ($allowedPaths as $allowedPath) {
        if (strpos($currentPath, $allowedPath) === 0) {
            $openBasedirOk = true;
            break;
        }
    }
    $openBasedirMsg = $openBasedirOk 
        ? "open_basedir configurado correctamente: $openBasedir"
        : "ADVERTENCIA: Ruta actual no está en open_basedir permitido: $openBasedir";
}

addCheck(
    'Configuración open_basedir',
    $openBasedirOk,
    $openBasedirMsg,
    $openBasedir
);

// 6. Verificar conexión a la base de datos (sin incluir archivos para evitar errores)
$dbCheckPassed = false;
$dbMessage = '';
$dbDetails = '';

// Intentar cargar .env si existe
$envFile = __DIR__ . '/.env';
$dbHost = 'localhost';
$dbName = 'inventario_albercas';
$dbUser = 'root';
$dbPass = '';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ($key === 'DB_HOST') $dbHost = $value;
            if ($key === 'DB_NAME') $dbName = $value;
            if ($key === 'DB_USER') $dbUser = $value;
            if ($key === 'DB_PASS') $dbPass = $value;
        }
    }
}

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    
    // Verificar que las tablas principales existen
    $tables = ['usuarios', 'productos', 'clientes', 'servicios', 'gastos'];
    $existingTables = [];
    $missingTables = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $existingTables[] = $table;
        } else {
            $missingTables[] = $table;
        }
    }
    
    $dbCheckPassed = count($missingTables) === 0;
    $dbMessage = $dbCheckPassed 
        ? "Conexión exitosa. Todas las tablas principales existen."
        : "Conexión exitosa pero faltan tablas: " . implode(', ', $missingTables);
    $dbDetails = "Base de datos: $dbName @ $dbHost";
    
} catch (PDOException $e) {
    $dbCheckPassed = false;
    $dbMessage = "No se pudo conectar: " . $e->getMessage();
    $dbDetails = "DSN: $dsn, Usuario: $dbUser";
}

addCheck(
    'Conexión a Base de Datos',
    $dbCheckPassed,
    $dbMessage,
    $dbDetails
);

// 7. Verificar módulos de Apache
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $requiredModules = ['mod_rewrite'];
    
    foreach ($requiredModules as $module) {
        $loaded = in_array($module, $modules);
        addCheck(
            "Módulo Apache: $module",
            $loaded,
            $loaded ? "Módulo cargado" : "Módulo NO cargado (requerido para URLs amigables)",
            ''
        );
    }
} else {
    addCheck(
        'Módulos Apache',
        true,
        'No se puede verificar (apache_get_modules no disponible)',
        'Función no disponible'
    );
}

// 8. Información del sistema
$systemInfo = [
    'PHP Version' => phpversion(),
    'PHP SAPI' => php_sapi_name(),
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'Script Path' => __DIR__,
    'Memory Limit' => ini_get('memory_limit'),
    'Max Execution Time' => ini_get('max_execution_time') . 's',
    'Upload Max Filesize' => ini_get('upload_max_filesize'),
    'Post Max Size' => ini_get('post_max_size'),
];

// Generar HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario - Health Check</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: <?php echo $allPassed ? '#10b981' : '#ef4444'; ?>;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 15px;
            font-size: 14px;
            background: rgba(255,255,255,0.2);
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        
        .check-item {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f9fafb;
            border-left: 4px solid #e5e7eb;
        }
        
        .check-item.passed {
            border-left-color: #10b981;
            background: #f0fdf4;
        }
        
        .check-item.failed {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        
        .check-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            font-weight: bold;
            font-size: 14px;
        }
        
        .check-item.passed .check-icon {
            background: #10b981;
            color: white;
        }
        
        .check-item.failed .check-icon {
            background: #ef4444;
            color: white;
        }
        
        .check-details {
            flex: 1;
        }
        
        .check-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .check-message {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .check-detail-info {
            font-size: 12px;
            color: #9ca3af;
            font-family: 'Courier New', monospace;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #1f2937;
            font-size: 14px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        
        .footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        
        .warning-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #92400e;
        }
        
        .warning-box strong {
            display: block;
            margin-bottom: 5px;
            color: #78350f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Sistema de Inventario - Health Check</h1>
            <p>Verificación del estado del sistema</p>
            <div class="status-badge">
                <?php echo $allPassed ? '✓ Todas las pruebas pasaron' : '⚠ Algunas pruebas fallaron'; ?>
            </div>
        </div>
        
        <div class="content">
            <div class="warning-box">
                <strong>⚠️ ADVERTENCIA DE SEGURIDAD</strong>
                Este archivo expone información sensible del sistema. Por favor, elimínalo de producción o protégelo con autenticación.
            </div>
            
            <div class="section">
                <h2>Resultados de las Pruebas</h2>
                <?php foreach ($checks as $check): ?>
                    <div class="check-item <?php echo $check['passed'] ? 'passed' : 'failed'; ?>">
                        <div class="check-icon">
                            <?php echo $check['passed'] ? '✓' : '✗'; ?>
                        </div>
                        <div class="check-details">
                            <div class="check-name"><?php echo htmlspecialchars($check['name']); ?></div>
                            <div class="check-message"><?php echo htmlspecialchars($check['message']); ?></div>
                            <?php if (!empty($check['details'])): ?>
                                <div class="check-detail-info"><?php echo htmlspecialchars($check['details']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section">
                <h2>Información del Sistema</h2>
                <div class="info-grid">
                    <?php foreach ($systemInfo as $label => $value): ?>
                        <div class="info-item">
                            <div class="info-label"><?php echo htmlspecialchars($label); ?></div>
                            <div class="info-value"><?php echo htmlspecialchars($value); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Generado el <?php echo date('d/m/Y H:i:s'); ?></p>
            <p>Sistema de Inventario y Gastos para Albercas v1.0</p>
        </div>
    </div>
</body>
</html>
