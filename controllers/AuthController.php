<?php
/**
 * Controlador de Autenticación
 */
class AuthController {
    
    public function login() {
        // Si ya está autenticado, redirigir al dashboard
        if (Auth::check()) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        
        // Mostrar vista de login
        require_once ROOT_PATH . '/views/auth/login.php';
    }
    
    public function authenticate() {
        // Validar datos
        if (!isset($_POST['usuario']) || !isset($_POST['password'])) {
            $_SESSION['error_message'] = 'Por favor, completa todos los campos';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $usuario = trim($_POST['usuario']);
        $password = $_POST['password'];
        
        if (empty($usuario) || empty($password)) {
            $_SESSION['error_message'] = 'Usuario y contraseña son requeridos';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        // Intentar login
        if (Auth::login($usuario, $password)) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        } else {
            $_SESSION['error_message'] = 'Usuario o contraseña incorrectos';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }
}
