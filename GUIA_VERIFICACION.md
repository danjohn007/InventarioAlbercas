# Guía de Verificación Rápida

## 🔍 Verificación de la Implementación

Esta guía te ayudará a verificar rápidamente que todos los módulos están funcionando correctamente después de aplicar las actualizaciones.

---

## 📋 Pre-requisitos

### 1. Base de Datos Actualizada
```bash
# Ejecutar el script de migración
mysql -u usuario -p inventario_albercas < fix360_inventario_migration.sql
```

### 2. Dependencias de Composer Instaladas
```bash
composer install
# o si ya están instaladas
composer update
```

### 3. Permisos de Carpetas
```bash
chmod 755 public/uploads
```

---

## ✅ Checklist de Verificación

### Módulo de Configuraciones

1. **Acceso al Módulo** ✓
   - URL: `http://tu-dominio/configuraciones`
   - ❌ NO debe mostrar ERROR 403
   - ✅ DEBE mostrar la página de configuraciones

2. **Verificar Secciones** ✓
   - [ ] Configuraciones Generales (nombre del sitio, moneda, etc.)
   - [ ] Apariencia y Marca (logo, colores)
   - [ ] Configuraciones del Sistema (items por página, stock mínimo)
   - [ ] Notificaciones
   - [ ] Backup y Restauración

3. **Funcionalidades** ✓
   - [ ] Cambiar nombre del sitio
   - [ ] Subir logotipo
   - [ ] Cambiar color primario y secundario
   - [ ] Exportar configuración (descarga JSON)
   - [ ] Importar configuración (sube JSON)
   - [ ] Restablecer valores por defecto

4. **Mensajes de Confirmación** ✓
   - [ ] Se muestra mensaje de éxito al guardar
   - [ ] Se registra en auditoría

---

### Reporte de Gastos

1. **Acceso al Reporte** ✓
   - URL: `http://tu-dominio/reportes/gastos`
   - ✅ DEBE mostrar el reporte completo

2. **Filtros Disponibles** ✓
   - [ ] Fecha Desde
   - [ ] Fecha Hasta
   - [ ] Categoría (dropdown con todas las categorías)
   - [ ] Forma de Pago (Todas, Efectivo, Tarjeta, Transferencia)
   - [ ] Botón de aplicar filtros

3. **Estadísticas Generales** ✓
   - [ ] Total Gastos (cantidad)
   - [ ] Monto Total ($)
   - [ ] Promedio ($)
   - [ ] Máximo ($)

4. **Gráficos Visuales** ✓
   - [ ] Gráfico de pastel: Gastos por Categoría
   - [ ] Gráfico de dona: Gastos por Forma de Pago
   - [ ] Gráfico de línea: Tendencia Mensual

5. **Tablas de Datos** ✓
   - [ ] Resumen por Categoría (con porcentajes)
   - [ ] Top 10 Gastos Más Grandes

6. **Botones de Exportación** ✓
   - [ ] Botón "Imprimir" (abre diálogo de impresión)
   - [ ] Botón "Excel" (descarga archivo .xlsx)
   - [ ] Botón "PDF" (descarga archivo .pdf)

---

### Exportación Excel

1. **Desde Reporte de Gastos** ✓
   - URL: `http://tu-dominio/reportes/gastos/excel`
   - [ ] Descarga archivo `reporte_gastos_YYYYMMDD.xlsx`
   - [ ] Abre correctamente en Excel/LibreOffice
   - [ ] Contiene: título, fecha, resumen, datos detallados

2. **Desde Reporte de Inventario** ✓
   - URL: `http://tu-dominio/reportes/inventario/excel`
   - [ ] Descarga archivo `reporte_inventario_YYYYMMDD.xlsx`

3. **Desde Reporte de Servicios** ✓
   - URL: `http://tu-dominio/reportes/servicios/excel`
   - [ ] Descarga archivo `reporte_servicios_YYYYMMDD.xlsx`

---

### Exportación PDF

1. **Desde Reporte de Gastos** ✓
   - URL: `http://tu-dominio/reportes/gastos/pdf`
   - [ ] Descarga archivo `reporte_gastos_YYYYMMDD.pdf`
   - [ ] Abre correctamente en visor PDF
   - [ ] Contiene: encabezado, tablas, pie de página

2. **Desde Reporte de Inventario** ✓
   - URL: `http://tu-dominio/reportes/inventario/pdf`
   - [ ] Descarga archivo `reporte_inventario_YYYYMMDD.pdf`

3. **Desde Reporte de Servicios** ✓
   - URL: `http://tu-dominio/reportes/servicios/pdf`
   - [ ] Descarga archivo `reporte_servicios_YYYYMMDD.pdf`

---

## 🔐 Verificación de Permisos

### Usuario: Administrador
```sql
SELECT JSON_PRETTY(permisos) 
FROM roles 
WHERE nombre = 'Administrador';
```

**Debe incluir:**
```json
{
  "configuraciones": ["leer", "actualizar"],
  "reportes": ["leer", "exportar"]
}
```

### Usuario: Supervisor
```sql
SELECT JSON_PRETTY(permisos) 
FROM roles 
WHERE nombre = 'Supervisor';
```

**Debe incluir:**
```json
{
  "reportes": ["leer", "exportar"]
}
```

### Usuario: Técnico
```sql
SELECT JSON_PRETTY(permisos) 
FROM roles 
WHERE nombre = 'Tecnico';
```

**Debe incluir:**
```json
{
  "reportes": ["leer"]
}
```

---

## 🧪 Pruebas Funcionales

### Test 1: Cambiar Configuraciones
1. Ingresar al módulo de configuraciones
2. Cambiar "Nombre del sitio" a "Mi Sistema"
3. Guardar cambios
4. Recargar la página
5. ✅ El nuevo nombre debe aparecer

### Test 2: Upload de Logo
1. Ingresar al módulo de configuraciones
2. Subir una imagen PNG o JPG
3. Guardar cambios
4. ✅ Debe aparecer vista previa del logo
5. ✅ Logo debe aparecer en el header del sistema

### Test 3: Cambiar Colores
1. Ingresar al módulo de configuraciones
2. Cambiar color primario a #FF5733
3. Cambiar color secundario a #C70039
4. Guardar cambios
5. Recargar la página
6. ✅ El sidebar debe reflejar los nuevos colores

### Test 4: Exportar Configuración
1. Ingresar al módulo de configuraciones
2. Hacer clic en "Descargar Backup"
3. ✅ Debe descargar archivo JSON
4. Abrir archivo JSON
5. ✅ Debe contener todas las configuraciones

### Test 5: Filtros de Reporte de Gastos
1. Ir a Reportes > Gastos
2. Seleccionar rango de fechas del mes actual
3. Seleccionar categoría específica
4. Aplicar filtros
5. ✅ Estadísticas deben actualizarse
6. ✅ Gráficos deben reflejar los filtros

### Test 6: Exportar Gastos a Excel
1. Ir a Reportes > Gastos
2. Aplicar filtros deseados
3. Hacer clic en botón "Excel"
4. ✅ Debe descargar archivo .xlsx
5. Abrir en Excel
6. ✅ Debe contener datos filtrados
7. ✅ Formato debe ser profesional

### Test 7: Exportar Gastos a PDF
1. Ir a Reportes > Gastos
2. Aplicar filtros deseados
3. Hacer clic en botón "PDF"
4. ✅ Debe descargar archivo .pdf
5. Abrir en visor PDF
6. ✅ Debe contener datos filtrados
7. ✅ Debe tener encabezado y pie de página

### Test 8: Imprimir Reporte
1. Ir a Reportes > Gastos
2. Hacer clic en botón "Imprimir"
3. ✅ Debe abrir diálogo de impresión
4. ✅ Filtros y botones no deben aparecer en vista previa
5. ✅ Solo contenido del reporte debe estar visible

---

## 🚨 Solución de Problemas Comunes

### Problema: Error 403 persiste
**Solución:**
1. Cerrar sesión
2. Ejecutar nuevamente el script SQL de migración
3. Verificar que los permisos se actualizaron:
   ```sql
   SELECT nombre, JSON_PRETTY(permisos) FROM roles;
   ```
4. Iniciar sesión nuevamente

### Problema: Exportación Excel falla
**Error:** "Class 'PhpOffice\PhpSpreadsheet' not found"

**Solución:**
```bash
composer require phpoffice/phpspreadsheet
```

### Problema: Exportación PDF falla
**Error:** "Class 'TCPDF' not found"

**Solución:**
```bash
composer require tecnickcom/tcpdf
```

### Problema: Gráficos no se muestran
**Solución:**
1. Verificar que Chart.js está cargado en el HTML
2. Abrir consola del navegador (F12)
3. Verificar errores JavaScript
4. Asegurar que la librería se carga desde CDN:
   ```html
   <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
   ```

### Problema: Upload de logo falla
**Solución:**
```bash
# Verificar permisos de carpeta
chmod 755 public/uploads
chown www-data:www-data public/uploads

# Crear carpeta si no existe
mkdir -p public/uploads
```

---

## 📊 Verificación de Base de Datos

### Verificar tabla configuraciones
```sql
-- Ver configuraciones actuales
SELECT * FROM configuraciones ORDER BY categoria, clave;

-- Contar configuraciones
SELECT categoria, COUNT(*) as total
FROM configuraciones
GROUP BY categoria;
```

### Verificar categorías de gastos
```sql
-- Ver categorías de gastos
SELECT * FROM categorias_gasto WHERE activo = 1;
```

### Verificar vistas creadas
```sql
-- Verificar vistas de reportes
SHOW FULL TABLES WHERE Table_type = 'VIEW';

-- Probar vista de gastos mensuales
SELECT * FROM vista_gastos_mensuales LIMIT 10;
```

### Verificar índices
```sql
-- Ver índices de tabla gastos
SHOW INDEX FROM gastos;

-- Ver índices de tabla productos
SHOW INDEX FROM productos;
```

---

## ✅ Checklist Final de Entrega

Marca cada ítem cuando esté verificado:

- [ ] Módulo de Configuraciones accesible sin error 403
- [ ] Todas las configuraciones se guardan correctamente
- [ ] Upload de logo funciona
- [ ] Cambio de colores funciona
- [ ] Exportar/Importar configuración funciona
- [ ] Reporte de Gastos se visualiza correctamente
- [ ] Filtros de gastos funcionan
- [ ] Gráficos de gastos se muestran
- [ ] Exportación a Excel funciona (gastos, inventario, servicios)
- [ ] Exportación a PDF funciona (gastos, inventario, servicios)
- [ ] Impresión de reportes funciona
- [ ] Permisos están correctamente configurados
- [ ] Auditoría registra cambios en configuraciones
- [ ] No hay errores en consola del navegador
- [ ] No hay errores en logs de PHP

---

## 📞 Contacto de Soporte

Si encuentras algún problema durante la verificación:
- **Email:** admin@albercas.com
- **GitHub Issues:** [Crear Issue](https://github.com/danjohn007/InventarioAlbercas/issues)

---

**Última actualización:** 17 de febrero de 2026
