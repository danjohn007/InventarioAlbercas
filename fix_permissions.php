<?php
/**
 * Script para corregir permisos de configuraciones e ingresos
 * Ejecutar: php fix_permissions.php
 * 
 * Este script debe ejecutarse desde el servidor web donde está
 * instalada la aplicación, con acceso a la base de datos.
 */

// Cargar configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "==============================================\n";
echo "Fix de Permisos - Configuraciones e Ingresos\n";
echo "==============================================\n\n";

try {
    echo "Intentando conectar a la base de datos...\n";
    $db = Database::getInstance();
    echo "✓ Conexión exitosa\n\n";
    
    echo "1. Obteniendo roles actuales...\n";
    $roles = $db->query("SELECT id, nombre, permisos FROM roles")->fetchAll();
    
    echo "\nRoles encontrados: " . count($roles) . "\n";
    foreach ($roles as $rol) {
        echo "  - {$rol['nombre']} (ID: {$rol['id']})\n";
        $permisos = json_decode($rol['permisos'], true);
        echo "    Módulos actuales: " . implode(', ', array_keys($permisos)) . "\n";
    }
    
    echo "\n2. Actualizando permisos...\n\n";
    
    // Actualizar Administrador
    echo "  Actualizando rol: Administrador\n";
    $sqlAdmin = "UPDATE roles 
                 SET permisos = JSON_SET(
                     permisos,
                     '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
                     '$.configuraciones', JSON_ARRAY('leer', 'actualizar')
                 )
                 WHERE nombre = 'Administrador'";
    
    $db->query($sqlAdmin);
    echo "    ✓ Permisos de Administrador actualizados\n";
    echo "      - ingresos: crear, leer, actualizar, eliminar\n";
    echo "      - configuraciones: leer, actualizar\n\n";
    
    // Actualizar Supervisor
    echo "  Actualizando rol: Supervisor\n";
    $sqlSupervisor = "UPDATE roles 
                      SET permisos = JSON_SET(
                          permisos,
                          '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar')
                      )
                      WHERE nombre = 'Supervisor'";
    
    $db->query($sqlSupervisor);
    echo "    ✓ Permisos de Supervisor actualizados\n";
    echo "      - ingresos: crear, leer, actualizar\n\n";
    
    echo "3. Verificando cambios...\n\n";
    $rolesActualizados = $db->query("SELECT id, nombre, permisos FROM roles ORDER BY id")->fetchAll();
    
    foreach ($rolesActualizados as $rol) {
        echo "  Rol: {$rol['nombre']}\n";
        $permisos = json_decode($rol['permisos'], true);
        
        if (is_array($permisos)) {
            foreach ($permisos as $modulo => $acciones) {
                echo "    - $modulo: " . implode(', ', $acciones) . "\n";
            }
        } else {
            echo "    ERROR: Permisos no son un array válido\n";
        }
        echo "\n";
    }
    
    echo "==============================================\n";
    echo "✓ Actualización completada exitosamente\n";
    echo "==============================================\n\n";
    echo "IMPORTANTE:\n";
    echo "- Los usuarios que ya tengan sesión activa deben cerrar sesión\n";
    echo "  y volver a iniciar sesión para que los cambios surtan efecto\n";
    echo "- Intenta acceder a /configuraciones nuevamente\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Traza: " . $e->getTraceAsString() . "\n";
    exit(1);
}
