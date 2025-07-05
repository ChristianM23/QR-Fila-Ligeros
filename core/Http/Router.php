<?php
/**
 * Router - Sistema de Rutas para CRM Ligeros
 */

class Router {
    
    private $routes = [];
    private $currentRoute = null;
    
    /**
     * Registrar ruta GET
     */
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Registrar ruta POST
     */
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Registrar ruta PUT
     */
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Registrar ruta DELETE
     */
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Agregar ruta al sistema
     */
    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->pathToPattern($path)
        ];
    }
    
    /**
     * Convertir path a patrón regex
     */
    private function pathToPattern($path) {
        // Convertir parámetros {id} a grupos de captura
        $pattern = preg_replace('/\{([^}]+)\}/', '([^\/]+)', $path);
        // Escapar caracteres especiales
        $pattern = str_replace('/', '\/', $pattern);
        // Anclar al inicio y final
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Resolver ruta actual
     */
    public function resolve($request = null) {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $this->getCurrentPath();
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                // Quitar el match completo
                array_shift($matches);
                
                $this->currentRoute = $route;
                return new Route($route['handler'], $matches);
            }
        }
        
        // No se encontró ruta
        throw new RouteNotFoundException("Ruta no encontrada: $method $path");
    }
    
    /**
     * Obtener path actual limpio
     */
    private function getCurrentPath() {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remover query string
        if (($pos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $pos);
        }
        
        // Normalizar path
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        
        return $path;
    }
    
    /**
     * Cargar rutas desde archivo
     */
    public function loadRoutes($routesFile) {
        if (file_exists($routesFile)) {
            $router = $this;
            require $routesFile;
        }
    }
    
    /**
     * Obtener todas las rutas registradas
     */
    public function getRoutes() {
        return $this->routes;
    }
    
    /**
     * Generar URL para una ruta con nombre
     */
    public function url($name, $params = []) {
        // Por implementar en versiones futuras
        return '#';
    }
}

/**
 * Clase Route - Representa una ruta específica
 */
class Route {
    
    private $handler;
    private $parameters;
    
    public function __construct($handler, $parameters = []) {
        $this->handler = $handler;
        $this->parameters = $parameters;
    }
    
    /**
     * Ejecutar el handler de la ruta
     */
    public function handle($request = null) {
        if (is_string($this->handler)) {
            return $this->handleStringHandler($request);
        }
        
        if (is_callable($this->handler)) {
            return call_user_func($this->handler, $request, ...$this->parameters);
        }
        
        throw new Exception("Handler de ruta inválido");
    }
    
    /**
     * Manejar handler tipo string (Controller@method)
     */
    private function handleStringHandler($request) {
        if (strpos($this->handler, '@') === false) {
            throw new Exception("Handler debe tener formato Controller@method");
        }
        
        list($controllerName, $method) = explode('@', $this->handler);
        
        // Buscar el controlador
        $controllerClass = $this->findController($controllerName);
        
        if (!class_exists($controllerClass)) {
            throw new Exception("Controlador no encontrado: $controllerClass");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Método no encontrado: $controllerClass::$method");
        }
        
        return $controller->$method($request, ...$this->parameters);
    }
    
    /**
     * Buscar clase de controlador
     */
    private function findController($name) {
        // Intentar con namespace App\Controllers
        $withNamespace = "App\\Controllers\\$name";
        if (class_exists($withNamespace)) {
            return $withNamespace;
        }
        
        // Intentar sin namespace
        if (class_exists($name)) {
            return $name;
        }
        
        // Intentar cargando archivo
        $file = PROJECT_ROOT . "/app/Controllers/$name.php";
        if (file_exists($file)) {
            require_once $file;
            return "App\\Controllers\\$name";
        }
        
        throw new Exception("Controlador no encontrado: $name");
    }
    
    /**
     * Obtener parámetros de la ruta
     */
    public function getParameters() {
        return $this->parameters;
    }
}

/**
 * Excepción para rutas no encontradas
 */
class RouteNotFoundException extends Exception {
    //
}
?>