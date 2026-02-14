# Guía Rápida - Resolución de Errores del Servidor

## 📋 Resumen de Errores

### ✅ Error 1: 403 en /public - **RESUELTO**
**Antes:**
```
ERROR 403 - FORBIDDEN en /public (no se ve archivo index o php alguno)
```

**Solución aplicada:**
- ✅ Creado `public/index.php` para redirigir al index principal
- ✅ Creado `public/.htaccess` con reglas de acceso apropiadas
- ✅ El error 403 en /public ya NO debería aparecer

### ⚠️ Error 2: open_basedir - **REQUIERE SERVIDOR**
**Error:**
```
PHP Warning: open_basedir restriction in effect. 
File(/home1/fix360/public_html/inventario/2/index.php) 
is not within the allowed path(s): (/home1/fix30/public_html:/tmp)
```

**Problema:** Discrepancia entre `fix360` y `fix30`

**Solución:** Debe ser corregido por el administrador del servidor en cPanel

---

## 🚀 Pasos Inmediatos

### 1. Verificar que el 403 está resuelto
Visitar en el navegador:
```
http://tudominio.com/public/
```
**Resultado esperado:** Redirige automáticamente al index principal

### 2. Ejecutar diagnóstico del servidor
Visitar en el navegador:
```
http://tudominio.com/diagnostico.php
```

Este script te mostrará:
- ✅ Si open_basedir está correctamente configurado
- ✅ Información de rutas y permisos
- ✅ Configuración de PHP
- ✅ Problemas detectados

**⚠️ IMPORTANTE:** Eliminar `diagnostico.php` después de usarlo por seguridad

### 3. Corregir open_basedir en cPanel

Si tienes acceso a cPanel:

1. **Login a cPanel** → Tu cuenta de hosting
2. Ir a **"Software"** → **"MultiPHP INI Editor"**
3. Seleccionar el dominio/directorio afectado
4. Buscar la línea `open_basedir`
5. Cambiar de:
   ```
   /home1/fix30/public_html:/tmp
   ```
   A:
   ```
   /home1/fix360/public_html:/tmp
   ```
6. Guardar cambios
7. Esperar 1-2 minutos para que se aplique

### 4. Si NO tienes acceso a cPanel

**Opción A:** Crear archivo `.user.ini` en el directorio de la aplicación:

```bash
cd /home1/fix360/public_html/inventario/2/
echo 'open_basedir = "/home1/fix360:/tmp"' > .user.ini
```

**Opción B:** Contactar al soporte del hosting

Usar esta plantilla de email:

```
Asunto: Error open_basedir - Solicitud de corrección

Hola,

Estoy experimentando un error de configuración en mi cuenta de hosting:

Error: PHP open_basedir restriction in effect
Ruta de la aplicación: /home1/fix360/public_html/inventario/2/
open_basedir actual: /home1/fix30/public_html:/tmp

Hay un error de tipeo en la configuración. Por favor actualizar:
De: /home1/fix30/public_html:/tmp
A: /home1/fix360/public_html:/tmp

O verificar si existe alguna configuración incorrecta en la cuenta fix360.

Gracias.
```

---

## 📚 Documentación Completa

Para información detallada, consultar:

1. **SOLUCION_OPEN_BASEDIR.md** - Guía completa con:
   - Análisis detallado del problema
   - Todas las soluciones posibles
   - Scripts de diagnóstico
   - Contacto con proveedor
   - Referencias técnicas

2. **diagnostico.php** - Script interactivo que:
   - Detecta automáticamente problemas
   - Muestra configuración actual
   - Sugiere soluciones
   - Genera reporte HTML

---

## 🔍 Verificación Post-Corrección

Después de corregir open_basedir, verificar:

### 1. Revisar logs de error
```bash
tail -f /home1/fix360/public_html/error_log
```
**Resultado esperado:** No más errores de open_basedir

### 2. Probar la aplicación
```
http://tudominio.com/
```
**Resultado esperado:** La aplicación carga normalmente

### 3. Verificar acceso a /public
```
http://tudominio.com/public/
```
**Resultado esperado:** Redirige al index

### 4. Verificar archivos estáticos
```
http://tudominio.com/public/js/algun-archivo.js
```
**Resultado esperado:** Sirve el archivo correctamente

---

## ⚙️ Archivos Modificados/Creados

### Nuevos archivos:
- ✅ `public/index.php` - Previene 403 en /public
- ✅ `public/.htaccess` - Configuración del directorio público
- ✅ `SOLUCION_OPEN_BASEDIR.md` - Documentación completa
- ✅ `diagnostico.php` - Script de diagnóstico
- ✅ `GUIA_RAPIDA.md` - Esta guía

### Archivos modificados:
- ✅ `.htaccess` - Agregado intento de sobrescribir open_basedir

---

## 🆘 Solución de Problemas

### Si aún aparece 403 en /public:
1. Verificar permisos del archivo `public/index.php`:
   ```bash
   chmod 644 public/index.php
   ```
2. Verificar que .htaccess está presente:
   ```bash
   ls -la public/.htaccess
   ```
3. Verificar que mod_rewrite está habilitado en Apache

### Si open_basedir persiste:
1. Ejecutar `diagnostico.php` para ver configuración actual
2. Verificar que estás en la cuenta correcta (`fix360` no `fix30`)
3. Contactar soporte del hosting con los logs de error
4. Considerar mover la aplicación a `/home1/fix30/` si todo lo demás falla

### Si la aplicación no carga:
1. Revisar logs de error de Apache/PHP
2. Verificar permisos de archivos (644 para PHP, 755 para directorios)
3. Verificar configuración de base de datos en `.env`

---

## 📞 Obtener Ayuda

Si necesitas ayuda adicional:

1. **Ejecutar diagnóstico:**
   ```
   http://tudominio.com/diagnostico.php
   ```
   
2. **Revisar logs:**
   ```bash
   tail -100 /home1/fix360/public_html/error_log
   ```

3. **Consultar documentación:**
   - SOLUCION_OPEN_BASEDIR.md
   - README.md

4. **Contactar soporte del hosting** con:
   - Logs de error
   - Resultado del diagnóstico
   - Descripción del problema

---

## ✅ Checklist de Verificación

- [ ] Ejecuté `diagnostico.php` y revisé el reporte
- [ ] Verifiqué que /public/ ya no muestra 403
- [ ] Corregí open_basedir en cPanel o solicité soporte
- [ ] Verifiqué que la aplicación carga correctamente
- [ ] Revisé que no hay más errores en error_log
- [ ] Eliminé `diagnostico.php` por seguridad
- [ ] Documenté cualquier cambio adicional realizado

---

**Estado actual:**
- ✅ Error 403 en /public: **RESUELTO**
- ⚠️ Error open_basedir: **PENDIENTE (requiere servidor)**

**Fecha:** 2026-02-14
**Versión:** 1.0
