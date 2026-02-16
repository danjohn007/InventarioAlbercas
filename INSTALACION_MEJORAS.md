# Guía de Instalación - Mejoras del Sistema

## Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Composer
- Servidor web (Apache/Nginx)
- Acceso a la base de datos existente

## Pasos de Instalación

### 1. Clonar o Actualizar el Repositorio

```bash
cd /path/to/InventarioAlbercas
git pull origin main
# O si es una nueva instalación:
git clone https://github.com/danjohn007/InventarioAlbercas.git
cd InventarioAlbercas
```

### 2. Instalar Dependencias de Composer

```bash
composer install --no-dev --optimize-autoloader
```

**Nota**: Si composer muestra errores de autenticación con GitHub, usa:
```bash
composer install --no-dev --prefer-source
```

### 3. Crear Directorio de Uploads

```bash
mkdir -p public/uploads
chmod 755 public/uploads
```

### 4. Aplicar Actualizaciones de Base de Datos

**Importante**: Haz un respaldo de tu base de datos antes de continuar.

```bash
# Respaldo
mysqldump -u usuario -p nombre_bd > backup_$(date +%Y%m%d_%H%M%S).sql

# Aplicar actualizaciones
mysql -u usuario -p nombre_bd < database_updates.sql
```

**Verificar que las tablas se crearon correctamente:**

```sql
USE inventario_albercas;
SHOW TABLES LIKE '%ingreso%';
SHOW TABLES LIKE 'configuraciones';
```

Deberías ver:
- `categorias_ingreso`
- `ingresos`
- `configuraciones`

### 5. Verificar Permisos en la Base de Datos

Ejecuta esta consulta para verificar que los permisos se actualizaron:

```sql
SELECT nombre, permisos FROM roles WHERE nombre = 'Administrador';
```

Deberías ver `ingresos` y `configuraciones` en los permisos JSON.

### 6. Verificar Configuración PHP

Asegúrate de que tu configuración PHP tenga estos valores mínimos:

```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 128M
max_execution_time = 300
```

### 7. Configurar el Servidor Web

#### Apache (.htaccess ya incluido)
El archivo `.htaccess` ya está configurado para reescritura de URLs.

Verifica que mod_rewrite esté habilitado:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx
Agrega esto a tu configuración de servidor:

```nginx
location / {
    try_files $uri $uri/ /index.php?url=$uri&$args;
}
```

### 8. Probar la Instalación

1. **Acceder al sistema**:
   ```
   http://tu-dominio.com/login
   ```

2. **Iniciar sesión como administrador**:
   - Usuario: `admin`
   - Contraseña: `admin123` (cambiar después del primer acceso)

3. **Verificar nuevos módulos**:
   - Ve al menú lateral
   - Deberías ver:
     - ✅ Ingresos
     - ✅ Configuraciones

4. **Probar exportación de reportes**:
   - Ve a "Reportes" → "Reporte de Inventario"
   - Haz clic en "PDF" o "Excel"
   - Debería descargar el archivo

## Troubleshooting

### Error: "Class 'TCPDF' not found"
**Solución**: Las dependencias de Composer no se instalaron correctamente.
```bash
composer install --no-dev
composer dump-autoload
```

### Error: "Table 'ingresos' doesn't exist"
**Solución**: El script de base de datos no se ejecutó.
```bash
mysql -u usuario -p nombre_bd < database_updates.sql
```

### Error: "Permission denied" al subir logo
**Solución**: Verificar permisos del directorio uploads.
```bash
chmod 755 public/uploads
chown www-data:www-data public/uploads  # En sistemas Linux
```

### Error: "You do not have permission to access this resource"
**Solución**: El usuario no tiene los permisos correctos.
```sql
-- Actualizar permisos del rol Administrador
UPDATE roles 
SET permisos = JSON_SET(
    permisos,
    '$.ingresos', JSON_ARRAY('crear', 'leer', 'actualizar', 'eliminar'),
    '$.configuraciones', JSON_ARRAY('leer', 'actualizar')
)
WHERE nombre = 'Administrador';
```

### Los colores personalizados no se aplican
**Solución**: Los colores se cargan desde la base de datos pero el sistema usa CSS inline. En futuras versiones se implementará la carga dinámica de estilos.

## Verificación Post-Instalación

Ejecuta esta lista de verificación:

- [ ] Puedo iniciar sesión correctamente
- [ ] Veo el menú "Ingresos" en el sidebar
- [ ] Veo el menú "Configuraciones" en el sidebar (solo admin)
- [ ] Puedo crear un nuevo ingreso
- [ ] Puedo exportar reportes a PDF
- [ ] Puedo exportar reportes a Excel
- [ ] Puedo acceder a Configuraciones
- [ ] Puedo guardar cambios en Configuraciones
- [ ] Los archivos se descargan correctamente

## Actualización de Versión

Si ya tenías el sistema instalado y estás actualizando:

1. Haz respaldo de la base de datos
2. Haz respaldo de archivos personalizados (si los hay)
3. Ejecuta `git pull` para obtener los cambios
4. Ejecuta `composer install`
5. Aplica `database_updates.sql`
6. Verifica que todo funcione correctamente

## Soporte

Si encuentras problemas:

1. Revisa los logs de PHP: `/var/log/apache2/error.log` o `/var/log/nginx/error.log`
2. Revisa los logs de MySQL: `/var/log/mysql/error.log`
3. Verifica la configuración en `config/config.php`
4. Consulta la documentación en `MEJORAS_SISTEMA.md`
5. Abre un issue en GitHub con detalles del error

## Notas de Seguridad

- **Cambia las contraseñas por defecto** inmediatamente después de la instalación
- **Revisa los permisos** de archivos y directorios
- **Mantén actualizado** el sistema con `composer update` regularmente
- **Haz respaldos** periódicos de la base de datos
- **Usa HTTPS** en producción para proteger las credenciales

## Próximos Pasos

Después de la instalación exitosa:

1. Cambia la contraseña del administrador
2. Crea usuarios adicionales según sea necesario
3. Configure el logotipo y colores del sistema en Configuraciones
4. Registra categorías de ingreso personalizadas si es necesario
5. Capacita a los usuarios en las nuevas funcionalidades

---

## Contacto

Para soporte técnico o consultas:
- GitHub Issues: https://github.com/danjohn007/InventarioAlbercas/issues
- Email: [tu-email@ejemplo.com]

---

**Última actualización**: 2026-02-16
**Versión**: 2.0.0 (con Mejoras)
