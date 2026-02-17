# Diagrama de Flujo - Fix 403 Configuraciones

## Estado Actual (Antes del Fix)

```
Usuario Administrador
    ↓
Intenta acceder a /configuraciones
    ↓
ConfiguracionController::index()
    ↓
Auth::requirePermission('configuraciones', 'leer')
    ↓
Busca 'configuraciones' en $_SESSION['user_permisos']
    ↓
❌ NO ENCONTRADO (el JSON no tiene esta clave)
    ↓
return false
    ↓
🚫 ERROR 403 FORBIDDEN
```

### JSON de Permisos ANTES:
```json
{
  "usuarios": ["crear", "leer", "actualizar", "eliminar"],
  "inventario": ["crear", "leer", "actualizar", "eliminar"],
  "gastos": ["crear", "leer", "actualizar", "eliminar"],
  "servicios": ["crear", "leer", "actualizar", "eliminar"],
  "clientes": ["crear", "leer", "actualizar", "eliminar"],
  "reportes": ["leer", "exportar"]
  ❌ FALTA: "configuraciones"
  ❌ FALTA: "ingresos"
}
```

---

## Estado Después del Fix

```
Usuario Administrador
    ↓
Intenta acceder a /configuraciones
    ↓
ConfiguracionController::index()
    ↓
Auth::requirePermission('configuraciones', 'leer')
    ↓
Busca 'configuraciones' en $_SESSION['user_permisos']
    ↓
✅ ENCONTRADO: ["leer", "actualizar"]
    ↓
Verifica si 'leer' está en el array
    ↓
✅ SÍ ESTÁ
    ↓
return true
    ↓
🎉 ACCESO PERMITIDO - Carga la página
```

### JSON de Permisos DESPUÉS:
```json
{
  "usuarios": ["crear", "leer", "actualizar", "eliminar"],
  "inventario": ["crear", "leer", "actualizar", "eliminar"],
  "gastos": ["crear", "leer", "actualizar", "eliminar"],
  "servicios": ["crear", "leer", "actualizar", "eliminar"],
  "clientes": ["crear", "leer", "actualizar", "eliminar"],
  "reportes": ["leer", "exportar"],
  ✅ "configuraciones": ["leer", "actualizar"],
  ✅ "ingresos": ["crear", "leer", "actualizar", "eliminar"]
}
```

---

## Proceso del Fix

```
                    INICIO
                      ↓
        ┌─────────────────────────┐
        │  Ejecutar fix script    │
        │  php fix_permissions.php│
        └─────────────────────────┘
                      ↓
        ┌─────────────────────────┐
        │ Conectar a Base de Datos│
        └─────────────────────────┘
                      ↓
        ┌─────────────────────────┐
        │   Leer roles actuales   │
        │   SELECT * FROM roles   │
        └─────────────────────────┘
                      ↓
        ┌─────────────────────────┐
        │ UPDATE roles            │
        │ Agregar permisos faltantes│
        │ - configuraciones       │
        │ - ingresos              │
        └─────────────────────────┘
                      ↓
        ┌─────────────────────────┐
        │   Verificar cambios     │
        │   SELECT permisos       │
        └─────────────────────────┘
                      ↓
        ┌─────────────────────────┐
        │ ✓ Mostrar confirmación  │
        └─────────────────────────┘
                      ↓
                     FIN
```

---

## Impacto por Rol

### Administrador
```
ANTES:  6 módulos accesibles
DESPUÉS: 8 módulos accesibles (+configuraciones, +ingresos)
```

### Supervisor  
```
ANTES:  5 módulos accesibles
DESPUÉS: 6 módulos accesibles (+ingresos)
```

### Técnico
```
ANTES:  3 módulos accesibles
DESPUÉS: 3 módulos accesibles (sin cambios)
```

---

## ¿Por Qué Ocurrió Este Error?

```
1. Se desarrollaron nuevos módulos
   ↓
2. Se crearon controllers y vistas
   ↓
3. Se añadieron rutas en index.php
   ↓
4. Se creó database_updates.sql con permisos
   ↓
5. ❌ NO se ejecutó database_updates.sql en producción
   ↓
6. Código nuevo + Base de datos vieja = 403 ERROR
```

---

## Cómo Prevenir en el Futuro

```
┌─────────────────────────────────────────┐
│ CHECKLIST: Nuevo Módulo                 │
├─────────────────────────────────────────┤
│ ☐ Crear Controller                      │
│ ☐ Crear Vistas                          │
│ ☐ Agregar Rutas                         │
│ ☐ AGREGAR PERMISOS a roles.permisos    │ ← IMPORTANTE
│ ☐ Probar con usuario de cada rol       │
│ ☐ Verificar logs de error              │
│ ☐ Documentar nuevo módulo              │
└─────────────────────────────────────────┘
```

---

## Comandos de Verificación

### Verificar permisos actuales
```sql
SELECT nombre, permisos FROM roles;
```

### Verificar accesos denegados recientes
```sql
SELECT * FROM auditoria 
WHERE accion = 'acceso_denegado' 
ORDER BY fecha_creacion DESC 
LIMIT 10;
```

### Ver logs de 403 errors
```bash
grep "403 FORBIDDEN" /var/log/php-errors.log | tail -20
```

---

## Resultado Final

```
┌────────────────────────────────────┐
│  USUARIO: Administrador            │
├────────────────────────────────────┤
│  ✓ Dashboard                       │
│  ✓ Usuarios                        │
│  ✓ Inventario                      │
│  ✓ Gastos                          │
│  ✓ Servicios                       │
│  ✓ Clientes                        │
│  ✓ Reportes                        │
│  ✅ Configuraciones  [NUEVO]       │
│  ✅ Ingresos  [NUEVO]              │
└────────────────────────────────────┘
```
