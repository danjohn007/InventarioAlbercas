<?php
/**
 * Controlador del Dashboard
 */
class DashboardController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        // Obtener estadísticas generales
        $stats = $this->getStats();
        
        // Obtener productos con stock bajo
        $productosBajoStock = $this->getProductosBajoStock();
        
        // Obtener servicios pendientes
        $serviciosPendientes = $this->getServiciosPendientes();
        
        // Obtener últimos gastos
        $ultimosGastos = $this->getUltimosGastos();
        
        // Obtener datos para gráficas
        $gastosChart = $this->getGastosPorCategoria();
        $ventasMes = $this->getVentasPorMes();
        $ingresosMes = $this->getIngresosPorMes();
        $ingresosVsGastos = $this->getIngresosVsGastos();
        
        // Renderizar vista
        $pageTitle = 'Dashboard';
        $activeMenu = 'dashboard';
        
        ob_start();
        require_once ROOT_PATH . '/views/dashboard/index.php';
        $content = ob_get_clean();
        
        require_once ROOT_PATH . '/views/layouts/main.php';
    }
    
    private function getStats() {
        $stats = [];
        
        // Total de productos
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
        $stats['productos'] = $stmt->fetch()['total'];
        
        // Total en inventario
        $stmt = $this->db->query("SELECT SUM(stock_actual * costo_unitario) as total FROM productos WHERE activo = 1");
        $stats['valor_inventario'] = $stmt->fetch()['total'] ?? 0;
        
        // Gastos del mes
        $stmt = $this->db->query("SELECT SUM(monto) as total FROM gastos WHERE MONTH(fecha_gasto) = MONTH(CURRENT_DATE()) AND YEAR(fecha_gasto) = YEAR(CURRENT_DATE())");
        $stats['gastos_mes'] = $stmt->fetch()['total'] ?? 0;
        
        // Ingresos del mes
        $stmt = $this->db->query("SELECT SUM(monto) as total FROM ingresos WHERE MONTH(fecha_ingreso) = MONTH(CURRENT_DATE()) AND YEAR(fecha_ingreso) = YEAR(CURRENT_DATE())");
        $stats['ingresos_mes'] = $stmt->fetch()['total'] ?? 0;
        
        // Servicios activos
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM servicios WHERE estado IN ('pendiente', 'en_proceso')");
        $stats['servicios_activos'] = $stmt->fetch()['total'];
        
        // Total de clientes
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
        $stats['clientes'] = $stmt->fetch()['total'];
        
        return $stats;
    }
    
    private function getProductosBajoStock() {
        $sql = "SELECT * FROM vista_productos_stock_bajo LIMIT 5";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    private function getServiciosPendientes() {
        $sql = "SELECT 
                    s.*,
                    CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente,
                    CONCAT(u.nombre, ' ', u.apellidos) as tecnico
                FROM servicios s
                INNER JOIN clientes c ON s.cliente_id = c.id
                INNER JOIN usuarios u ON s.tecnico_id = u.id
                WHERE s.estado IN ('pendiente', 'en_proceso')
                ORDER BY s.fecha_programada ASC
                LIMIT 5";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    private function getUltimosGastos() {
        $sql = "SELECT 
                    g.*,
                    cg.nombre as categoria
                FROM gastos g
                INNER JOIN categorias_gasto cg ON g.categoria_id = cg.id
                ORDER BY g.fecha_gasto DESC
                LIMIT 5";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    private function getGastosPorCategoria() {
        $sql = "SELECT 
                    cg.nombre,
                    SUM(g.monto) as total
                FROM gastos g
                INNER JOIN categorias_gasto cg ON g.categoria_id = cg.id
                WHERE MONTH(g.fecha_gasto) = MONTH(CURRENT_DATE()) 
                AND YEAR(g.fecha_gasto) = YEAR(CURRENT_DATE())
                GROUP BY cg.id, cg.nombre
                ORDER BY total DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    private function getVentasPorMes() {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_gasto, '%Y-%m') as mes,
                    SUM(monto) as total
                FROM gastos
                WHERE fecha_gasto >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(fecha_gasto, '%Y-%m')
                ORDER BY mes";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    private function getIngresosPorMes() {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_ingreso, '%Y-%m') as mes,
                    SUM(monto) as total
                FROM ingresos
                WHERE fecha_ingreso >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(fecha_ingreso, '%Y-%m')
                ORDER BY mes";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    private function getIngresosVsGastos() {
        $sql = "SELECT 
                    DATE_FORMAT(COALESCE(i.mes, g.mes), '%Y-%m') as mes,
                    COALESCE(i.total_ingresos, 0) as ingresos,
                    COALESCE(g.total_gastos, 0) as gastos,
                    COALESCE(i.total_ingresos, 0) - COALESCE(g.total_gastos, 0) as balance
                FROM 
                    (SELECT DATE_FORMAT(fecha_ingreso, '%Y-%m') as mes, SUM(monto) as total_ingresos
                     FROM ingresos 
                     WHERE fecha_ingreso >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(fecha_ingreso, '%Y-%m')) i
                LEFT JOIN 
                    (SELECT DATE_FORMAT(fecha_gasto, '%Y-%m') as mes, SUM(monto) as total_gastos
                     FROM gastos 
                     WHERE fecha_gasto >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(fecha_gasto, '%Y-%m')) g 
                ON i.mes = g.mes
                UNION
                SELECT 
                    DATE_FORMAT(COALESCE(i.mes, g.mes), '%Y-%m') as mes,
                    COALESCE(i.total_ingresos, 0) as ingresos,
                    COALESCE(g.total_gastos, 0) as gastos,
                    COALESCE(i.total_ingresos, 0) - COALESCE(g.total_gastos, 0) as balance
                FROM 
                    (SELECT DATE_FORMAT(fecha_ingreso, '%Y-%m') as mes, SUM(monto) as total_ingresos
                     FROM ingresos 
                     WHERE fecha_ingreso >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(fecha_ingreso, '%Y-%m')) i
                RIGHT JOIN 
                    (SELECT DATE_FORMAT(fecha_gasto, '%Y-%m') as mes, SUM(monto) as total_gastos
                     FROM gastos 
                     WHERE fecha_gasto >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(fecha_gasto, '%Y-%m')) g 
                ON i.mes = g.mes
                WHERE i.mes IS NULL
                ORDER BY mes";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
