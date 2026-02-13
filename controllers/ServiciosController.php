<?php
/**
 * Controlador de Servicios
 */
class ServiciosController {
    
    public function index() {
        Auth::requirePermission('servicios', 'leer');
        
        $db = Database::getInstance();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
        $tecnico_id = isset($_GET['tecnico_id']) ? (int)$_GET['tecnico_id'] : 0;
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
        
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($estado)) {
            $whereClause .= " AND s.estado = :estado";
            $params['estado'] = $estado;
        }
        
        if ($tecnico_id > 0) {
            $whereClause .= " AND s.tecnico_id = :tecnico_id";
            $params['tecnico_id'] = $tecnico_id;
        }
        
        if (!empty($fecha_desde)) {
            $whereClause .= " AND s.fecha_programada >= :fecha_desde";
            $params['fecha_desde'] = $fecha_desde;
        }
        
        if (!empty($fecha_hasta)) {
            $whereClause .= " AND s.fecha_programada <= :fecha_hasta";
            $params['fecha_hasta'] = $fecha_hasta;
        }
        
        $countSql = "SELECT COUNT(*) as total FROM servicios s $whereClause";
        $totalRecords = $db->query($countSql, $params)->fetch()['total'];
        $totalPages = ceil($totalRecords / $perPage);
        
        $sql = "SELECT s.*, 
                CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente_nombre, c.telefono as cliente_telefono,
                CONCAT(t.nombre, ' ', t.apellidos) as tecnico_nombre,
                CONCAT(u.nombre, ' ', u.apellidos) as usuario_registro_nombre
                FROM servicios s 
                INNER JOIN clientes c ON s.cliente_id = c.id
                INNER JOIN usuarios t ON s.tecnico_id = t.id
                INNER JOIN usuarios u ON s.usuario_registro_id = u.id
                $whereClause
                ORDER BY s.fecha_programada DESC, s.fecha_creacion DESC 
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
        $servicios = $stmt->fetchAll();
        
        $sqlTecnicos = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellidos) as nombre_completo 
                        FROM usuarios u 
                        INNER JOIN roles r ON u.rol_id = r.id 
                        WHERE u.activo = 1 AND r.nombre IN ('Tecnico', 'Supervisor', 'Administrador')
                        ORDER BY u.nombre";
        $tecnicos = $db->query($sqlTecnicos)->fetchAll();
        
        $pageTitle = 'Gestión de Servicios';
        $activeMenu = 'servicios';
        
        ob_start();
        include ROOT_PATH . '/views/servicios/index.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function crear() {
        Auth::requirePermission('servicios', 'crear');
        
        $db = Database::getInstance();
        
        $sqlClientes = "SELECT id, nombre, apellidos FROM clientes WHERE activo = 1 ORDER BY nombre";
        $clientes = $db->query($sqlClientes)->fetchAll();
        
        $sqlTecnicos = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellidos) as nombre_completo 
                        FROM usuarios u 
                        INNER JOIN roles r ON u.rol_id = r.id 
                        WHERE u.activo = 1 AND r.nombre IN ('Tecnico', 'Supervisor', 'Administrador')
                        ORDER BY u.nombre";
        $tecnicos = $db->query($sqlTecnicos)->fetchAll();
        
        $pageTitle = 'Crear Servicio';
        $activeMenu = 'servicios';
        
        ob_start();
        include ROOT_PATH . '/views/servicios/crear.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function guardar() {
        Auth::requirePermission('servicios', 'crear');
        
        try {
            $errores = [];
            
            $cliente_id = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;
            $tipo_servicio = isset($_POST['tipo_servicio']) ? trim($_POST['tipo_servicio']) : '';
            $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $direccion_servicio = isset($_POST['direccion_servicio']) ? trim($_POST['direccion_servicio']) : '';
            $fecha_programada = isset($_POST['fecha_programada']) ? trim($_POST['fecha_programada']) : '';
            $tecnico_id = isset($_POST['tecnico_id']) ? (int)$_POST['tecnico_id'] : 0;
            $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'pendiente';
            $costo_mano_obra = isset($_POST['costo_mano_obra']) ? (float)$_POST['costo_mano_obra'] : 0;
            $otros_gastos = isset($_POST['otros_gastos']) ? (float)$_POST['otros_gastos'] : 0;
            $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
            
            if ($cliente_id <= 0) {
                $errores[] = 'Debe seleccionar un cliente';
            }
            
            if (!in_array($tipo_servicio, ['mantenimiento', 'reparacion', 'instalacion', 'otro'])) {
                $errores[] = 'Tipo de servicio inválido';
            }
            
            if (empty($titulo)) {
                $errores[] = 'El título es requerido';
            }
            
            if (empty($fecha_programada)) {
                $errores[] = 'La fecha programada es requerida';
            }
            
            if ($tecnico_id <= 0) {
                $errores[] = 'Debe seleccionar un técnico';
            }
            
            if (!in_array($estado, ['pendiente', 'en_proceso', 'completado', 'cancelado'])) {
                $errores[] = 'Estado inválido';
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'servicios/crear');
                exit;
            }
            
            $db = Database::getInstance();
            
            $total = $costo_mano_obra + $otros_gastos;
            
            $sql = "INSERT INTO servicios (cliente_id, tipo_servicio, titulo, descripcion, direccion_servicio, 
                    fecha_programada, tecnico_id, estado, costo_mano_obra, costo_materiales, otros_gastos, total, 
                    observaciones, usuario_registro_id) 
                    VALUES (:cliente_id, :tipo_servicio, :titulo, :descripcion, :direccion_servicio, 
                    :fecha_programada, :tecnico_id, :estado, :costo_mano_obra, 0.00, :otros_gastos, :total, 
                    :observaciones, :usuario_registro_id)";
            
            $params = [
                'cliente_id' => $cliente_id,
                'tipo_servicio' => $tipo_servicio,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'direccion_servicio' => $direccion_servicio,
                'fecha_programada' => $fecha_programada,
                'tecnico_id' => $tecnico_id,
                'estado' => $estado,
                'costo_mano_obra' => $costo_mano_obra,
                'otros_gastos' => $otros_gastos,
                'total' => $total,
                'observaciones' => $observaciones,
                'usuario_registro_id' => Auth::user()['id']
            ];
            
            $db->query($sql, $params);
            $servicioId = $db->lastInsertId();
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'crear',
                'servicios',
                $servicioId,
                "Servicio creado: $titulo"
            );
            
            $_SESSION['success_message'] = 'Servicio creado exitosamente';
            header('Location: ' . BASE_URL . 'servicios');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al crear servicio: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al crear el servicio';
            header('Location: ' . BASE_URL . 'servicios/crear');
            exit;
        }
    }
    
    public function ver($id) {
        Auth::requirePermission('servicios', 'leer');
        
        $db = Database::getInstance();
        
        $sql = "SELECT s.*, 
                CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente_nombre, 
                c.telefono as cliente_telefono, c.email as cliente_email,
                c.direccion as cliente_direccion,
                CONCAT(t.nombre, ' ', t.apellidos) as tecnico_nombre,
                CONCAT(u.nombre, ' ', u.apellidos) as usuario_registro_nombre
                FROM servicios s 
                INNER JOIN clientes c ON s.cliente_id = c.id
                INNER JOIN usuarios t ON s.tecnico_id = t.id
                INNER JOIN usuarios u ON s.usuario_registro_id = u.id
                WHERE s.id = :id";
        $servicio = $db->query($sql, ['id' => $id])->fetch();
        
        if (!$servicio) {
            $_SESSION['error_message'] = 'Servicio no encontrado';
            header('Location: ' . BASE_URL . 'servicios');
            exit;
        }
        
        $sqlMateriales = "SELECT sm.*, p.codigo, p.nombre as producto_nombre, p.unidad_medida
                          FROM servicio_materiales sm
                          INNER JOIN productos p ON sm.producto_id = p.id
                          WHERE sm.servicio_id = :id
                          ORDER BY sm.fecha_asignacion DESC";
        $materiales = $db->query($sqlMateriales, ['id' => $id])->fetchAll();
        
        $sqlHistorial = "SELECT s.id, s.titulo, s.tipo_servicio, s.fecha_programada, s.estado, s.total
                         FROM servicios s
                         WHERE s.cliente_id = :cliente_id AND s.id != :id
                         ORDER BY s.fecha_programada DESC
                         LIMIT 10";
        $historial = $db->query($sqlHistorial, ['cliente_id' => $servicio['cliente_id'], 'id' => $id])->fetchAll();
        
        $pageTitle = 'Detalle del Servicio';
        $activeMenu = 'servicios';
        
        ob_start();
        include ROOT_PATH . '/views/servicios/ver.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function editar($id) {
        Auth::requirePermission('servicios', 'actualizar');
        
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM servicios WHERE id = :id";
        $servicio = $db->query($sql, ['id' => $id])->fetch();
        
        if (!$servicio) {
            $_SESSION['error_message'] = 'Servicio no encontrado';
            header('Location: ' . BASE_URL . 'servicios');
            exit;
        }
        
        $sqlClientes = "SELECT id, nombre, apellidos FROM clientes WHERE activo = 1 ORDER BY nombre";
        $clientes = $db->query($sqlClientes)->fetchAll();
        
        $sqlTecnicos = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellidos) as nombre_completo 
                        FROM usuarios u 
                        INNER JOIN roles r ON u.rol_id = r.id 
                        WHERE u.activo = 1 AND r.nombre IN ('Tecnico', 'Supervisor', 'Administrador')
                        ORDER BY u.nombre";
        $tecnicos = $db->query($sqlTecnicos)->fetchAll();
        
        $pageTitle = 'Editar Servicio';
        $activeMenu = 'servicios';
        
        ob_start();
        include ROOT_PATH . '/views/servicios/editar.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function actualizar() {
        Auth::requirePermission('servicios', 'actualizar');
        
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                throw new Exception('ID de servicio inválido');
            }
            
            $errores = [];
            
            $cliente_id = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;
            $tipo_servicio = isset($_POST['tipo_servicio']) ? trim($_POST['tipo_servicio']) : '';
            $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $direccion_servicio = isset($_POST['direccion_servicio']) ? trim($_POST['direccion_servicio']) : '';
            $fecha_programada = isset($_POST['fecha_programada']) ? trim($_POST['fecha_programada']) : '';
            $fecha_inicio = isset($_POST['fecha_inicio']) ? trim($_POST['fecha_inicio']) : null;
            $fecha_fin = isset($_POST['fecha_fin']) ? trim($_POST['fecha_fin']) : null;
            $tecnico_id = isset($_POST['tecnico_id']) ? (int)$_POST['tecnico_id'] : 0;
            $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'pendiente';
            $costo_mano_obra = isset($_POST['costo_mano_obra']) ? (float)$_POST['costo_mano_obra'] : 0;
            $otros_gastos = isset($_POST['otros_gastos']) ? (float)$_POST['otros_gastos'] : 0;
            $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
            
            if ($cliente_id <= 0) {
                $errores[] = 'Debe seleccionar un cliente';
            }
            
            if (!in_array($tipo_servicio, ['mantenimiento', 'reparacion', 'instalacion', 'otro'])) {
                $errores[] = 'Tipo de servicio inválido';
            }
            
            if (empty($titulo)) {
                $errores[] = 'El título es requerido';
            }
            
            if (empty($fecha_programada)) {
                $errores[] = 'La fecha programada es requerida';
            }
            
            if ($tecnico_id <= 0) {
                $errores[] = 'Debe seleccionar un técnico';
            }
            
            if (!in_array($estado, ['pendiente', 'en_proceso', 'completado', 'cancelado'])) {
                $errores[] = 'Estado inválido';
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'servicios/editar/' . $id);
                exit;
            }
            
            $db = Database::getInstance();
            
            $sqlCostoMateriales = "SELECT IFNULL(SUM(costo_total), 0) as total FROM servicio_materiales WHERE servicio_id = :id";
            $costo_materiales = $db->query($sqlCostoMateriales, ['id' => $id])->fetch()['total'];
            
            $total = $costo_mano_obra + $costo_materiales + $otros_gastos;
            
            if (empty($fecha_inicio)) $fecha_inicio = null;
            if (empty($fecha_fin)) $fecha_fin = null;
            
            $sql = "UPDATE servicios 
                    SET cliente_id = :cliente_id, tipo_servicio = :tipo_servicio, titulo = :titulo, 
                        descripcion = :descripcion, direccion_servicio = :direccion_servicio, 
                        fecha_programada = :fecha_programada, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin,
                        tecnico_id = :tecnico_id, estado = :estado, costo_mano_obra = :costo_mano_obra, 
                        costo_materiales = :costo_materiales, otros_gastos = :otros_gastos, total = :total, 
                        observaciones = :observaciones 
                    WHERE id = :id";
            
            $params = [
                'id' => $id,
                'cliente_id' => $cliente_id,
                'tipo_servicio' => $tipo_servicio,
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'direccion_servicio' => $direccion_servicio,
                'fecha_programada' => $fecha_programada,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'tecnico_id' => $tecnico_id,
                'estado' => $estado,
                'costo_mano_obra' => $costo_mano_obra,
                'costo_materiales' => $costo_materiales,
                'otros_gastos' => $otros_gastos,
                'total' => $total,
                'observaciones' => $observaciones
            ];
            
            $db->query($sql, $params);
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'actualizar',
                'servicios',
                $id,
                "Servicio actualizado: $titulo"
            );
            
            $_SESSION['success_message'] = 'Servicio actualizado exitosamente';
            header('Location: ' . BASE_URL . 'servicios/ver/' . $id);
            exit;
            
        } catch (Exception $e) {
            error_log("Error al actualizar servicio: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al actualizar el servicio';
            header('Location: ' . BASE_URL . 'servicios');
            exit;
        }
    }
}
