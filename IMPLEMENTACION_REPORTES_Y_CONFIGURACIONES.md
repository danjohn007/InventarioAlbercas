# Implementación: Reportes y Configuraciones del Sistema

## 📋 Resumen

Este documento detalla la implementación completa de los módulos de **Reportes** y **Configuraciones** solicitados en el issue, incluyendo la solución al error 403 FORBIDDEN y la funcionalidad de exportación Excel/PDF.

---

## ✅ Cambios Implementados

### 1. Solución al ERROR 403 - FORBIDDEN en Configuraciones

**Problema:** Al intentar acceder al módulo `/configuraciones`, el sistema mostraba un error 403 FORBIDDEN.

**Causa:** Los roles de usuarios no tenían los permisos necesarios para el módulo `configuraciones`.

**Solución Implementada:**
- ✅ Actualización de permisos en la tabla `roles` mediante SQL
- ✅ Agregado de permisos `["leer", "actualizar"]` para el módulo `configuraciones`
- ✅ Archivo SQL de migración: `fix360_inventario_migration.sql`

**Permisos por Rol:**
```json
Administrador: {
  "configuraciones": ["leer", "actualizar"],
  "reportes": ["leer", "exportar"]
}

Supervisor: {
  "reportes": ["leer", "exportar"]
}

Técnico: {
  "reportes": ["leer"]
}
```

---

### 2. Módulo de Configuraciones

**Ubicación:** `/configuraciones`

**Características Implementadas:**

#### 2.1 Configuraciones Generales
- ✅ Nombre del sitio
- ✅ Descripción del sitio
- ✅ Moneda del sistema (MXN, USD, etc.)
- ✅ Zona horaria
- ✅ Formato de fecha y hora

#### 2.2 Apariencia y Marca
- ✅ **Logotipo del sistema**: Upload de imagen (PNG, JPG, SVG)
- ✅ **Color primario**: Selector de color visual
- ✅ **Color secundario**: Selector de color visual
- ✅ Vista previa en tiempo real de cambios de color

#### 2.3 Configuraciones Globales del Sistema
- ✅ Items por página en listados
- ✅ Stock mínimo para alertas
- ✅ Notificaciones por email
- ✅ Alertas de stock bajo
- ✅ Respaldos automáticos
- ✅ Días entre respaldos

#### 2.4 Funcionalidades Adicionales
- ✅ **Exportar configuración**: Descarga backup en JSON
- ✅ **Importar configuración**: Restaurar desde backup JSON
- ✅ **Restablecer valores por defecto**: Reset a configuración de fábrica
- ✅ Auditoría de cambios

**Archivos Involucrados:**
```
/controllers/ConfiguracionController.php
/views/configuraciones/index.php
/utils/Auth.php (validación de permisos)
```

---

### 3. Módulos de Exportación Excel y PDF

**Ubicación de Utilidades:**
```
/utils/exports/ExcelExporter.php
/utils/exports/PdfExporter.php
```

**Dependencias (Composer):**
```json
{
  "tecnickcom/tcpdf": "^6.7",
  "phpoffice/phpspreadsheet": "^1.29 || ^2.0"
}
```

**Características:**

#### 3.1 ExcelExporter
- ✅ Creación de hojas de cálculo profesionales
- ✅ Encabezados con formato y color
- ✅ Tablas con bordes y estilos
- ✅ Resúmenes con estadísticas
- ✅ Auto-ajuste de columnas
- ✅ Formato de números y monedas

#### 3.2 PdfExporter
- ✅ Documentos PDF con TCPDF
- ✅ Encabezado y pie de página personalizados
- ✅ Soporte para logotipo
- ✅ Tablas con múltiples columnas
- ✅ Orientación vertical u horizontal
- ✅ Numeración de páginas

---

### 4. Módulo de Reporte de Gastos

**Ubicación:** `/reportes/gastos`

**Características Implementadas:**

#### 4.1 Filtros Avanzados
- ✅ Filtro por rango de fechas
- ✅ Filtro por categoría de gasto
- ✅ Filtro por forma de pago (efectivo, tarjeta, transferencia)
- ✅ Filtros combinables

#### 4.2 Estadísticas y Métricas
- ✅ **Total de gastos**: Cantidad de registros
- ✅ **Monto total**: Suma de todos los gastos
- ✅ **Promedio**: Gasto promedio
- ✅ **Máximo**: Gasto más alto del período

#### 4.3 Análisis por Categoría
- ✅ Tabla con resumen por categoría
- ✅ Cantidad de gastos por categoría
- ✅ Total y promedio por categoría
- ✅ Porcentaje del total (con barra de progreso visual)
- ✅ Gráfico de pastel (Chart.js)

#### 4.4 Análisis por Forma de Pago
- ✅ Distribución de gastos por forma de pago
- ✅ Gráfico de dona (Chart.js)
- ✅ Iconos visuales para cada forma de pago

#### 4.5 Tendencias Mensuales
- ✅ Gráfico de línea temporal
- ✅ Visualización de tendencias mes a mes
- ✅ Identificación de patrones de gasto

#### 4.6 Top 10 Gastos Más Grandes
- ✅ Tabla con los 10 gastos más altos
- ✅ Información completa: fecha, concepto, categoría, usuario, monto
- ✅ Iconos por forma de pago

#### 4.7 Exportación
- ✅ **Botón Excel**: Exporta a formato XLSX
  - Ruta: `/reportes/gastos/excel`
  - Incluye todos los campos y descripciones
  - Formato profesional con colores
  
- ✅ **Botón PDF**: Exporta a formato PDF
  - Ruta: `/reportes/gastos/pdf`
  - Diseño optimizado para impresión
  - Incluye encabezado y pie de página
  
- ✅ **Botón Imprimir**: Impresión directa desde navegador
  - Oculta elementos no imprimibles (filtros, botones)
  - Optimizado para papel

**Archivos Involucrados:**
```
/controllers/ReportesController.php
  - gastos()
  - exportarGastosPDF()
  - exportarGastosExcel()
  
/views/reportes/gastos.php
  - Interfaz completa con filtros y gráficos
  - Integración con Chart.js
  
/public/js/gastos.js (si existe)
```

---

### 5. Otros Reportes Implementados

#### 5.1 Reporte de Inventario
- ✅ Vista: `/reportes/inventario`
- ✅ Exportación: `/reportes/inventario/pdf` y `/reportes/inventario/excel`
- ✅ Filtros por categoría y orden
- ✅ Estadísticas de stock y valor
- ✅ Productos con stock bajo

#### 5.2 Reporte de Servicios
- ✅ Vista: `/reportes/servicios`
- ✅ Exportación: `/reportes/servicios/pdf` y `/reportes/servicios/excel`
- ✅ Filtros por fecha, estado y técnico
- ✅ Análisis por técnico y estado
- ✅ Top clientes

---

## 🗄️ Estructura de Base de Datos

### Tabla: `configuraciones`
```sql
CREATE TABLE configuraciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo ENUM('texto', 'numero', 'booleano', 'json', 'archivo'),
    descripcion TEXT,
    categoria ENUM('general', 'apariencia', 'sistema', 'notificaciones'),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Configuraciones Predefinidas
| Clave | Categoría | Descripción |
|-------|-----------|-------------|
| `sitio_nombre` | general | Nombre del sitio web |
| `sitio_logo` | apariencia | Ruta del logotipo |
| `color_primario` | apariencia | Color principal (#667eea) |
| `color_secundario` | apariencia | Color secundario (#764ba2) |
| `moneda` | general | Moneda del sistema (MXN) |
| `items_por_pagina` | sistema | Items por página (20) |
| `stock_minimo_alerta` | sistema | Stock mínimo para alertar (5) |
| `notificaciones_email` | notificaciones | Activar emails (1/0) |

### Vistas Creadas para Reportes
```sql
-- Vista de gastos mensuales
CREATE VIEW vista_gastos_mensuales AS ...

-- Vista de gastos por categoría y forma de pago
CREATE VIEW vista_gastos_categoria_pago AS ...

-- Vista de productos con stock bajo
CREATE VIEW vista_productos_stock_bajo AS ...
```

### Índices Optimizados
```sql
-- Para reportes de gastos
ALTER TABLE gastos 
ADD INDEX idx_fecha_monto (fecha_gasto, monto),
ADD INDEX idx_forma_pago (forma_pago);

-- Para reportes de inventario
ALTER TABLE productos 
ADD INDEX idx_stock_categoria (stock_actual, categoria_id);

-- Para reportes de servicios
ALTER TABLE servicios 
ADD INDEX idx_fecha_estado (fecha_programada, estado);
```

---

## 📦 Instalación y Actualización

### Paso 1: Actualizar Base de Datos
```bash
# Ejecutar el archivo de migración
mysql -u usuario -p inventario_albercas < fix360_inventario_migration.sql
```

O desde phpMyAdmin:
1. Seleccionar la base de datos `inventario_albercas`
2. Ir a la pestaña "SQL"
3. Copiar y ejecutar el contenido de `fix360_inventario_migration.sql`

### Paso 2: Verificar Dependencias de Composer
```bash
cd /ruta/del/proyecto
composer install
```

### Paso 3: Verificar Permisos de Carpetas
```bash
chmod 755 public/uploads
chmod 755 utils/exports
```

### Paso 4: Crear Directorio de Uploads (si no existe)
```bash
mkdir -p public/uploads
chmod 755 public/uploads
```

---

## 🔐 Seguridad

### Validación de Permisos
Todas las rutas están protegidas con `Auth::requirePermission()`:
```php
Auth::requirePermission('configuraciones', 'leer');
Auth::requirePermission('configuraciones', 'actualizar');
Auth::requirePermission('reportes', 'leer');
Auth::requirePermission('reportes', 'exportar');
```

### Auditoría
Todos los cambios en configuraciones se registran en la tabla `auditoria`:
- Usuario que realizó el cambio
- Fecha y hora
- IP y User Agent
- Detalles de la acción

### Sanitización
- Inputs HTML sanitizados con `htmlspecialchars()`
- Validación de archivos subidos (tipo y tamaño)
- Prepared statements en todas las consultas SQL
- Validación de JSON en importación de configuraciones

---

## 🎨 Interfaz de Usuario

### Configuraciones
- **Diseño moderno** con cards por categoría
- **Color pickers visuales** para colores primario y secundario
- **Vista previa de logo** antes de guardar
- **Alertas de éxito/error** con Bootstrap
- **Tooltips descriptivos** en cada campo

### Reportes
- **Filtros colapsables** fáciles de usar
- **Gráficos interactivos** con Chart.js
- **Tarjetas de estadísticas** con iconos
- **Tablas responsivas** con scroll horizontal
- **Botones de exportación** claramente visibles
- **Impresión optimizada** (oculta elementos no necesarios)

---

## 🧪 Pruebas Recomendadas

### Módulo de Configuraciones
1. ✅ Acceder a `/configuraciones` sin error 403
2. ✅ Cambiar nombre del sitio y verificar que se guarda
3. ✅ Subir logotipo y verificar vista previa
4. ✅ Cambiar colores y ver actualización en tiempo real
5. ✅ Exportar configuración (JSON)
6. ✅ Importar configuración desde JSON
7. ✅ Restablecer a valores por defecto
8. ✅ Verificar auditoría de cambios

### Reporte de Gastos
1. ✅ Acceder a `/reportes/gastos` sin errores
2. ✅ Aplicar filtros (fecha, categoría, forma de pago)
3. ✅ Verificar que estadísticas se actualizan
4. ✅ Verificar gráficos de categoría y forma de pago
5. ✅ Verificar gráfico de tendencia mensual
6. ✅ Exportar a Excel y abrir archivo
7. ✅ Exportar a PDF y verificar formato
8. ✅ Imprimir reporte desde navegador

### Otros Reportes
1. ✅ Exportar inventario a Excel/PDF
2. ✅ Exportar servicios a Excel/PDF

---

## 📊 Capturas de Pantalla

### Módulo de Configuraciones
![Configuraciones General](docs/screenshots/configuraciones-general.png)
![Configuraciones Apariencia](docs/screenshots/configuraciones-apariencia.png)

### Reporte de Gastos
![Reporte Gastos](docs/screenshots/reporte-gastos.png)
![Exportación](docs/screenshots/exportacion-botones.png)

---

## 🔧 Solución de Problemas

### Error: "Class 'TCPDF' not found"
```bash
composer require tecnickcom/tcpdf
```

### Error: "Class 'PhpOffice\PhpSpreadsheet' not found"
```bash
composer require phpoffice/phpspreadsheet
```

### Error 403 persiste después de actualizar SQL
1. Cerrar sesión del sistema
2. Volver a iniciar sesión
3. Verificar que el rol tiene los permisos:
```sql
SELECT JSON_PRETTY(permisos) FROM roles WHERE nombre = 'Administrador';
```

### Gráficos no se muestran
Verificar que Chart.js está cargado en el layout:
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

### Upload de logo falla
Verificar permisos de carpeta:
```bash
chmod 755 public/uploads
chown www-data:www-data public/uploads
```

---

## 📝 Notas Técnicas

### Tecnologías Utilizadas
- **Backend**: PHP 7.4+
- **Base de datos**: MySQL 5.7+
- **PDF**: TCPDF 6.7
- **Excel**: PhpSpreadsheet 1.29+
- **Gráficos**: Chart.js 3.x
- **Frontend**: Bootstrap 5, Bootstrap Icons
- **Arquitectura**: MVC personalizado

### Compatibilidad
- ✅ PHP 7.4, 8.0, 8.1, 8.2
- ✅ MySQL 5.7, 8.0
- ✅ Chrome, Firefox, Safari, Edge (últimas versiones)

### Performance
- Consultas optimizadas con índices
- Vistas de base de datos para queries complejos
- Lazy loading de gráficos
- Paginación en listados

---

## 🎯 Checklist de Implementación Completado

- [x] **Módulo de Configuraciones**
  - [x] Nombre del sitio y logotipo
  - [x] Cambiar estilos principales de color
  - [x] Configuraciones globales recomendadas
  - [x] Fix error 403 FORBIDDEN

- [x] **Exportación Excel y PDF**
  - [x] Implementar ExcelExporter
  - [x] Implementar PdfExporter
  - [x] Botones de exportación en reportes
  - [x] Rutas de exportación configuradas

- [x] **Reporte de Gastos**
  - [x] Análisis de gastos por categoría
  - [x] Análisis por forma de pago
  - [x] Tendencias mensuales
  - [x] Filtros avanzados
  - [x] Exportación Excel/PDF
  - [x] Gráficos interactivos

- [x] **Base de Datos**
  - [x] Tabla configuraciones
  - [x] Actualización de permisos
  - [x] Vistas optimizadas
  - [x] Índices para performance

- [x] **Seguridad**
  - [x] Validación de permisos
  - [x] Auditoría de cambios
  - [x] Sanitización de inputs
  - [x] Prepared statements

---

## 📞 Soporte

Para preguntas o problemas relacionados con esta implementación, contactar:
- Email: admin@albercas.com
- GitHub: [Crear Issue](https://github.com/danjohn007/InventarioAlbercas/issues)

---

**Fecha de implementación:** 17 de febrero de 2026
**Versión del sistema:** 1.0.0
**Estado:** ✅ Completado y probado
