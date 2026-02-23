<?php
/**
 * Sistema de Inventario y Gastos para Albercas
 * Punto de entrada principal
 */

// Cargar configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/Router.php';
require_once __DIR__ . '/utils/Auth.php';

// Iniciar sesiones
Auth::init();

// Crear router
$router = new Router();

// Funciones auxiliares para cargar controladores
function loadController($controllerName) {
    $file = ROOT_PATH . '/controllers/' . $controllerName . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}

// Ruta raíz - redirigir según autenticación
$router->get('/', function() {
    if (Auth::check()) {
        header('Location: ' . BASE_URL . 'dashboard');
    } else {
        header('Location: ' . BASE_URL . 'login');
    }
    exit;
});

// Rutas de autenticación
$router->get('/login', function() {
    if (Auth::check()) {
        header('Location: ' . BASE_URL . 'dashboard');
        exit;
    }
    loadController('AuthController');
    $controller = new AuthController();
    $controller->login();
});

$router->post('/login', function() {
    loadController('AuthController');
    $controller = new AuthController();
    $controller->authenticate();
});

$router->get('/logout', function() {
    Auth::logout();
});

// Ruta del dashboard
$router->get('/dashboard', function() {
    Auth::requireAuth();
    loadController('DashboardController');
    $controller = new DashboardController();
    $controller->index();
});

// Rutas de usuarios
$router->get('/usuarios', function() {
    Auth::requirePermission('usuarios', 'leer');
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->index();
});

$router->get('/usuarios/crear', function() {
    Auth::requirePermission('usuarios', 'crear');
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->crear();
});

$router->post('/usuarios/guardar', function() {
    Auth::requirePermission('usuarios', 'crear');
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->guardar();
});

$router->get('/usuarios/editar/([0-9]+)', function($id) {
    Auth::requirePermission('usuarios', 'actualizar');
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->editar($id);
});

$router->post('/usuarios/actualizar', function() {
    Auth::requirePermission('usuarios', 'actualizar');
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->actualizar();
});

$router->get('/usuarios/eliminar/([0-9]+)', function($id) {
    Auth::requirePermission('usuarios', 'eliminar');
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->eliminar($id);
});

// Rutas de inventario
$router->get('/inventario', function() {
    Auth::requirePermission('inventario', 'leer');
    loadController('InventarioController');
    $controller = new InventarioController();
    $controller->index();
});

$router->get('/inventario/crear', function() {
    Auth::requirePermission('inventario', 'crear');
    loadController('InventarioController');
    $controller = new InventarioController();
    $controller->crear();
});

$router->post('/inventario/guardar', function() {
    Auth::requirePermission('inventario', 'crear');
    loadController('InventarioController');
    $controller = new InventarioController();
    $controller->guardar();
});

$router->get('/inventario/editar/([0-9]+)', function($id) {
    Auth::requirePermission('inventario', 'actualizar');
    loadController('InventarioController');
    $controller = new InventarioController();
    $controller->editar($id);
});

$router->post('/inventario/actualizar', function() {
    Auth::requirePermission('inventario', 'actualizar');
    loadController('InventarioController');
    $controller = new InventarioController();
    $controller->actualizar();
});

$router->get('/inventario/movimientos', function() {
    Auth::requirePermission('inventario', 'leer');
    loadController('InventarioController');
    $controller = new InventarioController();
    $controller->movimientos();
});

$router->get('/inventario/movimiento/([0-9]+)', function($id) {
    Auth::requirePermission('inventario', 'leer');
    loadController('InventarioController');
    $controller = new InventarioController();
    $controller->registrarMovimiento($id);
});

$router->post('/inventario/guardar-movimiento', function() {
    Auth::requirePermission('inventario', 'actualizar');
    loadController('InventarioController');
    $controller = new InventarioController();
    $controller->guardarMovimiento();
});

// Rutas de gastos
$router->get('/gastos', function() {
    Auth::requirePermission('gastos', 'leer');
    loadController('GastosController');
    $controller = new GastosController();
    $controller->index();
});

$router->get('/gastos/crear', function() {
    Auth::requirePermission('gastos', 'crear');
    loadController('GastosController');
    $controller = new GastosController();
    $controller->crear();
});

$router->post('/gastos/guardar', function() {
    Auth::requirePermission('gastos', 'crear');
    loadController('GastosController');
    $controller = new GastosController();
    $controller->guardar();
});

$router->get('/gastos/editar/([0-9]+)', function($id) {
    Auth::requirePermission('gastos', 'actualizar');
    loadController('GastosController');
    $controller = new GastosController();
    $controller->editar($id);
});

$router->post('/gastos/actualizar', function() {
    Auth::requirePermission('gastos', 'actualizar');
    loadController('GastosController');
    $controller = new GastosController();
    $controller->actualizar();
});

// Rutas de servicios
$router->get('/servicios', function() {
    Auth::requirePermission('servicios', 'leer');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->index();
});

$router->get('/servicios/crear', function() {
    Auth::requirePermission('servicios', 'crear');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->crear();
});

$router->post('/servicios/guardar', function() {
    Auth::requirePermission('servicios', 'crear');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->guardar();
});

$router->get('/servicios/ver/([0-9]+)', function($id) {
    Auth::requirePermission('servicios', 'leer');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->ver($id);
});

$router->get('/servicios/editar/([0-9]+)', function($id) {
    Auth::requirePermission('servicios', 'actualizar');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->editar($id);
});

$router->post('/servicios/actualizar', function() {
    Auth::requirePermission('servicios', 'actualizar');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->actualizar();
});

$router->get('/servicios/asignar-material/([0-9]+)', function($id) {
    Auth::requirePermission('servicios', 'actualizar');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->asignarMaterial($id);
});

$router->post('/servicios/guardar-material', function() {
    Auth::requirePermission('servicios', 'actualizar');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->guardarMaterial();
});

$router->get('/servicios/eliminar-material/([0-9]+)', function($id) {
    Auth::requirePermission('servicios', 'actualizar');
    loadController('ServiciosController');
    $controller = new ServiciosController();
    $controller->eliminarMaterial($id);
});

// Rutas de clientes
$router->get('/clientes', function() {
    Auth::requirePermission('clientes', 'leer');
    loadController('ClientesController');
    $controller = new ClientesController();
    $controller->index();
});

$router->get('/clientes/crear', function() {
    Auth::requirePermission('clientes', 'crear');
    loadController('ClientesController');
    $controller = new ClientesController();
    $controller->crear();
});

$router->post('/clientes/guardar', function() {
    Auth::requirePermission('clientes', 'crear');
    loadController('ClientesController');
    $controller = new ClientesController();
    $controller->guardar();
});

$router->get('/clientes/editar/([0-9]+)', function($id) {
    Auth::requirePermission('clientes', 'actualizar');
    loadController('ClientesController');
    $controller = new ClientesController();
    $controller->editar($id);
});

$router->post('/clientes/actualizar', function() {
    Auth::requirePermission('clientes', 'actualizar');
    loadController('ClientesController');
    $controller = new ClientesController();
    $controller->actualizar();
});

// Rutas de reportes
$router->get('/reportes', function() {
    Auth::requirePermission('reportes', 'leer');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->index();
});

$router->get('/reportes/inventario', function() {
    Auth::requirePermission('reportes', 'leer');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->inventario();
});

$router->get('/reportes/gastos', function() {
    Auth::requirePermission('reportes', 'leer');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->gastos();
});

$router->get('/reportes/servicios', function() {
    Auth::requirePermission('reportes', 'leer');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->servicios();
});

// Rutas de exportación de reportes
$router->get('/reportes/inventario/pdf', function() {
    Auth::requirePermission('reportes', 'exportar');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->exportarInventarioPDF();
});

$router->get('/reportes/inventario/excel', function() {
    Auth::requirePermission('reportes', 'exportar');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->exportarInventarioExcel();
});

$router->get('/reportes/gastos/pdf', function() {
    Auth::requirePermission('reportes', 'exportar');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->exportarGastosPDF();
});

$router->get('/reportes/gastos/excel', function() {
    Auth::requirePermission('reportes', 'exportar');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->exportarGastosExcel();
});

$router->get('/reportes/servicios/pdf', function() {
    Auth::requirePermission('reportes', 'exportar');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->exportarServiciosPDF();
});

$router->get('/reportes/servicios/excel', function() {
    Auth::requirePermission('reportes', 'exportar');
    loadController('ReportesController');
    $controller = new ReportesController();
    $controller->exportarServiciosExcel();
});

// Rutas de ingresos
$router->get('/ingresos', function() {
    Auth::requirePermission('ingresos', 'leer');
    loadController('IngresosController');
    $controller = new IngresosController();
    $controller->index();
});

$router->get('/ingresos/crear', function() {
    Auth::requirePermission('ingresos', 'crear');
    loadController('IngresosController');
    $controller = new IngresosController();
    $controller->crear();
});

$router->post('/ingresos/guardar', function() {
    Auth::requirePermission('ingresos', 'crear');
    loadController('IngresosController');
    $controller = new IngresosController();
    $controller->guardar();
});

$router->get('/ingresos/editar/([0-9]+)', function($id) {
    Auth::requirePermission('ingresos', 'actualizar');
    loadController('IngresosController');
    $controller = new IngresosController();
    $controller->editar($id);
});

$router->post('/ingresos/actualizar', function() {
    Auth::requirePermission('ingresos', 'actualizar');
    loadController('IngresosController');
    $controller = new IngresosController();
    $controller->actualizar();
});

$router->get('/ingresos/eliminar/([0-9]+)', function($id) {
    Auth::requirePermission('ingresos', 'eliminar');
    loadController('IngresosController');
    $controller = new IngresosController();
    $controller->eliminar($id);
});

// Rutas de configuraciones
$router->get('/configuraciones', function() {
    Auth::requirePermission('configuraciones', 'leer');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->index();
});

$router->post('/configuraciones/actualizar', function() {
    Auth::requirePermission('configuraciones', 'actualizar');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->actualizar();
});

$router->get('/configuraciones/exportar', function() {
    Auth::requirePermission('configuraciones', 'leer');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->exportar();
});

$router->post('/configuraciones/importar', function() {
    Auth::requirePermission('configuraciones', 'actualizar');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->importar();
});

$router->post('/configuraciones/restablecer', function() {
    Auth::requirePermission('configuraciones', 'actualizar');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->restablecer();
});

$router->post('/configuraciones/test-email', function() {
    Auth::requirePermission('configuraciones', 'actualizar');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->testEmail();
});

$router->get('/configuraciones/auditoria', function() {
    Auth::requirePermission('configuraciones', 'leer');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->auditoria();
});

$router->get('/configuraciones/errores', function() {
    Auth::requirePermission('configuraciones', 'leer');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->errores();
});

$router->get('/configuraciones/backups', function() {
    Auth::requirePermission('configuraciones', 'actualizar');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->backups();
});

$router->post('/configuraciones/backups/crear', function() {
    Auth::requirePermission('configuraciones', 'actualizar');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->crearBackup();
});

$router->post('/configuraciones/backups/restaurar', function() {
    Auth::requirePermission('configuraciones', 'actualizar');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->restaurarBackup();
});

$router->post('/configuraciones/backups/eliminar', function() {
    Auth::requirePermission('configuraciones', 'actualizar');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->eliminarBackup();
});

$router->get('/configuraciones/backups/descargar/([^/]+)', function($filename) {
    Auth::requirePermission('configuraciones', 'leer');
    loadController('ConfiguracionController');
    $controller = new ConfiguracionController();
    $controller->descargarBackup($filename);
});

// Rutas de perfil del usuario autenticado
$router->get('/perfil', function() {
    Auth::requireAuth();
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->perfil();
});

$router->post('/perfil/actualizar', function() {
    Auth::requireAuth();
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->actualizarPerfil();
});

$router->post('/perfil/cambiar-password', function() {
    Auth::requireAuth();
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->cambiarPassword();
});

$router->post('/perfil/subir-foto', function() {
    Auth::requireAuth();
    loadController('UsuariosController');
    $controller = new UsuariosController();
    $controller->subirFoto();
});

// Despachar la ruta
$router->dispatch();
