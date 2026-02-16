<?php
/**
 * Controlador de Ingresos
 */
class IngresosController {
    
    /**
     * Listado de ingresos
     */
    public function index() {
        Auth::requirePermission('ingresos', 'leer');
        
        $db = Database::getInstance();
        
        // Filtros
        $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t');
        $categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
        
        $whereClause = 'WHERE i.fecha_ingreso >= :fecha_desde AND i.fecha_ingreso <= :fecha_hasta';
        $params = ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta];
        
        if ($categoria > 0) {
            $whereClause .= ' AND i.categoria_id = :categoria';
            $params['categoria'] = $categoria;
        }
        
        // Obtener ingresos
        $sql = "SELECT i.*, ci.nombre as categoria_nombre,
                CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente_nombre,
                s.titulo as servicio_titulo,
                CONCAT(u.nombre, ' ', u.apellidos) as usuario_nombre
                FROM ingresos i
                INNER JOIN categorias_ingreso ci ON i.categoria_id = ci.id
                LEFT JOIN clientes c ON i.cliente_id = c.id
                LEFT JOIN servicios s ON i.servicio_id = s.id
                INNER JOIN usuarios u ON i.usuario_registro_id = u.id
                $whereClause
                ORDER BY i.fecha_ingreso DESC, i.id DESC";
        
        $ingresos = $db->query($sql, $params)->fetchAll();
        
        // Estadísticas
        $statsSql = "SELECT 
                COUNT(*) as total_ingresos,
                SUM(monto) as total_monto,
                AVG(monto) as promedio_monto
                FROM ingresos i $whereClause";
        
        $stats = $db->query($statsSql, $params)->fetch();
        
        // Obtener categorías para el filtro
        $categorias = $db->query("SELECT * FROM categorias_ingreso WHERE activo = 1 ORDER BY nombre")->fetchAll();
        
        $pageTitle = 'Gestión de Ingresos';
        $activeMenu = 'ingresos';
        
        ob_start();
        require_once __DIR__ . '/../views/ingresos/index.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    /**
     * Formulario para crear ingreso
     */
    public function crear() {
        Auth::requirePermission('ingresos', 'crear');
        
        $db = Database::getInstance();
        
        $categorias = $db->query("SELECT * FROM categorias_ingreso WHERE activo = 1 ORDER BY nombre")->fetchAll();
        $clientes = $db->query("SELECT * FROM clientes WHERE activo = 1 ORDER BY nombre")->fetchAll();
        $servicios = $db->query("SELECT s.*, CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente_nombre 
                                FROM servicios s 
                                INNER JOIN clientes c ON s.cliente_id = c.id 
                                ORDER BY s.fecha_programada DESC LIMIT 100")->fetchAll();
        
        $pageTitle = 'Registrar Ingreso';
        $activeMenu = 'ingresos';
        
        ob_start();
        require_once __DIR__ . '/../views/ingresos/crear.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    /**
     * Guardar nuevo ingreso
     */
    public function guardar() {
        Auth::requirePermission('ingresos', 'crear');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'ingresos');
            exit;
        }
        
        $db = Database::getInstance();
        $usuario = Auth::user();
        
        try {
            $sql = "INSERT INTO ingresos (categoria_id, concepto, descripcion, monto, fecha_ingreso, 
                    forma_pago, servicio_id, cliente_id, observaciones, usuario_registro_id) 
                    VALUES (:categoria_id, :concepto, :descripcion, :monto, :fecha_ingreso, 
                    :forma_pago, :servicio_id, :cliente_id, :observaciones, :usuario_id)";
            
            $db->query($sql, [
                'categoria_id' => $_POST['categoria_id'],
                'concepto' => $_POST['concepto'],
                'descripcion' => $_POST['descripcion'] ?? null,
                'monto' => $_POST['monto'],
                'fecha_ingreso' => $_POST['fecha_ingreso'],
                'forma_pago' => $_POST['forma_pago'],
                'servicio_id' => !empty($_POST['servicio_id']) ? $_POST['servicio_id'] : null,
                'cliente_id' => !empty($_POST['cliente_id']) ? $_POST['cliente_id'] : null,
                'observaciones' => $_POST['observaciones'] ?? null,
                'usuario_id' => $usuario['id']
            ]);
            
            $ingreso_id = $db->lastInsertId();
            
            // Auditoría
            $db->query("INSERT INTO auditoria (usuario_id, accion, tabla, registro_id, detalles, ip_address, user_agent) 
                        VALUES (:usuario_id, 'crear', 'ingresos', :registro_id, :detalles, :ip, :ua)", [
                'usuario_id' => $usuario['id'],
                'registro_id' => $ingreso_id,
                'detalles' => 'Ingreso creado: ' . $_POST['concepto'] . ' - $' . number_format($_POST['monto'], 2),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'ua' => $_SERVER['HTTP_USER_AGENT']
            ]);
            
            $_SESSION['success'] = 'Ingreso registrado correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al registrar ingreso: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . 'ingresos');
        exit;
    }
    
    /**
     * Formulario para editar ingreso
     */
    public function editar($id) {
        Auth::requirePermission('ingresos', 'actualizar');
        
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM ingresos WHERE id = :id";
        $ingreso = $db->query($sql, ['id' => $id])->fetch();
        
        if (!$ingreso) {
            $_SESSION['error'] = 'Ingreso no encontrado';
            header('Location: ' . BASE_URL . 'ingresos');
            exit;
        }
        
        $categorias = $db->query("SELECT * FROM categorias_ingreso WHERE activo = 1 ORDER BY nombre")->fetchAll();
        $clientes = $db->query("SELECT * FROM clientes WHERE activo = 1 ORDER BY nombre")->fetchAll();
        $servicios = $db->query("SELECT s.*, CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente_nombre 
                                FROM servicios s 
                                INNER JOIN clientes c ON s.cliente_id = c.id 
                                ORDER BY s.fecha_programada DESC LIMIT 100")->fetchAll();
        
        $pageTitle = 'Editar Ingreso';
        $activeMenu = 'ingresos';
        
        ob_start();
        require_once __DIR__ . '/../views/ingresos/editar.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    /**
     * Actualizar ingreso
     */
    public function actualizar() {
        Auth::requirePermission('ingresos', 'actualizar');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'ingresos');
            exit;
        }
        
        $db = Database::getInstance();
        $usuario = Auth::user();
        
        try {
            $sql = "UPDATE ingresos SET 
                    categoria_id = :categoria_id,
                    concepto = :concepto,
                    descripcion = :descripcion,
                    monto = :monto,
                    fecha_ingreso = :fecha_ingreso,
                    forma_pago = :forma_pago,
                    servicio_id = :servicio_id,
                    cliente_id = :cliente_id,
                    observaciones = :observaciones
                    WHERE id = :id";
            
            $db->query($sql, [
                'id' => $_POST['id'],
                'categoria_id' => $_POST['categoria_id'],
                'concepto' => $_POST['concepto'],
                'descripcion' => $_POST['descripcion'] ?? null,
                'monto' => $_POST['monto'],
                'fecha_ingreso' => $_POST['fecha_ingreso'],
                'forma_pago' => $_POST['forma_pago'],
                'servicio_id' => !empty($_POST['servicio_id']) ? $_POST['servicio_id'] : null,
                'cliente_id' => !empty($_POST['cliente_id']) ? $_POST['cliente_id'] : null,
                'observaciones' => $_POST['observaciones'] ?? null
            ]);
            
            // Auditoría
            $db->query("INSERT INTO auditoria (usuario_id, accion, tabla, registro_id, detalles, ip_address, user_agent) 
                        VALUES (:usuario_id, 'actualizar', 'ingresos', :registro_id, :detalles, :ip, :ua)", [
                'usuario_id' => $usuario['id'],
                'registro_id' => $_POST['id'],
                'detalles' => 'Ingreso actualizado: ' . $_POST['concepto'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'ua' => $_SERVER['HTTP_USER_AGENT']
            ]);
            
            $_SESSION['success'] = 'Ingreso actualizado correctamente';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al actualizar ingreso: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . 'ingresos');
        exit;
    }
    
    /**
     * Eliminar ingreso
     */
    public function eliminar($id) {
        Auth::requirePermission('ingresos', 'eliminar');
        
        $db = Database::getInstance();
        $usuario = Auth::user();
        
        try {
            // Obtener información del ingreso antes de eliminar
            $ingreso = $db->query("SELECT concepto FROM ingresos WHERE id = :id", ['id' => $id])->fetch();
            
            if ($ingreso) {
                $sql = "DELETE FROM ingresos WHERE id = :id";
                $db->query($sql, ['id' => $id]);
                
                // Auditoría
                $db->query("INSERT INTO auditoria (usuario_id, accion, tabla, registro_id, detalles, ip_address, user_agent) 
                            VALUES (:usuario_id, 'eliminar', 'ingresos', :registro_id, :detalles, :ip, :ua)", [
                    'usuario_id' => $usuario['id'],
                    'registro_id' => $id,
                    'detalles' => 'Ingreso eliminado: ' . $ingreso['concepto'],
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'ua' => $_SERVER['HTTP_USER_AGENT']
                ]);
                
                $_SESSION['success'] = 'Ingreso eliminado correctamente';
            } else {
                $_SESSION['error'] = 'Ingreso no encontrado';
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al eliminar ingreso: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . 'ingresos');
        exit;
    }
}
