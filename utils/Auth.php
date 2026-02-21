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
                // Decodificar permisos del rol
                $permisos = json_decode($user['permisos'], true);
                
                // Validar que los permisos sean válidos
                if (!is_array($permisos)) {
                    error_log("ERROR: Permisos inválidos para el usuario '{$usuario}' (rol: {$user['rol_nombre']})");
                    $permisos = []; // Array vacío como fallback
                }
                
                // Self-healing: ensure the role has all required module permissions
                $permisos = self::ensureRequiredPermissions($permisos, $user['rol_nombre'], (int)$user['rol_id'], $db);
                
                // Guardar información en sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_apellidos'] = $user['apellidos'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_rol'] = $user['rol_nombre'];
                $_SESSION['user_rol_id'] = $user['rol_id'];
                $_SESSION['user_permisos'] = $permisos;
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
        
        // Verificar que exista la variable de permisos en sesión
        if (!isset($_SESSION['user_permisos']) || !is_array($_SESSION['user_permisos'])) {
            error_log("WARNING: user_permisos no está definido en la sesión para el usuario " . ($_SESSION['user_id'] ?? 'desconocido'));
            return false;
        }
        
        $permisos = $_SESSION['user_permisos'];
        
        if (!isset($permisos[$modulo])) {
            return false;
        }
        
        // Verificar que el módulo tenga un array de permisos
        if (!is_array($permisos[$modulo])) {
            error_log("WARNING: permisos para módulo '$modulo' no es un array");
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
            // Refresh permissions from DB to handle stale sessions
            // (e.g. role was updated in DB after the user logged in)
            if (isset($_SESSION['user_id'])) {
                self::refreshUserPermissions($_SESSION['user_id']);
            }
            
            // Re-check after refresh
            if (!self::can($modulo, $accion)) {
                // Registrar el intento de acceso no autorizado
                $userId = $_SESSION['user_id'] ?? 'desconocido';
                $userRol = $_SESSION['user_rol'] ?? 'desconocido';
                error_log("403 FORBIDDEN: Usuario ID $userId (rol: $userRol) intentó acceder a $modulo:$accion");
                
                // Registrar en auditoría si es posible
                if (isset($_SESSION['user_id'])) {
                    self::registrarAuditoria(
                        $_SESSION['user_id'], 
                        'acceso_denegado', 
                        $modulo, 
                        null, 
                        "Intento de acceso a: $modulo:$accion"
                    );
                }
                
                // Do NOT set http_response_code(403) here: on cPanel/LiteSpeed shared
                // hosting the web server intercepts any 403 status from PHP and replaces
                // the entire response with its own error page, hiding our custom view.
                require_once ROOT_PATH . '/views/errors/403.php';
                exit;
            }
        }
    }
    
    /**
     * Ensure a permissions array has all required modules for the given role.
     * Adds missing modules only (never removes existing ones).
     * If any module was added, persists the updated JSON back to the DB role.
     *
     * @param  array    $permisos  Current decoded permissions array
     * @param  string   $rolNombre Role name (e.g. 'Administrador')
     * @param  int      $rolId     Primary key of the role row
     * @param  mixed    $db        Database instance (fetched if null)
     * @return array               Permissions array (patched if needed)
     */
    public static function ensureRequiredPermissions(array $permisos, $rolNombre, $rolId, $db = null) {
        $requiredByRole = [
            'Administrador' => [
                'configuraciones' => ['leer', 'actualizar'],
                'ingresos'        => ['crear', 'leer', 'actualizar', 'eliminar'],
                'reportes'        => ['leer', 'exportar'],
            ],
            'Supervisor' => [
                'ingresos' => ['crear', 'leer', 'actualizar'],
                'reportes' => ['leer', 'exportar'],
            ],
        ];

        if (!isset($requiredByRole[$rolNombre])) {
            return $permisos;
        }

        $patched = false;
        foreach ($requiredByRole[$rolNombre] as $modulo => $acciones) {
            if (!isset($permisos[$modulo])) {
                $permisos[$modulo] = $acciones;
                $patched = true;
            }
        }

        if ($patched) {
            // Persist patched permissions to the DB so future logins receive the
            // correct permissions. Failures here are non-fatal: the session has
            // already been updated above and the current request will succeed.
            try {
                if ($db === null) {
                    $db = Database::getInstance();
                }
                $db->query(
                    "UPDATE roles SET permisos = :p WHERE id = :id",
                    ['p' => json_encode($permisos), 'id' => (int)$rolId]
                );
                error_log("INFO: Auto-migrated permissions for role '$rolNombre'");
            } catch (Exception $e) {
                error_log("WARNING: Could not persist migrated permissions for role '$rolNombre': " . $e->getMessage());
            }
        }

        return $permisos;
    }
    
    /**
     * Re-fetch the current user's role permissions from the database
     * and update the session. Fixes stale sessions when a role is
     * updated in the DB after the user has already logged in.
     *
     * Also applies ensureRequiredPermissions() so missing modules are
     * added if the production DB was never migrated.
     */
    public static function refreshUserPermissions($userId) {
        try {
            $db = Database::getInstance();
            // r.activo is intentionally omitted: it may not exist on older
            // production schemas, and a query failure would silently leave
            // the session unchanged.
            $sql = "SELECT u.rol_id, r.nombre as rol_nombre, r.permisos
                    FROM usuarios u
                    INNER JOIN roles r ON u.rol_id = r.id
                    WHERE u.id = :id AND u.activo = 1 LIMIT 1";
            $result = $db->query($sql, ['id' => (int)$userId])->fetch();

            if ($result) {
                $permisos = json_decode($result['permisos'], true);
                if (is_array($permisos)) {
                    // Apply self-healing migration.
                    // Session is updated unconditionally; DB persistence is best-effort
                    // inside ensureRequiredPermissions (has its own try-catch).
                    $permisos = self::ensureRequiredPermissions(
                        $permisos,
                        $result['rol_nombre'],
                        (int)$result['rol_id'],
                        $db
                    );
                    $_SESSION['user_permisos'] = $permisos;
                } else {
                    error_log("WARNING: JSON de permisos inválido para el usuario ID $userId");
                }
            }
        } catch (Exception $e) {
            error_log("Error refreshing user permissions: " . $e->getMessage());
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
