# 📦 Guía de Instalación - Sistema de Inventario

Esta guía detalla el proceso completo de instalación del sistema desde cero.

## 📋 Requisitos del Sistema

### Servidor Web
- **Apache** 2.4 o superior con `mod_rewrite` habilitado
- **PHP** 7.4 o superior (recomendado: PHP 8.x)
- **MySQL** 5.7 o superior (recomendado: MySQL 8.x o MariaDB 10.x)

### Extensiones de PHP Requeridas
- `pdo` y `pdo_mysql` - Conexión a base de datos
- `mbstring` - Manejo de caracteres multibyte
- `json` - Procesamiento de JSON
- `session` - Manejo de sesiones
- `gd` o `imagick` - Procesamiento de imágenes (opcional)
- `fileinfo` - Detección de tipos MIME

### Permisos del Servidor
- Escritura en directorio `public/uploads/`
- Lectura en todos los directorios del proyecto
- Ejecución de PHP en el directorio raíz

---

## 🚀 Instalación Paso a Paso

### Paso 1: Descargar el Código

#### Opción A: Clonar desde GitHub
```bash
cd /home1/fix360/public_html/
git clone https://github.com/danjohn007/InventarioAlbercas.git inventario
cd inventario
```

#### Opción B: Descargar ZIP
1. Descargar desde: https://github.com/danjohn007/InventarioAlbercas/archive/main.zip
2. Extraer en: `/home1/fix360/public_html/inventario/`

### Paso 2: Crear la Base de Datos

#### Usando cPanel (Recomendado)
1. Acceder a **cPanel** → **MySQL® Databases**
2. Crear nueva base de datos:
   - Nombre: `fix360_inventario`
3. Crear nuevo usuario:
   - Usuario: `fix360_inventario`
   - Contraseña: (generar una segura)
4. Asignar usuario a la base de datos con **todos los privilegios**

#### Usando phpMyAdmin
1. Acceder a phpMyAdmin
2. Crear nueva base de datos: `fix360_inventario`
3. Seleccionar la base de datos
4. Ir a pestaña **SQL**
5. Importar el archivo `database.sql`

#### Usando línea de comandos
```bash
mysql -u root -p
CREATE DATABASE fix360_inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fix360_inventario'@'localhost' IDENTIFIED BY 'tu_contraseña_segura';
GRANT ALL PRIVILEGES ON fix360_inventario.* TO 'fix360_inventario'@'localhost';
FLUSH PRIVILEGES;
EXIT;

mysql -u fix360_inventario -p fix360_inventario < database.sql
```

### Paso 3: Configurar Variables de Entorno

1. Copiar el archivo de ejemplo:
```bash
cp .env.example .env
```

2. Editar el archivo `.env`:
```ini
# Configuración de Base de Datos
DB_HOST=localhost
DB_NAME=fix360_inventario
DB_USER=fix360_inventario
DB_PASS=tu_contraseña_aqui
DB_PORT=3306

# Configuración de la Aplicación
APP_NAME=Sistema de Inventario y Gastos
APP_ENV=production
APP_TIMEZONE=America/Mexico_City

# Configuración de Sesiones
SESSION_LIFETIME=7200
SESSION_NAME=INVENTARIO_SESSION
```

**IMPORTANTE:** 
- Nunca subir el archivo `.env` al repositorio Git
- Usar contraseñas seguras en producción
- El archivo `.env` debe tener permisos `600` (solo lectura del propietario)

### Paso 4: Configurar Permisos de Archivos

```bash
# Permisos para directorios
find . -type d -exec chmod 755 {} \;

# Permisos para archivos PHP
find . -type f -name "*.php" -exec chmod 644 {} \;

# Permisos restrictivos para archivos sensibles
chmod 600 .env
chmod 600 .user.ini

# Permisos de escritura para uploads
chmod 755 public/uploads/
chmod 644 public/uploads/.htaccess

# Permisos para logs (si se crean)
touch error.log
chmod 666 error.log
```

### Paso 5: Configurar open_basedir (si es necesario)

El archivo `.user.ini` ya está incluido con la configuración correcta:

```ini
open_basedir = "/home1/fix360:/tmp"
```

**Si el error persiste:**

#### Opción A: Modificar en cPanel
1. Ir a **MultiPHP INI Editor**
2. Seleccionar el dominio
3. Buscar `open_basedir`
4. Cambiar a: `/home1/fix360:/tmp`
5. Guardar cambios

#### Opción B: Crear .htaccess local (si PHP como Apache module)
```apache
<IfModule mod_php.c>
    php_admin_value open_basedir "/home1/fix360:/tmp"
</IfModule>
```

### Paso 6: Verificar la Instalación

#### 6.1 Ejecutar Health Check
Visitar en el navegador:
```
http://tu-dominio.com/health-check.php
```

Este script verificará:
- ✅ Versión de PHP
- ✅ Extensiones requeridas
- ✅ Permisos de archivos
- ✅ Configuración de open_basedir
- ✅ Conexión a base de datos
- ✅ Existencia de tablas

**IMPORTANTE:** Eliminar `health-check.php` después de verificar

#### 6.2 Acceder al Sistema
```
http://tu-dominio.com/
```

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

**⚠️ CAMBIAR CONTRASEÑA INMEDIATAMENTE DESPUÉS DEL PRIMER LOGIN**

### Paso 7: Configuración Post-Instalación

#### 7.1 Cambiar contraseña del administrador
1. Login con credenciales por defecto
2. Ir a **Usuarios** → **Lista de Usuarios**
3. Editar usuario `admin`
4. Cambiar contraseña
5. Guardar cambios

#### 7.2 Crear usuarios adicionales
1. Ir a **Usuarios** → **Nuevo Usuario**
2. Llenar formulario con datos del usuario
3. Asignar rol apropiado:
   - **Administrador:** Acceso total
   - **Supervisor:** Gestión de inventario y servicios
   - **Técnico:** Solo consulta y servicios asignados
4. Guardar

#### 7.3 Configurar categorías de productos
Las categorías ya están creadas por defecto:
- Químicos
- Herramientas
- Refacciones
- Equipos

#### 7.4 Agregar productos al inventario
1. Ir a **Inventario** → **Nuevo Producto**
2. Llenar información del producto
3. Establecer stock mínimo para alertas
4. Guardar

---

## 🔧 Solución de Problemas Comunes

### Error: open_basedir restriction
**Síntoma:** `open_basedir restriction in effect`

**Solución:** Ver [SOLUCION_OPEN_BASEDIR.md](SOLUCION_OPEN_BASEDIR.md)

### Error: 403 Forbidden
**Síntoma:** Error 403 al acceder al sistema

**Solución:**
1. Verificar permisos de archivos: `chmod 644 index.php`
2. Verificar permisos de directorio: `chmod 755 .`
3. Ver [SOLUCION_403.md](SOLUCION_403.md)

### Error: Connection refused (Base de datos)
**Síntoma:** No se puede conectar a la base de datos

**Solución:**
1. Verificar que MySQL está corriendo
2. Verificar credenciales en `.env`
3. Verificar que el usuario tiene permisos en la base de datos
4. Ejecutar: `mysql -u fix360_inventario -p` para probar conexión

### Error: Page not found (404)
**Síntoma:** Todas las páginas excepto index.php dan 404

**Solución:**
1. Verificar que `mod_rewrite` está habilitado
2. Verificar que `.htaccess` existe y es legible
3. En cPanel: **Apache Handlers** → Verificar que `.htaccess` está permitido

### Sesión expira muy rápido
**Solución:**
1. Editar `.env`
2. Aumentar `SESSION_LIFETIME=7200` (en segundos)
3. Reiniciar servidor web

### No se pueden subir archivos
**Síntoma:** Error al subir comprobantes de gastos

**Solución:**
1. Verificar permisos: `chmod 755 public/uploads/`
2. Verificar límites de PHP en `.user.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```
3. Reiniciar PHP-FPM

---

## 🔒 Seguridad en Producción

### Checklist de Seguridad

- [ ] **Cambiar contraseña por defecto** del usuario admin
- [ ] **Eliminar archivos de diagnóstico**
  - `health-check.php`
  - `diagnostico.php`
  - `test.php`
- [ ] **Proteger archivos sensibles** (verificar `.htaccess`)
  - `.env` no accesible
  - `.user.ini` no accesible
  - `database.sql` no accesible
- [ ] **Configurar certificado SSL/TLS** (HTTPS)
- [ ] **Configurar backups automáticos**
  - Base de datos: diario
  - Archivos: semanal
- [ ] **Actualizar credenciales de base de datos**
- [ ] **Configurar logs de error**
  ```ini
  error_log = /home1/fix360/logs/php_errors.log
  ```
- [ ] **Deshabilitar display_errors en producción**
  ```ini
  display_errors = Off
  error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
  ```
- [ ] **Limitar intentos de login** (ya implementado)
- [ ] **Revisar permisos de usuarios**

### Headers de Seguridad
Ya incluidos en `.htaccess`:
- `X-Frame-Options: SAMEORIGIN` - Previene Clickjacking
- `X-XSS-Protection: 1; mode=block` - Protección XSS
- `X-Content-Type-Options: nosniff` - Previene MIME sniffing
- `Referrer-Policy` - Control de información de referrer

---

## 📊 Mantenimiento

### Respaldo de Base de Datos

#### Backup manual
```bash
mysqldump -u fix360_inventario -p fix360_inventario > backup_$(date +%Y%m%d).sql
```

#### Backup automático (cron)
```bash
# Editar crontab
crontab -e

# Agregar línea para backup diario a las 2 AM
0 2 * * * mysqldump -u fix360_inventario -p'contraseña' fix360_inventario > /home1/fix360/backups/db_$(date +\%Y\%m\%d).sql
```

### Limpieza de Logs

```bash
# Limpiar error.log cada semana
0 0 * * 0 > /home1/fix360/public_html/inventario/error.log
```

### Actualización del Sistema

```bash
cd /home1/fix360/public_html/inventario
git pull origin main
# Verificar cambios en database.sql y aplicarlos si es necesario
```

---

## 📞 Soporte

### Documentación Adicional
- [README.md](README.md) - Descripción general
- [GUIA_RAPIDA.md](GUIA_RAPIDA.md) - Guía rápida de errores
- [SOLUCION_OPEN_BASEDIR.md](SOLUCION_OPEN_BASEDIR.md) - Solución open_basedir
- [SOLUCION_403.md](SOLUCION_403.md) - Solución error 403
- [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - Resumen del proyecto

### Recursos
- Repositorio: https://github.com/danjohn007/InventarioAlbercas
- Documentación PHP: https://www.php.net/manual/es/
- Documentación MySQL: https://dev.mysql.com/doc/

---

## ✅ Verificación Final

Después de completar la instalación, verificar:

1. [ ] Sistema accesible en navegador
2. [ ] Login funciona correctamente
3. [ ] Dashboard muestra estadísticas
4. [ ] Se pueden crear usuarios
5. [ ] Se pueden agregar productos al inventario
6. [ ] Se pueden registrar gastos
7. [ ] Se pueden crear clientes
8. [ ] Se pueden crear servicios
9. [ ] Reportes se generan correctamente
10. [ ] Archivos se pueden subir (comprobantes)
11. [ ] No hay errores en error.log
12. [ ] health-check.php muestra todo en verde
13. [ ] Archivos de diagnóstico eliminados

---

**Fecha de actualización:** 2026-02-14  
**Versión del sistema:** 1.0  
**Estado:** Producción Ready ✅
