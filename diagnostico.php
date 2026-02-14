<?php
/**
 * Script de Diagnóstico del Servidor
 * Ejecutar este script para identificar problemas de configuración
 * 
 * USO: Visitar http://tudominio.com/diagnostico.php
 * 
 * IMPORTANTE: Eliminar este archivo después de usar por razones de seguridad
 */

// Verificar que solo se ejecute en entorno de desarrollo o por IP autorizada
$allowed_ips = ['127.0.0.1', '::1']; // Agregar IPs autorizadas aquí
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Servidor - Sistema de Inventario</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #34495e;
            margin-top: 30px;
            padding: 10px;
            background: #ecf0f1;
            border-left: 4px solid #3498db;
        }
        .info-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #34495e;
            color: white;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .status-ok::before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
        }
        .status-error::before {
            content: "✗ ";
            color: #dc3545;
            font-weight: bold;
        }
        .status-warning::before {
            content: "⚠ ";
            color: #ffc107;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico del Servidor</h1>
        
        <div class="info-box warning">
            <strong>⚠️ ADVERTENCIA DE SEGURIDAD:</strong> Este archivo expone información sensible del servidor. 
            <strong>ELIMÍNELO</strong> después de completar el diagnóstico.
        </div>

        <?php
        // Información básica del servidor
        ?>
        
        <h2>📍 Información de Rutas</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td><strong>DOCUMENT_ROOT</strong></td>
                <td><code><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'No definido'; ?></code></td>
            </tr>
            <tr>
                <td><strong>SCRIPT_FILENAME</strong></td>
                <td><code><?php echo $_SERVER['SCRIPT_FILENAME'] ?? 'No definido'; ?></code></td>
            </tr>
            <tr>
                <td><strong>Directorio del script (__DIR__)</strong></td>
                <td><code><?php echo __DIR__; ?></code></td>
            </tr>
            <tr>
                <td><strong>Ruta real (realpath)</strong></td>
                <td><code><?php echo realpath(__DIR__); ?></code></td>
            </tr>
            <tr>
                <td><strong>SCRIPT_NAME</strong></td>
                <td><code><?php echo $_SERVER['SCRIPT_NAME'] ?? 'No definido'; ?></code></td>
            </tr>
            <tr>
                <td><strong>REQUEST_URI</strong></td>
                <td><code><?php echo $_SERVER['REQUEST_URI'] ?? 'No definido'; ?></code></td>
            </tr>
        </table>

        <h2>🔐 Configuración open_basedir (CRÍTICO)</h2>
        <?php
        $open_basedir = ini_get('open_basedir');
        if (empty($open_basedir)) {
            echo '<div class="info-box success status-ok">open_basedir no está configurado (acceso sin restricciones)</div>';
        } else {
            $current_path = realpath(__DIR__);
            $allowed_paths = explode(PATH_SEPARATOR, $open_basedir);
            $is_allowed = false;
            
            foreach ($allowed_paths as $allowed) {
                if (strpos($current_path, $allowed) === 0) {
                    $is_allowed = true;
                    break;
                }
            }
            
            if ($is_allowed) {
                echo '<div class="info-box success status-ok">La ruta actual está dentro de open_basedir permitido</div>';
            } else {
                echo '<div class="info-box error status-error"><strong>ERROR:</strong> La ruta actual NO está dentro de open_basedir permitido</div>';
            }
            
            echo '<table>';
            echo '<tr><th>Ruta actual del script</th><td><code>' . $current_path . '</code></td></tr>';
            echo '<tr><th>Rutas permitidas (open_basedir)</th><td><code>' . htmlspecialchars($open_basedir) . '</code></td></tr>';
            echo '</table>';
            
            echo '<div class="info-box warning">';
            echo '<strong>Análisis de rutas permitidas:</strong><ul>';
            foreach ($allowed_paths as $path) {
                $path = trim($path);
                if (empty($path)) continue;
                $matches = strpos($current_path, $path) === 0;
                echo '<li>' . ($matches ? '✓' : '✗') . ' <code>' . htmlspecialchars($path) . '</code></li>';
            }
            echo '</ul></div>';
        }
        ?>

        <h2>👤 Usuario y Permisos</h2>
        <table>
            <?php
            if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
                $user_info = posix_getpwuid(posix_geteuid());
                echo '<tr><td><strong>Usuario PHP (posix)</strong></td><td><code>' . $user_info['name'] . '</code></td></tr>';
                echo '<tr><td><strong>UID</strong></td><td><code>' . posix_geteuid() . '</code></td></tr>';
                echo '<tr><td><strong>GID</strong></td><td><code>' . posix_getegid() . '</code></td></tr>';
            } else {
                echo '<tr><td colspan="2" class="warning">Funciones POSIX no disponibles</td></tr>';
            }
            
            if (function_exists('get_current_user')) {
                echo '<tr><td><strong>Usuario actual (get_current_user)</strong></td><td><code>' . get_current_user() . '</code></td></tr>';
            }
            
            $owner = fileowner(__FILE__);
            echo '<tr><td><strong>Propietario de este archivo</strong></td><td><code>' . $owner . '</code></td></tr>';
            
            $perms = substr(sprintf('%o', fileperms(__FILE__)), -4);
            echo '<tr><td><strong>Permisos de este archivo</strong></td><td><code>' . $perms . '</code></td></tr>';
            ?>
        </table>

        <h2>🐘 Información de PHP</h2>
        <table>
            <tr>
                <td><strong>Versión de PHP</strong></td>
                <td><code><?php echo phpversion(); ?></code></td>
            </tr>
            <tr>
                <td><strong>SAPI</strong></td>
                <td><code><?php echo php_sapi_name(); ?></code></td>
            </tr>
            <tr>
                <td><strong>Modo de ejecución</strong></td>
                <td><code><?php 
                    $sapi = php_sapi_name();
                    if (strpos($sapi, 'cgi') !== false || strpos($sapi, 'fpm') !== false) {
                        echo 'CGI/FastCGI/PHP-FPM';
                    } else {
                        echo 'Apache Module (mod_php)';
                    }
                ?></code></td>
            </tr>
            <tr>
                <td><strong>Archivo de configuración (php.ini)</strong></td>
                <td><code><?php echo php_ini_loaded_file() ?: 'No encontrado'; ?></code></td>
            </tr>
            <tr>
                <td><strong>Archivos .ini adicionales</strong></td>
                <td><code><?php echo php_ini_scanned_files() ?: 'Ninguno'; ?></code></td>
            </tr>
        </table>

        <h2>🧪 Prueba de Escritura</h2>
        <?php
        $test_file = __DIR__ . '/test_write_' . time() . '.txt';
        $can_write = @file_put_contents($test_file, 'Prueba de escritura - ' . date('Y-m-d H:i:s'));
        
        if ($can_write !== false) {
            echo '<div class="info-box success status-ok">Escritura exitosa en: <code>' . $test_file . '</code></div>';
            @unlink($test_file);
        } else {
            echo '<div class="info-box error status-error">No se puede escribir en: <code>' . $test_file . '</code></div>';
            echo '<div class="info-box warning">Posibles causas: permisos de directorio, open_basedir, safe_mode</div>';
        }
        ?>

        <h2>🔌 Módulos y Extensiones</h2>
        <?php
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            $has_rewrite = in_array('mod_rewrite', $modules);
            $has_php = in_array('mod_php5', $modules) || in_array('mod_php7', $modules) || in_array('mod_php', $modules);
            
            echo '<table>';
            echo '<tr><td><strong>mod_rewrite</strong></td><td class="' . ($has_rewrite ? 'status-ok' : 'status-error') . '">' . ($has_rewrite ? 'Instalado' : 'No instalado') . '</td></tr>';
            echo '<tr><td><strong>mod_php</strong></td><td class="' . ($has_php ? 'status-ok' : 'status-warning') . '">' . ($has_php ? 'Instalado' : 'No instalado (usando CGI/FPM)') . '</td></tr>';
            echo '</table>';
        } else {
            echo '<div class="info-box warning">apache_get_modules() no está disponible (puede estar usando nginx o CGI/FPM)</div>';
        }
        
        $loaded_extensions = get_loaded_extensions();
        $required_extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring'];
        
        echo '<h3>Extensiones Requeridas</h3>';
        echo '<table>';
        echo '<tr><th>Extensión</th><th>Estado</th></tr>';
        foreach ($required_extensions as $ext) {
            $loaded = in_array($ext, $loaded_extensions);
            echo '<tr><td><code>' . $ext . '</code></td><td class="' . ($loaded ? 'status-ok' : 'status-error') . '">' . ($loaded ? 'Instalado' : 'NO instalado') . '</td></tr>';
        }
        echo '</table>';
        ?>

        <h2>⚙️ Configuraciones Importantes de PHP</h2>
        <table>
            <?php
            $important_settings = [
                'display_errors' => 'Mostrar errores',
                'error_reporting' => 'Nivel de reporte',
                'upload_max_filesize' => 'Tamaño máx. de subida',
                'post_max_size' => 'Tamaño máx. POST',
                'max_execution_time' => 'Tiempo máx. ejecución',
                'memory_limit' => 'Límite de memoria',
                'session.save_path' => 'Ruta de sesiones',
                'date.timezone' => 'Zona horaria',
                'allow_url_fopen' => 'Permitir URL fopen',
                'file_uploads' => 'Subida de archivos',
            ];
            
            foreach ($important_settings as $setting => $description) {
                $value = ini_get($setting);
                echo '<tr><td><strong>' . $description . '</strong><br><code>' . $setting . '</code></td><td><code>' . htmlspecialchars($value ?: 'No configurado') . '</code></td></tr>';
            }
            ?>
        </table>

        <h2>🌐 Información del Cliente</h2>
        <table>
            <tr>
                <td><strong>IP del cliente</strong></td>
                <td><code><?php echo $client_ip; ?></code></td>
            </tr>
            <tr>
                <td><strong>User Agent</strong></td>
                <td><code><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'No disponible'); ?></code></td>
            </tr>
            <tr>
                <td><strong>HTTP Host</strong></td>
                <td><code><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'No disponible'); ?></code></td>
            </tr>
            <tr>
                <td><strong>Server Name</strong></td>
                <td><code><?php echo htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'No disponible'); ?></code></td>
            </tr>
            <tr>
                <td><strong>Server Software</strong></td>
                <td><code><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible'); ?></code></td>
            </tr>
        </table>

        <h2>📂 Verificación de Archivos Críticos</h2>
        <?php
        $critical_files = [
            'index.php' => 'Archivo principal',
            '.htaccess' => 'Configuración Apache',
            'config/config.php' => 'Configuración',
            'config/database.php' => 'Configuración BD',
            'utils/Auth.php' => 'Autenticación',
            'public/.htaccess' => 'Config. directorio público',
            'public/index.php' => 'Index público',
        ];
        
        echo '<table>';
        echo '<tr><th>Archivo</th><th>Descripción</th><th>Estado</th></tr>';
        foreach ($critical_files as $file => $desc) {
            $path = __DIR__ . '/' . $file;
            $exists = file_exists($path);
            $readable = $exists && is_readable($path);
            
            if ($exists && $readable) {
                $status = '<span class="status-ok">Existe y es legible</span>';
            } elseif ($exists) {
                $status = '<span class="status-warning">Existe pero no es legible</span>';
            } else {
                $status = '<span class="status-error">No existe</span>';
            }
            
            echo '<tr><td><code>' . htmlspecialchars($file) . '</code></td><td>' . $desc . '</td><td>' . $status . '</td></tr>';
        }
        echo '</table>';
        ?>

        <h2>📊 Resumen y Recomendaciones</h2>
        <?php
        $issues = [];
        
        // Verificar open_basedir
        if (!empty($open_basedir)) {
            $current_path = realpath(__DIR__);
            $allowed_paths = explode(PATH_SEPARATOR, $open_basedir);
            $is_allowed = false;
            foreach ($allowed_paths as $allowed) {
                if (strpos($current_path, trim($allowed)) === 0) {
                    $is_allowed = true;
                    break;
                }
            }
            if (!$is_allowed) {
                $issues[] = '❌ CRÍTICO: La ruta actual no está dentro de open_basedir. Contacte al administrador del servidor.';
            }
        }
        
        // Verificar extensiones
        foreach (['pdo', 'pdo_mysql'] as $ext) {
            if (!in_array($ext, $loaded_extensions)) {
                $issues[] = '❌ Extensión requerida faltante: ' . $ext;
            }
        }
        
        // Verificar permisos de escritura
        if ($can_write === false) {
            $issues[] = '⚠️ No se puede escribir en el directorio actual. Verifique permisos.';
        }
        
        if (empty($issues)) {
            echo '<div class="info-box success">';
            echo '<h3 class="status-ok">✓ No se detectaron problemas críticos</h3>';
            echo '<p>El servidor parece estar configurado correctamente. Si aún experimenta errores, revise los logs de Apache/PHP.</p>';
            echo '</div>';
        } else {
            echo '<div class="info-box error">';
            echo '<h3>Problemas Detectados:</h3>';
            echo '<ul>';
            foreach ($issues as $issue) {
                echo '<li>' . $issue . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div class="info-box warning" style="margin-top: 30px;">
            <h3>🚨 ACCIÓN REQUERIDA</h3>
            <p><strong>Elimine este archivo inmediatamente después de completar el diagnóstico:</strong></p>
            <code>rm <?php echo __FILE__; ?></code>
            <p style="margin-top: 10px;">Este archivo expone información sensible del servidor que podría ser explotada por atacantes.</p>
        </div>

        <hr style="margin: 30px 0;">
        <p style="text-align: center; color: #7f8c8d; font-size: 12px;">
            Diagnóstico generado el <?php echo date('Y-m-d H:i:s'); ?> - Sistema de Inventario de Albercas
        </p>
    </div>
</body>
</html>
