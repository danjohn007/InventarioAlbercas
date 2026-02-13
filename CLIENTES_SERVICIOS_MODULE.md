# Módulos de Clientes y Servicios

## Módulo 1: Clientes (Clients)

### Descripción
Sistema completo de gestión de clientes con funcionalidades CRUD, búsqueda y validación de datos.

### Controlador
**Ubicación:** `controllers/ClientesController.php`

**Métodos:**
- `index()` - Lista todos los clientes con paginación y búsqueda
- `crear()` - Muestra el formulario de creación
- `guardar()` - Guarda un nuevo cliente con validaciones
- `editar($id)` - Muestra el formulario de edición
- `actualizar()` - Actualiza los datos del cliente

### Vistas
**Ubicación:** `views/clientes/`

1. **index.php** - Listado de clientes
   - Búsqueda por nombre, apellidos, teléfono, email o ciudad
   - Paginación de 15 registros por página
   - Contador de servicios por cliente
   - Estados (Activo/Inactivo) con badges

2. **crear.php** - Formulario de creación
   - Campos: nombre*, apellidos, teléfono, email, dirección, ciudad, estado, código postal, RFC, notas
   - Validaciones en tiempo real

3. **editar.php** - Formulario de edición
   - Todos los campos editables
   - Campo de estado (Activo/Inactivo)
   - Muestra fecha de creación y última actualización

### Campos y Validaciones

| Campo | Tipo | Requerido | Validación |
|-------|------|-----------|------------|
| nombre | VARCHAR(100) | Sí | No vacío |
| apellidos | VARCHAR(100) | No | - |
| telefono | VARCHAR(20) | No | Formato: /^[0-9\-\+\(\)\s]{7,20}$/ |
| email | VARCHAR(100) | No | Formato válido de email |
| direccion | TEXT | No | - |
| ciudad | VARCHAR(50) | No | - |
| estado | VARCHAR(50) | No | - |
| codigo_postal | VARCHAR(10) | No | - |
| rfc | VARCHAR(20) | No | - |
| notas | TEXT | No | - |
| activo | TINYINT(1) | Sí | 0 o 1 |

### Permisos
- **leer** - Ver listado y búsqueda de clientes
- **crear** - Crear nuevos clientes
- **actualizar** - Editar clientes existentes
- **eliminar** - No implementado (se usa campo activo)

---

## Módulo 2: Servicios (Services)

### Descripción
Sistema completo de gestión de servicios con asignación de materiales, seguimiento de costos y estados, integración con inventario y clientes.

### Controlador
**Ubicación:** `controllers/ServiciosController.php`

**Métodos:**
- `index()` - Lista todos los servicios con filtros avanzados
- `crear()` - Muestra el formulario de creación
- `guardar()` - Guarda un nuevo servicio
- `ver($id)` - Muestra detalles completos del servicio
- `editar($id)` - Muestra el formulario de edición
- `actualizar()` - Actualiza el servicio
- `asignarMaterial($id)` - Formulario para asignar materiales
- `guardarMaterial()` - Asigna material y actualiza inventario
- `eliminarMaterial($id)` - Elimina material y devuelve al inventario

### Vistas
**Ubicación:** `views/servicios/`

1. **index.php** - Listado de servicios
   - Filtros por:
     - Estado (pendiente, en_proceso, completado, cancelado)
     - Técnico asignado
     - Rango de fechas
   - Badges de estado con colores
   - Iconos por tipo de servicio
   - Paginación de 15 registros

2. **crear.php** - Formulario de creación
   - Selección de cliente
   - Tipo de servicio
   - Asignación de técnico
   - Costos de mano de obra y otros gastos
   - Estado inicial

3. **ver.php** - Vista detallada
   - Información general del servicio
   - Datos del cliente
   - Materiales utilizados con tabla
   - Resumen de costos
   - Historial de servicios del cliente
   - Botones para editar y asignar materiales

4. **editar.php** - Formulario de edición
   - Todos los campos editables
   - Fechas de inicio y fin
   - Actualización de estado
   - Recálculo automático de totales

5. **asignar_material.php** - Asignación de materiales
   - Selector de productos con stock disponible
   - Validación de stock en tiempo real
   - Cálculo automático de costo total
   - JavaScript para validaciones

### Tipos de Servicio

| Valor | Etiqueta | Icono |
|-------|----------|-------|
| mantenimiento | Mantenimiento | wrench |
| reparacion | Reparación | hammer |
| instalacion | Instalación | gear-fill |
| otro | Otro | three-dots |

### Estados de Servicio

| Estado | Etiqueta | Color Badge | Descripción |
|--------|----------|-------------|-------------|
| pendiente | Pendiente | warning (amarillo) | Servicio programado, no iniciado |
| en_proceso | En Proceso | info (azul) | Servicio en curso |
| completado | Completado | success (verde) | Servicio finalizado |
| cancelado | Cancelado | secondary (gris) | Servicio cancelado |

### Cálculo de Costos

```
Total = Costo Mano de Obra + Costo Materiales + Otros Gastos

Donde:
- Costo Mano de Obra: Ingresado manualmente
- Costo Materiales: Suma automática de servicio_materiales
- Otros Gastos: Ingresado manualmente
```

### Asignación de Materiales

#### Proceso de Asignación
1. Usuario selecciona el producto del inventario
2. Sistema muestra stock disponible y costo unitario
3. Usuario ingresa cantidad deseada
4. Sistema valida que haya stock suficiente
5. Al confirmar:
   - Se crea registro en `servicio_materiales`
   - Se descuenta del inventario (UPDATE `productos.stock_actual`)
   - Se crea movimiento de salida en `inventario_movimientos`
   - Se recalcula `servicios.costo_materiales`
   - Se recalcula `servicios.total`
   - Se registra auditoría

#### Proceso de Eliminación
1. Usuario elimina material asignado
2. Sistema:
   - Elimina registro de `servicio_materiales`
   - Devuelve cantidad al inventario (UPDATE `productos.stock_actual`)
   - Crea movimiento de entrada en `inventario_movimientos`
   - Recalcula `servicios.costo_materiales`
   - Recalcula `servicios.total`
   - Registra auditoría

### Campos de la Tabla servicios

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| cliente_id | INT | FK a clientes |
| tipo_servicio | ENUM | Tipo del servicio |
| titulo | VARCHAR(200) | Título descriptivo |
| descripcion | TEXT | Descripción detallada |
| direccion_servicio | TEXT | Ubicación del servicio |
| fecha_programada | DATE | Fecha planeada |
| fecha_inicio | DATETIME | Fecha/hora real de inicio |
| fecha_fin | DATETIME | Fecha/hora real de fin |
| tecnico_id | INT | FK a usuarios (técnico) |
| estado | ENUM | Estado actual |
| costo_mano_obra | DECIMAL(10,2) | Costo de labor |
| costo_materiales | DECIMAL(10,2) | Auto-calculado |
| otros_gastos | DECIMAL(10,2) | Gastos adicionales |
| total | DECIMAL(10,2) | Suma de todos los costos |
| observaciones | TEXT | Notas adicionales |
| usuario_registro_id | INT | FK a usuarios (creador) |
| fecha_creacion | TIMESTAMP | Fecha de creación |
| fecha_actualizacion | TIMESTAMP | Última actualización |

### Tabla servicio_materiales

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único |
| servicio_id | INT | FK a servicios |
| producto_id | INT | FK a productos |
| cantidad | DECIMAL(10,2) | Cantidad utilizada |
| costo_unitario | DECIMAL(10,2) | Costo por unidad |
| costo_total | DECIMAL(10,2) | cantidad × costo_unitario |
| fecha_asignacion | DATETIME | Fecha de asignación |

### Permisos
- **leer** - Ver listado y detalles de servicios
- **crear** - Crear nuevos servicios
- **actualizar** - Editar servicios y asignar/eliminar materiales
- **eliminar** - No implementado

---

## Rutas

### Clientes
```
GET  /clientes                    - Listado (requiere: clientes.leer)
GET  /clientes/crear              - Formulario de creación (requiere: clientes.crear)
POST /clientes/guardar            - Guardar nuevo (requiere: clientes.crear)
GET  /clientes/editar/{id}        - Formulario de edición (requiere: clientes.actualizar)
POST /clientes/actualizar         - Guardar cambios (requiere: clientes.actualizar)
```

### Servicios
```
GET  /servicios                   - Listado (requiere: servicios.leer)
GET  /servicios/crear             - Formulario de creación (requiere: servicios.crear)
POST /servicios/guardar           - Guardar nuevo (requiere: servicios.crear)
GET  /servicios/ver/{id}          - Ver detalles (requiere: servicios.leer)
GET  /servicios/editar/{id}       - Formulario de edición (requiere: servicios.actualizar)
POST /servicios/actualizar        - Guardar cambios (requiere: servicios.actualizar)
GET  /servicios/asignar-material/{id}     - Formulario asignar (requiere: servicios.actualizar)
POST /servicios/guardar-material  - Guardar material (requiere: servicios.actualizar)
GET  /servicios/eliminar-material/{id}    - Eliminar material (requiere: servicios.actualizar)
```

---

## Integración con Otros Módulos

### Con Inventario
- Asignación de materiales descuenta automáticamente del stock
- Eliminación de materiales devuelve al stock
- Se registran movimientos de entrada/salida
- Auditoría completa de movimientos

### Con Usuarios
- Filtrado de técnicos (roles: Técnico, Supervisor, Administrador)
- Registro de usuario creador
- Control de permisos por rol

### Con Auditoría
- Todos los cambios se registran en tabla `auditoria`
- Incluye: creación, actualización, asignación y eliminación de materiales

---

## Características de UI/UX

### Bootstrap 5
- Diseño responsive
- Tarjetas (cards) para agrupación visual
- Tablas responsivas
- Formularios con validación
- Badges de estado con colores
- Iconos Bootstrap Icons

### JavaScript
- Validación de stock en tiempo real
- Cálculo automático de costos
- Actualización dinámica de campos

### Paginación
- 15 registros por página
- Navegación entre páginas
- Mantiene filtros aplicados

---

## Auditoría y Seguridad

### Auditoría
Todas las operaciones registran:
- Usuario que ejecuta la acción
- Tipo de acción (crear, actualizar, asignar_material, eliminar_material)
- Tabla afectada
- ID del registro
- Descripción detallada
- IP y User Agent
- Fecha/hora

### Validaciones
- Entrada de datos sanitizada
- Validación en servidor y cliente
- Prevención de SQL injection (uso de prepared statements)
- Control de permisos por rol
- Validación de stock antes de asignación

### Control de Acceso
- Autenticación requerida para todas las rutas
- Permisos específicos por módulo
- Verificación de permisos en controlador y vistas

---

## Casos de Uso

### Caso 1: Crear Cliente y Servicio
1. Registrar nuevo cliente en módulo Clientes
2. Crear servicio asignando al cliente
3. Asignar técnico responsable
4. Servicio queda en estado "Pendiente"

### Caso 2: Ejecutar Servicio con Materiales
1. Técnico cambia estado a "En Proceso"
2. Asigna materiales necesarios desde inventario
3. Sistema descuenta automáticamente del stock
4. Actualiza costos del servicio
5. Registra fecha de inicio
6. Al finalizar, cambia estado a "Completado"
7. Registra fecha de fin

### Caso 3: Consultar Historial de Cliente
1. Acceder a detalle de servicio
2. Ver sección "Historial de Servicios del Cliente"
3. Visualizar todos los servicios anteriores
4. Navegar a otros servicios del mismo cliente

---

## Mantenimiento y Soporte

### Logs de Error
Los errores se registran en el log de PHP con `error_log()`

### Mensajes de Usuario
- Mensajes de éxito en verde (Bootstrap success)
- Mensajes de error en rojo (Bootstrap danger)
- Información en azul (Bootstrap info)

### Base de Datos
Las tablas usan:
- InnoDB para soporte de transacciones
- UTF8MB4 para soporte de caracteres especiales
- Índices en campos de búsqueda frecuente
- Foreign keys para integridad referencial

---

## Futuras Mejoras Sugeridas

1. **Exportación de Reportes**
   - PDF de servicios por cliente
   - Excel de materiales utilizados
   - Reportes de costos

2. **Notificaciones**
   - Email a cliente cuando servicio se completa
   - Alertas de servicios próximos
   - Notificaciones de stock bajo

3. **Dashboard de Servicios**
   - Calendario de servicios
   - Estadísticas por técnico
   - Gráficas de costos

4. **Aplicación Móvil para Técnicos**
   - Check-in/check-out en servicios
   - Solicitud de materiales
   - Carga de fotos

5. **Integración de Pagos**
   - Registro de pagos parciales
   - Estado de cuenta por cliente
   - Facturación

---

## Conclusión

Los módulos de Clientes y Servicios proporcionan una solución completa para la gestión de servicios de albercas, con integración total al sistema de inventario, control de costos, seguimiento de estados y auditoría completa. La interfaz Bootstrap 5 garantiza una experiencia de usuario moderna y responsive.
