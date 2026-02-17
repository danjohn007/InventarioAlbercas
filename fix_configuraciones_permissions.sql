-- ============================================
-- Fix para Error 403 en Módulo de Configuraciones
-- Fecha: 2026-02-17
-- ============================================
-- Este script agrega los permisos necesarios para los módulos
-- "configuraciones" e "ingresos" que fueron añadidos recientemente
-- ============================================

-- Actualizar permisos del rol Administrador
-- Agregar permisos completos para ingresos y configuraciones
UPDATE roles 
SET permisos = JSON_SET(
    permisos,
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.configuraciones', JSON_ARRAY('leer', 'actualizar')
)
WHERE nombre = 'Administrador';

-- Actualizar permisos del rol Supervisor
-- Agregar permisos limitados para ingresos
UPDATE roles 
SET permisos = JSON_SET(
    permisos,
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar')
)
WHERE nombre = 'Supervisor';

-- Verificar que las actualizaciones se aplicaron correctamente
SELECT 
    id,
    nombre,
    permisos
FROM roles
ORDER BY id;

-- ============================================
-- Verificación de Permisos Actualizados
-- ============================================
-- Después de ejecutar este script, verifica que:
-- 1. Administrador tiene permisos de "configuraciones" y "ingresos"
-- 2. Supervisor tiene permisos de "ingresos"
-- 3. Los usuarios pueden acceder al módulo de configuraciones sin error 403
-- ============================================
