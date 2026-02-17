# Solución Rápida - Error 403 en Configuraciones

## ¿Qué hacer?

Ejecuta uno de estos comandos en tu servidor:

### Opción 1: Script PHP (Recomendado)
```bash
cd /home/runner/work/InventarioAlbercas/InventarioAlbercas
php fix_permissions.php
```

### Opción 2: SQL Directo
```bash
mysql -u tu_usuario -p tu_basedatos < fix_configuraciones_permissions.sql
```

### Opción 3: Copiar y Pegar en phpMyAdmin

Abre phpMyAdmin, selecciona tu base de datos y ejecuta:

```sql
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
```

## ⚠️ IMPORTANTE

Después de ejecutar el fix:

1. **Cerrar sesión** en el sistema
2. **Iniciar sesión** nuevamente
3. Probar acceso a `/configuraciones`

## ¿Funcionó?

✅ **Sí**: Ya puedes acceder al módulo de configuraciones

❌ **No**: Revisa `FIX_CONFIGURACIONES_403.md` para más opciones

## Causa del Problema

El módulo de configuraciones fue añadido pero faltaba agregarlo a los permisos de los roles. Este fix lo soluciona.

## Archivos de Referencia

- `fix_permissions.php` - Script automatizado
- `fix_configuraciones_permissions.sql` - SQL directo
- `FIX_CONFIGURACIONES_403.md` - Documentación completa
