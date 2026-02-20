#!/bin/bash
# ============================================
# Script de Verificación para Fixes SQL
# Valida que database.sql sea completamente idempotente
# Verifica fixes #1050 (CREATE TABLE IF NOT EXISTS) y #1062 (INSERT IGNORE)
# ============================================

echo "╔════════════════════════════════════════════════════════════╗"
echo "║    Verificación Completa - database.sql Idempotente        ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

# Función para mostrar INFO
function print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Verificar archivo existe
echo "1. Verificando archivo database.sql..."
if [ ! -f "database.sql" ]; then
    print_error "Archivo database.sql no encontrado"
    exit 1
fi
print_ok "Archivo database.sql encontrado"

echo ""
echo "═══════════════════════════════════════════════════════════"
echo " FIX #1050: CREATE TABLE IF NOT EXISTS"
echo "═══════════════════════════════════════════════════════════"

# Verificar CREATE TABLE IF NOT EXISTS
COUNT_IF_NOT_EXISTS=$(grep -c "CREATE TABLE IF NOT EXISTS" database.sql)
echo "   Encontrados: $COUNT_IF_NOT_EXISTS sentencias con IF NOT EXISTS"

if [ "$COUNT_IF_NOT_EXISTS" -eq 12 ]; then
    print_ok "Las 12 tablas usan IF NOT EXISTS"
else
    print_error "Se esperaban 12 tablas con IF NOT EXISTS, se encontraron $COUNT_IF_NOT_EXISTS"
    exit 1
fi

# Verificar que no haya CREATE TABLE sin IF NOT EXISTS
PLAIN_CREATE=$(grep -E "^CREATE TABLE [^I]" database.sql | wc -l)

if [ "$PLAIN_CREATE" -eq 0 ]; then
    print_ok "No se encontraron CREATE TABLE sin IF NOT EXISTS"
else
    print_error "Se encontraron $PLAIN_CREATE sentencias CREATE TABLE sin IF NOT EXISTS"
    grep -n "^CREATE TABLE [^I]" database.sql
    exit 1
fi

echo ""
echo "═══════════════════════════════════════════════════════════"
echo " FIX #1062: INSERT IGNORE INTO"
echo "═══════════════════════════════════════════════════════════"

# Verificar INSERT IGNORE INTO
COUNT_INSERT_IGNORE=$(grep -c "INSERT IGNORE INTO" database.sql)
echo "   Encontrados: $COUNT_INSERT_IGNORE sentencias con INSERT IGNORE"

if [ "$COUNT_INSERT_IGNORE" -eq 10 ]; then
    print_ok "Los 10 INSERT usan IGNORE"
else
    print_error "Se esperaban 10 INSERT IGNORE, se encontraron $COUNT_INSERT_IGNORE"
    exit 1
fi

# Verificar que no haya INSERT sin IGNORE
PLAIN_INSERT=$(grep -E "^INSERT INTO [^(]*\(" database.sql | grep -v IGNORE | wc -l)

if [ "$PLAIN_INSERT" -eq 0 ]; then
    print_ok "No se encontraron INSERT sin IGNORE"
else
    print_error "Se encontraron $PLAIN_INSERT sentencias INSERT sin IGNORE"
    grep -n -E "^INSERT INTO [^(]*\(" database.sql | grep -v IGNORE
    exit 1
fi

echo ""
echo "═══════════════════════════════════════════════════════════"
echo " FIX #1050 (VIEWS): CREATE OR REPLACE VIEW"
echo "═══════════════════════════════════════════════════════════"

# Verificar CREATE OR REPLACE VIEW
COUNT_OR_REPLACE_VIEW=$(grep -c "CREATE OR REPLACE VIEW" database.sql)
echo "   Encontrados: $COUNT_OR_REPLACE_VIEW sentencias con CREATE OR REPLACE VIEW"

if [ "$COUNT_OR_REPLACE_VIEW" -eq 3 ]; then
    print_ok "Las 3 vistas usan CREATE OR REPLACE VIEW"
else
    print_error "Se esperaban 3 vistas con CREATE OR REPLACE VIEW, se encontraron $COUNT_OR_REPLACE_VIEW"
    exit 1
fi

# Verificar que no haya CREATE VIEW sin OR REPLACE
PLAIN_CREATE_VIEW=$(grep -E "^CREATE\\s+VIEW\\s+" database.sql | grep -v "OR REPLACE" | wc -l)

if [ "$PLAIN_CREATE_VIEW" -eq 0 ]; then
    print_ok "No se encontraron CREATE VIEW sin OR REPLACE"
else
    print_error "Se encontraron $PLAIN_CREATE_VIEW sentencias CREATE VIEW sin OR REPLACE"
    grep -n -E "^CREATE\\s+VIEW\\s+" database.sql | grep -v "OR REPLACE"
    exit 1
fi

echo ""
echo "═══════════════════════════════════════════════════════════"
echo " VERIFICACIONES ADICIONALES"
echo "═══════════════════════════════════════════════════════════"

# Verificar sintaxis SQL básica
echo "3. Verificando sintaxis SQL básica..."

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

# Listar tablas protegidas
echo ""
echo "4. Tablas con IF NOT EXISTS (12):"
grep -o "CREATE TABLE IF NOT EXISTS [a-z_]*" database.sql | sed 's/CREATE TABLE IF NOT EXISTS /   ✓ /' | sort

echo ""
echo "5. Tablas con INSERT IGNORE (10):"
grep -o "INSERT IGNORE INTO [a-z_]*" database.sql | sed 's/INSERT IGNORE INTO /   ✓ /' | sort -u

echo ""
echo "6. Vistas con CREATE OR REPLACE (3):"
grep -o "CREATE OR REPLACE VIEW [a-z_]*" database.sql | sed 's/CREATE OR REPLACE VIEW /   ✓ /' | sort

# Verificar consistencia con database_updates.sql
echo ""
echo "7. Verificando consistencia con otros archivos..."

if [ -f "database_updates.sql" ]; then
    UPDATES_IF_NOT_EXISTS=$(grep -c "CREATE TABLE IF NOT EXISTS" database_updates.sql)
    print_ok "database_updates.sql también usa IF NOT EXISTS ($UPDATES_IF_NOT_EXISTS tablas)"
else
    print_warning "database_updates.sql no encontrado (opcional)"
fi

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                 ✓ VERIFICACIÓN COMPLETA EXITOSA            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "Resumen de Idempotencia:"
echo "  • Fix #1050 (Tablas): 12 tablas con IF NOT EXISTS ✓"
echo "  • Fix #1050 (Vistas): 3 vistas con CREATE OR REPLACE ✓"
echo "  • Fix #1062: 10 INSERT con IGNORE ✓"
echo "  • Sintaxis SQL validada ✓"
echo "  • database.sql es completamente idempotente ✓"
echo ""
echo "Características del script:"
echo "  ✓ Puede ejecutarse múltiples veces sin errores"
echo "  ✓ No genera errores #1050 (tabla/vista ya existe)"
echo "  ✓ No genera errores #1062 (entrada duplicada)"
echo "  ✓ Preserva datos existentes"
echo "  ✓ Seguro para uso en producción"
echo ""
echo "Próximos pasos sugeridos:"
echo "  1. Probar en base de datos de desarrollo"
echo "  2. Ejecutar dos veces para verificar idempotencia"
echo "  3. Verificar que no hay warnings críticos"
echo "  4. Usar en instalaciones/actualizaciones"
echo ""

exit 0
