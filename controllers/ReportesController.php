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
                $orderClause .= 'p.stock_actual ASC';
                break;
            case 'stock_desc':
                $orderClause .= 'p.stock_actual DESC';
                break;
            case 'precio_asc':
                $orderClause .= 'p.precio_venta ASC';
                break;
            case 'precio_desc':
                $orderClause .= 'p.precio_venta DESC';
                break;
            case 'nombre':
            default:
                $orderClause .= 'p.nombre ASC';
                break;
        }
        
        $sql = "SELECT p.*, c.nombre as categoria_nombre,
                p.stock_actual as stock, p.precio_venta as precio,
                (p.stock_actual * p.precio_venta) as valor_total
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id
                $whereClause
                $orderClause";
        
        $productos = $db->query($sql, $params)->fetchAll();
        
        $statsSql = "SELECT 
                COUNT(*) as total_productos,
                SUM(stock_actual) as total_unidades,
                SUM(stock_actual * precio_venta) as valor_total_inventario,
                COUNT(CASE WHEN stock_actual <= stock_minimo THEN 1 END) as productos_bajo_stock
                FROM productos p $whereClause";
        
        $stats = $db->query($statsSql, $params)->fetch();
        
        $categoriaSql = "SELECT c.nombre, 
                COUNT(p.id) as cantidad_productos,
                SUM(p.stock_actual) as total_stock,
                SUM(p.stock_actual * p.precio_venta) as valor_total
                FROM categorias_producto c
                LEFT JOIN productos p ON c.id = p.categoria_id
                GROUP BY c.id, c.nombre
                ORDER BY valor_total DESC";
        
        $categorias_stats = $db->query($categoriaSql)->fetchAll();
        
        $categoriasQuery = $db->query("SELECT * FROM categorias_producto ORDER BY nombre");
        $categorias = $categoriasQuery->fetchAll();
        
        $lowStockSql = "SELECT p.*, c.nombre as categoria_nombre,
                p.stock_actual as stock
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id
                WHERE p.stock_actual <= p.stock_minimo
                ORDER BY (p.stock_minimo - p.stock_actual) DESC
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
            $whereClause .= " AND s.fecha_programada >= :fecha_desde";
            $params['fecha_desde'] = $fecha_desde;
        }
        
        if (!empty($fecha_hasta)) {
            $whereClause .= " AND s.fecha_programada <= :fecha_hasta";
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
                SUM(total) as total_ingresos,
                AVG(total) as promedio_costo,
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as servicios_pendientes,
                COUNT(CASE WHEN estado = 'en_proceso' THEN 1 END) as servicios_en_proceso,
                COUNT(CASE WHEN estado = 'completado' THEN 1 END) as servicios_completados,
                COUNT(CASE WHEN estado = 'cancelado' THEN 1 END) as servicios_cancelados
                FROM servicios s $whereClause";
        
        $stats = $db->query($statsSql, $params)->fetch();
        
        $tecnicosSql = "SELECT u.id, u.nombre,
                COUNT(s.id) as total_servicios,
                SUM(s.total) as total_ingresos,
                AVG(s.total) as promedio_costo,
                COUNT(CASE WHEN s.estado = 'completado' THEN 1 END) as servicios_completados
                FROM usuarios u
                INNER JOIN servicios s ON u.id = s.tecnico_id
                $whereClause
                GROUP BY u.id, u.nombre
                ORDER BY total_ingresos DESC";
        
        $servicios_por_tecnico = $db->query($tecnicosSql, $params)->fetchAll();
        
        $estadosSql = "SELECT estado,
                COUNT(*) as cantidad,
                SUM(total) as total_ingresos
                FROM servicios s
                $whereClause
                GROUP BY estado
                ORDER BY cantidad DESC";
        
        $servicios_por_estado = $db->query($estadosSql, $params)->fetchAll();
        
        $mensualSql = "SELECT 
                DATE_FORMAT(s.fecha_programada, '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(s.total) as total_ingresos
                FROM servicios s
                $whereClause
                GROUP BY DATE_FORMAT(s.fecha_programada, '%Y-%m')
                ORDER BY mes ASC";
        
        $servicios_mensuales = $db->query($mensualSql, $params)->fetchAll();
        
        $tecnicosQuery = $db->query("SELECT id, nombre FROM usuarios WHERE activo = 1 ORDER BY nombre");
        $tecnicos = $tecnicosQuery->fetchAll();
        
        $costosSql = "SELECT 
                SUM(costo_mano_obra) as total_mano_obra,
                SUM(costo_materiales) as total_materiales,
                SUM(otros_gastos) as total_desplazamiento,
                SUM(total) as total_general
                FROM servicios s
                $whereClause";
        
        $costos_breakdown = $db->query($costosSql, $params)->fetch();
        
        $clientesSql = "SELECT c.nombre, c.telefono,
                COUNT(s.id) as total_servicios,
                SUM(s.total) as total_gastado
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
    
    // ============================================
    // MÉTODOS DE EXPORTACIÓN
    // ============================================
    
    /**
     * Exportar Reporte de Inventario a PDF
     */
    public function exportarInventarioPDF() {
        Auth::requirePermission('reportes', 'exportar');
        
        require_once __DIR__ . '/../utils/exports/PdfExporter.php';
        
        $db = Database::getInstance();
        
        // Obtener productos con filtros si existen
        $categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if ($categoria > 0) {
            $whereClause .= " AND p.categoria_id = :categoria";
            $params['categoria'] = $categoria;
        }
        
        // Obtener productos
        $sql = "SELECT p.codigo as sku, p.nombre, c.nombre as categoria, 
                p.stock_actual as stock, p.precio_venta as precio,
                (p.stock_actual * p.precio_venta) as valor_total
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id
                $whereClause
                ORDER BY p.nombre ASC";
        
        $productos = $db->query($sql, $params)->fetchAll();
        
        // Estadísticas
        $statsSql = "SELECT 
                COUNT(*) as total_productos,
                SUM(stock_actual) as total_unidades,
                SUM(stock_actual * precio_venta) as valor_total_inventario
                FROM productos p $whereClause";
        
        $stats = $db->query($statsSql, $params)->fetch();
        
        // Crear PDF
        $pdf = new PdfExporter('Reporte de Inventario', 'L');
        $pdf->setHeader(null, 'REPORTE DE INVENTARIO', 'Generado: ' . date('d/m/Y H:i'));
        $pdf->setFooter();
        $pdf->addPage();
        
        // Resumen
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->cell(0, 10, 'Resumen General', 0, 1);
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(60, 6, 'Total de Productos:', 1, 0, 'L', true);
        $pdf->cell(50, 6, number_format($stats['total_productos']), 1, 1);
        $pdf->cell(60, 6, 'Total de Unidades:', 1, 0, 'L', true);
        $pdf->cell(50, 6, number_format($stats['total_unidades'], 2), 1, 1);
        $pdf->cell(60, 6, 'Valor Total del Inventario:', 1, 0, 'L', true);
        $pdf->cell(50, 6, '$' . number_format($stats['valor_total_inventario'], 2), 1, 1);
        $pdf->ln(10);
        
        // Tabla de productos
        $pdf->setFont('helvetica', 'B', 11);
        $pdf->cell(0, 10, 'Detalle de Productos', 0, 1);
        
        $headers = ['SKU', 'Nombre', 'Categoría', 'Stock', 'Precio Unit.', 'Valor Total'];
        $widths = [30, 80, 50, 25, 30, 35];
        
        $data = [];
        foreach ($productos as $p) {
            $data[] = [
                $p['sku'] ?? '-',
                substr($p['nombre'], 0, 50),
                $p['categoria'] ?? 'Sin categoría',
                number_format($p['stock'], 2),
                '$' . number_format($p['precio'], 2),
                '$' . number_format($p['valor_total'], 2)
            ];
        }
        
        $pdf->createTable($headers, $data, $widths);
        
        $pdf->download('reporte_inventario_' . date('Ymd') . '.pdf');
    }
    
    /**
     * Exportar Reporte de Inventario a Excel
     */
    public function exportarInventarioExcel() {
        Auth::requirePermission('reportes', 'exportar');
        
        require_once __DIR__ . '/../utils/exports/ExcelExporter.php';
        
        $db = Database::getInstance();
        
        // Obtener productos con filtros si existen
        $categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if ($categoria > 0) {
            $whereClause .= " AND p.categoria_id = :categoria";
            $params['categoria'] = $categoria;
        }
        
        // Obtener productos
        $sql = "SELECT p.codigo as sku, p.nombre, c.nombre as categoria, 
                p.stock_actual as stock, p.precio_venta as precio,
                (p.stock_actual * p.precio_venta) as valor_total
                FROM productos p
                LEFT JOIN categorias_producto c ON p.categoria_id = c.id
                $whereClause
                ORDER BY p.nombre ASC";
        
        $productos = $db->query($sql, $params)->fetchAll();
        
        // Estadísticas
        $statsSql = "SELECT 
                COUNT(*) as total_productos,
                SUM(stock_actual) as total_unidades,
                SUM(stock_actual * precio_venta) as valor_total_inventario
                FROM productos p $whereClause";
        
        $stats = $db->query($statsSql, $params)->fetch();
        
        // Crear Excel
        $excel = new ExcelExporter('Inventario');
        $excel->setReportTitle('REPORTE DE INVENTARIO', 'Generado: ' . date('d/m/Y H:i'));
        
        // Resumen
        $excel->addSummary('Resumen General', [
            'Total de Productos' => number_format($stats['total_productos']),
            'Total de Unidades' => number_format($stats['total_unidades'], 2),
            'Valor Total del Inventario' => '$' . number_format($stats['valor_total_inventario'], 2)
        ]);
        
        // Tabla de productos
        $headers = ['SKU', 'Nombre', 'Categoría', 'Stock', 'Precio Unitario', 'Valor Total'];
        
        $data = [];
        foreach ($productos as $p) {
            $data[] = [
                $p['sku'] ?? '-',
                $p['nombre'],
                $p['categoria'] ?? 'Sin categoría',
                $p['stock'],
                $p['precio'],
                $p['valor_total']
            ];
        }
        
        $excel->createTable($headers, $data);
        
        $excel->download('reporte_inventario_' . date('Ymd') . '.xlsx');
    }
    
    /**
     * Exportar Reporte de Gastos a PDF
     */
    public function exportarGastosPDF() {
        Auth::requirePermission('reportes', 'exportar');
        
        require_once __DIR__ . '/../utils/exports/PdfExporter.php';
        
        $db = Database::getInstance();
        
        $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t');
        
        $whereClause = 'WHERE g.fecha_gasto >= :fecha_desde AND g.fecha_gasto <= :fecha_hasta';
        $params = ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta];
        
        // Obtener gastos
        $sql = "SELECT g.fecha_gasto, g.concepto, cg.nombre as categoria, g.monto, g.forma_pago
                FROM gastos g
                INNER JOIN categorias_gasto cg ON g.categoria_id = cg.id
                $whereClause
                ORDER BY g.fecha_gasto DESC";
        
        $gastos = $db->query($sql, $params)->fetchAll();
        
        // Estadísticas
        $statsSql = "SELECT COUNT(*) as total, SUM(monto) as total_monto
                FROM gastos g $whereClause";
        $stats = $db->query($statsSql, $params)->fetch();
        
        // Crear PDF
        $pdf = new PdfExporter('Reporte de Gastos', 'L');
        $pdf->setHeader(null, 'REPORTE DE GASTOS', 'Período: ' . date('d/m/Y', strtotime($fecha_desde)) . ' - ' . date('d/m/Y', strtotime($fecha_hasta)));
        $pdf->setFooter();
        $pdf->addPage();
        
        // Resumen
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->cell(0, 10, 'Resumen General', 0, 1);
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(60, 6, 'Total de Gastos:', 1, 0, 'L', true);
        $pdf->cell(50, 6, number_format($stats['total']), 1, 1);
        $pdf->cell(60, 6, 'Monto Total:', 1, 0, 'L', true);
        $pdf->cell(50, 6, '$' . number_format($stats['total_monto'], 2), 1, 1);
        $pdf->ln(10);
        
        // Tabla
        $pdf->setFont('helvetica', 'B', 11);
        $pdf->cell(0, 10, 'Detalle de Gastos', 0, 1);
        
        $headers = ['Fecha', 'Concepto', 'Categoría', 'Forma de Pago', 'Monto'];
        $widths = [30, 90, 50, 40, 30];
        
        $data = [];
        foreach ($gastos as $g) {
            $data[] = [
                date('d/m/Y', strtotime($g['fecha_gasto'])),
                substr($g['concepto'], 0, 60),
                $g['categoria'],
                ucfirst($g['forma_pago']),
                '$' . number_format($g['monto'], 2)
            ];
        }
        
        $pdf->createTable($headers, $data, $widths);
        
        $pdf->download('reporte_gastos_' . date('Ymd') . '.pdf');
    }
    
    /**
     * Exportar Reporte de Gastos a Excel
     */
    public function exportarGastosExcel() {
        Auth::requirePermission('reportes', 'exportar');
        
        require_once __DIR__ . '/../utils/exports/ExcelExporter.php';
        
        $db = Database::getInstance();
        
        $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t');
        
        $whereClause = 'WHERE g.fecha_gasto >= :fecha_desde AND g.fecha_gasto <= :fecha_hasta';
        $params = ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta];
        
        // Obtener gastos
        $sql = "SELECT g.fecha_gasto, g.concepto, cg.nombre as categoria, g.monto, g.forma_pago, g.descripcion
                FROM gastos g
                INNER JOIN categorias_gasto cg ON g.categoria_id = cg.id
                $whereClause
                ORDER BY g.fecha_gasto DESC";
        
        $gastos = $db->query($sql, $params)->fetchAll();
        
        // Estadísticas
        $statsSql = "SELECT COUNT(*) as total, SUM(monto) as total_monto
                FROM gastos g $whereClause";
        $stats = $db->query($statsSql, $params)->fetch();
        
        // Crear Excel
        $excel = new ExcelExporter('Gastos');
        $excel->setReportTitle('REPORTE DE GASTOS', 'Período: ' . date('d/m/Y', strtotime($fecha_desde)) . ' - ' . date('d/m/Y', strtotime($fecha_hasta)));
        
        // Resumen
        $excel->addSummary('Resumen General', [
            'Total de Gastos' => number_format($stats['total']),
            'Monto Total' => '$' . number_format($stats['total_monto'], 2)
        ]);
        
        // Tabla
        $headers = ['Fecha', 'Concepto', 'Categoría', 'Forma de Pago', 'Monto', 'Descripción'];
        
        $data = [];
        foreach ($gastos as $g) {
            $data[] = [
                $g['fecha_gasto'],
                $g['concepto'],
                $g['categoria'],
                ucfirst($g['forma_pago']),
                $g['monto'],
                $g['descripcion']
            ];
        }
        
        $excel->createTable($headers, $data);
        
        $excel->download('reporte_gastos_' . date('Ymd') . '.xlsx');
    }
    
    /**
     * Exportar Reporte de Servicios a PDF
     */
    public function exportarServiciosPDF() {
        Auth::requirePermission('reportes', 'exportar');
        
        require_once __DIR__ . '/../utils/exports/PdfExporter.php';
        
        $db = Database::getInstance();
        
        $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t');
        
        $whereClause = 'WHERE s.fecha_programada >= :fecha_desde AND s.fecha_programada <= :fecha_hasta';
        $params = ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta];
        
        // Obtener servicios
        $sql = "SELECT s.fecha_programada, s.titulo, s.tipo_servicio, s.estado, 
                CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente,
                CONCAT(u.nombre, ' ', u.apellidos) as tecnico,
                s.total
                FROM servicios s
                INNER JOIN clientes c ON s.cliente_id = c.id
                INNER JOIN usuarios u ON s.tecnico_id = u.id
                $whereClause
                ORDER BY s.fecha_programada DESC";
        
        $servicios = $db->query($sql, $params)->fetchAll();
        
        // Estadísticas
        $statsSql = "SELECT COUNT(*) as total, SUM(total) as total_ingresos
                FROM servicios s $whereClause";
        $stats = $db->query($statsSql, $params)->fetch();
        
        // Crear PDF
        $pdf = new PdfExporter('Reporte de Servicios', 'L');
        $pdf->setHeader(null, 'REPORTE DE SERVICIOS', 'Período: ' . date('d/m/Y', strtotime($fecha_desde)) . ' - ' . date('d/m/Y', strtotime($fecha_hasta)));
        $pdf->setFooter();
        $pdf->addPage();
        
        // Resumen
        $pdf->setFont('helvetica', 'B', 12);
        $pdf->cell(0, 10, 'Resumen General', 0, 1);
        $pdf->setFont('helvetica', '', 10);
        $pdf->cell(60, 6, 'Total de Servicios:', 1, 0, 'L', true);
        $pdf->cell(50, 6, number_format($stats['total']), 1, 1);
        $pdf->cell(60, 6, 'Total Ingresos:', 1, 0, 'L', true);
        $pdf->cell(50, 6, '$' . number_format($stats['total_ingresos'], 2), 1, 1);
        $pdf->ln(10);
        
        // Tabla
        $pdf->setFont('helvetica', 'B', 11);
        $pdf->cell(0, 10, 'Detalle de Servicios', 0, 1);
        
        $headers = ['Fecha', 'Título', 'Tipo', 'Cliente', 'Técnico', 'Estado', 'Total'];
        $widths = [25, 50, 30, 40, 40, 25, 30];
        
        $data = [];
        foreach ($servicios as $s) {
            $data[] = [
                date('d/m/Y', strtotime($s['fecha_programada'])),
                substr($s['titulo'], 0, 35),
                ucfirst(str_replace('_', ' ', $s['tipo_servicio'])),
                substr($s['cliente'], 0, 30),
                substr($s['tecnico'], 0, 30),
                ucfirst(str_replace('_', ' ', $s['estado'])),
                '$' . number_format($s['total'], 2)
            ];
        }
        
        $pdf->createTable($headers, $data, $widths);
        
        $pdf->download('reporte_servicios_' . date('Ymd') . '.pdf');
    }
    
    /**
     * Exportar Reporte de Servicios a Excel
     */
    public function exportarServiciosExcel() {
        Auth::requirePermission('reportes', 'exportar');
        
        require_once __DIR__ . '/../utils/exports/ExcelExporter.php';
        
        $db = Database::getInstance();
        
        $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-t');
        
        $whereClause = 'WHERE s.fecha_programada >= :fecha_desde AND s.fecha_programada <= :fecha_hasta';
        $params = ['fecha_desde' => $fecha_desde, 'fecha_hasta' => $fecha_hasta];
        
        // Obtener servicios
        $sql = "SELECT s.fecha_programada, s.titulo, s.tipo_servicio, s.estado, s.descripcion,
                CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente,
                CONCAT(u.nombre, ' ', u.apellidos) as tecnico,
                s.costo_mano_obra, s.costo_materiales, s.otros_gastos, s.total
                FROM servicios s
                INNER JOIN clientes c ON s.cliente_id = c.id
                INNER JOIN usuarios u ON s.tecnico_id = u.id
                $whereClause
                ORDER BY s.fecha_programada DESC";
        
        $servicios = $db->query($sql, $params)->fetchAll();
        
        // Estadísticas
        $statsSql = "SELECT COUNT(*) as total, SUM(total) as total_ingresos
                FROM servicios s $whereClause";
        $stats = $db->query($statsSql, $params)->fetch();
        
        // Crear Excel
        $excel = new ExcelExporter('Servicios');
        $excel->setReportTitle('REPORTE DE SERVICIOS', 'Período: ' . date('d/m/Y', strtotime($fecha_desde)) . ' - ' . date('d/m/Y', strtotime($fecha_hasta)));
        
        // Resumen
        $excel->addSummary('Resumen General', [
            'Total de Servicios' => number_format($stats['total']),
            'Total Ingresos' => '$' . number_format($stats['total_ingresos'], 2)
        ]);
        
        // Tabla
        $headers = ['Fecha', 'Título', 'Tipo', 'Cliente', 'Técnico', 'Estado', 'Mano Obra', 'Materiales', 'Otros', 'Total'];
        
        $data = [];
        foreach ($servicios as $s) {
            $data[] = [
                $s['fecha_programada'],
                $s['titulo'],
                ucfirst(str_replace('_', ' ', $s['tipo_servicio'])),
                $s['cliente'],
                $s['tecnico'],
                ucfirst(str_replace('_', ' ', $s['estado'])),
                $s['costo_mano_obra'],
                $s['costo_materiales'],
                $s['otros_gastos'],
                $s['total']
            ];
        }
        
        $excel->createTable($headers, $data);
        
        $excel->download('reporte_servicios_' . date('Ymd') . '.xlsx');
    }
}
