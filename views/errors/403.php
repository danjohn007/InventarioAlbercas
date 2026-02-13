<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            color: white;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <h2 class="mb-4">Acceso denegado</h2>
        <p class="mb-4">No tienes permisos para acceder a este recurso.</p>
        <a href="<?php echo BASE_URL; ?>dashboard" class="btn btn-light btn-lg">
            <i class="bi bi-house me-2"></i>Volver al dashboard
        </a>
    </div>
</body>
</html>
