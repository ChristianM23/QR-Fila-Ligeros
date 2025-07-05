<?php
/**
 * BaseController - Controlador Base
 * Funcionalidades comunes para todos los controladores
 */

namespace App\Controllers;

class BaseController {
    
    protected $user = null;
    
    public function __construct() {
        $this->initializeController();
    }
    
    /**
     * Inicializar controlador
     */
    protected function initializeController() {
        // Cargar usuario actual si hay sesión
        $this->loadCurrentUser();
    }
    
    /**
     * Cargar usuario actual desde la sesión
     */
    protected function loadCurrentUser() {
        if (class_exists('AuthManager')) {
            $this->user = \AuthManager::validateSession();
        }
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    protected function isAuthenticated() {
        return $this->user !== false && $this->user !== null;
    }
    
    /**
     * Verificar si el usuario tiene el nivel mínimo requerido
     */
    protected function hasPermission($requiredLevel) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        return $this->user['user_level'] >= $requiredLevel;
    }
    
    /**
     * Requerir autenticación - redirigir a login si no está autenticado
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            exit;
        }
    }
    
    /**
     * Requerir nivel de usuario específico
     */
    protected function requireLevel($level) {
        $this->requireAuth();
        
        if (!$this->hasPermission($level)) {
            $this->unauthorized();
            exit;
        }
    }
    
    /**
     * Cargar vista
     */
    protected function view($viewName, $data = []) {
        return $this->renderView($viewName, $data);
    }
    
    /**
     * Renderizar vista con layout
     */
    protected function renderView($viewName, $data = []) {
        // Extraer variables para la vista
        extract($data);
        
        // Añadir datos globales
        $currentUser = $this->user;
        $isAuthenticated = $this->isAuthenticated();
        $appName = defined('APP_NAME') ? APP_NAME : 'CRM Ligeros';
        $appVersion = defined('APP_VERSION') ? APP_VERSION : '2.0.0';
        
        // Iniciar buffer de salida
        ob_start();
        
        // Buscar archivo de vista
        $viewFile = $this->findViewFile($viewName);
        
        if ($viewFile) {
            include $viewFile;
        } else {
            echo "<h1>Vista no encontrada: $viewName</h1>";
            echo "<p>Archivo esperado: " . str_replace(PROJECT_ROOT, '', $this->getViewPath($viewName)) . "</p>";
        }
        
        $content = ob_get_clean();
        
        // Si la vista no usa layout, retornar contenido directo
        if (strpos($content, '<!DOCTYPE') !== false) {
            return $content;
        }
        
        // Usar layout por defecto
        return $this->wrapWithLayout($content, $data);
    }
    
    /**
     * Buscar archivo de vista
     */
    private function findViewFile($viewName) {
        $possiblePaths = [
            $this->getViewPath($viewName),
            $this->getViewPath($viewName . '.php'),
            PROJECT_ROOT . '/app/Views/' . str_replace('.', '/', $viewName) . '.php'
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Obtener ruta de vista
     */
    private function getViewPath($viewName) {
        return PROJECT_ROOT . '/app/Views/' . str_replace('.', '/', $viewName);
    }
    
    /**
     * Envolver contenido con layout
     */
    private function wrapWithLayout($content, $data = []) {
        $layoutFile = PROJECT_ROOT . '/app/Views/layouts/main.php';
        
        if (file_exists($layoutFile)) {
            // Extraer datos para el layout
            extract($data);
            $currentUser = $this->user;
            $isAuthenticated = $this->isAuthenticated();
            $appName = defined('APP_NAME') ? APP_NAME : 'CRM Ligeros';
            $appVersion = defined('APP_VERSION') ? APP_VERSION : '2.0.0';
            
            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }
        
        // Layout básico si no existe archivo
        return $this->getBasicLayout($content, $data);
    }
    
    /**
     * Layout básico si no existe archivo
     */
    private function getBasicLayout($content, $data = []) {
        $title = $data['title'] ?? 'CRM Ligeros';
        $appName = defined('APP_NAME') ? APP_NAME : 'CRM Ligeros';
        
        return "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$title - $appName</title>
    <link rel='stylesheet' href='/assets/css/app.css'>
</head>
<body>
    <div class='container'>
        <header>
            <h1>$appName</h1>
            " . ($this->isAuthenticated() ? 
                "<p>Bienvenido, {$this->user['username']} | <a href='/logout'>Cerrar Sesión</a></p>" : 
                "<p><a href='/login'>Iniciar Sesión</a></p>"
            ) . "
        </header>
        <main>
            $content
        </main>
    </div>
    <script src='/assets/js/app.js'></script>
</body>
</html>";
    }
    
    /**
     * Redireccionar
     */
    protected function redirect($url, $statusCode = 302) {
        header("Location: $url", true, $statusCode);
        exit;
    }
    
    /**
     * Respuesta JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Respuesta de error 401 - No autorizado
     */
    protected function unauthorized() {
        http_response_code(401);
        echo "<h1>401 - No Autorizado</h1>";
        echo "<p>No tienes permisos para acceder a esta página.</p>";
        echo "<p><a href='/login'>Iniciar Sesión</a> | <a href='/'>Volver al Inicio</a></p>";
    }
    
    /**
     * Respuesta de error 403 - Prohibido
     */
    protected function forbidden() {
        http_response_code(403);
        echo "<h1>403 - Acceso Prohibido</h1>";
        echo "<p>No tienes permisos suficientes para realizar esta acción.</p>";
        echo "<p><a href='/dashboard'>Volver al Dashboard</a></p>";
    }
    
    /**
     * Validar datos de entrada
     */
    protected function validate($data, $rules) {
        if (class_exists('SecurityManager')) {
            return \SecurityManager::validateInput($data, $rules);
        }
        
        // Validación básica si no hay SecurityManager
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (isset($rule['required']) && $rule['required'] && empty($data[$field])) {
                $errors[$field] = "El campo $field es requerido";
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitizar entrada
     */
    protected function sanitize($data, $type = 'string') {
        if (class_exists('SecurityManager')) {
            return \SecurityManager::sanitizeInput($data, $type);
        }
        
        // Sanitización básica
        if (is_array($data)) {
            return array_map(function($item) use ($type) {
                return $this->sanitize($item, $type);
            }, $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Obtener datos del request actual
     */
    protected function getRequestData() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                return $_GET;
            case 'POST':
                return $_POST;
            case 'PUT':
            case 'DELETE':
                parse_str(file_get_contents('php://input'), $data);
                return $data;
            default:
                return [];
        }
    }
    
    /**
     * Generar token CSRF
     */
    protected function getCsrfToken() {
        if (class_exists('SecurityManager')) {
            return \SecurityManager::generateCSRFToken();
        }
        
        // Token básico si no hay SecurityManager
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}
?>