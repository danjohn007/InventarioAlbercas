# 🏊‍♂️ Sistema de Inventario y Gastos para Albercas

Sistema web completo de administración de inventario, gastos y servicios para empresas de mantenimiento, reparación e instalación de albercas.

> **✅ Última actualización (2026-02-14):** 
> - Se resolvió el error 403 - FORBIDDEN con validación robusta de permisos. Ver [SOLUCION_403.md](SOLUCION_403.md)
> - Se resolvió el error 403 en directorio /public. Ver [GUIA_RAPIDA.md](GUIA_RAPIDA.md)
> - Se implementó solución para error open_basedir con archivo .user.ini. Ver [SOLUCION_OPEN_BASEDIR.md](SOLUCION_OPEN_BASEDIR.md)
> - Se actualizaron rutas en documentación (inventario/3)
> - **NUEVO:** Se resolvió el error 404 en ruta de login. Ver [SOLUCION_404_LOGIN.md](SOLUCION_404_LOGIN.md)

## 📋 Características Principales

### 🔐 Autenticación y Seguridad
- Sistema de login con sesiones seguras
- Control de acceso basado en roles (Administrador, Supervisor, Técnico)
- Validación robusta de permisos con auditoría completa
- Encriptación de contraseñas con `password_hash()`
- Registro de auditoría de todas las acciones
- Protección contra SQL Injection y XSS

### 👥 Gestión de Usuarios
- CRUD completo de usuarios
- Asignación de roles y permisos
- Control de usuarios activos/inactivos
- Registro de último acceso

### 📦 Módulo de Inventario
- CRUD de productos con categorías (Químicos, Herramientas, Refacciones, Equipos)
- Control de stock en tiempo real
- Alertas de stock mínimo
- Registro de movimientos (entrada, salida, ajuste)
- Validación de stock negativo
- Trazabilidad completa de movimientos
- Gestión de proveedores

### 💰 Módulo de Gastos
- Registro detallado de gastos
- Categorías: Materiales, Gasolina, Viáticos, Mano de Obra, Servicios Externos, Mantenimiento
- Formas de pago: Efectivo, Tarjeta, Transferencia, Cheque
- Adjuntar comprobantes (PDF, JPG, PNG)
- Relación con servicios y clientes
- Filtros por fecha, categoría y forma de pago

### 🛠️ Módulo de Servicios
- Gestión de clientes
- Registro de servicios (Mantenimiento, Reparación, Instalación)
- Asignación de técnicos
- Estados: Pendiente, En Proceso, Completado, Cancelado
- Asignación de materiales desde inventario
- Cálculo automático de costos (Mano de obra + Materiales + Otros)
- Historial de servicios por cliente

### 📊 Reportes y Analíticas
- Dashboard con estadísticas en tiempo real
- Reporte de inventario actual con gráficas
- Reporte de gastos con análisis por categoría
- Reporte de servicios con métricas de desempeño
- Gráficas interactivas con Chart.js
- Exportación a PDF e impresión

## 🛠️ Stack Tecnológico

- **Backend:** PHP 7+ (puro, sin framework)
- **Base de Datos:** MySQL 5.7
- **Frontend:** HTML5, CSS3, JavaScript
- **UI Framework:** Bootstrap 5
- **Gráficas:** Chart.js
- **Arquitectura:** MVC (Modelo-Vista-Controlador)
- **Seguridad:** Sesiones, password_hash(), PDO preparado

## 📁 Estructura del Proyecto

```
InventarioAlbercas/
├── config/              # Configuración de la aplicación
│   ├── config.php      # Configuración general y URL base automática
│   └── database.php    # Conexión a base de datos
├── controllers/         # Controladores MVC
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── UsuariosController.php
│   ├── InventarioController.php
│   ├── GastosController.php
│   ├── ServiciosController.php
│   ├── ClientesController.php
│   └── ReportesController.php
├── models/             # Modelos (si se requieren)
├── views/              # Vistas
│   ├── layouts/       # Plantillas principales
│   ├── auth/          # Login
│   ├── dashboard/     # Dashboard
│   ├── usuarios/      # Usuarios
│   ├── inventario/    # Inventario y movimientos
│   ├── gastos/        # Gastos
│   ├── servicios/     # Servicios
│   ├── clientes/      # Clientes
│   ├── reportes/      # Reportes
│   └── errors/        # Páginas de error
├── public/             # Archivos públicos
│   ├── css/           # Estilos personalizados
│   ├── js/            # JavaScript
│   ├── img/           # Imágenes
│   └── uploads/       # Archivos subidos
├── utils/              # Utilidades
│   ├── Router.php     # Enrutador de URLs
│   └── Auth.php       # Gestión de autenticación
├── middlewares/        # Middlewares (si se requieren)
├── .htaccess          # Configuración Apache (URLs amigables)
├── .env.example       # Ejemplo de configuración
├── database.sql       # Script de base de datos
├── index.php          # Punto de entrada
├── test.php           # Test de conexión
└── README.md          # Este archivo
```

## 🚀 Instalación en Apache

### Requisitos Previos

- Apache 2.4+
- PHP 7.0+ con extensiones:
  - PDO
  - PDO_MySQL
  - JSON
  - FileInfo (para uploads)
- MySQL 5.7+
- mod_rewrite activado en Apache

### Pasos de Instalación

#### 1. Clonar o Descargar el Proyecto

```bash
# Clonar desde GitHub
git clone https://github.com/danjohn007/InventarioAlbercas.git

# O descargar y extraer el ZIP en tu directorio web
cd /var/www/html/InventarioAlbercas
# O en Windows: C:\xampp\htdocs\InventarioAlbercas
```

#### 2. Configurar Apache

Asegúrate de que mod_rewrite esté habilitado:

```bash
# En Linux/Ubuntu
sudo a2enmod rewrite
sudo systemctl restart apache2

# En Windows/XAMPP: ya viene habilitado por defecto
```

Si instalas en un subdirectorio, el sistema detectará automáticamente la URL base.

#### 3. Configurar la Base de Datos

**Opción A: Línea de comandos**
```bash
# Crear base de datos e importar
mysql -u root -p < database.sql
```

**Opción B: phpMyAdmin**
1. Accede a phpMyAdmin
2. Crea una nueva base de datos llamada `inventario_albercas`
3. Importa el archivo `database.sql`

#### 4. Configurar Variables de Entorno

Copia el archivo de ejemplo y edita las credenciales:

```bash
cp .env.example .env
nano .env  # o usa tu editor favorito
```

Edita las siguientes variables:

```env
# Configuración de Base de Datos
DB_HOST=localhost
DB_NAME=inventario_albercas
DB_USER=root
DB_PASS=tu_contraseña
DB_PORT=3306

# Configuración de la Aplicación
APP_NAME=Sistema de Inventario y Gastos
APP_ENV=production
APP_TIMEZONE=America/Mexico_City

# Configuración de Sesiones
SESSION_LIFETIME=7200
SESSION_NAME=INVENTARIO_SESSION
```

#### 5. Configurar Permisos

```bash
# En Linux/Ubuntu
sudo chown -R www-data:www-data /var/www/html/InventarioAlbercas
sudo chmod -R 755 /var/www/html/InventarioAlbercas
sudo chmod -R 775 /var/www/html/InventarioAlbercas/public/uploads

# En Windows/XAMPP: los permisos suelen estar bien por defecto
```

#### 6. Verificar la Instalación

Abre tu navegador y accede a:

```
http://localhost/InventarioAlbercas/test.php
```

Este archivo verificará:
- ✅ Versión de PHP y extensiones
- ✅ Configuración de URL base
- ✅ Conexión a la base de datos
- ✅ Tablas creadas correctamente
- ✅ Permisos de archivos

Si todo está en verde, ¡el sistema está listo!

#### 7. Acceder al Sistema

```
http://localhost/InventarioAlbercas/
```

## 🔑 Credenciales de Acceso

El sistema viene con 3 usuarios de prueba:

| Usuario | Contraseña | Rol | Permisos |
|---------|-----------|-----|----------|
| `admin` | `admin123` | Administrador | Control total del sistema |
| `supervisor` | `supervisor123` | Supervisor | Gestión de inventario, gastos y servicios |
| `tecnico` | `tecnico123` | Técnico | Consulta de servicios y registro de consumo |

**⚠️ IMPORTANTE:** Cambia estas contraseñas en producción.

## 🎯 Uso del Sistema

### Para Administradores
1. Gestionar usuarios y roles
2. Configurar categorías de productos y gastos
3. Gestionar proveedores
4. Acceso a todos los reportes
5. Eliminar registros

### Para Supervisores
1. Gestión completa de inventario
2. Registro y control de gastos
3. Crear y gestionar servicios
4. Asignar materiales a servicios
5. Consultar reportes

### Para Técnicos
1. Ver servicios asignados
2. Registrar consumo de materiales
3. Consultar inventario (solo lectura)
4. Registrar gastos de campo

## 📊 Módulos del Sistema

### Dashboard
- Resumen de estadísticas clave
- Productos con stock bajo
- Servicios pendientes
- Últimos gastos registrados
- Gráficas de tendencias

### Inventario
- Listado de productos con filtros
- Crear/Editar productos
- Registrar movimientos (entrada/salida/ajuste)
- Alertas de stock mínimo
- Historial de movimientos

### Gastos
- Registro de gastos con categorías
- Adjuntar comprobantes
- Filtros por fecha, categoría, forma de pago
- Relación con servicios y clientes

### Servicios
- Gestión de clientes
- Crear servicios con asignación de técnico
- Asignar materiales desde inventario
- Seguimiento de estado
- Cálculo automático de costos

### Reportes
- Inventario actual con análisis
- Gastos por período y categoría
- Servicios con métricas de desempeño
- Exportación e impresión

## 🔧 Configuración Avanzada

### URLs Amigables

El sistema usa `.htaccess` para URLs limpias:
```
/dashboard          → Dashboard
/inventario         → Inventario
/gastos             → Gastos
/servicios          → Servicios
/clientes           → Clientes
/reportes           → Reportes
```

### URL Base Automática

No necesitas configurar manualmente la URL base. El sistema la detecta automáticamente, funcionando tanto en:
- Raíz del dominio: `http://example.com/`
- Subdirectorios: `http://localhost/InventarioAlbercas/`

### Cambiar Zona Horaria

Edita en `.env`:
```env
APP_TIMEZONE=America/Mexico_City
```

Zonas horarias disponibles: [PHP Timezones](https://www.php.net/manual/es/timezones.php)

## 🛡️ Seguridad

### Buenas Prácticas Implementadas
- ✅ Contraseñas hasheadas con `password_hash()` (bcrypt)
- ✅ Consultas preparadas (PDO) para prevenir SQL Injection
- ✅ Escapado de HTML para prevenir XSS
- ✅ Validación de permisos en cada acción
- ✅ Registro de auditoría de todas las operaciones
- ✅ Protección de archivos sensibles (.env, .sql, logs)
- ✅ Sesiones seguras con configuración personalizada
- ✅ Validación de tipos de archivo en uploads

### Recomendaciones Adicionales
1. Cambia las contraseñas por defecto
2. Usa HTTPS en producción
3. Mantén PHP y MySQL actualizados
4. Realiza respaldos regulares de la base de datos
5. Limita el acceso a directorios sensibles

## 🐛 Solución de Problemas

### Error: "No se puede conectar a la base de datos"
- Verifica las credenciales en `.env`
- Asegúrate de que MySQL esté corriendo
- Verifica que la base de datos exista

### Error: "404 - Página no encontrada"
- Asegúrate de que mod_rewrite esté habilitado
- Verifica que `.htaccess` exista en la raíz
- Revisa los permisos de archivos

### URLs no funcionan correctamente
- Ejecuta `test.php` para ver la URL base detectada
- Verifica la configuración de Apache
- Asegúrate de que AllowOverride esté en "All"

### No se pueden subir archivos
- Verifica permisos de `public/uploads/`
- Revisa `upload_max_filesize` en php.ini
- Asegúrate de que la extensión fileinfo esté habilitada

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 👨‍💻 Desarrollo

### Creado con GitHub Copilot Agent
Este sistema fue desarrollado siguiendo las mejores prácticas de programación con la asistencia de GitHub Copilot Agent.

### Contribuciones
Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/NuevaCaracteristica`)
3. Commit tus cambios (`git commit -m 'Agrega nueva característica'`)
4. Push a la rama (`git push origin feature/NuevaCaracteristica`)
5. Abre un Pull Request

## 📞 Soporte

Para reportar problemas o solicitar nuevas características, por favor abre un issue en GitHub.

## 🙏 Agradecimientos

- Bootstrap 5 por el framework CSS
- Chart.js por las gráficas
- Bootstrap Icons por los iconos

---

**Desarrollado con ❤️ para la industria del mantenimiento de albercas**
