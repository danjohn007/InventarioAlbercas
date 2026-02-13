# Módulo de Reportes (Reports Module)

## Descripción General

El módulo de Reportes proporciona análisis completos y visualizaciones de datos del sistema de inventario de albercas. Incluye reportes detallados de inventario, gastos y servicios con filtros personalizables, gráficos interactivos y opciones de exportación.

## Archivos Creados

### Controlador
- **controllers/ReportesController.php** - Controlador principal que maneja todos los reportes

### Vistas
- **views/reportes/index.php** - Dashboard central de reportes
- **views/reportes/inventario.php** - Reporte de inventario
- **views/reportes/gastos.php** - Reporte de gastos
- **views/reportes/servicios.php** - Reporte de servicios

## Características Principales

### 1. Centro de Reportes (Dashboard)
- **Ruta:** `/reportes`
- **Método:** `ReportesController::index()`
- **Características:**
  - Tarjetas visuales para cada tipo de reporte
  - Enlaces directos a cada reporte
  - Diseño moderno con gradientes y animaciones
  - Información sobre las capacidades de cada reporte

### 2. Reporte de Inventario
- **Ruta:** `/reportes/inventario`
- **Método:** `ReportesController::inventario()`
- **Características:**
  - **Estadísticas Principales:**
    - Total de productos
    - Total de unidades en stock
    - Valor total del inventario
    - Productos con bajo stock
  
  - **Filtros:**
    - Por categoría
    - Ordenamiento (nombre, stock, precio)
  
  - **Visualizaciones:**
    - Gráfico de dona: Valor por categoría
    - Gráfico de barras: Stock por categoría
  
  - **Tablas:**
    - Alertas de stock bajo (top 10)
    - Detalle completo de inventario con SKU, precios y valores
  
  - **Alertas:**
    - Identificación visual de productos sin stock
    - Productos bajo el stock mínimo

### 3. Reporte de Gastos
- **Ruta:** `/reportes/gastos`
- **Método:** `ReportesController::gastos()`
- **Características:**
  - **Estadísticas Principales:**
    - Total de gastos registrados
    - Monto total gastado
    - Promedio de gasto
    - Gasto máximo
  
  - **Filtros:**
    - Rango de fechas (desde/hasta)
    - Por categoría de gasto
    - Por forma de pago
  
  - **Visualizaciones:**
    - Gráfico circular: Gastos por categoría
    - Gráfico de dona: Gastos por forma de pago
    - Gráfico de línea: Tendencia mensual
  
  - **Tablas:**
    - Resumen por categoría con porcentajes
    - Top 10 gastos más grandes
  
  - **Análisis:**
    - Distribución porcentual por categoría
    - Comparación de formas de pago
    - Tendencias temporales

### 4. Reporte de Servicios
- **Ruta:** `/reportes/servicios`
- **Método:** `ReportesController::servicios()`
- **Características:**
  - **Estadísticas Principales:**
    - Total de servicios
    - Ingresos totales generados
    - Costo promedio por servicio
    - Servicios en proceso
  
  - **Dashboard de Estados:**
    - Servicios pendientes
    - Servicios en proceso
    - Servicios completados
    - Servicios cancelados
  
  - **Filtros:**
    - Rango de fechas
    - Por estado del servicio
    - Por técnico asignado
  
  - **Visualizaciones:**
    - Gráfico de dona: Servicios por estado
    - Gráfico circular: Desglose de costos
    - Gráfico dual: Tendencia mensual (cantidad e ingresos)
  
  - **Análisis de Rendimiento:**
    - Tabla de rendimiento por técnico
    - Tasa de éxito de cada técnico
    - Ingresos generados por técnico
  
  - **Desglose de Costos:**
    - Mano de obra
    - Materiales
    - Desplazamiento
    - Porcentajes del total
  
  - **Top Clientes:**
    - Top 10 clientes por servicios
    - Total gastado por cliente
    - Promedio por servicio

## Funcionalidades Técnicas

### Permisos y Seguridad
- Todos los métodos requieren permiso de lectura: `Auth::requirePermission('reportes', 'leer')`
- Integración con el sistema de autenticación existente
- Validación de permisos en la barra lateral del menú

### Filtros y Consultas
- Consultas SQL optimizadas con parámetros preparados
- Filtros dinámicos que se preservan entre páginas
- Agrupaciones y agregaciones eficientes

### Visualizaciones con Chart.js
- Gráficos interactivos y responsivos
- Tipos de gráficos utilizados:
  - Dona (Doughnut)
  - Circular (Pie)
  - Barras (Bar)
  - Líneas (Line)
  - Múltiples ejes Y
- Tooltips personalizados con formato de moneda
- Colores temáticos coordinados

### Diseño Responsivo
- Bootstrap 5 para diseño adaptable
- Optimizado para escritorio, tablet y móvil
- Tarjetas de estadísticas con íconos
- Tablas responsivas con scroll horizontal

### Funcionalidad de Impresión
- CSS específico para impresión (`@media print`)
- Oculta elementos de navegación y filtros al imprimir
- Optimiza diseño para papel
- Clase `.no-print` para excluir elementos

### Exportación (Preparado)
- Botones de exportación a PDF
- Botones de exportación a Excel
- Funcionalidad preparada para implementación futura
- Alertas informativas cuando se presionan

## Integración con el Sistema

### Menú de Navegación
El módulo se integra automáticamente en el menú lateral si el usuario tiene permisos:

```php
<?php if (Auth::can('reportes', 'leer')): ?>
<li class="nav-item">
    <a href="<?php echo BASE_URL; ?>reportes" class="nav-link <?php echo $activeMenu == 'reportes' ? 'active' : ''; ?>">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Reportes</span>
    </a>
</li>
<?php endif; ?>
```

### Rutas
El enrutador debe configurarse para mapear las siguientes rutas:
- `reportes` → `ReportesController::index()`
- `reportes/inventario` → `ReportesController::inventario()`
- `reportes/gastos` → `ReportesController::gastos()`
- `reportes/servicios` → `ReportesController::servicios()`

## Dependencias

### Backend
- PHP 7.4+
- PDO para consultas de base de datos
- Clases existentes: `Auth`, `Database`

### Frontend
- Bootstrap 5.3.0
- Bootstrap Icons 1.11.0
- Chart.js 4.4.0
- JavaScript ES6+

### Base de Datos
El módulo utiliza las siguientes tablas:
- `productos`
- `categorias`
- `gastos`
- `gastos_categorias`
- `servicios`
- `clientes`
- `usuarios`

## Estilos y Diseño

### Colores Temáticos
- **Primary (Azul-Púrpura):** #667eea → #764ba2
- **Success (Verde):** #43e97b → #38f9d7
- **Info (Azul Claro):** #4facfe → #00f2fe
- **Warning (Amarillo):** #ffc107
- **Danger (Rojo):** #dc3545

### Componentes Visuales
- Tarjetas con gradientes (report-card)
- Íconos grandes para reportes (report-icon)
- Tarjetas de estadísticas con íconos (stat-card)
- Barras de progreso para porcentajes
- Badges de estado con colores

## Uso

### Acceso al Dashboard de Reportes
1. Iniciar sesión con un usuario que tenga permisos de `reportes.leer`
2. Hacer clic en "Reportes" en el menú lateral
3. Seleccionar el tipo de reporte deseado

### Filtrar Datos
1. En cada reporte, usar el formulario de filtros en la parte superior
2. Seleccionar criterios (fechas, categorías, técnicos, etc.)
3. Hacer clic en el botón "Filtrar"
4. Los resultados se actualizarán automáticamente

### Imprimir Reportes
1. Abrir el reporte deseado
2. Hacer clic en el botón "Imprimir"
3. Los filtros y navegación se ocultarán automáticamente
4. Usar la función de impresión del navegador

### Exportar Datos (Futuro)
1. Los botones de exportación están preparados
2. Implementación futura añadirá funcionalidad real
3. Generación de PDF con TCPDF o similar
4. Generación de Excel con PhpSpreadsheet

## Mejoras Futuras

### Funcionalidades Pendientes
1. **Exportación Real:**
   - Implementar generación de PDF
   - Implementar generación de Excel
   - Incluir gráficos en exportaciones

2. **Reportes Adicionales:**
   - Reporte de clientes
   - Reporte de rentabilidad
   - Comparativas año a año
   - Análisis predictivo

3. **Filtros Avanzados:**
   - Guardado de filtros favoritos
   - Comparación de períodos
   - Filtros combinados más complejos

4. **Envío por Email:**
   - Programar envío automático de reportes
   - Suscripciones a reportes
   - Alertas personalizadas

5. **Interactividad:**
   - Drill-down en gráficos
   - Filtrado dinámico desde gráficos
   - Dashboard personalizable

## Solución de Problemas

### Error: "No tienes permisos"
- Verificar que el usuario tenga el permiso `reportes.leer`
- Revisar la tabla `permisos` y `roles_permisos` en la base de datos

### Gráficos no se muestran
- Verificar que Chart.js esté cargando correctamente
- Abrir la consola del navegador para ver errores
- Verificar que los datos existan en la base de datos

### Datos incorrectos o vacíos
- Verificar las consultas SQL en el controlador
- Asegurar que existan datos en las tablas correspondientes
- Revisar los filtros aplicados

### Problemas de Impresión
- Verificar estilos CSS de `@media print`
- Probar en diferentes navegadores
- Ajustar configuración de impresión del navegador

## Mantenimiento

### Actualización de Consultas
- Las consultas SQL están en el controlador
- Modificar según cambios en el esquema de base de datos
- Mantener consultas optimizadas y con índices

### Actualización de Visualizaciones
- Configuración de Chart.js en cada vista
- Actualizar versiones de bibliotecas cuando sea necesario
- Mantener consistencia en colores y estilos

### Optimización de Rendimiento
- Añadir paginación si los reportes crecen mucho
- Implementar caché para consultas frecuentes
- Considerar vistas materializadas para datos agregados

## Conclusión

El módulo de Reportes proporciona una solución completa para análisis de datos del sistema de inventario de albercas. Con visualizaciones interactivas, filtros flexibles y diseño profesional, permite a los usuarios obtener insights valiosos sobre inventario, gastos y servicios de manera eficiente y atractiva.
