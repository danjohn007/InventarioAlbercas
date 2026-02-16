<?php
/**
 * Controlador de Reportes
 */
class ReportesController {
    
    public function index() {
        Auth::requirePermission('reportes', 'leer');
        
        $pageTitle = 'Centro de Reportes';
        $activeMenu = 'reportes';
        
        ob_start();
        require_once __DIR__ . '/../views/reportes/index.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    public function inventario() {
        Auth::requirePermission('reportes', 'leer');
        
        $db = Database::getInstance();
        
        $categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
        $orden = isset($_GET['orden']) ? $_GET['orden'] : 'nombre';
        
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if ($categoria > 0) {
            $whereClause .= " AND p.categoria_id = :categoria";
            $params['categoria'] = $categoria;
        }
        
        $orderClause = 'ORDER BY ';
        switch ($orden) {
            case 'stock_asc':
                $orderClause .= 'p.stock ASC';
                break;
            case 'stock_desc':
                $orderClause .= 'p.stock DESC';
                break;
            case 'precio_asc':
                $orderClause .= 'p.precio ASC';
                break;
            case 'precio_desc':
                $orderClause .= 'p.precio DESC';
                break;
            case 'nombre':
            default:
                $orderClause .= 'p.nombre ASC';
                break;
        }
        
        $sql = "SELECT p.*, c.nombre as categoria_nombre,
                (p.stock * p.precio) as valor_total
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id
                $whereClause
                $orderClause";
        
        $productos = $db->query($sql, $params)->fetchAll();
        
        $statsSql = "SELECT 
                COUNT(*) as total_productos,
                SUM(stock) as total_unidades,
                SUM(stock * precio) as valor_total_inventario,
                COUNT(CASE WHEN stock <= stock_minimo THEN 1 END) as productos_bajo_stock
                FROM productos p $whereClause";
        
        $stats = $db->query($statsSql, $params)->fetch();
        
        $categoriaSql = "SELECT c.nombre, 
                COUNT(p.id) as cantidad_productos,
                SUM(p.stock) as total_stock,
                SUM(p.stock * p.precio) as valor_total
                FROM categorias_producto c
                LEFT JOIN productos p ON c.id = p.categoria_id
                GROUP BY c.id, c.nombre
                ORDER BY valor_total DESC";
        
        $categorias_stats = $db->query($categoriaSql)->fetchAll();
        
        $categoriasQuery = $db->query("SELECT * FROM categorias_producto ORDER BY nombre");
        $categorias = $categoriasQuery->fetchAll();
        
        $lowStockSql = "SELECT p.*, c.nombre as categoria_nombre
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id
                WHERE p.stock <= p.stock_minimo
                ORDER BY (p.stock_minimo - p.stock) DESC
                LIMIT 10";
        
        $productos_bajo_stock = $db->query($lowStockSql)->fetchAll();
        
        $pageTitle = 'Reporte de Inventario';
        $activeMenu = 'reportes';
        
        ob_start();
        require_once __DIR__ . '/../views/reportes/inventario.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    public function gastos() {
        Auth::requirePermission('reportes', 'leer');
        
        $db = Database::getInstance();
        
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : date('Y-m-01');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : date('Y-m-t');
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
        
        $totalSql = "SELECT 
                COUNT(*) as total_gastos,
                SUM(monto) as total_monto,
                AVG(monto) as promedio_monto,
                MAX(monto) as monto_maximo
                FROM gastos g $whereClause";
        
        $stats = $db->query($totalSql, $params)->fetch();
        
        $categoriaSql = "SELECT gc.nombre, 
                COUNT(g.id) as cantidad_gastos,
                SUM(g.monto) as total_monto,
                AVG(g.monto) as promedio_monto
                FROM categorias_gasto gc
                LEFT JOIN gastos g ON gc.id = g.categoria_id
                WHERE 1=1 " . str_replace('g.categoria_id', 'gc.id', str_replace('WHERE 1=1', '', $whereClause)) . "
                GROUP BY gc.id, gc.nombre
                ORDER BY total_monto DESC";
        
        $gastos_por_categoria = $db->query($categoriaSql, $params)->fetchAll();
        
        $formaPagoSql = "SELECT g.forma_pago,
                COUNT(*) as cantidad,
                SUM(g.monto) as total_monto
                FROM gastos g
                $whereClause
                GROUP BY g.forma_pago
                ORDER BY total_monto DESC";
        
        $gastos_por_forma_pago = $db->query($formaPagoSql, $params)->fetchAll();
        
        $mensualSql = "SELECT 
                DATE_FORMAT(g.fecha_gasto, '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(g.monto) as total_monto
                FROM gastos g
                $whereClause
                GROUP BY DATE_FORMAT(g.fecha_gasto, '%Y-%m')
                ORDER BY mes ASC";
        
        $gastos_mensuales = $db->query($mensualSql, $params)->fetchAll();
        
        $categoriasQuery = $db->query("SELECT * FROM categorias_gasto ORDER BY nombre");
        $categorias = $categoriasQuery->fetchAll();
        
        $topGastosSql = "SELECT g.*, gc.nombre as categoria_nombre, u.nombre as usuario_nombre
                FROM gastos g
                LEFT JOIN categorias_gasto gc ON g.categoria_id = gc.id
                LEFT JOIN usuarios u ON g.usuario_id = u.id
                $whereClause
                ORDER BY g.monto DESC
                LIMIT 10";
        
        $top_gastos = $db->query($topGastosSql, $params)->fetchAll();
        
        $pageTitle = 'Reporte de Gastos';
        $activeMenu = 'reportes';
        
        ob_start();
        require_once __DIR__ . '/../views/reportes/gastos.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
    
    public function servicios() {
        Auth::requirePermission('reportes', 'leer');
        
        $db = Database::getInstance();
        
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : date('Y-m-01');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : date('Y-m-t');
        $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
        $tecnico_id = isset($_GET['tecnico_id']) ? (int)$_GET['tecnico_id'] : 0;
        
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if (!empty($fecha_desde)) {
            $whereClause .= " AND s.fecha_servicio >= :fecha_desde";
            $params['fecha_desde'] = $fecha_desde;
        }
        
        if (!empty($fecha_hasta)) {
            $whereClause .= " AND s.fecha_servicio <= :fecha_hasta";
            $params['fecha_hasta'] = $fecha_hasta;
        }
        
        if (!empty($estado)) {
            $whereClause .= " AND s.estado = :estado";
            $params['estado'] = $estado;
        }
        
        if ($tecnico_id > 0) {
            $whereClause .= " AND s.tecnico_id = :tecnico_id";
            $params['tecnico_id'] = $tecnico_id;
        }
        
        $statsSql = "SELECT 
                COUNT(*) as total_servicios,
                SUM(costo_total) as total_ingresos,
                AVG(costo_total) as promedio_costo,
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as servicios_pendientes,
                COUNT(CASE WHEN estado = 'en_proceso' THEN 1 END) as servicios_en_proceso,
                COUNT(CASE WHEN estado = 'completado' THEN 1 END) as servicios_completados,
                COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as servicios_cancelados
                FROM servicios s $whereClause";
        
        $stats = $db->query($statsSql, $params)->fetch();
        
        $tecnicosSql = "SELECT u.id, u.nombre,
                COUNT(s.id) as total_servicios,
                SUM(s.costo_total) as total_ingresos,
                AVG(s.costo_total) as promedio_costo,
                COUNT(CASE WHEN s.estado = 'completado' THEN 1 END) as servicios_completados
                FROM usuarios u
                INNER JOIN servicios s ON u.id = s.tecnico_id
                $whereClause
                GROUP BY u.id, u.nombre
                ORDER BY total_ingresos DESC";
        
        $servicios_por_tecnico = $db->query($tecnicosSql, $params)->fetchAll();
        
        $estadosSql = "SELECT estado,
                COUNT(*) as cantidad,
                SUM(costo_total) as total_ingresos
                FROM servicios s
                $whereClause
                GROUP BY estado
                ORDER BY cantidad DESC";
        
        $servicios_por_estado = $db->query($estadosSql, $params)->fetchAll();
        
        $mensualSql = "SELECT 
                DATE_FORMAT(s.fecha_servicio, '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(s.costo_total) as total_ingresos
                FROM servicios s
                $whereClause
                GROUP BY DATE_FORMAT(s.fecha_servicio, '%Y-%m')
                ORDER BY mes ASC";
        
        $servicios_mensuales = $db->query($mensualSql, $params)->fetchAll();
        
        $tecnicosQuery = $db->query("SELECT id, nombre FROM usuarios WHERE activo = 1 ORDER BY nombre");
        $tecnicos = $tecnicosQuery->fetchAll();
        
        $costosSql = "SELECT 
                SUM(costo_mano_obra) as total_mano_obra,
                SUM(costo_materiales) as total_materiales,
                SUM(costo_desplazamiento) as total_desplazamiento,
                SUM(costo_total) as total_general
                FROM servicios s
                $whereClause";
        
        $costos_breakdown = $db->query($costosSql, $params)->fetch();
        
        $clientesSql = "SELECT c.nombre, c.telefono,
                COUNT(s.id) as total_servicios,
                SUM(s.costo_total) as total_gastado
                FROM clientes c
                INNER JOIN servicios s ON c.id = s.cliente_id
                $whereClause
                GROUP BY c.id, c.nombre, c.telefono
                ORDER BY total_gastado DESC
                LIMIT 10";
        
        $top_clientes = $db->query($clientesSql, $params)->fetchAll();
        
        $pageTitle = 'Reporte de Servicios';
        $activeMenu = 'reportes';
        
        ob_start();
        require_once __DIR__ . '/../views/reportes/servicios.php';
        $content = ob_get_clean();
        
        require_once __DIR__ . '/../views/layouts/main.php';
    }
}
