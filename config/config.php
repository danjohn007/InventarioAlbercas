<?php
/**
 * Configuración de Base URL automática
 */
class Config {
    // Detectar URL base automáticamente
    public static function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $path = str_replace(basename($script), '', $script);
        
        return $protocol . "://" . $host . $path;
    }
    
    // Obtener ruta de instalación
    public static function getBasePath() {
        return str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    }
    
    // Cargar variables de entorno
    public static function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                }
            }
        }
    }
    
    // Obtener valor de configuración
    public static function get($key, $default = null) {
        self::loadEnv();
        return isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }
}

// Definir constantes globales
define('BASE_URL', Config::getBaseUrl());
define('BASE_PATH', Config::getBasePath());
define('ROOT_PATH', dirname(__DIR__));

// Cargar configuración de entorno
Config::loadEnv();

// Configuración de zona horaria
date_default_timezone_set(Config::get('APP_TIMEZONE', 'America/Mexico_City'));

// Configuración de errores según entorno
if (Config::get('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
