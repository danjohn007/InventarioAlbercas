-- ============================================
-- Configuraciones de Email/SMTP
-- Fecha: 2026-02-18
-- ============================================

USE inventario_albercas;

-- Agregar configuraciones de email
INSERT INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('email_enabled', '0', 'booleano', 'Activar envío de correos electrónicos', 'notificaciones'),
('smtp_host', '', 'texto', 'Servidor SMTP (ej: smtp.gmail.com)', 'notificaciones'),
('smtp_port', '587', 'numero', 'Puerto SMTP (587 para TLS, 465 para SSL)', 'notificaciones'),
('smtp_encryption', 'tls', 'texto', 'Tipo de encriptación (tls, ssl, none)', 'notificaciones'),
('smtp_username', '', 'texto', 'Usuario/Email para autenticación SMTP', 'notificaciones'),
('smtp_password', '', 'texto', 'Contraseña para autenticación SMTP', 'notificaciones'),
('email_from_address', '', 'texto', 'Dirección de correo remitente', 'notificaciones'),
('email_from_name', 'Sistema Inventario Albercas', 'texto', 'Nombre del remitente', 'notificaciones')
ON DUPLICATE KEY UPDATE 
    clave = VALUES(clave);

-- Configuraciones de seguridad
INSERT INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('session_timeout', '7200', 'numero', 'Tiempo de sesión en segundos (2 horas por defecto)', 'sistema'),
('password_min_length', '8', 'numero', 'Longitud mínima de contraseña', 'sistema'),
('password_require_uppercase', '1', 'booleano', 'Requerir mayúsculas en contraseña', 'sistema'),
('password_require_lowercase', '1', 'booleano', 'Requerir minúsculas en contraseña', 'sistema'),
('password_require_numbers', '1', 'booleano', 'Requerir números en contraseña', 'sistema'),
('password_require_special', '0', 'booleano', 'Requerir caracteres especiales en contraseña', 'sistema'),
('login_max_attempts', '5', 'numero', 'Máximo de intentos de login fallidos', 'sistema'),
('login_lockout_time', '900', 'numero', 'Tiempo de bloqueo tras intentos fallidos (segundos)', 'sistema')
ON DUPLICATE KEY UPDATE 
    clave = VALUES(clave);

-- Configuraciones de backup automático
INSERT INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('backup_enabled', '0', 'booleano', 'Activar respaldos automáticos de base de datos', 'sistema'),
('backup_frequency', 'weekly', 'texto', 'Frecuencia de respaldo (daily, weekly, monthly)', 'sistema'),
('backup_retention_days', '30', 'numero', 'Días para retener respaldos antiguos', 'sistema'),
('backup_path', '/backups', 'texto', 'Ruta para almacenar respaldos', 'sistema')
ON DUPLICATE KEY UPDATE 
    clave = VALUES(clave);

COMMIT;

-- Verificar que se agregaron las configuraciones
SELECT COUNT(*) as total_configuraciones FROM configuraciones;
SELECT categoria, COUNT(*) as cantidad FROM configuraciones GROUP BY categoria;
