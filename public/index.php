<?php
/**
 * Public directory index
 * Este archivo previene errores 403 en el directorio /public
 * y redirige al index principal de la aplicación
 */

// Redirigir al index principal
header('Location: ../index.php');
exit;
