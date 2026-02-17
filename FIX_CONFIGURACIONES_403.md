# Fix para Error 403 - Módulo Configuraciones

## Problema
Al intentar acceder al módulo de configuraciones (`/configuraciones`), el sistema muestra un error **403 FORBIDDEN**.

## Causa Raíz
Los módulos `configuraciones` e `ingresos` fueron añadidos recientemente al sistema, pero los permisos correspondientes no fueron agregados a los roles en la base de datos.

Cuando un usuario intenta acceder a `/configuraciones`, el sistema ejecuta:
```php
Auth::requirePermission('configuraciones', 'leer');
```

Como el rol del usuario no tiene el módulo `configuraciones` en su JSON de permisos, la verificación falla y se genera el error 403.

## Solución

### Opción 1: Ejecutar Script PHP (Recomendado)

Este script actualiza los permisos de forma segura y muestra el resultado:

```bash
cd /home/runner/work/InventarioAlbercas/InventarioAlbercas
php fix_permissions.php
```

**Salida esperada:**
```
==============================================
Fix de Permisos - Configuraciones e Ingresos
==============================================

1. Obteniendo roles actuales...
Roles encontrados: 3
  - Administrador (ID: 1)
  - Supervisor (ID: 2)
  - Tecnico (ID: 3)

2. Actualizando permisos...
  ✓ Permisos de Administrador actualizados
  ✓ Permisos de Supervisor actualizados

3. Verificando cambios...
✓ Actualización completada exitosamente
```

### Opción 2: Ejecutar SQL Directamente

Si prefieres ejecutar SQL directamente en tu gestor de base de datos:

```bash
mysql -u tu_usuario -p tu_basedatos < fix_configuraciones_permissions.sql
```

O ejecuta manualmente en phpMyAdmin/MySQL Workbench:

```sql
-- Administrador: agregar configuraciones e ingresos
UPDATE roles 
SET permisos = JSON_SET(
    permisos,
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.configuraciones', JSON_ARRAY('leer', 'actualizar')
)
WHERE nombre = 'Administrador';

-- Supervisor: agregar ingresos
UPDATE roles 
SET permisos = JSON_SET(
    permisos,
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar')
)
WHERE nombre = 'Supervisor';
```

## Permisos Agregados

### Rol: Administrador
- **configuraciones**: `["leer", "actualizar"]`
- **ingresos**: `["crear", "leer", "actualizar", "eliminar"]`

### Rol: Supervisor
- **ingresos**: `["crear", "leer", "actualizar"]`

### Rol: Técnico
- Sin cambios (no requiere acceso a configuraciones ni ingresos)

## Verificación

Después de aplicar el fix:

### 1. Verificar en Base de Datos
```sql
SELECT nombre, permisos 
FROM roles 
WHERE nombre IN ('Administrador', 'Supervisor');
```

**Resultado esperado para Administrador:**
```json
{
  "usuarios": ["crear", "leer", "actualizar", "eliminar"],
  "inventario": ["crear", "leer", "actualizar", "eliminar"],
  "gastos": ["crear", "leer", "actualizar", "eliminar"],
  "servicios": ["crear", "leer", "actualizar", "eliminar"],
  "clientes": ["crear", "leer", "actualizar", "eliminar"],
  "reportes": ["leer", "exportar"],
  "ingresos": ["crear", "leer", "actualizar", "eliminar"],
  "configuraciones": ["leer", "actualizar"]
}
```

### 2. Probar Acceso al Módulo

1. **Cerrar sesión** y volver a iniciar sesión (importante para que se recarguen los permisos)
2. Navegar a `/configuraciones` o hacer clic en el menú "Configuraciones"
3. **Resultado esperado**: La página debe cargar correctamente sin error 403

### 3. Verificar Logs
```bash
# No debe haber nuevos errores 403 para configuraciones
grep "403 FORBIDDEN.*configuraciones" /var/log/php-errors.log
```

## Prevención de Problemas Futuros

### Al Agregar Nuevos Módulos

Cuando agregues un nuevo módulo al sistema:

1. **Definir el módulo** en el código (controlador, vistas, rutas)
2. **Actualizar permisos** inmediatamente en la base de datos
3. **Probar con diferentes roles** antes de desplegar

### Ejemplo de Script de Migración

```sql
-- Agregar nuevo módulo "ejemplo"
UPDATE roles 
SET permisos = JSON_SET(
    permisos,
    '$.ejemplo', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar')
)
WHERE nombre = 'Administrador';
```

### Checklist Pre-Deploy

- [ ] ¿El nuevo módulo usa `Auth::requirePermission()`?
- [ ] ¿Los permisos están agregados en todos los roles necesarios?
- [ ] ¿Se probó el acceso con usuarios de diferentes roles?
- [ ] ¿Se documentó el nuevo módulo en los permisos?

## Troubleshooting

### Error persiste después del fix

**Problema**: El error 403 sigue apareciendo

**Soluciones**:
1. **Cerrar sesión completamente** y volver a iniciar sesión
2. Verificar que los cambios se aplicaron en la base de datos:
   ```sql
   SELECT permisos FROM roles WHERE nombre = 'Administrador';
   ```
3. Limpiar sesiones antiguas:
   ```bash
   rm -rf /tmp/sess_*
   # O en tu directorio de sesiones configurado
   ```

### Otros usuarios siguen sin acceso

**Problema**: Solo algunos usuarios pueden acceder

**Solución**: Verificar que el rol del usuario tenga los permisos:
```sql
SELECT u.nombre, u.usuario, r.nombre as rol, r.permisos
FROM usuarios u
INNER JOIN roles r ON u.rol_id = r.id
WHERE u.id = [ID_DEL_USUARIO];
```

### Error al ejecutar script PHP

**Problema**: Error de conexión a base de datos

**Solución**: 
1. Verificar credenciales en `config/config.php`
2. Verificar que el usuario de base de datos tenga permisos de UPDATE
3. Ejecutar con permisos apropiados: `sudo -u www-data php fix_permissions.php`

## Archivos Relacionados

- `/fix_permissions.php` - Script PHP para aplicar el fix
- `/fix_configuraciones_permissions.sql` - Script SQL alternativo
- `/database_updates.sql` - Contiene las migraciones originales
- `/controllers/ConfiguracionController.php` - Requiere permisos de configuraciones
- `/controllers/IngresosController.php` - Requiere permisos de ingresos
- `/utils/Auth.php` - Maneja la verificación de permisos

## Notas Adicionales

- Este fix es **retrocompatible** - no afecta funcionalidades existentes
- Los permisos se almacenan como JSON en la columna `roles.permisos`
- MySQL 5.7+ soporta funciones JSON nativas (`JSON_SET`, `JSON_ARRAY`)
- Los cambios son **inmediatos** pero requieren que el usuario cierre sesión

## Contacto y Soporte

Si tienes problemas después de aplicar este fix:
1. Revisa los logs del servidor PHP
2. Verifica la tabla de auditoría para ver intentos de acceso denegado
3. Consulta `SOLUCION_403.md` para información sobre el manejo de errores 403

---

**Fecha**: 2026-02-17  
**Versión**: 1.0  
**Estado**: Probado y funcional
