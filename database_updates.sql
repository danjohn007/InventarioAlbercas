-- ============================================
-- Actualizaciones para el Sistema de Inventario
-- Nuevas tablas: ingresos, categorias_ingreso, configuraciones
-- ============================================

-- ============================================
-- TABLA: categorias_ingreso
-- ============================================
CREATE TABLE IF NOT EXISTS categorias_ingreso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: ingresos
-- ============================================
CREATE TABLE IF NOT EXISTS ingresos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    concepto VARCHAR(200) NOT NULL,
    descripcion TEXT,
    monto DECIMAL(10,2) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque', 'otro') NOT NULL,
    servicio_id INT,
    cliente_id INT,
    comprobante VARCHAR(255),
    observaciones TEXT,
    usuario_registro_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_ingreso(id),
    FOREIGN KEY (servicio_id) REFERENCES servicios(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_fecha (fecha_ingreso),
    INDEX idx_servicio (servicio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: configuraciones
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
-- DATOS INICIALES: Categorías de Ingreso
-- ============================================
INSERT INTO categorias_ingreso (nombre, descripcion) VALUES
('Servicios', 'Ingresos por servicios realizados'),
('Ventas', 'Ingresos por venta de productos'),
('Mantenimiento', 'Ingresos por contratos de mantenimiento'),
('Instalaciones', 'Ingresos por instalaciones'),
('Reparaciones', 'Ingresos por reparaciones'),
('Otros', 'Otros ingresos diversos')
ON DUPLICATE KEY UPDATE nombre=nombre;

-- ============================================
-- DATOS INICIALES: Configuraciones del Sistema
-- ============================================
INSERT INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('sitio_nombre', 'Sistema Albercas', 'texto', 'Nombre del sitio', 'general'),
('sitio_logo', '', 'archivo', 'Ruta del logotipo del sitio', 'apariencia'),
('color_primario', '#667eea', 'texto', 'Color primario del sistema', 'apariencia'),
('color_secundario', '#764ba2', 'texto', 'Color secundario del sistema', 'apariencia'),
('moneda', 'MXN', 'texto', 'Moneda del sistema', 'general'),
('zona_horaria', 'America/Mexico_City', 'texto', 'Zona horaria del sistema', 'sistema'),
('items_por_pagina', '20', 'numero', 'Número de items por página', 'sistema'),
('formato_fecha', 'd/m/Y', 'texto', 'Formato de fecha para mostrar', 'sistema'),
('notificaciones_email', '1', 'booleano', 'Activar notificaciones por email', 'notificaciones'),
('stock_bajo_alerta', '1', 'booleano', 'Activar alertas de stock bajo', 'notificaciones')
ON DUPLICATE KEY UPDATE clave=clave;

-- ============================================
-- ACTUALIZAR PERMISOS DE ROLES
-- ============================================
UPDATE roles 
SET permisos = JSON_SET(
    permisos,
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.configuraciones', JSON_ARRAY('leer', 'actualizar')
)
WHERE nombre = 'Administrador';

UPDATE roles 
SET permisos = JSON_SET(
    permisos,
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar')
)
WHERE nombre = 'Supervisor';

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista de ingresos con información relacionada
CREATE OR REPLACE VIEW vista_ingresos_completos AS
SELECT 
    i.id,
    i.concepto,
    i.monto,
    i.fecha_ingreso,
    i.forma_pago,
    ci.nombre as categoria,
    s.titulo as servicio,
    CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente,
    CONCAT(u.nombre, ' ', u.apellidos) as usuario_registro
FROM ingresos i
INNER JOIN categorias_ingreso ci ON i.categoria_id = ci.id
LEFT JOIN servicios s ON i.servicio_id = s.id
LEFT JOIN clientes c ON i.cliente_id = c.id
INNER JOIN usuarios u ON i.usuario_registro_id = u.id;
