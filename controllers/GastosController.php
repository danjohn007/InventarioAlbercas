<?php
/**
 * Controlador de Gastos
 */
class GastosController {
    
    public function index() {
        Auth::requirePermission('gastos', 'leer');
        
        $db = Database::getInstance();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
        $categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
        $forma_pago = isset($_GET['forma_pago']) ? trim($_GET['forma_pago']) : '';
        
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($fecha_desde)) {
            $whereClause .= " AND g.fecha_gasto >= :fecha_desde";
            $params['fecha_desde'] = $fecha_desde;
        }
        
        if (!empty($fecha_hasta)) {
            $whereClause .= " AND g.fecha_gasto <= :fecha_hasta";
            $params['fecha_hasta'] = $fecha_hasta;
        }
        
        if ($categoria > 0) {
            $whereClause .= " AND g.categoria_id = :categoria";
            $params['categoria'] = $categoria;
        }
        
        if (!empty($forma_pago)) {
            $whereClause .= " AND g.forma_pago = :forma_pago";
            $params['forma_pago'] = $forma_pago;
        }
        
        $countSql = "SELECT COUNT(*) as total FROM gastos g $whereClause";
        $totalRecords = $db->query($countSql, $params)->fetch()['total'];
        $totalPages = ceil($totalRecords / $perPage);
        
        $totalSql = "SELECT SUM(monto) as total_monto FROM gastos g $whereClause";
        $totalMonto = $db->query($totalSql, $params)->fetch()['total_monto'] ?? 0;
        
        $sql = "SELECT g.*, cg.nombre as categoria_nombre, 
                CONCAT(u.nombre, ' ', u.apellidos) as usuario_nombre,
                s.titulo as servicio_titulo,
                CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente_nombre,
                p.nombre as proveedor_nombre
                FROM gastos g 
                INNER JOIN categorias_gasto cg ON g.categoria_id = cg.id 
                INNER JOIN usuarios u ON g.usuario_registro_id = u.id
                LEFT JOIN servicios s ON g.servicio_id = s.id
                LEFT JOIN clientes c ON g.cliente_id = c.id
                LEFT JOIN proveedores p ON g.proveedor_id = p.id
                $whereClause
                ORDER BY g.fecha_gasto DESC, g.fecha_creacion DESC 
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
        $gastos = $stmt->fetchAll();
        
        $sqlCategorias = "SELECT id, nombre FROM categorias_gasto WHERE activo = 1 ORDER BY nombre";
        $categorias = $db->query($sqlCategorias)->fetchAll();
        
        $pageTitle = 'Gestión de Gastos';
        $activeMenu = 'gastos';
        
        ob_start();
        include ROOT_PATH . '/views/gastos/index.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function crear() {
        Auth::requirePermission('gastos', 'crear');
        
        $db = Database::getInstance();
        
        $sqlCategorias = "SELECT id, nombre FROM categorias_gasto WHERE activo = 1 ORDER BY nombre";
        $categorias = $db->query($sqlCategorias)->fetchAll();
        
        $sqlServicios = "SELECT id, titulo, cliente_id FROM servicios WHERE estado != 'cancelado' ORDER BY fecha_programada DESC LIMIT 100";
        $servicios = $db->query($sqlServicios)->fetchAll();
        
        $sqlClientes = "SELECT id, nombre, apellidos FROM clientes WHERE activo = 1 ORDER BY nombre";
        $clientes = $db->query($sqlClientes)->fetchAll();
        
        $sqlProveedores = "SELECT id, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre";
        $proveedores = $db->query($sqlProveedores)->fetchAll();
        
        $pageTitle = 'Registrar Gasto';
        $activeMenu = 'gastos';
        
        ob_start();
        include ROOT_PATH . '/views/gastos/crear.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function guardar() {
        Auth::requirePermission('gastos', 'crear');
        
        try {
            $errores = [];
            
            $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 0;
            $concepto = isset($_POST['concepto']) ? trim($_POST['concepto']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
            $fecha_gasto = isset($_POST['fecha_gasto']) ? trim($_POST['fecha_gasto']) : '';
            $forma_pago = isset($_POST['forma_pago']) ? trim($_POST['forma_pago']) : '';
            $servicio_id = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : null;
            $cliente_id = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
            $proveedor_id = isset($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
            $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
            
            if ($categoria_id <= 0) {
                $errores[] = 'Debe seleccionar una categoría';
            }
            
            if (empty($concepto)) {
                $errores[] = 'El concepto es requerido';
            }
            
            if ($monto <= 0) {
                $errores[] = 'El monto debe ser mayor a 0';
            }
            
            if (empty($fecha_gasto)) {
                $errores[] = 'La fecha del gasto es requerida';
            } else {
                $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_gasto);
                if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha_gasto) {
                    $errores[] = 'Formato de fecha inválido';
                } else {
                    $hoy = new DateTime();
                    if ($fecha_obj > $hoy) {
                        $errores[] = 'La fecha del gasto no puede ser futura';
                    }
                }
            }
            
            if (!in_array($forma_pago, ['efectivo', 'tarjeta', 'transferencia', 'cheque'])) {
                $errores[] = 'Forma de pago inválida';
            }
            
            if ($servicio_id !== null && $servicio_id <= 0) {
                $servicio_id = null;
            }
            
            if ($cliente_id !== null && $cliente_id <= 0) {
                $cliente_id = null;
            }
            
            if ($proveedor_id !== null && $proveedor_id <= 0) {
                $proveedor_id = null;
            }
            
            $comprobante = null;
            if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array(strtolower($extension), $allowed)) {
                    $errores[] = 'Formato de archivo no permitido. Use PDF, JPG o PNG';
                } else {
                    $comprobante = 'comprobante_' . time() . '_' . uniqid() . '.' . $extension;
                }
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'gastos/crear');
                exit;
            }
            
            $db = Database::getInstance();
            
            $sql = "INSERT INTO gastos (categoria_id, concepto, descripcion, monto, fecha_gasto, 
                    forma_pago, servicio_id, cliente_id, proveedor_id, comprobante, observaciones, usuario_registro_id) 
                    VALUES (:categoria_id, :concepto, :descripcion, :monto, :fecha_gasto, 
                    :forma_pago, :servicio_id, :cliente_id, :proveedor_id, :comprobante, :observaciones, :usuario_registro_id)";
            
            $params = [
                'categoria_id' => $categoria_id,
                'concepto' => $concepto,
                'descripcion' => $descripcion,
                'monto' => $monto,
                'fecha_gasto' => $fecha_gasto,
                'forma_pago' => $forma_pago,
                'servicio_id' => $servicio_id,
                'cliente_id' => $cliente_id,
                'proveedor_id' => $proveedor_id,
                'comprobante' => $comprobante,
                'observaciones' => $observaciones,
                'usuario_registro_id' => Auth::user()['id']
            ];
            
            $db->query($sql, $params);
            $gastoId = $db->lastInsertId();
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'crear',
                'gastos',
                $gastoId,
                "Gasto creado: $concepto - $" . number_format($monto, 2)
            );
            
            $_SESSION['success_message'] = 'Gasto registrado exitosamente';
            header('Location: ' . BASE_URL . 'gastos');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al crear gasto: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al registrar el gasto';
            header('Location: ' . BASE_URL . 'gastos/crear');
            exit;
        }
    }
    
    public function editar($id) {
        Auth::requirePermission('gastos', 'actualizar');
        
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM gastos WHERE id = :id";
        $gasto = $db->query($sql, ['id' => $id])->fetch();
        
        if (!$gasto) {
            $_SESSION['error_message'] = 'Gasto no encontrado';
            header('Location: ' . BASE_URL . 'gastos');
            exit;
        }
        
        $sqlCategorias = "SELECT id, nombre FROM categorias_gasto WHERE activo = 1 ORDER BY nombre";
        $categorias = $db->query($sqlCategorias)->fetchAll();
        
        $sqlServicios = "SELECT id, titulo, cliente_id FROM servicios WHERE estado != 'cancelado' ORDER BY fecha_programada DESC LIMIT 100";
        $servicios = $db->query($sqlServicios)->fetchAll();
        
        $sqlClientes = "SELECT id, nombre, apellidos FROM clientes WHERE activo = 1 ORDER BY nombre";
        $clientes = $db->query($sqlClientes)->fetchAll();
        
        $sqlProveedores = "SELECT id, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre";
        $proveedores = $db->query($sqlProveedores)->fetchAll();
        
        $pageTitle = 'Editar Gasto';
        $activeMenu = 'gastos';
        
        ob_start();
        include ROOT_PATH . '/views/gastos/editar.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function actualizar() {
        Auth::requirePermission('gastos', 'actualizar');
        
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                throw new Exception('ID de gasto inválido');
            }
            
            $errores = [];
            
            $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 0;
            $concepto = isset($_POST['concepto']) ? trim($_POST['concepto']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
            $fecha_gasto = isset($_POST['fecha_gasto']) ? trim($_POST['fecha_gasto']) : '';
            $forma_pago = isset($_POST['forma_pago']) ? trim($_POST['forma_pago']) : '';
            $servicio_id = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : null;
            $cliente_id = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
            $proveedor_id = isset($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
            $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
            
            if ($categoria_id <= 0) {
                $errores[] = 'Debe seleccionar una categoría';
            }
            
            if (empty($concepto)) {
                $errores[] = 'El concepto es requerido';
            }
            
            if ($monto <= 0) {
                $errores[] = 'El monto debe ser mayor a 0';
            }
            
            if (empty($fecha_gasto)) {
                $errores[] = 'La fecha del gasto es requerida';
            } else {
                $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_gasto);
                if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha_gasto) {
                    $errores[] = 'Formato de fecha inválido';
                } else {
                    $hoy = new DateTime();
                    if ($fecha_obj > $hoy) {
                        $errores[] = 'La fecha del gasto no puede ser futura';
                    }
                }
            }
            
            if (!in_array($forma_pago, ['efectivo', 'tarjeta', 'transferencia', 'cheque'])) {
                $errores[] = 'Forma de pago inválida';
            }
            
            if ($servicio_id !== null && $servicio_id <= 0) {
                $servicio_id = null;
            }
            
            if ($cliente_id !== null && $cliente_id <= 0) {
                $cliente_id = null;
            }
            
            if ($proveedor_id !== null && $proveedor_id <= 0) {
                $proveedor_id = null;
            }
            
            $db = Database::getInstance();
            
            $sqlGasto = "SELECT comprobante FROM gastos WHERE id = :id";
            $gastoActual = $db->query($sqlGasto, ['id' => $id])->fetch();
            $comprobante = $gastoActual['comprobante'];
            
            if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!in_array(strtolower($extension), $allowed)) {
                    $errores[] = 'Formato de archivo no permitido. Use PDF, JPG o PNG';
                } else {
                    $comprobante = 'comprobante_' . time() . '_' . uniqid() . '.' . $extension;
                }
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'gastos/editar/' . $id);
                exit;
            }
            
            $sql = "UPDATE gastos 
                    SET categoria_id = :categoria_id, concepto = :concepto, descripcion = :descripcion, 
                        monto = :monto, fecha_gasto = :fecha_gasto, forma_pago = :forma_pago, 
                        servicio_id = :servicio_id, cliente_id = :cliente_id, proveedor_id = :proveedor_id, 
                        comprobante = :comprobante, observaciones = :observaciones 
                    WHERE id = :id";
            
            $params = [
                'id' => $id,
                'categoria_id' => $categoria_id,
                'concepto' => $concepto,
                'descripcion' => $descripcion,
                'monto' => $monto,
                'fecha_gasto' => $fecha_gasto,
                'forma_pago' => $forma_pago,
                'servicio_id' => $servicio_id,
                'cliente_id' => $cliente_id,
                'proveedor_id' => $proveedor_id,
                'comprobante' => $comprobante,
                'observaciones' => $observaciones
            ];
            
            $db->query($sql, $params);
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'actualizar',
                'gastos',
                $id,
                "Gasto actualizado: $concepto - $" . number_format($monto, 2)
            );
            
            $_SESSION['success_message'] = 'Gasto actualizado exitosamente';
            header('Location: ' . BASE_URL . 'gastos');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al actualizar gasto: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al actualizar el gasto';
            header('Location: ' . BASE_URL . 'gastos');
            exit;
        }
    }
}
