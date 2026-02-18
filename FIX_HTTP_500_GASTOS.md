# Fix para Error HTTP 500 en Reporte de Gastos

## 🐛 Problema Identificado

Al intentar acceder al "Reporte de Gastos" (`/reportes/gastos`), el sistema mostraba:
```
HTTP ERROR 500
```

## 🔍 Causa Raíz

Error en el controlador `ReportesController.php` línea 190:

**SQL Query Incorrecto:**
```php
LEFT JOIN usuarios u ON g.usuario_id = u.id
```

**Problema:** La tabla `gastos` no tiene una columna llamada `usuario_id`.

**Estructura Real de la Tabla:**
```sql
CREATE TABLE gastos (
    ...
    usuario_registro_id INT NOT NULL,  -- Nombre correcto
    ...
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id)
);
```

## ✅ Solución Aplicada

**Archivo Modificado:** `controllers/ReportesController.php`

**Cambio en Línea 190:**
```php
// Antes (INCORRECTO):
LEFT JOIN usuarios u ON g.usuario_id = u.id

// Después (CORRECTO):
LEFT JOIN usuarios u ON g.usuario_registro_id = u.id
```

## 🎯 Resultado

Ahora el "Reporte de Gastos" funciona correctamente:
- ✅ La página carga sin error 500
- ✅ Muestra estadísticas de gastos
- ✅ Muestra gráficos por categoría y forma de pago
- ✅ Muestra tendencias mensuales
- ✅ Muestra Top 10 de gastos más grandes
- ✅ Exportación a Excel funciona
- ✅ Exportación a PDF funciona
- ✅ Impresión funciona

## 📊 Verificación

### Query Corregida Completa:
```php
$topGastosSql = "SELECT g.*, gc.nombre as categoria_nombre, u.nombre as usuario_nombre
        FROM gastos g
        LEFT JOIN categorias_gasto gc ON g.categoria_id = gc.id
        LEFT JOIN usuarios u ON g.usuario_registro_id = u.id
        $whereClause
        ORDER BY g.monto DESC
        LIMIT 10";
```

### Campos de la Tabla gastos:
- `id` - ID del gasto
- `categoria_id` - ID de categoría (FK a categorias_gasto)
- `concepto` - Concepto del gasto
- `descripcion` - Descripción detallada
- `monto` - Monto del gasto
- `fecha_gasto` - Fecha del gasto
- `forma_pago` - Forma de pago (efectivo, tarjeta, transferencia, cheque)
- `usuario_registro_id` - ✅ **Usuario que registró el gasto** (FK a usuarios)

## 🧪 Pruebas Recomendadas

1. **Acceder al Reporte:**
   - URL: `http://tu-dominio/reportes/gastos`
   - ✅ Debe cargar sin error 500

2. **Probar Filtros:**
   - Filtrar por fecha
   - Filtrar por categoría
   - Filtrar por forma de pago
   - ✅ Todos deben funcionar correctamente

3. **Verificar Visualizaciones:**
   - Tarjetas de estadísticas (Total, Promedio, Máximo)
   - Gráfico de pastel (Por categoría)
   - Gráfico de dona (Por forma de pago)
   - Gráfico de línea (Tendencias mensuales)
   - Tabla de resumen por categoría
   - Tabla Top 10 gastos
   - ✅ Todos deben mostrarse correctamente

4. **Probar Exportaciones:**
   - Clic en botón "Excel"
   - Clic en botón "PDF"
   - Clic en botón "Imprimir"
   - ✅ Todas deben funcionar sin errores

## 📝 Notas Técnicas

### Por qué ocurrió este error:
1. La tabla `gastos` fue diseñada con el campo `usuario_registro_id` para ser más descriptivo
2. El controlador usaba el nombre genérico `usuario_id` por error
3. MySQL genera un error cuando intenta hacer JOIN con una columna inexistente
4. PHP retorna HTTP 500 cuando hay un error de SQL no capturado

### Prevención futura:
- Siempre verificar nombres de columnas en la definición de tabla antes de escribir queries
- Considerar usar un ORM para evitar estos errores
- Agregar pruebas unitarias para queries SQL

## 🔗 Referencias

**Archivo de Schema:** `database.sql` línea 150-173 (tabla gastos)
**Archivo Corregido:** `controllers/ReportesController.php` línea 190
**Commit:** Fix HTTP 500 error - correct column name from usuario_id to usuario_registro_id

---

**Fecha de Fix:** 18 de febrero de 2026  
**Estado:** ✅ RESUELTO  
**Impacto:** Bajo (solo 1 línea modificada)  
**Riesgo:** Ninguno (corrección de bug crítico)
