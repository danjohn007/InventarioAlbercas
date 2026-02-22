<?php
$_sitioNombre    = 'Sistema Albercas';
$_sitioLogo      = '';
$_colorPrimario  = '#667eea';
$_colorSecundario = '#764ba2';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?><?php echo htmlspecialchars($_sitioNombre); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($_colorPrimario); ?>;
            --secondary-color: <?php echo htmlspecialchars($_colorSecundario); ?>;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-nav {
            padding: 15px 0;
        }
        
        .nav-item {
            margin: 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .stat-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card-blue {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff;
        }
        .stat-card-green {
            background: linear-gradient(135deg, #1cc88a, #13855c);
            color: #fff;
        }
        .stat-card-teal {
            background: linear-gradient(135deg, #36b9cc, #1a6272);
            color: #fff;
        }
        .stat-card-red {
            background: linear-gradient(135deg, #e74a3b, #be2617);
            color: #fff;
        }
        .stat-card-yellow {
            background: linear-gradient(135deg, #f6c23e, #dda20a);
            color: #fff;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        
        .badge {
            padding: 6px 12px;
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-menu .dropdown-menu {
            right: 0;
            left: auto;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block !important;
            }
        }
        
        .mobile-toggle {
            display: none;
        }
    </style>
    
    <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <?php if (!empty($_sitioLogo)): ?>
                <img src="<?php echo BASE_URL . htmlspecialchars($_sitioLogo); ?>" alt="<?php echo htmlspecialchars($_sitioNombre); ?>" style="max-height:40px; max-width:180px; object-fit:contain; margin-bottom:5px;"><br>
            <?php else: ?>
                <i class="bi bi-water"></i>
            <?php endif; ?>
            <?php echo htmlspecialchars($_sitioNombre); ?>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>dashboard" class="nav-link <?php echo $activeMenu == 'dashboard' ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if (Auth::can('usuarios', 'leer')): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>usuarios" class="nav-link <?php echo $activeMenu == 'usuarios' ? 'active' : ''; ?>">
                        <i class="bi bi-people"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Auth::can('inventario', 'leer')): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>inventario" class="nav-link <?php echo $activeMenu == 'inventario' ? 'active' : ''; ?>">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventario</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>inventario/movimientos" class="nav-link <?php echo $activeMenu == 'movimientos' ? 'active' : ''; ?>">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Movimientos</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Auth::can('gastos', 'leer')): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>gastos" class="nav-link <?php echo $activeMenu == 'gastos' ? 'active' : ''; ?>">
                        <i class="bi bi-cash-stack"></i>
                        <span>Gastos</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Auth::can('ingresos', 'leer')): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>ingresos" class="nav-link <?php echo $activeMenu == 'ingresos' ? 'active' : ''; ?>">
                        <i class="bi bi-currency-dollar"></i>
                        <span>Ingresos</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Auth::can('servicios', 'leer')): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>servicios" class="nav-link <?php echo $activeMenu == 'servicios' ? 'active' : ''; ?>">
                        <i class="bi bi-tools"></i>
                        <span>Servicios</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Auth::can('clientes', 'leer')): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>clientes" class="nav-link <?php echo $activeMenu == 'clientes' ? 'active' : ''; ?>">
                        <i class="bi bi-person-badge"></i>
                        <span>Clientes</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (Auth::can('reportes', 'leer')): ?>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>reportes" class="nav-link <?php echo $activeMenu == 'reportes' ? 'active' : ''; ?>">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span>Reportes</span>
                    </a>
                </li>
                <?php endif; ?>
                

            </ul>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div>
                <button class="btn btn-link mobile-toggle" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <span class="fs-5 fw-bold text-muted"><?php echo $pageTitle ?? 'Dashboard'; ?></span>
            </div>
            
            <div class="user-menu dropdown">
                <button class="btn btn-link text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4"></i>
                    <span class="ms-2"><?php echo Auth::user()['nombre']; ?></span>
                    <span class="badge bg-secondary ms-2"><?php echo Auth::user()['rol']; ?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <?php 
            // Mostrar alertas de sesión
            if (isset($_SESSION['success_message'])): 
            ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php echo $content; ?>
        </div>
    </main>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    
    <?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
