<?php
/**
 * Controlador de Inventario
 */
class InventarioController {
    
    public function index() {
        Auth::requirePermission('inventario', 'leer');
        
        $db = Database::getInstance();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
        $stock_bajo = isset($_GET['stock_bajo']) ? 1 : 0;
        
        $whereClause = 'WHERE p.activo = 1';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (p.codigo LIKE :search OR p.nombre LIKE :search OR p.descripcion LIKE :search)";
            $params['search'] = "%$search%";
        }
        
        if ($categoria > 0) {
            $whereClause .= " AND p.categoria_id = :categoria";
            $params['categoria'] = $categoria;
        }
        
        if ($stock_bajo) {
            $whereClause .= " AND p.stock_actual <= p.stock_minimo";
        }
        
        $countSql = "SELECT COUNT(*) as total FROM productos p $whereClause";
        $totalRecords = $db->query($countSql, $params)->fetch()['total'];
        $totalPages = ceil($totalRecords / $perPage);
        
        $sql = "SELECT p.*, c.nombre as categoria_nombre, pr.nombre as proveedor_nombre 
                FROM productos p 
                INNER JOIN categorias_producto c ON p.categoria_id = c.id 
                LEFT JOIN proveedores pr ON p.proveedor_id = pr.id 
                $whereClause
                ORDER BY p.nombre ASC 
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
        $productos = $stmt->fetchAll();
        
        $sqlCategorias = "SELECT id, nombre FROM categorias_producto WHERE activo = 1 ORDER BY nombre";
        $categorias = $db->query($sqlCategorias)->fetchAll();
        
        $pageTitle = 'Inventario de Productos';
        $activeMenu = 'inventario';
        
        ob_start();
        include ROOT_PATH . '/views/inventario/index.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function crear() {
        Auth::requirePermission('inventario', 'crear');
        
        $db = Database::getInstance();
        
        $sqlCategorias = "SELECT id, nombre FROM categorias_producto WHERE activo = 1 ORDER BY nombre";
        $categorias = $db->query($sqlCategorias)->fetchAll();
        
        $sqlProveedores = "SELECT id, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre";
        $proveedores = $db->query($sqlProveedores)->fetchAll();
        
        $pageTitle = 'Crear Producto';
        $activeMenu = 'inventario';
        
        ob_start();
        include ROOT_PATH . '/views/inventario/crear.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function guardar() {
        Auth::requirePermission('inventario', 'crear');
        
        try {
            $errores = [];
            
            $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 0;
            $unidad_medida = isset($_POST['unidad_medida']) ? trim($_POST['unidad_medida']) : '';
            $costo_unitario = isset($_POST['costo_unitario']) ? (float)$_POST['costo_unitario'] : 0;
            $precio_venta = isset($_POST['precio_venta']) ? (float)$_POST['precio_venta'] : 0;
            $stock_minimo = isset($_POST['stock_minimo']) ? (float)$_POST['stock_minimo'] : 0;
            $proveedor_id = isset($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
            
            if (empty($nombre)) {
                $errores[] = 'El nombre es requerido';
            }
            
            if ($categoria_id <= 0) {
                $errores[] = 'Debe seleccionar una categoría';
            }
            
            if (empty($unidad_medida)) {
                $errores[] = 'La unidad de medida es requerida';
            }
            
            if ($costo_unitario <= 0) {
                $errores[] = 'El costo unitario debe ser mayor a 0';
            }
            
            if (empty($codigo)) {
                $codigo = 'PROD-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            }
            
            $db = Database::getInstance();
            
            $checkCodigo = "SELECT COUNT(*) as count FROM productos WHERE codigo = :codigo";
            $count = $db->query($checkCodigo, ['codigo' => $codigo])->fetch()['count'];
            if ($count > 0) {
                $errores[] = 'El código ya existe';
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'inventario/crear');
                exit;
            }
            
            $sql = "INSERT INTO productos (codigo, nombre, descripcion, categoria_id, unidad_medida, 
                    costo_unitario, precio_venta, stock_actual, stock_minimo, proveedor_id, activo) 
                    VALUES (:codigo, :nombre, :descripcion, :categoria_id, :unidad_medida, 
                    :costo_unitario, :precio_venta, 0.00, :stock_minimo, :proveedor_id, 1)";
            
            $params = [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'categoria_id' => $categoria_id,
                'unidad_medida' => $unidad_medida,
                'costo_unitario' => $costo_unitario,
                'precio_venta' => $precio_venta,
                'stock_minimo' => $stock_minimo,
                'proveedor_id' => $proveedor_id
            ];
            
            $db->query($sql, $params);
            $productoId = $db->lastInsertId();
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'crear',
                'productos',
                $productoId,
                "Producto creado: $nombre ($codigo)"
            );
            
            $_SESSION['success_message'] = 'Producto creado exitosamente';
            header('Location: ' . BASE_URL . 'inventario');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al crear producto: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al crear el producto';
            header('Location: ' . BASE_URL . 'inventario/crear');
            exit;
        }
    }
    
    public function editar($id) {
        Auth::requirePermission('inventario', 'actualizar');
        
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM productos WHERE id = :id";
        $producto = $db->query($sql, ['id' => $id])->fetch();
        
        if (!$producto) {
            $_SESSION['error_message'] = 'Producto no encontrado';
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }
        
        $sqlCategorias = "SELECT id, nombre FROM categorias_producto WHERE activo = 1 ORDER BY nombre";
        $categorias = $db->query($sqlCategorias)->fetchAll();
        
        $sqlProveedores = "SELECT id, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre";
        $proveedores = $db->query($sqlProveedores)->fetchAll();
        
        $pageTitle = 'Editar Producto';
        $activeMenu = 'inventario';
        
        ob_start();
        include ROOT_PATH . '/views/inventario/editar.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function actualizar() {
        Auth::requirePermission('inventario', 'actualizar');
        
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                throw new Exception('ID de producto inválido');
            }
            
            $errores = [];
            
            $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 0;
            $unidad_medida = isset($_POST['unidad_medida']) ? trim($_POST['unidad_medida']) : '';
            $costo_unitario = isset($_POST['costo_unitario']) ? (float)$_POST['costo_unitario'] : 0;
            $precio_venta = isset($_POST['precio_venta']) ? (float)$_POST['precio_venta'] : 0;
            $stock_minimo = isset($_POST['stock_minimo']) ? (float)$_POST['stock_minimo'] : 0;
            $proveedor_id = isset($_POST['proveedor_id']) ? (int)$_POST['proveedor_id'] : null;
            $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;
            
            if (empty($codigo)) {
                $errores[] = 'El código es requerido';
            }
            
            if (empty($nombre)) {
                $errores[] = 'El nombre es requerido';
            }
            
            if ($categoria_id <= 0) {
                $errores[] = 'Debe seleccionar una categoría';
            }
            
            if (empty($unidad_medida)) {
                $errores[] = 'La unidad de medida es requerida';
            }
            
            if ($costo_unitario <= 0) {
                $errores[] = 'El costo unitario debe ser mayor a 0';
            }
            
            $db = Database::getInstance();
            
            $checkCodigo = "SELECT COUNT(*) as count FROM productos WHERE codigo = :codigo AND id != :id";
            $count = $db->query($checkCodigo, ['codigo' => $codigo, 'id' => $id])->fetch()['count'];
            if ($count > 0) {
                $errores[] = 'El código ya existe';
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'inventario/editar/' . $id);
                exit;
            }
            
            $sql = "UPDATE productos 
                    SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion, 
                        categoria_id = :categoria_id, unidad_medida = :unidad_medida, 
                        costo_unitario = :costo_unitario, precio_venta = :precio_venta, 
                        stock_minimo = :stock_minimo, proveedor_id = :proveedor_id, activo = :activo 
                    WHERE id = :id";
            
            $params = [
                'id' => $id,
                'codigo' => $codigo,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'categoria_id' => $categoria_id,
                'unidad_medida' => $unidad_medida,
                'costo_unitario' => $costo_unitario,
                'precio_venta' => $precio_venta,
                'stock_minimo' => $stock_minimo,
                'proveedor_id' => $proveedor_id,
                'activo' => $activo
            ];
            
            $db->query($sql, $params);
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'actualizar',
                'productos',
                $id,
                "Producto actualizado: $nombre ($codigo)"
            );
            
            $_SESSION['success_message'] = 'Producto actualizado exitosamente';
            header('Location: ' . BASE_URL . 'inventario');
            exit;
            
        } catch (Exception $e) {
            error_log("Error al actualizar producto: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al actualizar el producto';
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }
    }
    
    public function movimientos() {
        Auth::requirePermission('inventario', 'leer');
        
        $db = Database::getInstance();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $producto = isset($_GET['producto']) ? (int)$_GET['producto'] : 0;
        $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
        
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if ($producto > 0) {
            $whereClause .= " AND im.producto_id = :producto";
            $params['producto'] = $producto;
        }
        
        if (!empty($tipo)) {
            $whereClause .= " AND im.tipo_movimiento = :tipo";
            $params['tipo'] = $tipo;
        }
        
        if (!empty($fecha_desde)) {
            $whereClause .= " AND DATE(im.fecha_movimiento) >= :fecha_desde";
            $params['fecha_desde'] = $fecha_desde;
        }
        
        if (!empty($fecha_hasta)) {
            $whereClause .= " AND DATE(im.fecha_movimiento) <= :fecha_hasta";
            $params['fecha_hasta'] = $fecha_hasta;
        }
        
        $countSql = "SELECT COUNT(*) as total FROM inventario_movimientos im $whereClause";
        $totalRecords = $db->query($countSql, $params)->fetch()['total'];
        $totalPages = ceil($totalRecords / $perPage);
        
        $sql = "SELECT im.*, p.codigo, p.nombre as producto_nombre, 
                CONCAT(u.nombre, ' ', u.apellidos) as usuario_nombre
                FROM inventario_movimientos im
                INNER JOIN productos p ON im.producto_id = p.id
                INNER JOIN usuarios u ON im.usuario_id = u.id
                $whereClause
                ORDER BY im.fecha_movimiento DESC 
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
        $movimientos = $stmt->fetchAll();
        
        $sqlProductos = "SELECT id, codigo, nombre FROM productos WHERE activo = 1 ORDER BY nombre";
        $productos = $db->query($sqlProductos)->fetchAll();
        
        $pageTitle = 'Movimientos de Inventario';
        $activeMenu = 'movimientos';
        
        ob_start();
        include ROOT_PATH . '/views/inventario/movimientos.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function registrarMovimiento($id) {
        Auth::requirePermission('inventario', 'leer');
        
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM productos WHERE id = :id AND activo = 1";
        $producto = $db->query($sql, ['id' => $id])->fetch();
        
        if (!$producto) {
            $_SESSION['error_message'] = 'Producto no encontrado';
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }
        
        $pageTitle = 'Registrar Movimiento';
        $activeMenu = 'inventario';
        
        ob_start();
        include ROOT_PATH . '/views/inventario/registrar_movimiento.php';
        $content = ob_get_clean();
        
        require ROOT_PATH . '/views/layouts/main.php';
    }
    
    public function guardarMovimiento() {
        Auth::requirePermission('inventario', 'actualizar');
        
        try {
            $db = Database::getInstance();
            $db->getConnection()->beginTransaction();
            
            $errores = [];
            
            $producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
            $tipo_movimiento = isset($_POST['tipo_movimiento']) ? trim($_POST['tipo_movimiento']) : '';
            $cantidad = isset($_POST['cantidad']) ? (float)$_POST['cantidad'] : 0;
            $costo_unitario = isset($_POST['costo_unitario']) ? (float)$_POST['costo_unitario'] : 0;
            $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
            $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
            
            if ($producto_id <= 0) {
                $errores[] = 'Producto inválido';
            }
            
            if (!in_array($tipo_movimiento, ['entrada', 'salida', 'ajuste'])) {
                $errores[] = 'Tipo de movimiento inválido';
            }
            
            if ($cantidad <= 0) {
                $errores[] = 'La cantidad debe ser mayor a 0';
            }
            
            if (empty($motivo)) {
                $errores[] = 'El motivo es requerido';
            }
            
            $sqlProducto = "SELECT stock_actual, costo_unitario FROM productos WHERE id = :id AND activo = 1";
            $producto = $db->query($sqlProducto, ['id' => $producto_id])->fetch();
            
            if (!$producto) {
                $errores[] = 'Producto no encontrado';
            }
            
            $stock_anterior = $producto['stock_actual'];
            $stock_nuevo = $stock_anterior;
            
            if ($tipo_movimiento == 'entrada') {
                $stock_nuevo = $stock_anterior + $cantidad;
            } elseif ($tipo_movimiento == 'salida') {
                if ($stock_anterior < $cantidad) {
                    $errores[] = 'Stock insuficiente. Stock actual: ' . number_format($stock_anterior, 2);
                }
                $stock_nuevo = $stock_anterior - $cantidad;
            } elseif ($tipo_movimiento == 'ajuste') {
                $stock_nuevo = $cantidad;
            }
            
            if ($stock_nuevo < 0) {
                $errores[] = 'El stock no puede ser negativo';
            }
            
            if ($costo_unitario <= 0) {
                $costo_unitario = $producto['costo_unitario'];
            }
            
            if (!empty($errores)) {
                $_SESSION['error_message'] = '<ul class="mb-0"><li>' . implode('</li><li>', $errores) . '</li></ul>';
                header('Location: ' . BASE_URL . 'inventario/movimiento/' . $producto_id);
                exit;
            }
            
            $costo_total = $cantidad * $costo_unitario;
            $fecha_movimiento = date('Y-m-d H:i:s');
            
            $sqlMovimiento = "INSERT INTO inventario_movimientos 
                            (producto_id, tipo_movimiento, cantidad, costo_unitario, costo_total, 
                            stock_anterior, stock_nuevo, motivo, observaciones, usuario_id, fecha_movimiento) 
                            VALUES (:producto_id, :tipo_movimiento, :cantidad, :costo_unitario, :costo_total, 
                            :stock_anterior, :stock_nuevo, :motivo, :observaciones, :usuario_id, :fecha_movimiento)";
            
            $paramsMovimiento = [
                'producto_id' => $producto_id,
                'tipo_movimiento' => $tipo_movimiento,
                'cantidad' => $cantidad,
                'costo_unitario' => $costo_unitario,
                'costo_total' => $costo_total,
                'stock_anterior' => $stock_anterior,
                'stock_nuevo' => $stock_nuevo,
                'motivo' => $motivo,
                'observaciones' => $observaciones,
                'usuario_id' => Auth::user()['id'],
                'fecha_movimiento' => $fecha_movimiento
            ];
            
            $db->query($sqlMovimiento, $paramsMovimiento);
            $movimientoId = $db->lastInsertId();
            
            $sqlUpdateStock = "UPDATE productos SET stock_actual = :stock_nuevo WHERE id = :id";
            $db->query($sqlUpdateStock, ['stock_nuevo' => $stock_nuevo, 'id' => $producto_id]);
            
            Auth::registrarAuditoria(
                Auth::user()['id'],
                'movimiento_inventario',
                'inventario_movimientos',
                $movimientoId,
                "Movimiento de inventario: $tipo_movimiento - Producto ID: $producto_id - Cantidad: $cantidad"
            );
            
            $db->getConnection()->commit();
            
            $_SESSION['success_message'] = 'Movimiento registrado exitosamente';
            header('Location: ' . BASE_URL . 'inventario/movimientos');
            exit;
            
        } catch (Exception $e) {
            $db->getConnection()->rollBack();
            error_log("Error al registrar movimiento: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error al registrar el movimiento';
            header('Location: ' . BASE_URL . 'inventario');
            exit;
        }
    }
}
