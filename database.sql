-- ============================================
-- Sistema de Inventario y Gastos para Albercas
-- Base de Datos MySQL 5.7
-- ============================================

-- Crear base de datos
-- CREATE DATABASE IF NOT EXISTS inventario_albercas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE inventario_albercas;

-- ============================================
-- TABLA: roles
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    permisos JSON,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: auditoria
-- ============================================
CREATE TABLE IF NOT EXISTS auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    tabla VARCHAR(50),
    registro_id INT,
    detalles TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: proveedores
-- ============================================
CREATE TABLE IF NOT EXISTS proveedores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    rfc VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: categorias_producto
-- ============================================
CREATE TABLE IF NOT EXISTS categorias_producto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: productos
-- ============================================
CREATE TABLE IF NOT EXISTS productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria_id INT NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL,
    costo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_venta DECIMAL(10,2) DEFAULT 0.00,
    stock_actual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_minimo DECIMAL(10,2) DEFAULT 0.00,
    proveedor_id INT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_producto(id),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
    INDEX idx_codigo (codigo),
    INDEX idx_nombre (nombre),
    INDEX idx_categoria (categoria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: inventario_movimientos
-- ============================================
CREATE TABLE IF NOT EXISTS inventario_movimientos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    tipo_movimiento ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    costo_unitario DECIMAL(10,2),
    costo_total DECIMAL(10,2),
    stock_anterior DECIMAL(10,2) NOT NULL,
    stock_nuevo DECIMAL(10,2) NOT NULL,
    motivo VARCHAR(100),
    observaciones TEXT,
    usuario_id INT NOT NULL,
    servicio_id INT,
    fecha_movimiento DATETIME NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_producto (producto_id),
    INDEX idx_fecha (fecha_movimiento),
    INDEX idx_tipo (tipo_movimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: clientes
-- ============================================
CREATE TABLE IF NOT EXISTS clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    ciudad VARCHAR(50),
    estado VARCHAR(50),
    codigo_postal VARCHAR(10),
    rfc VARCHAR(20),
    notas TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_telefono (telefono)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: servicios
-- ============================================
CREATE TABLE IF NOT EXISTS servicios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    tipo_servicio ENUM('mantenimiento', 'reparacion', 'instalacion', 'otro') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    direccion_servicio TEXT,
    fecha_programada DATE,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    tecnico_id INT NOT NULL,
    estado ENUM('pendiente', 'en_proceso', 'completado', 'cancelado') DEFAULT 'pendiente',
    costo_mano_obra DECIMAL(10,2) DEFAULT 0.00,
    costo_materiales DECIMAL(10,2) DEFAULT 0.00,
    otros_gastos DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) DEFAULT 0.00,
    observaciones TEXT,
    usuario_registro_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id),
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_tecnico (tecnico_id),
    INDEX idx_fecha (fecha_programada),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: servicio_materiales
-- ============================================
CREATE TABLE IF NOT EXISTS servicio_materiales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    servicio_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    costo_unitario DECIMAL(10,2) NOT NULL,
    costo_total DECIMAL(10,2) NOT NULL,
    fecha_asignacion DATETIME NOT NULL,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    INDEX idx_servicio (servicio_id),
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: categorias_gasto
-- ============================================
CREATE TABLE IF NOT EXISTS categorias_gasto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLA: gastos
-- ============================================
CREATE TABLE IF NOT EXISTS gastos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    concepto VARCHAR(200) NOT NULL,
    descripcion TEXT,
    monto DECIMAL(10,2) NOT NULL,
    fecha_gasto DATE NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque') NOT NULL,
    servicio_id INT,
    cliente_id INT,
    proveedor_id INT,
    comprobante VARCHAR(255),
    observaciones TEXT,
    usuario_registro_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_gasto(id),
    FOREIGN KEY (servicio_id) REFERENCES servicios(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_fecha (fecha_gasto),
    INDEX idx_servicio (servicio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DATOS DE EJEMPLO
-- ============================================

-- Insertar roles
INSERT IGNORE INTO roles (nombre, descripcion, permisos) VALUES
('Administrador', 'Control total del sistema', '{"usuarios": ["crear", "leer", "actualizar", "eliminar"], "inventario": ["crear", "leer", "actualizar", "eliminar"], "gastos": ["crear", "leer", "actualizar", "eliminar"], "servicios": ["crear", "leer", "actualizar", "eliminar"], "clientes": ["crear", "leer", "actualizar", "eliminar"], "reportes": ["leer", "exportar"], "ingresos": ["crear", "leer", "actualizar", "eliminar"], "configuraciones": ["leer", "actualizar"]}'),
('Supervisor', 'Gestión de inventario, gastos y servicios', '{"usuarios": ["leer"], "inventario": ["crear", "leer", "actualizar"], "gastos": ["crear", "leer", "actualizar"], "servicios": ["crear", "leer", "actualizar"], "clientes": ["crear", "leer", "actualizar"], "reportes": ["leer"], "ingresos": ["crear", "leer", "actualizar"]}'),
('Tecnico', 'Consulta y registro de consumo', '{"inventario": ["leer"], "servicios": ["leer", "actualizar"], "clientes": ["leer"], "gastos": ["crear", "leer"]}');

-- Insertar usuarios (password: admin123, supervisor123, tecnico123)
INSERT IGNORE INTO usuarios (nombre, apellidos, email, telefono, usuario, password, rol_id) VALUES
('Juan', 'Pérez García', 'admin@albercas.com', '5551234567', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('María', 'González López', 'supervisor@albercas.com', '5557654321', 'supervisor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('Carlos', 'Ramírez Torres', 'tecnico@albercas.com', '5559876543', 'tecnico', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);

-- Insertar categorías de producto
INSERT IGNORE INTO categorias_producto (nombre, descripcion) VALUES
('Químicos', 'Productos químicos para tratamiento de agua'),
('Herramientas', 'Herramientas para mantenimiento'),
('Refacciones', 'Repuestos y refacciones'),
('Equipos', 'Equipos y maquinaria');

-- Insertar proveedores
INSERT IGNORE INTO proveedores (nombre, contacto, telefono, email, direccion, rfc) VALUES
('Químicos del Norte', 'Pedro Sánchez', '5551122334', 'ventas@quimicosnorte.com', 'Av. Industrial 123', 'QDN123456ABC'),
('Herramientas Pro', 'Ana Martínez', '5552233445', 'contacto@herramientaspro.com', 'Calle Comercio 456', 'HPR789012DEF'),
('Equipos de Piscina SA', 'Roberto López', '5553344556', 'info@equipospiscina.com', 'Blvd. Central 789', 'EPS345678GHI');

-- Insertar productos
INSERT IGNORE INTO productos (codigo, nombre, descripcion, categoria_id, unidad_medida, costo_unitario, precio_venta, stock_actual, stock_minimo, proveedor_id) VALUES
('CLORO-001', 'Cloro granulado 10kg', 'Cloro granulado para desinfección', 1, 'kg', 350.00, 500.00, 50.00, 10.00, 1),
('PH-001', 'Reductor de PH 5kg', 'Reductor de pH para agua', 1, 'kg', 180.00, 280.00, 30.00, 5.00, 1),
('ALGICIDA-001', 'Algicida líquido 4L', 'Prevención de algas', 1, 'litro', 220.00, 350.00, 25.00, 5.00, 1),
('ASPIRADORA-001', 'Aspiradora manual', 'Aspiradora manual para piscina', 2, 'pieza', 450.00, 750.00, 8.00, 2.00, 2),
('CEPILLO-001', 'Cepillo de cerdas', 'Cepillo para limpieza de paredes', 2, 'pieza', 150.00, 250.00, 15.00, 3.00, 2),
('FILTRO-001', 'Filtro de arena', 'Filtro de arena para piscina', 3, 'pieza', 2500.00, 3800.00, 5.00, 1.00, 3),
('BOMBA-001', 'Bomba 1.5HP', 'Bomba para circulación de agua', 4, 'pieza', 3200.00, 5000.00, 3.00, 1.00, 3),
('MANGUERA-001', 'Manguera flexible 15m', 'Manguera para aspiradora', 3, 'pieza', 280.00, 450.00, 10.00, 2.00, 2);

-- Insertar categorías de gasto
INSERT IGNORE INTO categorias_gasto (nombre, descripcion) VALUES
('Materiales', 'Compra de materiales e insumos'),
('Gasolina', 'Combustible para vehículos'),
('Viáticos', 'Gastos de alimentación y viaje'),
('Mano de Obra', 'Pago de mano de obra externa'),
('Servicios Externos', 'Servicios contratados'),
('Mantenimiento Equipo', 'Mantenimiento de herramientas y equipos');

-- Insertar clientes
INSERT IGNORE INTO clientes (nombre, apellidos, telefono, email, direccion, ciudad, estado, codigo_postal) VALUES
('Roberto', 'Hernández Silva', '5551111111', 'roberto.hernandez@email.com', 'Calle Privada 123, Col. Jardines', 'Ciudad de México', 'CDMX', '01234'),
('Laura', 'Martínez Ruiz', '5552222222', 'laura.martinez@email.com', 'Av. Principal 456, Col. Centro', 'Guadalajara', 'Jalisco', '44100'),
('Fernando', 'López García', '5553333333', 'fernando.lopez@email.com', 'Calle Norte 789, Col. Las Palmas', 'Monterrey', 'Nuevo León', '64000'),
('Hotel Paradise', '', '5554444444', 'info@hotelparadise.com', 'Blvd. Turístico 100, Zona Hotelera', 'Cancún', 'Quintana Roo', '77500');

-- Insertar servicios de ejemplo
INSERT IGNORE INTO servicios (cliente_id, tipo_servicio, titulo, descripcion, direccion_servicio, fecha_programada, fecha_inicio, tecnico_id, estado, costo_mano_obra, usuario_registro_id) VALUES
(1, 'mantenimiento', 'Mantenimiento mensual', 'Limpieza y balanceo químico', 'Calle Privada 123, Col. Jardines', '2024-01-15', '2024-01-15 10:00:00', 3, 'completado', 800.00, 2),
(2, 'reparacion', 'Reparación de bomba', 'Cambio de rodamientos y sello mecánico', 'Av. Principal 456, Col. Centro', '2024-01-20', '2024-01-20 09:00:00', 3, 'completado', 1200.00, 2),
(3, 'mantenimiento', 'Mantenimiento preventivo', 'Revisión general y limpieza', 'Calle Norte 789, Col. Las Palmas', '2024-02-05', NULL, 3, 'pendiente', 850.00, 2);

-- Insertar movimientos de inventario de ejemplo
INSERT IGNORE INTO inventario_movimientos (producto_id, tipo_movimiento, cantidad, costo_unitario, costo_total, stock_anterior, stock_nuevo, motivo, usuario_id, fecha_movimiento) VALUES
(1, 'entrada', 50.00, 350.00, 17500.00, 0.00, 50.00, 'Compra inicial', 2, '2024-01-01 10:00:00'),
(2, 'entrada', 30.00, 180.00, 5400.00, 0.00, 30.00, 'Compra inicial', 2, '2024-01-01 10:00:00'),
(3, 'entrada', 25.00, 220.00, 5500.00, 0.00, 25.00, 'Compra inicial', 2, '2024-01-01 10:00:00'),
(1, 'salida', 5.00, 350.00, 1750.00, 50.00, 45.00, 'Uso en servicio', 3, '2024-01-15 11:00:00'),
(2, 'salida', 2.00, 180.00, 360.00, 30.00, 28.00, 'Uso en servicio', 3, '2024-01-15 11:00:00');

-- Insertar gastos de ejemplo
INSERT IGNORE INTO gastos (categoria_id, concepto, descripcion, monto, fecha_gasto, forma_pago, servicio_id, usuario_registro_id) VALUES
(2, 'Gasolina camioneta', 'Llenado de tanque', 650.00, '2024-01-10', 'tarjeta', NULL, 2),
(3, 'Comida técnico', 'Viáticos por servicio', 200.00, '2024-01-15', 'efectivo', 1, 3),
(1, 'Compra de químicos', 'Reposición de inventario', 17500.00, '2024-01-01', 'transferencia', NULL, 2),
(3, 'Viáticos', 'Comida durante reparación', 180.00, '2024-01-20', 'efectivo', 2, 3);

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

-- Datos iniciales de configuraciones
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
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
('dias_backup', '7', 'numero', 'Días entre respaldos automáticos', 'sistema');

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista de stock bajo
CREATE OR REPLACE VIEW vista_productos_stock_bajo AS
SELECT 
    p.id,
    p.codigo,
    p.nombre,
    p.stock_actual,
    p.stock_minimo,
    c.nombre as categoria,
    pr.nombre as proveedor
FROM productos p
INNER JOIN categorias_producto c ON p.categoria_id = c.id
LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
WHERE p.stock_actual <= p.stock_minimo AND p.activo = 1;

-- Vista de servicios con información del cliente
CREATE OR REPLACE VIEW vista_servicios_completos AS
SELECT 
    s.id,
    s.tipo_servicio,
    s.titulo,
    s.fecha_programada,
    s.estado,
    s.total,
    CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente,
    c.telefono as telefono_cliente,
    CONCAT(u.nombre, ' ', u.apellidos) as tecnico
FROM servicios s
INNER JOIN clientes c ON s.cliente_id = c.id
INNER JOIN usuarios u ON s.tecnico_id = u.id;

-- Vista de gastos con información relacionada
CREATE OR REPLACE VIEW vista_gastos_completos AS
SELECT 
    g.id,
    g.concepto,
    g.monto,
    g.fecha_gasto,
    g.forma_pago,
    cg.nombre as categoria,
    s.titulo as servicio,
    CONCAT(c.nombre, ' ', IFNULL(c.apellidos, '')) as cliente,
    CONCAT(u.nombre, ' ', u.apellidos) as usuario_registro
FROM gastos g
INNER JOIN categorias_gasto cg ON g.categoria_id = cg.id
LEFT JOIN servicios s ON g.servicio_id = s.id
LEFT JOIN clientes c ON g.cliente_id = c.id
INNER JOIN usuarios u ON g.usuario_registro_id = u.id;
