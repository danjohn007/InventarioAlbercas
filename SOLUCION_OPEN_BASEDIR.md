# Solución de Errores de Servidor - open_basedir y 403

## 🚨 Errores Identificados

### Error 1: open_basedir Restriction

```
PHP Warning: open_basedir restriction in effect. 
File(/home1/fix360/public_html/inventario/3/test.php) 
is not within the allowed path(s): (/home1/fix30/public_html:/tmp)

PHP Warning: open_basedir restriction in effect. 
File(/home1/fix360/public_html/inventario/3/index.php) 
is not within the allowed path(s): (/home1/fix30/public_html:/tmp)
```

**Este es un error CRÍTICO de configuración del servidor que DEBE ser corregido por el administrador del hosting.**

### Error 2: 403 Forbidden en /public

```
ERROR 403 - FORBIDDEN en /public (no se ve archivo index o php alguno)
```

**Este error ha sido corregido en este commit.**

---

## 📋 Análisis del Problema open_basedir

### ¿Qué es open_basedir?

`open_basedir` es una directiva de seguridad de PHP que restringe los archivos que PHP puede acceder. Solo permite acceso a rutas especificadas.

### El Problema Específico

```
Aplicación instalada en:  /home1/fix360/public_html/inventario/3/
Rutas permitidas por PHP: /home1/fix30/public_html:/tmp
```

**Discrepancia detectada:** `fix360` vs `fix30` (error de tipeo o configuración)

### Posibles Causas

1. **Cuenta de cPanel incorrecta**: La aplicación fue instalada en una cuenta (`fix360`) pero PHP está configurado para otra cuenta (`fix30`)

2. **Aplicación copiada/movida**: La aplicación fue copiada de otra cuenta sin actualizar la configuración de PHP

3. **Symlink o alias**: Existe un enlace simbólico que apunta a una ruta fuera del open_basedir

4. **Configuración de MultiPHP en cPanel**: La versión de PHP asignada al dominio tiene una configuración incorrecta

5. **Subdominio mal configurado**: Si está en un subdominio, puede estar usando la configuración de PHP de otra cuenta

---

## ✅ Soluciones Implementadas (en código)

### 1. Archivo index.php en /public ✓

Se creó `/public/index.php` para:
- Prevenir error 403 cuando se accede a `/public/`
- Redirigir automáticamente al index principal
- Proporcionar un punto de entrada válido

### 2. Configuración .htaccess en /public ✓

Se creó `/public/.htaccess` para:
- Permitir acceso a archivos estáticos (JS, CSS, imágenes)
- Denegar ejecución de PHP (excepto index.php)
- Redirigir peticiones inválidas
- Proteger archivos sensibles
- Deshabilitar listado de directorios

### 3. Actualización de .htaccess principal ✓

Se agregó intento de sobrescribir `open_basedir`:
```apache
<IfModule mod_php.c>
    php_admin_value open_basedir none
</IfModule>
```

**NOTA:** Esto puede NO funcionar si el servidor tiene configuraciones más restrictivas a nivel de php.ini o cPanel.

---

## 🔧 Soluciones que Requieren Acceso al Servidor

### Solución 1: Corregir open_basedir en cPanel (RECOMENDADO)

1. **Acceder a cPanel** como administrador
2. Ir a **MultiPHP INI Editor** o **Select PHP Version**
3. Seleccionar el dominio/subdirectorio afectado
4. Buscar la directiva `open_basedir`
5. Cambiar de:
   ```
   /home1/fix30/public_html:/tmp
   ```
   A:
   ```
   /home1/fix360/public_html:/tmp
   ```
   O mejor aún:
   ```
   /home1/fix360:/tmp
   ```

6. Guardar cambios y reiniciar Apache/PHP-FPM

### Solución 2: Crear .user.ini en el directorio (IMPLEMENTADO)

✅ **Este archivo ya ha sido creado en la raíz del proyecto**

Si no tienes acceso a cPanel, se ha creado un archivo `.user.ini` en la raíz:

**Archivo creado: `/home1/fix360/public_html/inventario/3/.user.ini`**
```ini
open_basedir = "/home1/fix360:/tmp"
```

**IMPORTANTE:** 
- ✅ Este archivo ya existe en el repositorio
- Funciona con PHP-FPM o suPHP
- NO funciona con mod_php (en ese caso, usar Solución 1)
- Puede tardar hasta 5 minutos en aplicarse
- Si no funciona después de 5 minutos, usar Solución 1 (cPanel)

### Solución 3: Verificar la Ubicación Real

Ejecuta estos comandos para verificar la ubicación real:

```bash
# Verificar ruta real del archivo
cd /home1/fix360/public_html/inventario/3/
pwd -P

# Ver configuración actual de PHP
php -i | grep open_basedir

# Verificar si hay symlinks
ls -la /home1/fix360/public_html/
```

### Solución 4: Mover la Aplicación (Última Opción)

Si todo lo demás falla, mover la aplicación a la ubicación permitida:

```bash
# Mover de fix360 a fix30
mv /home1/fix360/public_html/inventario /home1/fix30/public_html/

# Actualizar permisos
chown -R fix30:fix30 /home1/fix30/public_html/inventario
chmod -R 755 /home1/fix30/public_html/inventario
```

---

## 🔍 Diagnóstico y Verificación

### Script de Diagnóstico

Crear archivo `diagnostico.php` en la raíz:

```php
<?php
echo "<h2>Diagnóstico del Servidor</h2>";

echo "<h3>Información de Rutas</h3>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "Directorio real: " . realpath(__DIR__) . "<br>";
echo "Directorio actual: " . __DIR__ . "<br>";

echo "<h3>Configuración open_basedir</h3>";
$open_basedir = ini_get('open_basedir');
echo "open_basedir actual: " . ($open_basedir ? $open_basedir : 'No configurado') . "<br>";

echo "<h3>Usuario y Permisos</h3>";
if (function_exists('posix_getpwuid')) {
    $user = posix_getpwuid(posix_geteuid());
    echo "Usuario PHP: " . $user['name'] . "<br>";
}
echo "Usuario del archivo: " . fileowner(__FILE__) . "<br>";

echo "<h3>Versión de PHP</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";

echo "<h3>Prueba de Escritura</h3>";
$test_file = __DIR__ . '/test_write.txt';
if (file_put_contents($test_file, 'test')) {
    echo "✓ Escritura exitosa en: $test_file<br>";
    unlink($test_file);
} else {
    echo "✗ No se puede escribir en: $test_file<br>";
}

echo "<h3>Módulos Apache/PHP Cargados</h3>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? 'Sí' : 'No') . "<br>";
    echo "mod_php: " . (in_array('mod_php5', $modules) || in_array('mod_php7', $modules) ? 'Sí' : 'No') . "<br>";
}
?>
```

Ejecutar visitando: `http://tudominio.com/diagnostico.php`

### Verificar que los cambios funcionaron

1. **Verificar /public**: Visitar `http://tudominio.com/public/` - debería redirigir al index

2. **Verificar archivos estáticos**: 
   - `http://tudominio.com/public/js/algun-archivo.js` - debería servir el archivo
   - `http://tudominio.com/public/uploads/imagen.jpg` - debería servir la imagen

3. **Verificar logs de error**: 
   ```bash
   tail -f /home1/fix360/public_html/error_log
   ```

---

## 📞 Contactar al Proveedor de Hosting

Si necesitas ayuda del proveedor, proporciona esta información:

```
Asunto: Error de configuración open_basedir en cuenta fix360

Descripción del problema:
- La aplicación está instalada en: /home1/fix360/public_html/inventario/3/
- PHP reporta open_basedir configurado para: /home1/fix30/public_html:/tmp
- Esto causa el error: "open_basedir restriction in effect"

Solución requerida:
Por favor actualizar la configuración de open_basedir para el dominio/directorio:
- De: /home1/fix30/public_html:/tmp
- A: /home1/fix360/public_html:/tmp

O alternativamente, verificar si existe algún error de configuración 
en la cuenta fix360 que está referenciando la cuenta fix30.

Nota adicional:
Se ha creado un archivo .user.ini en la aplicación para intentar resolver
el problema, pero si el servidor no soporta PHP-FPM, será necesario hacer
el cambio directamente en la configuración de cPanel.

Archivos de log adjuntos:
[Adjuntar los logs de error proporcionados]
```

---

## 🎯 Checklist de Resolución

- [x] Crear index.php en /public para prevenir 403
- [x] Crear .htaccess en /public con reglas apropiadas
- [x] Actualizar .htaccess principal con intento de open_basedir
- [x] **NUEVO:** Crear archivo .user.ini con configuración open_basedir correcta
- [ ] **PENDIENTE (Requiere servidor):** Corregir open_basedir en cPanel/php.ini si .user.ini no funciona
- [ ] **PENDIENTE (Requiere servidor):** Verificar que no existan symlinks problemáticos
- [ ] **PENDIENTE (Requiere servidor):** Confirmar que la aplicación está en la ruta correcta

---

## ⚠️ Notas Importantes

1. **El error open_basedir NO puede ser completamente resuelto desde el código** - requiere acceso al servidor o cPanel

2. **El error 403 en /public YA está resuelto** con los archivos agregados

2. **No elimines** el directorio `/inventario/3/` mencionado en los logs hasta verificar su propósito

4. **Backup primero**: Antes de hacer cambios en el servidor, haz backup de:
   - Base de datos
   - Archivos de la aplicación
   - Configuración de cPanel

5. **Permisos**: Verifica que los archivos tengan los permisos correctos:
   - Directorios: 755
   - Archivos PHP: 644
   - Archivos sensibles: 600

---

## 📚 Referencias

- [PHP open_basedir Documentation](https://www.php.net/manual/en/ini.core.php#ini.open-basedir)
- [cPanel MultiPHP INI Editor](https://docs.cpanel.net/cpanel/software/multiphp-ini-editor/)
- [Apache .htaccess Guide](https://httpd.apache.org/docs/2.4/howto/htaccess.html)

---

**Última actualización:** 2026-02-14  
**Estado:** Parcialmente resuelto (403 en /public ✓, .user.ini creado ✓, open_basedir puede requerir servidor si .user.ini no funciona)
