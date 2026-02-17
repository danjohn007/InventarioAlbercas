<?php
/**
 * Controlador de Configuraciones
 */
class ConfiguracionController {
    
    /**
     * Vista principal de configuraciones
     */
    public function index() {
        Auth::requirePermission('configuraciones', 'leer');
        
        $db = Database::getInstance();
        
        // Obtener todas las configuraciones agrupadas por categoría
        $sql = "SELECT * FROM configuraciones ORDER BY categoria, clave";
        $configuraciones = $db->query($sql)->fetchAll();
        
        // Agrupar por categoría
        $config_grouped = [];
        foreach ($configuraciones as $config) {
            $config_grouped[$config['categoria']][] = $config;
        }
        
        $pageTitle = 'Configuraciones del Sistema';
        $activeMenu = 'configuraciones';
        
        ob_start();
        require_once __DIR__ . '/../views/configuraciones/index.php';
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
        
        $db = Database::getInstance();
        
        try {
            // Procesar logo si se subió
            if (isset($_FILES['sitio_logo']) && $_FILES['sitio_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = ROOT_PATH . '/public/uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['sitio_logo']['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['sitio_logo']['tmp_name'], $filepath)) {
                    $_POST['sitio_logo'] = 'uploads/' . $filename;
                }
            }
            
            // Actualizar cada configuración
            foreach ($_POST as $clave => $valor) {
                if ($clave === 'csrf_token') continue;
                
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
            
            // Restaurar configuraciones
            foreach ($backup['configuraciones'] as $config) {
                $sql = "UPDATE configuraciones SET valor = :valor WHERE clave = :clave";
                $db->query($sql, [
                    'valor' => $config['valor'],
                    'clave' => $config['clave']
                ]);
            }
            
            // Auditoría
            $usuario = Auth::user();
            $db->query("INSERT INTO auditoria (usuario_id, accion, tabla, detalles, ip_address, user_agent) 
                        VALUES (:usuario_id, 'importar', 'configuraciones', :detalles, :ip, :ua)", [
                'usuario_id' => $usuario['id'],
                'detalles' => 'Configuraciones restauradas desde backup',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'ua' => $_SERVER['HTTP_USER_AGENT']
            ]);
            
            $_SESSION['success'] = 'Configuraciones restauradas correctamente';
            
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
}
