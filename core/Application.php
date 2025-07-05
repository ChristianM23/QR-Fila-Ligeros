<?php
/**
 * Application - Aplicación Moderna CRM Ligeros
 */

class Application {
    
    private static $instance = null;
    private $config = [];
    private $basePath;
    private $services = [];
    
    public function __construct($basePath = null) {
        self::$instance = $this;
        $this->basePath = $basePath ?: dirname(__DIR__);
        
        $this->loadConfiguration();
        $this->defineConstants();
        $this->registerAutoloader();
        $this->initializeServices();
    }
    
    public static function getInstance() {
        return self::$instance;
    }
    
    /**
     * Ejecutar aplicación moderna
     */
    public function run() {
        try {
            // Configurar entorno
            $this->configureEnvironment();
            
            // Crear router y cargar rutas
            $router = $this->get('router');
            $router->loadRoutes($this->basePath . '/config/routes.php');
            
            try {
                // Resolver ruta actual
                $route = $router->resolve();
                $result = $route->handle();
                
                // Enviar respuesta
                if (is_string($result)) {
                    echo $result;
                }
                
            } catch (RouteNotFoundException $e) {
                $this->show404();
            } catch (Exception $e) {
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Cargar configuración
     */
    private function loadConfiguration() {
        // Cargar .env si existe
        $this->loadEnvironmentFile();
        
        // Cargar configuración principal
        $configFile = $this->basePath . '/config/app.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }
    
    /**
     * Cargar archivo .env
     */
    private function loadEnvironmentFile() {
        $envFile = $this->basePath . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value, '"\'');
                }
            }
        }
    }
    
    /**
     * Definir constantes necesarias
     */
    private function defineConstants() {
        // Constantes de aplicación
        if (!defined('APP_NAME')) define('APP_NAME', $this->config['name'] ?? 'CRM Ligeros');
        if (!defined('APP_VERSION')) define('APP_VERSION', $this->config['version'] ?? '2.0.0');
        if (!defined('APP_ENV')) define('APP_ENV', $this->config['env'] ?? 'development');
        if (!defined('BASE_URL')) define('BASE_URL', ($this->config['url'] ?? 'http://localhost') . '/');
        
        // Constantes de base de datos
        if (!defined('DB_HOST')) define('DB_HOST', $this->config['database']['host'] ?? 'localhost');
        if (!defined('DB_NAME')) define('DB_NAME', $this->config['database']['name'] ?? 'crm_ligeros');
        if (!defined('DB_USER')) define('DB_USER', $this->config['database']['user'] ?? 'root');
        if (!defined('DB_PASS')) define('DB_PASS', $this->config['database']['pass'] ?? '');
        if (!defined('DB_CHARSET')) define('DB_CHARSET', $this->config['database']['charset'] ?? 'utf8mb4');
        
        // Rutas
        if (!defined('PROJECT_ROOT')) define('PROJECT_ROOT', $this->basePath);
        if (!defined('STORAGE_PATH')) define('STORAGE_PATH', $this->basePath . '/storage/');
        if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', $this->basePath . '/public/');
        
        // Crear directorios necesarios
        $this->createDirectories();
    }
    
    /**
     * Autoloader moderno
     */
    private function registerAutoloader() {
        spl_autoload_register(function ($class) {
            $prefixes = [
                'App\\Controllers\\' => $this->basePath . '/app/Controllers/',
                'App\\Models\\' => $this->basePath . '/app/Models/',
                'App\\Services\\' => $this->basePath . '/app/Services/',
                'App\\Middleware\\' => $this->basePath . '/app/Middleware/',
                'Core\\' => $this->basePath . '/core/',
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
     * Inicializar servicios modernos
     */
    private function initializeServices() {
        // Router
        $this->singleton('router', function() {
            require_once $this->basePath . '/core/Http/Router.php';
            return new Router();
        });
        
        // Database
        $this->singleton('db', function() {
            return $this->createDatabaseConnection();
        });
        
        // Security
        $this->singleton('security', function() {
            require_once $this->basePath . '/core/Security/SecurityManager.php';
            return new SecurityManager();
        });
        
        // Auth
        $this->singleton('auth', function() {
            require_once $this->basePath . '/core/Security/AuthManager.php';
            return new AuthManager();
        });
    }
    
    /**
     * Crear conexión a base de datos
     */
    private function createDatabaseConnection() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            return $pdo;
            
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Configurar entorno
     */
    private function configureEnvironment() {
        // Zona horaria
        date_default_timezone_set('Europe/Madrid');
        
        // Configuración de errores
        if (APP_ENV === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ALL & ~E_NOTICE);
            ini_set('display_errors', 0);
        }
        
        // Configurar sesiones
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', $this->isHTTPS() ? 1 : 0);
            ini_set('session.use_strict_mode', 1);
            session_name('CRM_SESSION');
            session_start();
        }
    }
    
    /**
     * Crear directorios necesarios
     */
    private function createDirectories() {
        $directories = [
            $this->basePath . '/storage/logs',
            $this->basePath . '/storage/cache',
            $this->basePath . '/storage/sessions',
            $this->basePath . '/storage/uploads',
            $this->basePath . '/public/uploads',
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Registrar servicio singleton
     */
    public function singleton($name, $callback) {
        $this->services[$name] = $callback;
    }
    
    /**
     * Obtener servicio
     */
    public function get($name) {
        if (!isset($this->services[$name])) {
            throw new Exception("Service '$name' not found");
        }
        
        $service = $this->services[$name];
        
        if (is_callable($service)) {
            $this->services[$name] = $service();
            return $this->services[$name];
        }
        
        return $service;
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
     * Mostrar página 404
     */
    private function show404() {
        http_response_code(404);
        
        $html = '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Página no encontrada</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
                .error-container { max-width: 500px; margin: 0 auto; }
                .error-code { font-size: 6rem; color: #667eea; margin: 0; }
                .error-title { font-size: 2rem; margin: 20px 0; }
                .error-description { color: #666; margin: 20px 0; }
                .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-code">404</div>
                <h1 class="error-title">Página no encontrada</h1>
                <p class="error-description">La página que buscas no existe o ha sido movida.</p>
                <a href="/" class="btn">🏠 Volver al Inicio</a>
                <a href="/login" class="btn">🔐 Iniciar Sesión</a>
            </div>
        </body>
        </html>';
        
        echo $html;
    }
    
    /**
     * Manejar errores
     */
    private function handleError($exception) {
        error_log('Application Error: ' . $exception->getMessage());
        
        http_response_code(500);
        
        if (APP_ENV === 'development') {
            echo "<h1>Error de Aplicación</h1>";
            echo "<pre>" . htmlspecialchars($exception->getMessage()) . "</pre>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            echo "<h1>Error interno del servidor</h1>";
            echo "<p>Por favor, contacta al administrador.</p>";
        }
    }
    
    /**
     * Detectar HTTPS
     */
    private function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Obtener ruta base
     */
    public function getBasePath() {
        return $this->basePath;
    }
}

// Funciones helper globales
function app($service = null) {
    $app = Application::getInstance();
    return $service ? $app->get($service) : $app;
}

function config($key = null, $default = null) {
    return app()->config($key, $default);
}

function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}
?>