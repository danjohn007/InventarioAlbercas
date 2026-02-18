-- ============================================
-- FIX360 - Sistema de Inventario Albercas
-- Actualización: Reportes y Configuraciones
-- Fecha: 2026-02-17
-- ============================================
-- Este script incluye todas las correcciones necesarias para:
-- 1. Solucionar el ERROR 403 en el módulo de configuraciones
-- 2. Habilitar exportación Excel/PDF en reportes
-- 3. Completar el módulo de Reporte de Gastos
-- ============================================

USE inventario_albercas;

-- ============================================
-- TABLA: configuraciones (Si no existe)
-- ============================================
CREATE TABLE IF NOT EXISTS configuraciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo ENUM('texto', 'numero', 'booleano', 'json', 'archivo') DEFAULT 'texto',
    descripcion TEXT,
    categoria ENUM('general', 'apariencia', 'sistema', 'notificaciones') DEFAULT 'general',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clave (clave),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DATOS INICIALES: Configuraciones del Sistema
-- ============================================
INSERT INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('sitio_nombre', 'Sistema de Inventario Albercas', 'texto', 'Nombre del sitio web', 'general'),
('sitio_descripcion', 'Sistema de gestión integral para albercas', 'texto', 'Descripción del sitio', 'general'),
('sitio_logo', '', 'archivo', 'Ruta del logotipo del sitio (uploads/logo.png)', 'apariencia'),
('color_primario', '#667eea', 'texto', 'Color primario del sistema (gradiente inicio)', 'apariencia'),
('color_secundario', '#764ba2', 'texto', 'Color secundario del sistema (gradiente fin)', 'apariencia'),
('moneda', 'MXN', 'texto', 'Moneda del sistema (MXN, USD, etc)', 'general'),
('simbolo_moneda', '$', 'texto', 'Símbolo de la moneda', 'general'),
('zona_horaria', 'America/Mexico_City', 'texto', 'Zona horaria del sistema', 'sistema'),
('items_por_pagina', '20', 'numero', 'Número de items por página en listados', 'sistema'),
('formato_fecha', 'd/m/Y', 'texto', 'Formato de fecha para mostrar (d/m/Y, Y-m-d, etc)', 'sistema'),
('formato_hora', 'H:i', 'texto', 'Formato de hora para mostrar', 'sistema'),
('stock_minimo_alerta', '5', 'numero', 'Cantidad mínima de stock para alertar', 'sistema'),
('notificaciones_email', '1', 'booleano', 'Activar notificaciones por email', 'notificaciones'),
('stock_bajo_alerta', '1', 'booleano', 'Activar alertas de stock bajo', 'notificaciones'),
('email_admin', 'admin@albercas.com', 'texto', 'Email del administrador del sistema', 'notificaciones'),
('backup_automatico', '1', 'booleano', 'Activar respaldos automáticos', 'sistema'),
('dias_backup', '7', 'numero', 'Días entre respaldos automáticos', 'sistema')
ON DUPLICATE KEY UPDATE 
    valor = VALUES(valor),
    tipo = VALUES(tipo),
    descripcion = VALUES(descripcion),
    categoria = VALUES(categoria);

-- ============================================
-- ACTUALIZAR PERMISOS DE ROLES
-- Solución al ERROR 403 - FORBIDDEN
-- ============================================

-- Rol: Administrador - Permisos completos
UPDATE roles 
SET permisos = JSON_SET(
    COALESCE(permisos, '{}'),
    '$.usuarios', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.inventario', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.gastos', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.servicios', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.clientes', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.configuraciones', JSON_ARRAY('leer', 'actualizar'),
    '$.reportes', JSON_ARRAY('leer', 'exportar')
)
WHERE nombre = 'Administrador';

-- Rol: Supervisor - Permisos de gestión
UPDATE roles 
SET permisos = JSON_SET(
    COALESCE(permisos, '{}'),
    '$.usuarios', JSON_ARRAY('leer'),
    '$.inventario', JSON_ARRAY('crear', 'leer', 'actualizar'),
    '$.gastos', JSON_ARRAY('crear', 'leer', 'actualizar'),
    '$.servicios', JSON_ARRAY('crear', 'leer', 'actualizar'),
    '$.clientes', JSON_ARRAY('crear', 'leer', 'actualizar'),
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar'),
    '$.reportes', JSON_ARRAY('leer', 'exportar')
)
WHERE nombre = 'Supervisor';

-- Rol: Técnico - Permisos de consulta y registro básico
UPDATE roles 
SET permisos = JSON_SET(
    COALESCE(permisos, '{}'),
    '$.inventario', JSON_ARRAY('leer'),
    '$.servicios', JSON_ARRAY('leer', 'actualizar'),
    '$.clientes', JSON_ARRAY('leer'),
    '$.gastos', JSON_ARRAY('crear', 'leer'),
    '$.reportes', JSON_ARRAY('leer')
)
WHERE nombre = 'Tecnico';

-- ============================================
-- TABLA: categorias_gasto (Si no existe)
-- Necesaria para el reporte de gastos
-- ============================================
CREATE TABLE IF NOT EXISTS categorias_gasto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos iniciales de categorías de gastos
INSERT INTO categorias_gasto (nombre, descripcion) VALUES
('Químicos', 'Compra de químicos para tratamiento de agua'),
('Materiales', 'Materiales para instalación y reparación'),
('Herramientas', 'Herramientas y equipamiento'),
('Transporte', 'Gastos de transporte y combustible'),
('Administrativos', 'Gastos administrativos y de oficina'),
('Servicios', 'Servicios externos contratados'),
('Mantenimiento', 'Mantenimiento de equipos y vehículos'),
('Otros', 'Otros gastos diversos')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- ============================================
-- TABLA: gastos (Verificar estructura)
-- ============================================
CREATE TABLE IF NOT EXISTS gastos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    concepto VARCHAR(200) NOT NULL,
    descripcion TEXT,
    monto DECIMAL(10,2) NOT NULL,
    fecha_gasto DATE NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque', 'otro') NOT NULL,
    comprobante VARCHAR(255),
    proveedor_id INT,
    usuario_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_gasto(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_fecha (fecha_gasto),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- VERIFICACIÓN DE ÍNDICES PARA REPORTES
-- ============================================

-- Índices para mejorar performance de reportes de gastos
ALTER TABLE gastos 
ADD INDEX IF NOT EXISTS idx_fecha_monto (fecha_gasto, monto),
ADD INDEX IF NOT EXISTS idx_forma_pago (forma_pago);

-- Índices para mejorar performance de reportes de inventario
ALTER TABLE productos 
ADD INDEX IF NOT EXISTS idx_stock_categoria (stock_actual, categoria_id),
ADD INDEX IF NOT EXISTS idx_precio (precio_venta);

-- Índices para mejorar performance de reportes de servicios
ALTER TABLE servicios 
ADD INDEX IF NOT EXISTS idx_fecha_estado (fecha_programada, estado),
ADD INDEX IF NOT EXISTS idx_tecnico (tecnico_id);

-- ============================================
-- VISTAS PARA REPORTES
-- ============================================

-- Vista: Resumen de gastos mensuales
CREATE OR REPLACE VIEW vista_gastos_mensuales AS
SELECT 
    DATE_FORMAT(g.fecha_gasto, '%Y-%m') as mes,
    COUNT(*) as cantidad_gastos,
    SUM(g.monto) as total_monto,
    AVG(g.monto) as promedio_monto,
    cg.nombre as categoria
FROM gastos g
INNER JOIN categorias_gasto cg ON g.categoria_id = cg.id
GROUP BY mes, cg.id, cg.nombre
ORDER BY mes DESC, total_monto DESC;

-- Vista: Gastos por categoría y forma de pago
CREATE OR REPLACE VIEW vista_gastos_categoria_pago AS
SELECT 
    cg.nombre as categoria,
    g.forma_pago,
    COUNT(*) as cantidad,
    SUM(g.monto) as total_monto,
    AVG(g.monto) as promedio
FROM gastos g
INNER JOIN categorias_gasto cg ON g.categoria_id = cg.id
GROUP BY cg.id, cg.nombre, g.forma_pago
ORDER BY total_monto DESC;

-- Vista: Productos con stock bajo para alertas
CREATE OR REPLACE VIEW vista_productos_stock_bajo AS
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.stock_actual,
    p.stock_minimo,
    (p.stock_minimo - p.stock_actual) as faltante,
    c.nombre as categoria,
    p.precio_venta,
    (p.stock_minimo - p.stock_actual) * p.precio_venta as valor_reponer
FROM productos p
INNER JOIN categorias_producto c ON p.categoria_id = c.id
WHERE p.stock_actual <= p.stock_minimo
ORDER BY faltante DESC;

-- ============================================
-- PROCEDIMIENTOS ALMACENADOS PARA REPORTES
-- ============================================

-- Procedimiento: Obtener estadísticas de gastos por período
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS sp_estadisticas_gastos(
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT 
        COUNT(*) as total_gastos,
        SUM(monto) as total_monto,
        AVG(monto) as promedio_monto,
        MIN(monto) as monto_minimo,
        MAX(monto) as monto_maximo,
        COUNT(DISTINCT categoria_id) as categorias_usadas,
        COUNT(DISTINCT usuario_id) as usuarios_registraron
    FROM gastos
    WHERE fecha_gasto BETWEEN p_fecha_inicio AND p_fecha_fin;
END$$

DELIMITER ;

-- ============================================
-- VERIFICACIÓN DE RESULTADOS
-- ============================================

-- Verificar que los roles tienen los permisos correctos
SELECT 
    id,
    nombre,
    JSON_PRETTY(permisos) as permisos_formateados
FROM roles
ORDER BY id;

-- Verificar que la tabla de configuraciones tiene datos
SELECT COUNT(*) as total_configuraciones FROM configuraciones;

-- Verificar que las categorías de gastos existen
SELECT COUNT(*) as total_categorias_gasto FROM categorias_gasto;

-- Mostrar resumen de configuraciones por categoría
SELECT 
    categoria,
    COUNT(*) as cantidad_configs
FROM configuraciones
GROUP BY categoria;

-- ============================================
-- NOTAS DE IMPLEMENTACIÓN
-- ============================================
-- 
-- 1. MÓDULO DE CONFIGURACIONES:
--    - Nombre del sitio y Logotipo: ✓ Implementado (tabla configuraciones)
--    - Cambiar estilos principales de color: ✓ Implementado (color_primario, color_secundario)
--    - Configuraciones globales recomendadas: ✓ Implementado (múltiples configuraciones)
--    - El error 403 se soluciona actualizando los permisos del rol
--
-- 2. MÓDULOS DE EXCEL Y PDF:
--    - Clases ExcelExporter y PdfExporter: ✓ Ya existen en /utils/exports/
--    - Rutas de exportación: ✓ Ya definidas en index.php
--    - Botones en UI: ✓ Ya implementados en vistas de reportes
--
-- 3. MÓDULO DE REPORTE DE GASTOS:
--    - Vista de reporte: ✓ Implementada en /views/reportes/gastos.php
--    - Análisis por categoría: ✓ Implementado
--    - Análisis por fecha: ✓ Implementado
--    - Análisis por forma de pago: ✓ Implementado
--    - Tendencias mensuales: ✓ Implementado con gráficos
--    - Exportación PDF/Excel: ✓ Implementado
--
-- 4. SEGURIDAD:
--    - Todas las rutas protegidas con Auth::requirePermission()
--    - Auditoría de cambios en configuraciones
--    - Validación de permisos por rol
--
-- ============================================

-- Mensaje de éxito
SELECT '✓ Actualización completada exitosamente' as estado,
       'Los módulos de reportes y configuraciones están listos para usar' as mensaje;
