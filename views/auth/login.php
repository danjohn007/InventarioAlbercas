<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Inventario</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-left h2 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .login-left p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .login-right {
            flex: 1;
            padding: 60px 40px;
        }
        
        .login-right h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .login-right p {
            color: #666;
            margin-bottom: 30px;
        }
        
        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            padding: 12px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .icon-input {
            position: relative;
        }
        
        .icon-input i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .icon-input input {
            padding-left: 40px;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .test-credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .test-credentials strong {
            color: #667eea;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-left {
                padding: 40px 30px;
            }
            
            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div>
                <h2><i class="bi bi-water me-2"></i> Sistema de Inventario</h2>
                <p>Administración de Inventario y Gastos para Albercas</p>
                <hr style="border-color: rgba(255,255,255,0.3); margin: 30px 0;">
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 15px;">
                        <i class="bi bi-check-circle me-2"></i> Control de inventario en tiempo real
                    </li>
                    <li style="margin-bottom: 15px;">
                        <i class="bi bi-check-circle me-2"></i> Gestión de gastos y servicios
                    </li>
                    <li style="margin-bottom: 15px;">
                        <i class="bi bi-check-circle me-2"></i> Reportes detallados
                    </li>
                    <li style="margin-bottom: 15px;">
                        <i class="bi bi-check-circle me-2"></i> Seguridad y auditoría
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="login-right">
            <h3>Bienvenido</h3>
            <p>Inicia sesión para continuar</p>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo BASE_URL; ?>login" id="loginForm">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <div class="icon-input">
                        <i class="bi bi-person"></i>
                        <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="icon-input">
                        <i class="bi bi-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label" for="remember">
                        Recordar sesión
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>
            </form>
            
            <div class="test-credentials">
                <strong>Credenciales de prueba:</strong><br>
                <small>
                    • <strong>admin</strong> / admin123 (Administrador)<br>
                    • <strong>supervisor</strong> / supervisor123 (Supervisor)<br>
                    • <strong>tecnico</strong> / tecnico123 (Técnico)
                </small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const usuario = document.getElementById('usuario').value.trim();
            const password = document.getElementById('password').value;
            
            if (!usuario || !password) {
                e.preventDefault();
                alert('Por favor, completa todos los campos');
            }
        });
    </script>
</body>
</html>
