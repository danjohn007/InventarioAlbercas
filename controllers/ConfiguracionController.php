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
}
