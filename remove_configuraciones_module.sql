-- ============================================
-- Migración: Eliminar módulo Configuraciones Globales
-- ============================================

-- Eliminar tabla de configuraciones
DROP TABLE IF EXISTS configuraciones;

-- Eliminar permiso de configuraciones del rol Administrador
UPDATE roles
SET permisos = JSON_REMOVE(permisos, '$.configuraciones')
WHERE nombre = 'Administrador'
  AND JSON_CONTAINS_PATH(permisos, 'one', '$.configuraciones');
