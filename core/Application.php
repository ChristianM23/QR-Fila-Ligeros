<?php
/**
 * Clase Principal de la Aplicación - CRM Ligeros v2.0
 * Esta clase coordina toda la aplicación y mantiene compatibilidad con el sistema existente
 */

class Application {
    
    private static $instance = null;
    private $config = [];
    private $basePath;
    
    public function __construct($basePath = null) {
        self::$instance = $this;
        $this->basePath = $basePath ?: dirname(__DIR__);
        
        $this->loadConfiguration();
        $this->defineConstants();
        $this->registerAutoloader();
    }
    
    public static function getInstance() {
        return self::$instance;
    }
    
    /**
     * Cargar configuración desde archivos
     */
    private function loadConfiguration() {
        // Cargar configuración principal
        $configFile = $this->basePath . '/config/app.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
        
        // Cargar configuración de seguridad existente si existe
        $securityFile = $this->basePath . '/config/security.php';
        if (file_exists($securityFile)) {
            require_once $securityFile;
        }
    }
    
    /**
 * Definir constantes para compatibilidad con sistema existente
 * VERIFICAR si ya existen antes de definir
 */
private function defineConstants() {
    // Constantes de aplicación
    if (!defined('APP_NAME')) define('APP_NAME', $this->config['name']);
    if (!defined('APP_VERSION')) define('APP_VERSION', $this->config['version']);
    if (!defined('APP_ENV')) define('APP_ENV', $this->config['env']);
    if (!defined('BASE_URL')) define('BASE_URL', $this->config['url'] . '/');
    
    // Constantes de base de datos
    if (!defined('DB_HOST')) define('DB_HOST', $this->config['database']['host']);
    if (!defined('DB_NAME')) define('DB_NAME', $this->config['database']['name']);
    if (!defined('DB_USER')) define('DB_USER', $this->config['database']['user']);
    if (!defined('DB_PASS')) define('DB_PASS', $this->config['database']['pass']);
    if (!defined('DB_CHARSET')) define('DB_CHARSET', $this->config['database']['charset']);
    
    // Constantes de seguridad
    if (!defined('JWT_SECRET')) define('JWT_SECRET', $this->config['security']['jwt_secret']);
    if (!defined('JWT_EXPIRE')) define('JWT_EXPIRE', $this->config['security']['jwt_expire']);
    
    // Rutas importantes
    if (!defined('LOG_PATH')) define('LOG_PATH', $this->basePath . '/storage/logs/');
    if (!defined('QR_PATH')) define('QR_PATH', $this->basePath . '/storage/qr-codes/');
    if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', $this->basePath . '/public/uploads/');
    
    // Crear directorios si no existen
    $this->ensureDirectoriesExist();
}
    
    /**
     * Autoloader simple para las nuevas clases
     */
    private function registerAutoloader() {
        spl_autoload_register(function ($class) {
            // Mapeo de namespaces a directorios
            $prefixes = [
                'App\\Controllers\\' => $this->basePath . '/app/Controllers/',
                'App\\Models\\' => $this->basePath . '/app/Models/',
                'App\\Services\\' => $this->basePath . '/app/Services/',
                'App\\Middleware\\' => $this->basePath . '/app/Middleware/',
                'Core\\Security\\' => $this->basePath . '/core/Security/',
                'Core\\Http\\' => $this->basePath . '/core/Http/',
                'Core\\Database\\' => $this->basePath . '/core/Database/',
            ];
            
            foreach ($prefixes as $prefix => $baseDir) {
                if (strncmp($prefix, $class, strlen($prefix)) === 0) {
                    $relativeClass = substr($class, strlen($prefix));
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                    
                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        });
    }
    
    /**
     * Crear directorios necesarios
     */
    private function ensureDirectoriesExist() {
        $directories = [
            $this->basePath . '/storage/logs',
            $this->basePath . '/storage/qr-codes',
            $this->basePath . '/storage/cache',
            $this->basePath . '/storage/sessions',
            $this->basePath . '/public/uploads',
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Inicializar sistema existente de seguridad
     */
    public function initializeLegacySystem() {
        // Cargar Router
        require_once $this->basePath . '/core/Http/Router.php';
        
        // Cargar sistema de seguridad desde la nueva ubicación
        $securityFiles = [
            $this->basePath . '/core/Security/SecurityManager.php',
            $this->basePath . '/core/Security/SecurityBootstrap.php',
        ];
        
        foreach ($securityFiles as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
        
        // Inicializar seguridad si está configurado
        if (class_exists('SecurityBootstrap')) {
            SecurityBootstrap::initialize();
        }
    }
    
    /**
     * Ejecutar aplicación con sistema de rutas moderno
     */
    public function run() {
        try {
            // Inicializar sistema de seguridad
            $this->initializeLegacySystem();
            
            // Crear router y cargar rutas
            $router = new Router();
            $router->loadRoutes($this->basePath . '/config/routes.php');
            
            try {
                // Resolver ruta actual
                $route = $router->resolve();
                $result = $route->handle();
                
                // Si el resultado es una respuesta, mostrarla
                if (is_string($result)) {
                    echo $result;
                }
                
            } catch (RouteNotFoundException $e) {
                // Ruta no encontrada - mostrar 404
                $this->show404();
            } catch (Exception $e) {
                // Error en el controlador
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Mostrar página 404
     */
    private function show404() {
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        echo "<p>La página que buscas no existe.</p>";
        echo "<p><a href='/'>← Volver al inicio</a></p>";
    }
    
    /**
     * Mostrar dashboard
     */
    private function showDashboard() {
        // Verificar si hay sesión activa
        if (!$this->isUserLoggedIn()) {
            $this->redirectToLogin();
            return;
        }
        
        echo "<h1>🚀 Dashboard - Sistema Migrado</h1>";
        echo "<p>¡El sistema se ha migrado exitosamente a la nueva estructura!</p>";
        echo "<p><strong>Usuario logueado:</strong> Sí</p>";
        echo "<p><strong>Versión:</strong> " . APP_VERSION . "</p>";
        echo "<p><a href='/login'>Ir al Login</a></p>";
        echo "<p><a href='/logout'>Cerrar Sesión</a></p>";
    }
    
    /**
     * Mostrar login usando el sistema existente
     */
    private function showLogin() {
        // Si ya está logueado, redirigir a dashboard
        if ($this->isUserLoggedIn()) {
            header('Location: /dashboard');
            exit;
        }
        
        // Cargar el sistema de login existente
        $loginFile = $this->basePath . '/login.php';
        if (file_exists($loginFile)) {
            // Incluir el archivo de login actual
            include $loginFile;
        } else {
            echo "<h1>🔐 Login</h1>";
            echo "<p>Sistema de login en migración...</p>";
            echo "<p>El archivo login.php será adaptado próximamente.</p>";
        }
    }
    
    /**
     * Manejar requests de API
     */
    private function handleApi() {
        $apiFile = $this->basePath . '/api/index.php';
        if (file_exists($apiFile)) {
            include $apiFile;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'API en desarrollo']);
        }
    }
    
    /**
     * Verificar si hay usuario logueado
     */
    private function isUserLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Redirigir a login
     */
    private function redirectToLogin() {
        header('Location: /login');
        exit;
    }
    
    /**
     * Manejar errores
     */
    private function handleError($exception) {
        // Log del error
        error_log('Application Error: ' . $exception->getMessage());
        
        if ($this->config['debug'] ?? false) {
            echo "<h1>⚠️ Error de Aplicación:</h1>";
            echo "<pre>" . htmlspecialchars($exception->getMessage()) . "</pre>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            echo "<h1>Error interno del servidor</h1>";
            echo "<p>Por favor, contacta al administrador.</p>";
        }
    }
    
    /**
     * Obtener configuración
     */
    public function config($key = null, $default = null) {
        if ($key === null) {
            return $this->config;
        }
        
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Obtener ruta base
     */
    public function getBasePath() {
        return $this->basePath;
    }
}

// Función helper global para acceder a la aplicación
function app() {
    return Application::getInstance();
}

// Función helper para configuración
function config($key = null, $default = null) {
    return app()->config($key, $default);
}
?>