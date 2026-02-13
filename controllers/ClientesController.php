<?php
/**
 * Controlador de Clientes
 */
class ClientesController {
    
    public function index() {
        Auth::requirePermission('clientes', 'leer');
        
        $db = Database::getInstance();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (c.nombre LIKE :search OR c.apellidos LIKE :search OR c.telefono LIKE :search OR c.email LIKE :search OR c.ciudad LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        $countSql = "SELECT COUNT(*) as total FROM clientes c $whereClause";
        $totalRecords = $db->query($countSql, $params)->fetch()['total'];
        $totalPages = ceil($totalRecords / $perPage);
        
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM servicios WHERE cliente_id = c.id) as total_servicios
                FROM clientes c 
                $whereClause
                ORDER BY c.nombre ASC, c.apellidos ASC 
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
        $clientes = $stmt->fetchAll();
        
        $pageTitle = 'Gestión de Clientes';
        $activeMenu = 'clientes';
        
        ob_start();
        include ROOT_PATH . '/views/clientes/index.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function crear() {
        Auth::requirePermission('clientes', 'crear');
        
        $pageTitle = 'Crear Cliente';
        $activeMenu = 'clientes';
        
        ob_start();
        include ROOT_PATH . '/views/clientes/crear.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function guardar() {
        Auth::requirePermission('clientes', 'crear');
        
        try {
            $errores = [];
            
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
            $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
            $ciudad = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
            $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
            $codigo_postal = isset($_POST['codigo_postal']) ? trim($_POST['codigo_postal']) : '';
            $rfc = isset($_POST['rfc']) ? trim($_POST['rfc']) : '';
            $notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
            
            if (empty($nombre)) {
                $errores[] = 'El nombre es requerido';
            }
            
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El formato del email no es válido';
            }
            
            if (!empty($telefono) && !preg_match('/^[0-9\-\+\(\)\s]{7,20}$/', $telefono)) {
                $errores[] = 'El formato del teléfono no es válido';
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'clientes/crear');
                exit;
            }
            
            $db = Database::getInstance();
            
            $sql = "INSERT INTO clientes (nombre, apellidos, telefono, email, direccion, ciudad, estado, codigo_postal, rfc, notas, activo) 
                    VALUES (:nombre, :apellidos, :telefono, :email, :direccion, :ciudad, :estado, :codigo_postal, :rfc, :notas, 1)";
            
            $params = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'telefono' => $telefono,
                'email' => $email,
                'direccion' => $direccion,
                'ciudad' => $ciudad,
                'estado' => $estado,
                'codigo_postal' => $codigo_postal,
                'rfc' => $rfc,
                'notas' => $notas
            ];
            
            $db->query($sql, $params);
            $clienteId = $db->lastInsertId();
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'crear',
                'clientes',
                $clienteId,
                "Cliente creado: $nombre $apellidos"
            );
            
            $_SESSION['success_message'] = 'Cliente creado exitosamente';
            header('Location: ' . BASE_URL . 'clientes');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al crear cliente: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al crear el cliente';
            header('Location: ' . BASE_URL . 'clientes/crear');
            exit;
        }
    }
    
    public function editar($id) {
        Auth::requirePermission('clientes', 'actualizar');
        
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM clientes WHERE id = :id";
        $cliente = $db->query($sql, ['id' => $id])->fetch();
        
        if (!$cliente) {
            $_SESSION['error_message'] = 'Cliente no encontrado';
            header('Location: ' . BASE_URL . 'clientes');
            exit;
        }
        
        $pageTitle = 'Editar Cliente';
        $activeMenu = 'clientes';
        
        ob_start();
        include ROOT_PATH . '/views/clientes/editar.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function actualizar() {
        Auth::requirePermission('clientes', 'actualizar');
        
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                throw new Exception('ID de cliente inválido');
            }
            
            $errores = [];
            
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
            $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
            $ciudad = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
            $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
            $codigo_postal = isset($_POST['codigo_postal']) ? trim($_POST['codigo_postal']) : '';
            $rfc = isset($_POST['rfc']) ? trim($_POST['rfc']) : '';
            $notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
            $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;
            
            if (empty($nombre)) {
                $errores[] = 'El nombre es requerido';
            }
            
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El formato del email no es válido';
            }
            
            if (!empty($telefono) && !preg_match('/^[0-9\-\+\(\)\s]{7,20}$/', $telefono)) {
                $errores[] = 'El formato del teléfono no es válido';
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'clientes/editar/' . $id);
                exit;
            }
            
            $db = Database::getInstance();
            
            $sql = "UPDATE clientes 
                    SET nombre = :nombre, apellidos = :apellidos, telefono = :telefono, email = :email, 
                        direccion = :direccion, ciudad = :ciudad, estado = :estado, codigo_postal = :codigo_postal, 
                        rfc = :rfc, notas = :notas, activo = :activo 
                    WHERE id = :id";
            
            $params = [
                'id' => $id,
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'telefono' => $telefono,
                'email' => $email,
                'direccion' => $direccion,
                'ciudad' => $ciudad,
                'estado' => $estado,
                'codigo_postal' => $codigo_postal,
                'rfc' => $rfc,
                'notas' => $notas,
                'activo' => $activo
            ];
            
            $db->query($sql, $params);
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'actualizar',
                'clientes',
                $id,
                "Cliente actualizado: $nombre $apellidos"
            );
            
            $_SESSION['success_message'] = 'Cliente actualizado exitosamente';
            header('Location: ' . BASE_URL . 'clientes');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al actualizar cliente: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al actualizar el cliente';
            header('Location: ' . BASE_URL . 'clientes');
            exit;
        }
    }
}
