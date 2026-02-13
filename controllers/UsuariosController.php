<?php
/**
 * Controlador de Usuarios
 */
class UsuariosController {
    
    public function index() {
        Auth::requirePermission('usuarios', 'leer');
        
        $db = Database::getInstance();
        
        // Paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Búsqueda
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // Construir query
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE u.nombre LIKE :search OR u.apellidos LIKE :search 
                           OR u.email LIKE :search OR u.usuario LIKE :search";
            $params['search'] = "%$search%";
        }
        
        // Obtener total de registros
        $countSql = "SELECT COUNT(*) as total FROM usuarios u $whereClause";
        $totalRecords = $db->query($countSql, $params)->fetch()['total'];
        $totalPages = ceil($totalRecords / $perPage);
        
        // Obtener usuarios
        $sql = "SELECT u.*, r.nombre as rol_nombre 
                FROM usuarios u 
                INNER JOIN roles r ON u.rol_id = r.id 
                $whereClause
                ORDER BY u.fecha_creacion DESC 
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $stmt = $db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(":$key", (int)$value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        $stmt->execute();
        $usuarios = $stmt->fetchAll();
        
        // Preparar vista
        $pageTitle = 'Usuarios';
        $activeMenu = 'usuarios';
        
        ob_start();
        include ROOT_PATH . '/views/usuarios/index.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function crear() {
        Auth::requirePermission('usuarios', 'crear');
        
        $db = Database::getInstance();
        
        // Obtener roles
        $sql = "SELECT id, nombre FROM roles WHERE activo = 1 ORDER BY nombre";
        $roles = $db->query($sql)->fetchAll();
        
        // Preparar vista
        $pageTitle = 'Crear Usuario';
        $activeMenu = 'usuarios';
        
        ob_start();
        include ROOT_PATH . '/views/usuarios/crear.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function guardar() {
        Auth::requirePermission('usuarios', 'crear');
        
        try {
            // Validar campos requeridos
            $errores = [];
            
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
            $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
            $rol_id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 0;
            
            if (empty($nombre)) {
                $errores[] = 'El nombre es requerido';
            }
            
            if (empty($apellidos)) {
                $errores[] = 'Los apellidos son requeridos';
            }
            
            if (empty($email)) {
                $errores[] = 'El email es requerido';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido';
            }
            
            if (empty($usuario)) {
                $errores[] = 'El usuario es requerido';
            } elseif (strlen($usuario) < 4) {
                $errores[] = 'El usuario debe tener al menos 4 caracteres';
            }
            
            if (empty($password)) {
                $errores[] = 'La contraseña es requerida';
            } elseif (strlen($password) < 6) {
                $errores[] = 'La contraseña debe tener al menos 6 caracteres';
            } elseif ($password !== $password_confirm) {
                $errores[] = 'Las contraseñas no coinciden';
            }
            
            if ($rol_id <= 0) {
                $errores[] = 'Debe seleccionar un rol';
            }
            
            // Verificar unicidad de email
            $db = Database::getInstance();
            $checkEmail = "SELECT COUNT(*) as count FROM usuarios WHERE email = :email";
            $count = $db->query($checkEmail, ['email' => $email])->fetch()['count'];
            if ($count > 0) {
                $errores[] = 'El email ya está registrado';
            }
            
            // Verificar unicidad de usuario
            $checkUsuario = "SELECT COUNT(*) as count FROM usuarios WHERE usuario = :usuario";
            $count = $db->query($checkUsuario, ['usuario' => $usuario])->fetch()['count'];
            if ($count > 0) {
                $errores[] = 'El nombre de usuario ya está en uso';
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = implode(', ', $errores);
                header('Location: ' . BASE_URL . 'usuarios/crear');
                exit;
            }
            
            // Hash de contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario
            $sql = "INSERT INTO usuarios (nombre, apellidos, email, telefono, usuario, password, rol_id, activo) 
                    VALUES (:nombre, :apellidos, :email, :telefono, :usuario, :password, :rol_id, 1)";
            
            $params = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'telefono' => $telefono,
                'usuario' => $usuario,
                'password' => $passwordHash,
                'rol_id' => $rol_id
            ];
            
            $db->query($sql, $params);
            $usuarioId = $db->lastInsertId();
            
            // Registrar auditoría
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'crear',
                'usuarios',
                $usuarioId,
                "Usuario creado: $nombre $apellidos ($usuario)"
            );
            
            $_SESSION['success_message'] = 'Usuario creado exitosamente';
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al crear el usuario';
            header('Location: ' . BASE_URL . 'usuarios/crear');
            exit;
        }
    }
    
    public function editar($id) {
        Auth::requirePermission('usuarios', 'actualizar');
        
        $db = Database::getInstance();
        
        // Obtener usuario
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $usuario = $db->query($sql, ['id' => $id])->fetch();
        
        if (!$usuario) {
            $_SESSION['error_message'] = 'Usuario no encontrado';
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }
        
        // Obtener roles
        $sqlRoles = "SELECT id, nombre FROM roles WHERE activo = 1 ORDER BY nombre";
        $roles = $db->query($sqlRoles)->fetchAll();
        
        // Preparar vista
        $pageTitle = 'Editar Usuario';
        $activeMenu = 'usuarios';
        
        ob_start();
        include ROOT_PATH . '/views/usuarios/editar.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function actualizar() {
        Auth::requirePermission('usuarios', 'actualizar');
        
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                throw new Exception('ID de usuario inválido');
            }
            
            // Validar campos requeridos
            $errores = [];
            
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
            $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
            $rol_id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 0;
            $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;
            
            if (empty($nombre)) {
                $errores[] = 'El nombre es requerido';
            }
            
            if (empty($apellidos)) {
                $errores[] = 'Los apellidos son requeridos';
            }
            
            if (empty($email)) {
                $errores[] = 'El email es requerido';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido';
            }
            
            if (empty($usuario)) {
                $errores[] = 'El usuario es requerido';
            } elseif (strlen($usuario) < 4) {
                $errores[] = 'El usuario debe tener al menos 4 caracteres';
            }
            
            // Validar contraseña solo si se proporciona
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $errores[] = 'La contraseña debe tener al menos 6 caracteres';
                } elseif ($password !== $password_confirm) {
                    $errores[] = 'Las contraseñas no coinciden';
                }
            }
            
            if ($rol_id <= 0) {
                $errores[] = 'Debe seleccionar un rol';
            }
            
            // Verificar unicidad de email
            $db = Database::getInstance();
            $checkEmail = "SELECT COUNT(*) as count FROM usuarios WHERE email = :email AND id != :id";
            $count = $db->query($checkEmail, ['email' => $email, 'id' => $id])->fetch()['count'];
            if ($count > 0) {
                $errores[] = 'El email ya está registrado';
            }
            
            // Verificar unicidad de usuario
            $checkUsuario = "SELECT COUNT(*) as count FROM usuarios WHERE usuario = :usuario AND id != :id";
            $count = $db->query($checkUsuario, ['usuario' => $usuario, 'id' => $id])->fetch()['count'];
            if ($count > 0) {
                $errores[] = 'El nombre de usuario ya está en uso';
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = implode(', ', $errores);
                header('Location: ' . BASE_URL . 'usuarios/editar/' . $id);
                exit;
            }
            
            // Preparar actualización
            $params = [
                'id' => $id,
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'telefono' => $telefono,
                'usuario' => $usuario,
                'rol_id' => $rol_id,
                'activo' => $activo
            ];
            
            // Si se proporciona nueva contraseña, actualizar también
            if (!empty($password)) {
                $sql = "UPDATE usuarios 
                        SET nombre = :nombre, apellidos = :apellidos, email = :email, 
                            telefono = :telefono, usuario = :usuario, password = :password, 
                            rol_id = :rol_id, activo = :activo 
                        WHERE id = :id";
                $params['password'] = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $sql = "UPDATE usuarios 
                        SET nombre = :nombre, apellidos = :apellidos, email = :email, 
                            telefono = :telefono, usuario = :usuario, rol_id = :rol_id, activo = :activo 
                        WHERE id = :id";
            }
            
            $db->query($sql, $params);
            
            // Registrar auditoría
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'actualizar',
                'usuarios',
                $id,
                "Usuario actualizado: $nombre $apellidos ($usuario)"
            );
            
            $_SESSION['success_message'] = 'Usuario actualizado exitosamente';
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al actualizar el usuario';
            header('Location: ' . BASE_URL . 'usuarios');
            exit;
        }
    }
    
    public function eliminar($id) {
        Auth::requirePermission('usuarios', 'eliminar');
        
        try {
            $db = Database::getInstance();
            
            // Verificar que el usuario existe
            $sql = "SELECT nombre, apellidos, usuario FROM usuarios WHERE id = :id";
            $usuario = $db->query($sql, ['id' => $id])->fetch();
            
            if (!$usuario) {
                $_SESSION['error_message'] = 'Usuario no encontrado';
                header('Location: ' . BASE_URL . 'usuarios');
                exit;
            }
            
            // No permitir eliminar el usuario actual
            if ($id == Auth::user()['id']) {
                $_SESSION['error_message'] = 'No puedes desactivar tu propio usuario';
                header('Location: ' . BASE_URL . 'usuarios');
                exit;
            }
            
            // Desactivar usuario (soft delete)
            $updateSql = "UPDATE usuarios SET activo = 0 WHERE id = :id";
            $db->query($updateSql, ['id' => $id]);
            
            // Registrar auditoría
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'eliminar',
                'usuarios',
                $id,
                "Usuario desactivado: {$usuario['nombre']} {$usuario['apellidos']} ({$usuario['usuario']})"
            );
            
            $_SESSION['success_message'] = 'Usuario desactivado exitosamente';
            
        } catch (Exception $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al desactivar el usuario';
        }
        
        header('Location: ' . BASE_URL . 'usuarios');
        exit;
    }
}
