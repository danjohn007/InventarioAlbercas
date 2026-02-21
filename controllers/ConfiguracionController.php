<?php
/**
 * Controlador de Configuraciones
 */
class ConfiguracionController {
    
    /**
     * Vista principal de configuraciones globales
     */
    public function index() {
        Auth::requirePermission('configuraciones', 'leer');
        
        $db = Database::getInstance();
        
        // Ensure the configuraciones table exists and has all seed data.
        $this->ensureConfiguracionesTable($db);
        $this->ensureExtendedConfig($db);
        
        // Obtener todas las configuraciones
        try {
            $sql = "SELECT * FROM configuraciones ORDER BY categoria, clave";
            $configuraciones = $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            error_log("Error loading configuraciones: " . $e->getMessage());
            $configuraciones = [];
        }
        
        // Flat array keyed by clave for easy access in view
        $config_all = [];
        foreach ($configuraciones as $config) {
            $config_all[$config['clave']] = $config['valor'];
        }
        
        // Agrupar por categoría (backwards compat)
        $config_grouped = [];
        foreach ($configuraciones as $config) {
            $config_grouped[$config['categoria']][] = $config;
        }
        
        // Recent audit log entries for Bitácora tab (last 10)
        try {
            $auditLogs = $db->query(
                "SELECT a.*, u.nombre, u.usuario 
                 FROM auditoria a 
                 LEFT JOIN usuarios u ON a.usuario_id = u.id 
                 ORDER BY a.fecha_creacion DESC LIMIT 10"
            )->fetchAll();
        } catch (Exception $e) {
            $auditLogs = [];
        }
        
        $pageTitle = 'Configuraciones Globales';
        $activeMenu = 'configuraciones';
        
        ob_start();
        require_once __DIR__ . '/../views/configuraciones/index.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    /**
     * Ensure the configuraciones table exists with its seed data.
     * Called on every visit to the index page so that production servers
     * that never ran the SQL migration still get a working module.
     * Uses a session flag so the CREATE/INSERT only runs once per session.
     */
    private function ensureConfiguracionesTable($db) {
        // Skip if already verified this session
        if (!empty($_SESSION['configuraciones_table_ok'])) {
            return;
        }

        try {
            $db->query("CREATE TABLE IF NOT EXISTS configuraciones (
                id INT PRIMARY KEY AUTO_INCREMENT,
                clave VARCHAR(100) NOT NULL UNIQUE,
                valor TEXT,
                tipo ENUM('texto','numero','booleano','json','archivo') DEFAULT 'texto',
                descripcion TEXT,
                categoria ENUM('general','apariencia','sistema','notificaciones') DEFAULT 'general',
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_clave (clave),
                INDEX idx_categoria (categoria)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            
            // Seed default values (INSERT IGNORE preserves any existing custom values)
            $db->query("INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
                ('sitio_nombre',       'Sistema de Inventario Albercas',        'texto',    'Nombre del sitio web',                      'general'),
                ('sitio_descripcion',  'Sistema de gestión integral para albercas','texto','Descripción del sitio',                    'general'),
                ('sitio_logo',         '',                                       'archivo',  'Ruta del logotipo del sitio',               'apariencia'),
                ('color_primario',     '#667eea',                                'texto',    'Color primario del sistema',                'apariencia'),
                ('color_secundario',   '#764ba2',                                'texto',    'Color secundario del sistema',              'apariencia'),
                ('moneda',             'MXN',                                    'texto',    'Moneda del sistema',                        'general'),
                ('simbolo_moneda',     '$',                                      'texto',    'Símbolo de la moneda',                      'general'),
                ('zona_horaria',       'America/Mexico_City',                    'texto',    'Zona horaria del sistema',                  'sistema'),
                ('items_por_pagina',   '20',                                     'numero',   'Número de items por página en listados',    'sistema'),
                ('formato_fecha',      'd/m/Y',                                  'texto',    'Formato de fecha para mostrar',             'sistema'),
                ('formato_hora',       'H:i',                                    'texto',    'Formato de hora para mostrar',              'sistema'),
                ('stock_minimo_alerta','5',                                       'numero',   'Cantidad mínima de stock para alertar',     'sistema'),
                ('notificaciones_email','1',                                      'booleano', 'Activar notificaciones por email',          'notificaciones'),
                ('stock_bajo_alerta',  '1',                                       'booleano', 'Activar alertas de stock bajo',             'notificaciones'),
                ('email_admin',        'admin@albercas.com',                     'texto',    'Email del administrador del sistema',       'notificaciones'),
                ('backup_automatico',  '1',                                       'booleano', 'Activar respaldos automáticos',             'sistema'),
                ('dias_backup',        '7',                                       'numero',   'Días entre respaldos automáticos',          'sistema')");

            // Mark as verified for the rest of this session
            $_SESSION['configuraciones_table_ok'] = true;
        } catch (Exception $e) {
            // Non-fatal: log and continue. The index() method wraps its own
            // SELECT in a try-catch so a missing table won't crash the page.
            error_log("WARNING: Could not ensure configuraciones table: " . $e->getMessage());
        }
    }
    
    /**
     * Ensure extended config keys (new module features) exist.
     * Expands the categoria ENUM and seeds new configuration rows.
     */
    private function ensureExtendedConfig($db) {
        if (!empty($_SESSION['configuraciones_extended_ok'])) {
            return;
        }
        try {
            // Expand the categoria ENUM to include new categories
            $db->query("ALTER TABLE configuraciones MODIFY COLUMN categoria 
                ENUM('general','apariencia','sistema','notificaciones','contacto','integraciones') DEFAULT 'general'");
        } catch (Exception $e) {
            error_log("INFO: Could not expand configuraciones ENUM: " . $e->getMessage());
        }
        try {
            $db->query("INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
                ('telefono_principal',           '',                                    'texto',    'Teléfono de contacto principal',                       'contacto'),
                ('telefono_whatsapp',            '',                                    'texto',    'Número de WhatsApp para contacto',                     'contacto'),
                ('telefono_emergencias',         '',                                    'texto',    'Teléfono de emergencias 24h',                          'contacto'),
                ('horario_lunes_viernes',        '09:00 - 18:00',                       'texto',    'Horario de atención lunes a viernes',                  'contacto'),
                ('horario_sabado',               '09:00 - 14:00',                       'texto',    'Horario de atención sábado',                           'contacto'),
                ('horario_domingo',              'Cerrado',                             'texto',    'Horario de atención domingo',                          'contacto'),
                ('direccion_contacto',           '',                                    'texto',    'Dirección física de la empresa',                       'contacto'),
                ('paypal_mode',                  'sandbox',                             'texto',    'Modo de PayPal: sandbox o live',                       'integraciones'),
                ('paypal_email',                 '',                                    'texto',    'Email principal de la cuenta PayPal',                  'integraciones'),
                ('paypal_client_id',             '',                                    'texto',    'Client ID de la API de PayPal',                        'integraciones'),
                ('paypal_secret',                '',                                    'texto',    'Clave secreta (Secret) de la API de PayPal',           'integraciones'),
                ('qr_api_provider',              '',                                    'texto',    'Proveedor de API para generación masiva de QR',        'integraciones'),
                ('qr_api_key',                   '',                                    'texto',    'Clave API para generación de QR masivos',              'integraciones'),
                ('qr_api_url',                   '',                                    'texto',    'URL del endpoint de la API QR',                        'integraciones'),
                ('shelly_api_url',               'https://shelly-12-eu.shelly.cloud',  'texto',    'URL base de la API de Shelly Cloud',                   'integraciones'),
                ('shelly_account_id',            '',                                    'texto',    'ID de cuenta en Shelly Cloud',                         'integraciones'),
                ('shelly_api_key',               '',                                    'texto',    'API Key de Shelly Cloud',                              'integraciones'),
                ('hikvision_device_ip',          '',                                    'texto',    'Dirección IP del dispositivo HikVision',               'integraciones'),
                ('hikvision_username',           'admin',                               'texto',    'Usuario de acceso a HikVision',                        'integraciones'),
                ('hikvision_password',           '',                                    'texto',    'Contraseña de acceso a HikVision',                     'integraciones'),
                ('whatsapp_provider',            'meta',                                'texto',    'Proveedor del chatbot de WhatsApp (meta, twilio, etc)','integraciones'),
                ('whatsapp_phone_number',        '',                                    'texto',    'Número de teléfono de WhatsApp Business',              'integraciones'),
                ('whatsapp_api_key',             '',                                    'texto',    'API Key para el chatbot de WhatsApp',                  'integraciones'),
                ('whatsapp_access_token',        '',                                    'texto',    'Token de acceso de la WhatsApp Business API',          'integraciones'),
                ('whatsapp_webhook_verify_token','',                                    'texto',    'Token de verificación del webhook de WhatsApp',        'integraciones'),
                ('whatsapp_phone_number_id',     '',                                    'texto',    'ID del número de teléfono en Meta Business API',       'integraciones'),
                ('email_enabled',               '0',                                   'booleano', 'Activar envío de correos electrónicos',                 'notificaciones'),
                ('smtp_host',                   '',                                    'texto',    'Servidor SMTP (ej: smtp.gmail.com)',                    'notificaciones'),
                ('smtp_port',                   '587',                                 'numero',   'Puerto del servidor SMTP',                              'notificaciones'),
                ('smtp_encryption',             'tls',                                 'texto',    'Cifrado SMTP: tls, ssl o none',                         'notificaciones'),
                ('smtp_username',               '',                                    'texto',    'Usuario o email de autenticación SMTP',                 'notificaciones'),
                ('smtp_password',               '',                                    'texto',    'Contraseña de autenticación SMTP',                      'notificaciones'),
                ('email_from_address',          '',                                    'texto',    'Dirección de correo del remitente (From)',               'notificaciones'),
                ('email_from_name',             'Sistema Inventario Albercas',         'texto',    'Nombre visible del remitente de correos',               'notificaciones')");
            $_SESSION['configuraciones_extended_ok'] = true;
        } catch (Exception $e) {
            error_log("WARNING: Could not seed extended configuraciones: " . $e->getMessage());
        }
    }

    /**
     * Registro de Errores del sistema
     */
    public function errores() {
        Auth::requirePermission('configuraciones', 'leer');

        $errorLogPath    = ini_get('error_log');
        $errorEntries    = [];
        $errorLogReadable = false;

        if ($errorLogPath && file_exists($errorLogPath) && is_readable($errorLogPath)) {
            $errorLogReadable = true;
            $fp = fopen($errorLogPath, 'r');
            if ($fp) {
                $all = [];
                while (!feof($fp)) {
                    $line = fgets($fp);
                    if ($line !== false && trim($line) !== '') {
                        // Escape here; the view outputs these entries directly
                        $all[] = htmlspecialchars(trim($line), ENT_QUOTES, 'UTF-8');
                    }
                }
                fclose($fp);
                // Keep only the last 500 entries, most recent first
                $errorEntries = array_reverse(array_slice($all, -500));
                unset($all);
            }
        }

        $pageTitle = 'Registro de Errores';
        $activeMenu = 'configuraciones';

        ob_start();
        require_once __DIR__ . '/../views/configuraciones/errores.php';
        $content = ob_get_clean();

        require_once __DIR__ . '/../views/layouts/main.php';
    }

    /**
     * Actualizar configuraciones
     */
    public function actualizar() {
        Auth::requirePermission('configuraciones', 'actualizar');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuraciones');
            exit;
        }

        // CSRF verification
        if (empty($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['error'] = 'Token de seguridad inválido. Por favor intenta de nuevo.';
            header('Location: ' . BASE_URL . 'configuraciones');
            exit;
        }
        
        $db = Database::getInstance();
        
        try {
            // Boolean/checkbox fields must default to '0' when not submitted (unchecked)
            $booleanFields = ['email_enabled', 'notificaciones_email', 'stock_bajo_alerta', 'backup_automatico'];
            foreach ($booleanFields as $boolField) {
                if (!isset($_POST[$boolField])) {
                    $_POST[$boolField] = '0';
                }
            }

            // Procesar logo si se subió
            if (isset($_FILES['sitio_logo']) && $_FILES['sitio_logo']['error'] === UPLOAD_ERR_OK) {
                $allowedMimeTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detectedMime = $finfo->file($_FILES['sitio_logo']['tmp_name']);

                if ($detectedMime === false || !array_key_exists($detectedMime, $allowedMimeTypes)) {
                    throw new Exception('Tipo de archivo no permitido para el logotipo. Use PNG, JPG, GIF o WEBP.');
                }

                $uploadDir = ROOT_PATH . '/public/uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = $allowedMimeTypes[$detectedMime];
                $filename = 'logo_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['sitio_logo']['tmp_name'], $filepath)) {
                    $_POST['sitio_logo'] = 'uploads/' . $filename;
                }
            }
            
            // Actualizar cada configuración
            foreach ($_POST as $clave => $valor) {
                if (in_array($clave, ['csrf_token', 'sitio_logo_current'])) continue;
                
                $sql = "UPDATE configuraciones SET valor = :valor WHERE clave = :clave";
                $db->query($sql, [
                    'valor' => $valor,
                    'clave' => $clave
                ]);
            }
            
            // Auditoría
            $usuario = Auth::user();
            $db->query("INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
                        VALUES (:usuario_id, 'actualizar', 'configuraciones', :detalles, :ip, :ua)", [
                'usuario_id' => $usuario['id'],
                'detalles' => 'Configuraciones del sistema actualizadas',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'ua' => $_SERVER['HTTP_USER_AGENT']
            ]);
            
            $_SESSION['success'] = 'Configuraciones actualizadas correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar configuraciones: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . 'configuraciones');
        exit;
    }
    
    /**
     * Obtener valor de configuración
     */
    public static function get($clave, $default = null) {
        $db = Database::getInstance();
        
        $sql = "SELECT valor FROM configuraciones WHERE clave = :clave LIMIT 1";
        $result = $db->query($sql, ['clave' => $clave])->fetch();
        
        return $result ? $result['valor'] : $default;
    }
    
    /**
     * Establecer valor de configuración
     */
    public static function set($clave, $valor) {
        $db = Database::getInstance();
        
        $sql = "UPDATE configuraciones SET valor = :valor WHERE clave = :clave";
        return $db->query($sql, [
            'valor' => $valor,
            'clave' => $clave
        ]);
    }
    
    /**
     * Exportar configuraciones (backup)
     */
    public function exportar() {
        Auth::requirePermission('configuraciones', 'leer');
        
        $db = Database::getInstance();
        $configuraciones = $db->query("SELECT * FROM configuraciones ORDER BY categoria, clave")->fetchAll();
        
        $backup = [
            'fecha_exportacion' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'configuraciones' => $configuraciones
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="configuraciones_backup_' . date('Ymd_His') . '.json"');
        echo json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Importar configuraciones (restore)
     */
    public function importar() {
        Auth::requirePermission('configuraciones', 'actualizar');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuraciones');
            exit;
        }
        
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Error al subir el archivo de backup';
            header('Location: ' . BASE_URL . 'configuraciones');
            exit;
        }
        
        $db = Database::getInstance();
        
        try {
            $jsonContent = file_get_contents($_FILES['backup_file']['tmp_name']);
            $backup = json_decode($jsonContent, true);
            
            if (!$backup || !isset($backup['configuraciones'])) {
                throw new Exception('Formato de backup inválido');
            }
            
            // Verificar la versión del backup
            if (isset($backup['version']) && $backup['version'] !== '1.0') {
                throw new Exception('Versión de backup no compatible');
            }
            
            // Obtener claves de configuración válidas
            $validKeys = $db->query("SELECT clave FROM configuraciones")->fetchAll();
            $validKeysList = array_column($validKeys, 'clave');
            
            // Validar y restaurar configuraciones
            $updated = 0;
            foreach ($backup['configuraciones'] as $config) {
                // Validar que la clave existe
                if (!in_array($config['clave'], $validKeysList)) {
                    continue; // Ignorar claves no válidas
                }
                
                // Sanitizar el valor
                $valor = is_string($config['valor']) ? htmlspecialchars($config['valor'], ENT_QUOTES, 'UTF-8') : $config['valor'];
                
                $sql = "UPDATE configuraciones SET valor = :valor WHERE clave = :clave";
                $db->query($sql, [
                    'valor' => $valor,
                    'clave' => $config['clave']
                ]);
                $updated++;
            }
            
            // Auditoría
            $usuario = Auth::user();
            $db->query("INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
                        VALUES (:usuario_id, 'importar', 'configuraciones', :detalles, :ip, :ua)", [
                'usuario_id' => $usuario['id'],
                'detalles' => 'Configuraciones restauradas desde backup (' . $updated . ' configuraciones actualizadas)',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'ua' => $_SERVER['HTTP_USER_AGENT']
            ]);
            
            $_SESSION['success'] = 'Configuraciones restauradas correctamente (' . $updated . ' configuraciones actualizadas)';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al restaurar configuraciones: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . 'configuraciones');
        exit;
    }
    
    /**
     * Restablecer configuraciones a valores por defecto
     */
    public function restablecer() {
        Auth::requirePermission('configuraciones', 'actualizar');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuraciones');
            exit;
        }
        
        $db = Database::getInstance();
        
        try {
            // Valores por defecto
            $defaults = [
                'sitio_nombre' => 'Sistema de Inventario Albercas',
                'sitio_descripcion' => 'Sistema de gestión integral',
                'color_primario' => '#667eea',
                'color_secundario' => '#764ba2',
                'items_por_pagina' => '20',
                'moneda' => 'MXN',
                'notificaciones_email' => '1',
                'stock_minimo_alerta' => '5'
            ];
            
            foreach ($defaults as $clave => $valor) {
                $sql = "UPDATE configuraciones SET valor = :valor WHERE clave = :clave";
                $db->query($sql, ['valor' => $valor, 'clave' => $clave]);
            }
            
            // Auditoría
            $usuario = Auth::user();
            $db->query("INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
                        VALUES (:usuario_id, 'restablecer', 'configuraciones', :detalles, :ip, :ua)", [
                'usuario_id' => $usuario['id'],
                'detalles' => 'Configuraciones restablecidas a valores por defecto',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'ua' => $_SERVER['HTTP_USER_AGENT']
            ]);
            
            $_SESSION['success'] = 'Configuraciones restablecidas correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al restablecer configuraciones: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . 'configuraciones');
        exit;
    }
    
    /**
     * Probar configuración de email
     */
    public function testEmail() {
        Auth::requirePermission('configuraciones', 'actualizar');
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        try {
            require_once __DIR__ . '/../utils/EmailSender.php';
            $emailSender = new EmailSender();
            
            // Probar conexión primero
            $connectionTest = $emailSender->testConnection();
            if (!$connectionTest['success']) {
                echo json_encode([
                    'success' => false,
                    'message' => $connectionTest['message']
                ]);
                exit;
            }
            
            // Obtener email de destino del POST
            $testEmail = $_POST['test_email'] ?? '';
            if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email de destino inválido'
                ]);
                exit;
            }
            
            // Enviar email de prueba
            $result = $emailSender->sendTest($testEmail);
            
            if ($result) {
                // Registrar en auditoría
                $usuario = Auth::user();
                $db = Database::getInstance();
                $db->query("INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
                            VALUES (:usuario_id, 'test_email', 'configuraciones', :detalles, :ip, :ua)", [
                    'usuario_id' => $usuario['id'],
                    'detalles' => 'Prueba de email enviada a ' . $testEmail,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'ua' => $_SERVER['HTTP_USER_AGENT']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Email de prueba enviado correctamente a ' . $testEmail
                ]);
            } else {
                $errors = $emailSender->getErrors();
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al enviar email: ' . implode(', ', $errors)
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Ver historial de auditoría
     */
    public function auditoria() {
        Auth::requirePermission('configuraciones', 'leer');
        
        $db = Database::getInstance();
        
        // Parámetros de paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Filtros
        $where = ['1=1'];
        $params = [];
        
        if (!empty($_GET['usuario_id'])) {
            $where[] = 'a.usuario_id = :usuario_id';
            $params['usuario_id'] = $_GET['usuario_id'];
        }
        
        if (!empty($_GET['accion'])) {
            $where[] = 'a.accion = :accion';
            $params['accion'] = $_GET['accion'];
        }
        
        if (!empty($_GET['tabla'])) {
            $where[] = 'a.tabla = :tabla';
            $params['tabla'] = $_GET['tabla'];
        }
        
        if (!empty($_GET['fecha_desde'])) {
            $where[] = 'DATE(a.fecha_creacion) >= :fecha_desde';
            $params['fecha_desde'] = $_GET['fecha_desde'];
        }
        
        if (!empty($_GET['fecha_hasta'])) {
            $where[] = 'DATE(a.fecha_creacion) <= :fecha_hasta';
            $params['fecha_hasta'] = $_GET['fecha_hasta'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Obtener total de registros
        $sql = "SELECT COUNT(*) as total FROM auditoria a WHERE $whereClause";
        $totalResult = $db->query($sql, $params)->fetch();
        $total = $totalResult['total'];
        $totalPages = ceil($total / $perPage);
        
        // Obtener registros de auditoría
        $sql = "SELECT a.*, u.nombre, u.apellidos, u.usuario 
                FROM auditoria a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE $whereClause
                ORDER BY a.fecha_creacion DESC
                LIMIT $perPage OFFSET $offset";
        $auditLogs = $db->query($sql, $params)->fetchAll();
        
        // Obtener lista de usuarios para filtro
        $usuarios = $db->query("SELECT id, nombre, apellidos, usuario FROM usuarios ORDER BY nombre")->fetchAll();
        
        // Obtener acciones únicas para filtro
        $acciones = $db->query("SELECT DISTINCT accion FROM auditoria ORDER BY accion")->fetchAll();
        
        // Obtener tablas únicas para filtro
        $tablas = $db->query("SELECT DISTINCT tabla FROM auditoria WHERE tabla IS NOT NULL ORDER BY tabla")->fetchAll();
        
        $pageTitle = 'Historial de Auditoría';
        $activeMenu = 'configuraciones';
        
        ob_start();
        require_once __DIR__ . '/../views/configuraciones/auditoria.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    /**
     * Administrar respaldos de base de datos
     */
    public function backups() {
        Auth::requirePermission('configuraciones', 'actualizar');
        
        require_once __DIR__ . '/../utils/DatabaseBackup.php';
        $backupManager = new DatabaseBackup();
        
        // Obtener lista de backups
        $backups = $backupManager->listBackups();
        
        // Verificar disponibilidad de herramientas
        $mysqldumpAvailable = DatabaseBackup::isMysqldumpAvailable();
        $mysqlAvailable = DatabaseBackup::isMysqlAvailable();
        
        $pageTitle = 'Respaldos de Base de Datos';
        $activeMenu = 'configuraciones';
        
        ob_start();
        require_once __DIR__ . '/../views/configuraciones/backups.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    /**
     * Crear nuevo backup
     */
    public function crearBackup() {
        Auth::requirePermission('configuraciones', 'actualizar');
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        try {
            require_once __DIR__ . '/../utils/DatabaseBackup.php';
            $backupManager = new DatabaseBackup();
            
            $description = $_POST['description'] ?? '';
            $result = $backupManager->create($description);
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Restaurar desde backup
     */
    public function restaurarBackup() {
        Auth::requirePermission('configuraciones', 'actualizar');
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        try {
            require_once __DIR__ . '/../utils/DatabaseBackup.php';
            $backupManager = new DatabaseBackup();
            
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['success' => false, 'message' => 'Nombre de archivo requerido']);
                exit;
            }
            
            $result = $backupManager->restore($filename);
            echo json_encode($result);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Eliminar backup
     */
    public function eliminarBackup() {
        Auth::requirePermission('configuraciones', 'actualizar');
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        try {
            require_once __DIR__ . '/../utils/DatabaseBackup.php';
            $backupManager = new DatabaseBackup();
            
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['success' => false, 'message' => 'Nombre de archivo requerido']);
                exit;
            }
            
            $result = $backupManager->delete($filename);
            echo json_encode($result);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Descargar backup
     */
    public function descargarBackup($filename) {
        Auth::requirePermission('configuraciones', 'leer');
        
        require_once __DIR__ . '/../utils/DatabaseBackup.php';
        $backupManager = new DatabaseBackup();
        $backupManager->download($filename);
    }
}
