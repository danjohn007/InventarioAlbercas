<?php
/**
 * Clase para gestionar autenticación de usuarios
 */
class Auth {
    
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            $sessionName = Config::get('SESSION_NAME', 'INVENTARIO_SESSION');
            $sessionLifetime = Config::get('SESSION_LIFETIME', 7200);
            
            session_name($sessionName);
            session_set_cookie_params($sessionLifetime);
            session_start();
        }
    }
    
    public static function login($usuario, $password) {
        try {
            $db = Database::getInstance();
            
            $sql = "SELECT u.*, r.nombre as rol_nombre, r.permisos 
                    FROM usuarios u 
                    INNER JOIN roles r ON u.rol_id = r.id 
                    WHERE u.usuario = :usuario AND u.activo = 1";
            
            $stmt = $db->query($sql, ['usuario' => $usuario]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Guardar información en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_apellidos'] = $user['apellidos'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_rol'] = $user['rol_nombre'];
                $_SESSION['user_rol_id'] = $user['rol_id'];
                $_SESSION['user_permisos'] = json_decode($user['permisos'], true);
                $_SESSION['logged_in'] = true;
                
                // Actualizar último acceso
                $updateSql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
                $db->query($updateSql, ['id' => $user['id']]);
                
                // Registrar auditoría
                self::registrarAuditoria($user['id'], 'login', 'usuarios', $user['id']);
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return false;
        }
    }
    
    public static function logout() {
        if (isset($_SESSION['user_id'])) {
            self::registrarAuditoria($_SESSION['user_id'], 'logout', 'usuarios', $_SESSION['user_id']);
        }
        
        session_destroy();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    public static function check() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function user() {
        if (!self::check()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_nombre'],
            'apellidos' => $_SESSION['user_apellidos'],
            'email' => $_SESSION['user_email'],
            'rol' => $_SESSION['user_rol'],
            'rol_id' => $_SESSION['user_rol_id']
        ];
    }
    
    public static function hasRole($role) {
        if (!self::check()) {
            return false;
        }
        
        return $_SESSION['user_rol'] === $role;
    }
    
    public static function can($modulo, $accion) {
        if (!self::check()) {
            return false;
        }
        
        $permisos = $_SESSION['user_permisos'];
        
        if (!isset($permisos[$modulo])) {
            return false;
        }
        
        return in_array($accion, $permisos[$modulo]);
    }
    
    public static function requireAuth() {
        if (!self::check()) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }
    
    public static function requireRole($role) {
        self::requireAuth();
        
        if (!self::hasRole($role)) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }
    
    public static function requirePermission($modulo, $accion) {
        self::requireAuth();
        
        if (!self::can($modulo, $accion)) {
            http_response_code(403);
            require_once ROOT_PATH . '/views/errors/403.php';
            exit;
        }
    }
    
    public static function registrarAuditoria($usuarioId, $accion, $tabla = null, $registroId = null, $detalles = null) {
        try {
            $db = Database::getInstance();
            
            $sql = "INSERT INTO auditoria (usuario_id, accion, tabla, registro_id, detalles, ip_address, user_agent) 
                    VALUES (:usuario_id, :accion, :tabla, :registro_id, :detalles, :ip_address, :user_agent)";
            
            $params = [
                'usuario_id' => $usuarioId,
                'accion' => $accion,
                'tabla' => $tabla,
                'registro_id' => $registroId,
                'detalles' => $detalles,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error registrando auditoría: " . $e->getMessage());
        }
    }
}
