-- ============================================
-- Migración: Módulo Configuraciones Globales
-- Fecha: 2026-02-21
-- Descripción: Expande el módulo de Configuraciones con
--   nuevas secciones: Correo SMTP, Contacto & Horarios,
--   PayPal, QR API, Dispositivos IoT (Shelly Cloud + HikVision)
--   y Chatbot de WhatsApp.
-- ============================================

USE fix360_inventario;

-- ============================================
-- 1. Expandir el ENUM de categorías
-- ============================================
ALTER TABLE configuraciones
    MODIFY COLUMN categoria
        ENUM('general','apariencia','sistema','notificaciones','contacto','integraciones')
        DEFAULT 'general';

-- ============================================
-- 2. Nuevas configuraciones: Correo SMTP
-- ============================================
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('email_enabled',      '0',                               'booleano', 'Activar envío de correos electrónicos',     'notificaciones'),
('smtp_host',          '',                                'texto',    'Servidor SMTP (ej: smtp.gmail.com)',         'notificaciones'),
('smtp_port',          '587',                             'numero',   'Puerto SMTP (587 para TLS, 465 para SSL)',   'notificaciones'),
('smtp_encryption',    'tls',                             'texto',    'Tipo de encriptación SMTP (tls/ssl/none)',   'notificaciones'),
('smtp_username',      '',                                'texto',    'Usuario/Email para autenticación SMTP',     'notificaciones'),
('smtp_password',      '',                                'texto',    'Contraseña para autenticación SMTP',        'notificaciones'),
('email_from_address', '',                                'texto',    'Dirección de correo remitente',             'notificaciones'),
('email_from_name',    'Sistema Inventario Albercas',     'texto',    'Nombre del remitente',                      'notificaciones');

-- ============================================
-- 3. Nuevas configuraciones: Contacto & Horarios
-- ============================================
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('telefono_principal',           '',                                    'texto',    'Teléfono de contacto principal',                       'contacto'),
('telefono_whatsapp',            '',                                    'texto',    'Número de WhatsApp para contacto con clientes',        'contacto'),
('telefono_emergencias',         '',                                    'texto',    'Teléfono de emergencias 24h',                          'contacto'),
('horario_lunes_viernes',        '09:00 - 18:00',                       'texto',    'Horario de atención lunes a viernes',                  'contacto'),
('horario_sabado',               '09:00 - 14:00',                       'texto',    'Horario de atención sábado',                           'contacto'),
('horario_domingo',              'Cerrado',                             'texto',    'Horario de atención domingo',                          'contacto'),
('direccion_contacto',           '',                                    'texto',    'Dirección física de la empresa',                       'contacto');

-- ============================================
-- 4. Nuevas configuraciones: PayPal
-- ============================================
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('paypal_mode',                  'sandbox',                             'texto',    'Modo de PayPal: sandbox o live',                       'integraciones'),
('paypal_email',                 '',                                    'texto',    'Email principal de la cuenta PayPal',                  'integraciones'),
('paypal_client_id',             '',                                    'texto',    'Client ID de la API de PayPal',                        'integraciones'),
('paypal_secret',                '',                                    'texto',    'Clave secreta (Secret) de la API de PayPal',           'integraciones');

-- ============================================
-- 5. Nuevas configuraciones: API QR Masivo
-- ============================================
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('qr_api_provider',              '',                                    'texto',    'Proveedor de API para generación masiva de QR',        'integraciones'),
('qr_api_key',                   '',                                    'texto',    'Clave API para generación de QR masivos',              'integraciones'),
('qr_api_url',                   '',                                    'texto',    'URL del endpoint de la API QR',                        'integraciones');

-- ============================================
-- 6. Nuevas configuraciones: IoT – Shelly Cloud
-- ============================================
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('shelly_api_url',               'https://shelly-12-eu.shelly.cloud',  'texto',    'URL base de la API de Shelly Cloud',                   'integraciones'),
('shelly_account_id',            '',                                    'texto',    'ID de cuenta en Shelly Cloud',                         'integraciones'),
('shelly_api_key',               '',                                    'texto',    'API Key de Shelly Cloud',                              'integraciones');

-- ============================================
-- 7. Nuevas configuraciones: IoT – HikVision
-- ============================================
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('hikvision_device_ip',          '',                                    'texto',    'Dirección IP del dispositivo HikVision',               'integraciones'),
('hikvision_username',           'admin',                               'texto',    'Usuario de acceso a HikVision',                        'integraciones'),
('hikvision_password',           '',                                    'texto',    'Contraseña de acceso a HikVision',                     'integraciones');

-- ============================================
-- 8. Nuevas configuraciones: Chatbot WhatsApp
-- ============================================
INSERT IGNORE INTO configuraciones (clave, valor, tipo, descripcion, categoria) VALUES
('whatsapp_provider',            'meta',                                'texto',    'Proveedor del chatbot de WhatsApp (meta, twilio, etc)', 'integraciones'),
('whatsapp_phone_number',        '',                                    'texto',    'Número de teléfono de WhatsApp Business',              'integraciones'),
('whatsapp_api_key',             '',                                    'texto',    'API Key adicional para el chatbot de WhatsApp',        'integraciones'),
('whatsapp_access_token',        '',                                    'texto',    'Token de acceso de la WhatsApp Business API',          'integraciones'),
('whatsapp_webhook_verify_token','',                                    'texto',    'Token de verificación del webhook de WhatsApp',        'integraciones'),
('whatsapp_phone_number_id',     '',                                    'texto',    'ID del número de teléfono en Meta Business API',       'integraciones');

-- ============================================
-- 9. Verificación de resultados
-- ============================================
SELECT categoria, COUNT(*) AS total
FROM configuraciones
GROUP BY categoria
ORDER BY categoria;

SELECT '✓ Migración de Configuraciones Globales completada exitosamente' AS estado;
