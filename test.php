<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - Sistema de Inventario</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .test-section {
            margin-bottom: 25px;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        .test-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #444;
        }
        .test-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .status {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 14px;
        }
        .success {
            background: #10b981;
            border-left-color: #10b981 !important;
        }
        .error {
            background: #ef4444;
            border-left-color: #ef4444 !important;
        }
        .warning {
            background: #f59e0b;
            border-left-color: #f59e0b !important;
        }
        .info {
            background: #3b82f6;
        }
        .test-label {
            flex: 1;
            font-weight: 500;
            color: #333;
        }
        .test-value {
            font-family: monospace;
            background: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            color: #666;
        }
        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin-top: 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏊‍♂️ Test de Conexión del Sistema</h1>
        <p class="subtitle">Sistema de Inventario y Gastos para Albercas</p>

        <?php
        // Cargar configuración
        require_once __DIR__ . '/config/config.php';
        require_once __DIR__ . '/config/database.php';

        $tests = [
            'php' => false,
            'db' => false,
            'files' => false,
            'url' => true
        ];
        $errors = [];
        ?>

        <!-- Test de PHP -->
        <div class="test-section <?php echo $tests['php'] ? 'success' : 'error'; ?>">
            <h2>✓ Versión de PHP</h2>
            <div class="test-item">
                <div class="status <?php echo version_compare(PHP_VERSION, '7.0.0', '>=') ? 'success' : 'error'; ?>">
                    <?php echo version_compare(PHP_VERSION, '7.0.0', '>=') ? '✓' : '✗'; ?>
                </div>
                <div class="test-label">Versión PHP</div>
                <div class="test-value"><?php echo PHP_VERSION; ?></div>
            </div>
            <?php
            $required_extensions = ['pdo', 'pdo_mysql', 'json'];
            foreach ($required_extensions as $ext) {
                $loaded = extension_loaded($ext);
                ?>
                <div class="test-item">
                    <div class="status <?php echo $loaded ? 'success' : 'error'; ?>">
                        <?php echo $loaded ? '✓' : '✗'; ?>
                    </div>
                    <div class="test-label">Extensión <?php echo $ext; ?></div>
                    <div class="test-value"><?php echo $loaded ? 'Cargada' : 'No disponible'; ?></div>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- Test de URL Base -->
        <div class="test-section success">
            <h2>🌐 Configuración de URLs</h2>
            <div class="test-item">
                <div class="status info">📍</div>
                <div class="test-label">URL Base</div>
                <div class="test-value"><?php echo BASE_URL; ?></div>
            </div>
            <div class="test-item">
                <div class="status info">📁</div>
                <div class="test-label">Ruta Base</div>
                <div class="test-value"><?php echo BASE_PATH; ?></div>
            </div>
            <div class="test-item">
                <div class="status info">💾</div>
                <div class="test-label">Directorio Raíz</div>
                <div class="test-value"><?php echo ROOT_PATH; ?></div>
            </div>
        </div>

        <!-- Test de Conexión a Base de Datos -->
        <div class="test-section <?php 
            try {
                $db = Database::getInstance();
                $conn = $db->getConnection();
                $tests['db'] = true;
                echo 'success';
            } catch (Exception $e) {
                $tests['db'] = false;
                $errors[] = 'Base de datos: ' . $e->getMessage();
                echo 'error';
            }
        ?>">
            <h2>💾 Base de Datos</h2>
            <?php if ($tests['db']): ?>
                <div class="test-item">
                    <div class="status success">✓</div>
                    <div class="test-label">Conexión a MySQL</div>
                    <div class="test-value">Exitosa</div>
                </div>
                <div class="test-item">
                    <div class="status info">📊</div>
                    <div class="test-label">Base de Datos</div>
                    <div class="test-value"><?php echo Config::get('DB_NAME'); ?></div>
                </div>
                <div class="test-item">
                    <div class="status info">🖥️</div>
                    <div class="test-label">Servidor</div>
                    <div class="test-value"><?php echo Config::get('DB_HOST'); ?></div>
                </div>
                <?php
                // Verificar tablas
                try {
                    $stmt = $conn->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $requiredTables = ['usuarios', 'roles', 'productos', 'inventario_movimientos', 
                                      'gastos', 'servicios', 'clientes', 'proveedores'];
                    $missingTables = array_diff($requiredTables, $tables);
                    ?>
                    <div class="test-item">
                        <div class="status <?php echo empty($missingTables) ? 'success' : 'warning'; ?>">
                            <?php echo empty($missingTables) ? '✓' : '⚠'; ?>
                        </div>
                        <div class="test-label">Tablas del Sistema</div>
                        <div class="test-value">
                            <?php echo count($tables) > 0 ? count($tables) . ' tablas encontradas' : 'No hay tablas'; ?>
                        </div>
                    </div>
                    <?php if (!empty($missingTables)): ?>
                        <div class="code-block">
                            ⚠️ Tablas faltantes: <?php echo implode(', ', $missingTables); ?><br>
                            Por favor, ejecuta el archivo database.sql para crear la estructura completa.
                        </div>
                    <?php endif; ?>
                <?php } catch (Exception $e) { ?>
                    <div class="test-item">
                        <div class="status warning">⚠</div>
                        <div class="test-label">Tablas del Sistema</div>
                        <div class="test-value">No se pudieron verificar</div>
                    </div>
                <?php } ?>
            <?php else: ?>
                <div class="test-item">
                    <div class="status error">✗</div>
                    <div class="test-label">Conexión a MySQL</div>
                    <div class="test-value">Fallida</div>
                </div>
                <div class="code-block">
                    📝 Instrucciones:<br>
                    1. Crea un archivo .env en la raíz del proyecto (copia .env.example)<br>
                    2. Configura las credenciales de tu base de datos MySQL<br>
                    3. Ejecuta el archivo database.sql para crear las tablas<br>
                    4. Recarga esta página
                </div>
            <?php endif; ?>
        </div>

        <!-- Test de Archivos y Permisos -->
        <div class="test-section <?php
            $writableDir = is_writable(__DIR__ . '/public/uploads');
            $tests['files'] = $writableDir;
            echo $writableDir ? 'success' : 'warning';
        ?>">
            <h2>📂 Archivos y Permisos</h2>
            <?php
            $checkPaths = [
                'Directorio uploads' => __DIR__ . '/public/uploads',
                'Archivo .htaccess' => __DIR__ . '/.htaccess',
                'Configuración' => __DIR__ . '/config/config.php',
                'Base de datos SQL' => __DIR__ . '/database.sql'
            ];
            
            foreach ($checkPaths as $label => $path) {
                $exists = file_exists($path);
                $writable = is_writable($path);
                ?>
                <div class="test-item">
                    <div class="status <?php echo $exists ? 'success' : 'error'; ?>">
                        <?php echo $exists ? '✓' : '✗'; ?>
                    </div>
                    <div class="test-label"><?php echo $label; ?></div>
                    <div class="test-value">
                        <?php 
                        if ($exists) {
                            echo is_dir($path) ? 
                                ($writable ? 'Escribible' : 'Solo lectura') : 
                                'Existe';
                        } else {
                            echo 'No encontrado';
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- Resumen -->
        <div class="footer">
            <?php if ($tests['db'] && $tests['files']): ?>
                <p style="color: #10b981; font-weight: bold; font-size: 18px;">
                    ✅ ¡Sistema listo para usar!
                </p>
                <a href="<?php echo BASE_URL; ?>" class="btn">Ir al Sistema</a>
            <?php elseif ($tests['db']): ?>
                <p style="color: #f59e0b; font-weight: bold;">
                    ⚠️ Sistema casi listo. Revisa los permisos de archivos.
                </p>
                <a href="<?php echo BASE_URL; ?>" class="btn">Ir al Sistema</a>
            <?php else: ?>
                <p style="color: #ef4444; font-weight: bold;">
                    ❌ Completa la configuración de la base de datos
                </p>
                <div class="code-block" style="text-align: left; margin-top: 20px;">
                    <strong>Pasos para completar la instalación:</strong><br><br>
                    1. Copia .env.example a .env<br>
                    2. Edita .env con tus credenciales de MySQL<br>
                    3. Importa database.sql en MySQL:<br>
                    &nbsp;&nbsp;&nbsp;mysql -u root -p &lt; database.sql<br>
                    4. Recarga esta página<br><br>
                    <strong>Usuarios de prueba:</strong><br>
                    - admin / admin123 (Administrador)<br>
                    - supervisor / supervisor123 (Supervisor)<br>
                    - tecnico / tecnico123 (Técnico)
                </div>
            <?php endif; ?>
            
            <p style="margin-top: 20px; color: #999; font-size: 12px;">
                Sistema de Inventario y Gastos v1.0 | <?php echo date('Y'); ?>
            </p>
        </div>
    </div>
</body>
</html>
