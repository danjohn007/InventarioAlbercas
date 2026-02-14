<?php
/**
 * Clase de enrutamiento para URLs amigables
 */
class Router {
    private $routes = [];
    private $notFound;
    
    public function __construct() {
        $this->notFound = function() {
            http_response_code(404);
            require_once ROOT_PATH . '/views/errors/404.php';
        };
    }
    
    public function get($pattern, $callback) {
        $this->addRoute('GET', $pattern, $callback);
    }
    
    public function post($pattern, $callback) {
        $this->addRoute('POST', $pattern, $callback);
    }
    
    private function addRoute($method, $pattern, $callback) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }
    
    public function dispatch() {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = '#^' . $route['pattern'] . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                return call_user_func_array($route['callback'], $matches);
            }
        }
        
        call_user_func($this->notFound);
    }
    
    private function getUri() {
        $uri = isset($_GET['url']) ? $_GET['url'] : '/';
        $uri = rtrim($uri, '/');
        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        
        // Ensure URI always starts with /
        if ($uri === '' || $uri === false) {
            return '/';
        }
        
        // Add leading slash if not present
        if ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        return $uri;
    }
}
