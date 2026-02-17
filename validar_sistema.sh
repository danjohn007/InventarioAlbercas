#!/bin/bash
# Script de Validación del Sistema - Mejoras Implementadas
# Este script verifica que todas las mejoras estén correctamente implementadas

echo "=========================================="
echo "Validación del Sistema - Mejoras"
echo "=========================================="
echo ""

# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Contador de errores
ERRORS=0
WARNINGS=0

echo "1. Verificando Dependencias de Composer..."
if [ -d "vendor" ]; then
    echo -e "${GREEN}✓${NC} Directorio vendor existe"
    
    if [ -f "vendor/autoload.php" ]; then
        echo -e "${GREEN}✓${NC} Autoload de Composer encontrado"
    else
        echo -e "${RED}✗${NC} Autoload de Composer no encontrado"
        ((ERRORS++))
    fi
    
    if [ -d "vendor/tecnickcom/tcpdf" ]; then
        echo -e "${GREEN}✓${NC} TCPDF instalado"
    else
        echo -e "${RED}✗${NC} TCPDF no instalado"
        ((ERRORS++))
    fi
    
    if [ -d "vendor/phpoffice/phpspreadsheet" ]; then
        echo -e "${GREEN}✓${NC} PhpSpreadsheet instalado"
    else
        echo -e "${RED}✗${NC} PhpSpreadsheet no instalado"
        ((ERRORS++))
    fi
else
    echo -e "${RED}✗${NC} Directorio vendor no existe. Ejecute: composer install"
    ((ERRORS++))
fi

echo ""
echo "2. Verificando Controladores..."

CONTROLLERS=(
    "controllers/IngresosController.php"
    "controllers/ConfiguracionController.php"
    "controllers/ReportesController.php"
)

for controller in "${CONTROLLERS[@]}"; do
    if [ -f "$controller" ]; then
        # Verificar sintaxis PHP
        if php -l "$controller" > /dev/null 2>&1; then
            echo -e "${GREEN}✓${NC} $controller - Sintaxis válida"
        else
            echo -e "${RED}✗${NC} $controller - Error de sintaxis"
            ((ERRORS++))
        fi
    else
        echo -e "${RED}✗${NC} $controller - No encontrado"
        ((ERRORS++))
    fi
done

echo ""
echo "3. Verificando Helpers de Exportación..."

HELPERS=(
    "utils/exports/PdfExporter.php"
    "utils/exports/ExcelExporter.php"
)

for helper in "${HELPERS[@]}"; do
    if [ -f "$helper" ]; then
        if php -l "$helper" > /dev/null 2>&1; then
            echo -e "${GREEN}✓${NC} $helper - Sintaxis válida"
        else
            echo -e "${RED}✗${NC} $helper - Error de sintaxis"
            ((ERRORS++))
        fi
    else
        echo -e "${RED}✗${NC} $helper - No encontrado"
        ((ERRORS++))
    fi
done

echo ""
echo "4. Verificando Vistas de Nuevos Módulos..."

VIEWS=(
    "views/ingresos/index.php"
    "views/ingresos/crear.php"
    "views/ingresos/editar.php"
    "views/configuraciones/index.php"
)

for view in "${VIEWS[@]}"; do
    if [ -f "$view" ]; then
        if php -l "$view" > /dev/null 2>&1; then
            echo -e "${GREEN}✓${NC} $view - Sintaxis válida"
        else
            echo -e "${RED}✗${NC} $view - Error de sintaxis"
            ((ERRORS++))
        fi
    else
        echo -e "${RED}✗${NC} $view - No encontrado"
        ((ERRORS++))
    fi
done

echo ""
echo "5. Verificando Vistas de Reportes con Exportación..."

REPORT_VIEWS=(
    "views/reportes/inventario.php"
    "views/reportes/gastos.php"
    "views/reportes/servicios.php"
)

for view in "${REPORT_VIEWS[@]}"; do
    if [ -f "$view" ]; then
        # Verificar que contenga funciones de exportación
        if grep -q "exportarPDF\|exportarExcel" "$view"; then
            echo -e "${GREEN}✓${NC} $view - Funciones de exportación encontradas"
        else
            echo -e "${YELLOW}⚠${NC} $view - Funciones de exportación no encontradas"
            ((WARNINGS++))
        fi
    else
        echo -e "${RED}✗${NC} $view - No encontrado"
        ((ERRORS++))
    fi
done

echo ""
echo "6. Verificando Archivos de Base de Datos..."

if [ -f "database.sql" ]; then
    echo -e "${GREEN}✓${NC} database.sql encontrado"
else
    echo -e "${RED}✗${NC} database.sql no encontrado"
    ((ERRORS++))
fi

if [ -f "database_updates.sql" ]; then
    echo -e "${GREEN}✓${NC} database_updates.sql encontrado"
    
    # Verificar que contenga las tablas nuevas
    if grep -q "categorias_ingreso\|ingresos\|configuraciones" "database_updates.sql"; then
        echo -e "${GREEN}✓${NC} database_updates.sql contiene las tablas nuevas"
    else
        echo -e "${RED}✗${NC} database_updates.sql no contiene las tablas nuevas"
        ((ERRORS++))
    fi
else
    echo -e "${RED}✗${NC} database_updates.sql no encontrado"
    ((ERRORS++))
fi

echo ""
echo "7. Verificando Rutas en index.php..."

if [ -f "index.php" ]; then
    # Verificar rutas de exportación
    if grep -q "reportes/inventario/pdf\|reportes/inventario/excel" "index.php"; then
        echo -e "${GREEN}✓${NC} Rutas de exportación de reportes encontradas"
    else
        echo -e "${RED}✗${NC} Rutas de exportación no encontradas en index.php"
        ((ERRORS++))
    fi
    
    # Verificar rutas de configuraciones
    if grep -q "configuraciones" "index.php"; then
        echo -e "${GREEN}✓${NC} Rutas de configuraciones encontradas"
    else
        echo -e "${RED}✗${NC} Rutas de configuraciones no encontradas"
        ((ERRORS++))
    fi
    
    # Verificar rutas de ingresos
    if grep -q "ingresos" "index.php"; then
        echo -e "${GREEN}✓${NC} Rutas de ingresos encontradas"
    else
        echo -e "${RED}✗${NC} Rutas de ingresos no encontradas"
        ((ERRORS++))
    fi
else
    echo -e "${RED}✗${NC} index.php no encontrado"
    ((ERRORS++))
fi

echo ""
echo "8. Verificando Configuración de Git..."

if [ -f ".gitignore" ]; then
    if grep -q "vendor/\|composer.lock" ".gitignore"; then
        echo -e "${GREEN}✓${NC} .gitignore configurado correctamente"
    else
        echo -e "${YELLOW}⚠${NC} .gitignore podría no estar configurado correctamente"
        ((WARNINGS++))
    fi
else
    echo -e "${YELLOW}⚠${NC} .gitignore no encontrado"
    ((WARNINGS++))
fi

echo ""
echo "9. Verificando Documentación..."

DOCS=(
    "MEJORAS_SISTEMA.md"
    "INSTALACION_MEJORAS.md"
    "ESTADO_ACTUAL_SISTEMA.md"
)

for doc in "${DOCS[@]}"; do
    if [ -f "$doc" ]; then
        echo -e "${GREEN}✓${NC} $doc encontrado"
    else
        echo -e "${YELLOW}⚠${NC} $doc no encontrado"
        ((WARNINGS++))
    fi
done

echo ""
echo "10. Verificando Sintaxis de Todos los Archivos PHP..."

PHP_ERROR_COUNT=0
PHP_FILE_COUNT=0

while IFS= read -r -d '' file; do
    ((PHP_FILE_COUNT++))
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "${RED}✗${NC} Error de sintaxis en: $file"
        ((PHP_ERROR_COUNT++))
        ((ERRORS++))
    fi
done < <(find . -name "*.php" -not -path "./vendor/*" -not -path "./.git/*" -type f -print0)

if [ $PHP_ERROR_COUNT -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Todos los archivos PHP ($PHP_FILE_COUNT) tienen sintaxis válida"
else
    echo -e "${RED}✗${NC} $PHP_ERROR_COUNT de $PHP_FILE_COUNT archivos PHP tienen errores"
fi

echo ""
echo "=========================================="
echo "Resumen de Validación"
echo "=========================================="

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✓ ÉXITO:${NC} Sistema completamente validado"
    echo "Todas las mejoras están correctamente implementadas"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠ ADVERTENCIAS:${NC} $WARNINGS advertencia(s) encontrada(s)"
    echo "El sistema funciona pero hay mejoras opcionales"
    exit 0
else
    echo -e "${RED}✗ ERRORES:${NC} $ERRORS error(es) encontrado(s)"
    if [ $WARNINGS -gt 0 ]; then
        echo -e "${YELLOW}⚠ ADVERTENCIAS:${NC} $WARNINGS advertencia(s) encontrada(s)"
    fi
    echo "Por favor corrija los errores antes de continuar"
    exit 1
fi
