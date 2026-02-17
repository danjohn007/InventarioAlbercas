# Corrección de Error de Columna en Base de Datos

## Problema
```
Database query error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'p.stock' in 'field list'
```

## Causa Raíz
El código en `ReportesController.php` estaba utilizando nombres de columnas incorrectos que no coincidían con el esquema de la base de datos:

- **Incorrecto**: `p.stock` 
- **Correcto**: `p.stock_actual`

- **Incorrecto**: `p.precio`
- **Correcto**: `p.precio_venta`

## Solución Implementada

### Archivo Modificado
`controllers/ReportesController.php`

### Cambios Realizados (líneas 36-95)

1. **Ordenamiento por stock:**
   - `p.stock ASC` → `p.stock_actual ASC`
   - `p.stock DESC` → `p.stock_actual DESC`

2. **Ordenamiento por precio:**
   - `p.precio ASC` → `p.precio_venta ASC`
   - `p.precio DESC` → `p.precio_venta DESC`

3. **Cálculo de valor total:**
   - `(p.stock * p.precio)` → `(p.stock_actual * p.precio_venta)`

4. **Estadísticas:**
   - `SUM(stock)` → `SUM(stock_actual)`
   - `SUM(stock * precio)` → `SUM(stock_actual * precio_venta)`
   - `COUNT(CASE WHEN stock <= stock_minimo)` → `COUNT(CASE WHEN stock_actual <= stock_minimo)`

5. **Consultas por categoría:**
   - `SUM(p.stock)` → `SUM(p.stock_actual)`
   - `SUM(p.stock * p.precio)` → `SUM(p.stock_actual * p.precio_venta)`

6. **Productos con stock bajo:**
   - `WHERE p.stock <= p.stock_minimo` → `WHERE p.stock_actual <= p.stock_minimo`
   - `ORDER BY (p.stock_minimo - p.stock)` → `ORDER BY (p.stock_minimo - p.stock_actual)`

## Esquema de Base de Datos Correcto

Según `database.sql`, la tabla `productos` tiene las siguientes columnas relevantes:

```sql
CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    categoria_id INT NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL,
    costo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_venta DECIMAL(10,2) DEFAULT 0.00,        -- ← Nombre correcto
    stock_actual DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- ← Nombre correcto
    stock_minimo DECIMAL(10,2) DEFAULT 0.00,
    ...
);
```

## Impacto
Esta corrección afecta el módulo de **Reportes de Inventario** (`/reportes/inventario`), específicamente:

- ✅ Consulta principal de productos
- ✅ Estadísticas generales de inventario
- ✅ Estadísticas por categoría
- ✅ Listado de productos con stock bajo
- ✅ Ordenamiento por stock y precio
- ✅ Cálculo de valor total del inventario

## Verificación
El reporte de inventario ahora debería:
1. Cargar sin errores SQL
2. Mostrar correctamente el stock actual de cada producto
3. Calcular correctamente los valores totales
4. Ordenar correctamente por stock y precio
5. Identificar correctamente productos con stock bajo

## Estado
✅ **CORREGIDO** - Commit: 65a19ab

Todas las referencias a las columnas antiguas han sido actualizadas al esquema correcto de la base de datos.
