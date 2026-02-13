# Fix para Error 403 - Forbidden

## Problema Identificado

El error 403 - FORBIDDEN estaba ocurriendo potencialmente por las siguientes razones:

1. **Permisos de sesión no validados**: La variable `$_SESSION['user_permisos']` podía ser accedida sin verificar su existencia
2. **JSON malformado**: Si el JSON de permisos en la base de datos estaba corrupto, `json_decode()` retornaba `null`
3. **Estructura de permisos incorrecta**: No se validaba que los permisos fueran un array válido

## Cambios Implementados

### 1. Validación en `Auth::login()`

```php
// Decodificar permisos del rol
$permisos = json_decode($user['permisos'], true);

// Validar que los permisos sean válidos
if (!is_array($permisos)) {
    error_log("ERROR: Permisos inválidos para el usuario '{$usuario}' (rol: {$user['rol_nombre']})");
    $permisos = []; // Array vacío como fallback
}
```

**Beneficios:**
- Detecta permisos malformados al momento del login
- Usa un array vacío como fallback seguro
- Registra errores en el log para debugging

### 2. Validación robusta en `Auth::can()`

```php
// Verificar que exista la variable de permisos en sesión
if (!isset($_SESSION['user_permisos']) || !is_array($_SESSION['user_permisos'])) {
    error_log("WARNING: user_permisos no está definido en la sesión para el usuario " . ($_SESSION['user_id'] ?? 'desconocido'));
    return false;
}

// Verificar que el módulo tenga un array de permisos
if (!is_array($permisos[$modulo])) {
    error_log("WARNING: permisos para módulo '$modulo' no es un array");
    return false;
}
```

**Beneficios:**
- Previene errores de "undefined index"
- Valida la estructura completa de permisos
- Proporciona logs detallados para debugging

### 3. Auditoría mejorada en `Auth::requirePermission()`

```php
// Registrar el intento de acceso no autorizado
$userId = $_SESSION['user_id'] ?? 'desconocido';
$userRol = $_SESSION['user_rol'] ?? 'desconocido';
error_log("403 FORBIDDEN: Usuario ID $userId (rol: $userRol) intentó acceder a $modulo:$accion");

// Registrar en auditoría
if (isset($_SESSION['user_id'])) {
    self::registrarAuditoria(
        $_SESSION['user_id'], 
        'acceso_denegado', 
        $modulo, 
        null, 
        "Intento de acceso a: $modulo:$accion"
    );
}
```

**Beneficios:**
- Todos los intentos de acceso no autorizado se registran
- Información completa en logs del servidor
- Trazabilidad en la tabla de auditoría
- Facilita la detección de intentos de acceso no autorizado

## Tests Ejecutados

Todos los tests pasaron exitosamente (4/4):

- ✅ Permisos null son correctamente rechazados
- ✅ Permisos válidos funcionan correctamente  
- ✅ Módulos inexistentes son rechazados
- ✅ Permisos con formato incorrecto son rechazados

## Cómo Prevenir Futuros Problemas

### 1. Validar Permisos en la Base de Datos

Al insertar o actualizar roles, asegúrate de que el JSON de permisos sea válido:

```sql
-- Ejemplo de permisos válidos
INSERT INTO roles (nombre, permisos) VALUES
('Ejemplo', '{"inventario": ["leer", "crear"], "gastos": ["leer"]}');
```

### 2. Validar al Actualizar Roles

En `RolesController` (si existe), valida que el JSON sea correcto antes de guardarlo:

```php
$permisos = json_decode($permisosJson, true);
if (!is_array($permisos)) {
    throw new Exception("Formato de permisos inválido");
}
```

### 3. Monitorear Logs

Revisa regularmente los logs del servidor para detectar problemas:

```bash
# Buscar errores de permisos
grep "WARNING: user_permisos" /var/log/php-errors.log

# Buscar intentos de acceso denegado
grep "403 FORBIDDEN" /var/log/php-errors.log
```

### 4. Auditoría Regular

Consulta la tabla de auditoría para revisar intentos de acceso no autorizado:

```sql
SELECT * FROM auditoria 
WHERE accion = 'acceso_denegado' 
ORDER BY fecha_creacion DESC 
LIMIT 20;
```

## Documentación de Estructura de Permisos

Formato esperado en `roles.permisos` (JSON):

```json
{
  "modulo1": ["accion1", "accion2"],
  "modulo2": ["accion1"]
}
```

### Módulos Disponibles:
- `usuarios`: Gestión de usuarios
- `inventario`: Gestión de inventario
- `gastos`: Gestión de gastos
- `servicios`: Gestión de servicios
- `clientes`: Gestión de clientes
- `reportes`: Visualización de reportes

### Acciones por Módulo:
- `crear`: Crear nuevos registros
- `leer`: Ver/listar registros
- `actualizar`: Modificar registros existentes
- `eliminar`: Eliminar registros
- `exportar`: Exportar datos (solo reportes)

## Impacto de los Cambios

### Seguridad
- ✅ Mayor robustez contra datos corruptos
- ✅ Mejor auditoría de intentos de acceso
- ✅ Logs más informativos para debugging

### Rendimiento
- ✅ Mínimo impacto (solo validaciones adicionales)
- ✅ Sin consultas adicionales a la base de datos

### Compatibilidad
- ✅ 100% compatible con código existente
- ✅ No requiere cambios en la base de datos
- ✅ No requiere cambios en otros archivos

## Verificación Post-Deploy

Después de desplegar estos cambios:

1. Verificar que el login funciona correctamente
2. Probar con diferentes roles (Admin, Supervisor, Técnico)
3. Verificar que los permisos se respetan correctamente
4. Revisar los logs para confirmar que no hay errores
5. Verificar que la tabla de auditoría registra correctamente

## Soporte

Si encuentras problemas después de estos cambios, revisa:

1. **Logs del servidor PHP**: Busca mensajes de WARNING o ERROR
2. **Tabla de auditoría**: Consulta intentos de acceso denegado
3. **Permisos en base de datos**: Verifica que el JSON sea válido en `roles.permisos`
