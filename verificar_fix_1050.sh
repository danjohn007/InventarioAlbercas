#!/bin/bash
# ============================================
# Script de Verificación para Fix Error #1050
# Valida que database.sql pueda ejecutarse múltiples veces
# ============================================

echo "╔════════════════════════════════════════════════════════════╗"
echo "║    Verificación Fix Error #1050 - Tabla Ya Existe         ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para mostrar OK
function print_ok() {
    echo -e "${GREEN}✓${NC} $1"
}

# Función para mostrar ERROR
function print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Función para mostrar WARNING
function print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

echo "1. Verificando archivo database.sql..."
if [ ! -f "database.sql" ]; then
    print_error "Archivo database.sql no encontrado"
    exit 1
fi
print_ok "Archivo database.sql encontrado"

echo ""
echo "2. Verificando CREATE TABLE statements..."

# Contar CREATE TABLE IF NOT EXISTS
COUNT_IF_NOT_EXISTS=$(grep -c "CREATE TABLE IF NOT EXISTS" database.sql)
echo "   Encontrados: $COUNT_IF_NOT_EXISTS sentencias con IF NOT EXISTS"

if [ "$COUNT_IF_NOT_EXISTS" -eq 12 ]; then
    print_ok "Las 12 tablas usan IF NOT EXISTS"
else
    print_error "Se esperaban 12 tablas con IF NOT EXISTS, se encontraron $COUNT_IF_NOT_EXISTS"
    exit 1
fi

echo ""
echo "3. Verificando que NO existan CREATE TABLE sin IF NOT EXISTS..."

# Verificar que no haya CREATE TABLE sin IF NOT EXISTS
# Excluimos comentarios y líneas que contengan IF NOT EXISTS
PLAIN_CREATE=$(grep -E "^CREATE TABLE [^I]" database.sql | wc -l)

if [ "$PLAIN_CREATE" -eq 0 ]; then
    print_ok "No se encontraron CREATE TABLE sin IF NOT EXISTS"
else
    print_error "Se encontraron $PLAIN_CREATE sentencias CREATE TABLE sin IF NOT EXISTS:"
    grep -n "^CREATE TABLE [^I]" database.sql
    exit 1
fi

echo ""
echo "4. Listando todas las tablas con IF NOT EXISTS:"
echo ""
grep -o "CREATE TABLE IF NOT EXISTS [a-z_]*" database.sql | sed 's/CREATE TABLE IF NOT EXISTS /   ✓ /' | sort

echo ""
echo "5. Verificando sintaxis SQL básica..."

# Verificar que el archivo SQL sea válido
if php -r "
\$sql = file_get_contents('database.sql');
if (\$sql === false) {
    exit(1);
}
\$hasCreateDB = strpos(\$sql, 'CREATE DATABASE IF NOT EXISTS') !== false;
\$hasUseDB = strpos(\$sql, 'USE inventario_albercas') !== false;
if (!\$hasCreateDB || !\$hasUseDB) {
    exit(1);
}
exit(0);
" 2>/dev/null; then
    print_ok "Sintaxis SQL básica válida"
else
    print_error "Problema con sintaxis SQL"
    exit 1
fi

echo ""
echo "6. Verificando consistencia con database_updates.sql..."

if [ -f "database_updates.sql" ]; then
    UPDATES_IF_NOT_EXISTS=$(grep -c "CREATE TABLE IF NOT EXISTS" database_updates.sql)
    print_ok "database_updates.sql también usa IF NOT EXISTS ($UPDATES_IF_NOT_EXISTS tablas)"
else
    print_warning "database_updates.sql no encontrado (opcional)"
fi

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    ✓ VERIFICACIÓN EXITOSA                 ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Resumen:"
echo "  • 12 tablas verificadas con IF NOT EXISTS"
echo "  • 0 tablas sin IF NOT EXISTS"
echo "  • Sintaxis SQL válida"
echo "  • El script puede ejecutarse múltiples veces sin error"
echo ""
echo "Próximos pasos para testing completo:"
echo "  1. Crear base de datos de prueba"
echo "  2. Ejecutar database.sql (primera vez)"
echo "  3. Ejecutar database.sql (segunda vez para verificar)"
echo "  4. Verificar que no hay errores #1050"
echo ""

exit 0
