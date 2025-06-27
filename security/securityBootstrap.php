<?php
/**
 * Integración del Sistema de Seguridad
 */

/**
 * Clase para inicializar todo el sistema de seguridad
 */
class SecurityBootstrap {
    
    private static $initialized = false;
    
    /**
     * Inicializar todo el sistema de seguridad
     * Se debe llamar al inicio de la aplicación
     */
    public static function initialize() {
        if (self::$initialized) {
            return; // Ya inicializado
        }
        
        try {
            // 1. Cargar dependencias
            self::loadDependencies();
            
            // 2. Configurar entorno seguro
            self::configureSecureEnvironment();
            
            // 3. Ejecutar middleware de seguridad
            SecurityMiddleware::handle();
            
            // 4. Marcar como inicializado
            self::$initialized = true;
            
        } catch (Exception $e) {
            // Log del error y continuar
            error_log("Error en SecurityBootstrap: " . $e->getMessage());
            
            // En desarrollo, mostrar error
            if (defined('APP_ENV') && APP_ENV === 'development') {
                echo "Error de seguridad: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Cargar todas las dependencias necesarias
     */
    private static function loadDependencies() {
        $basePath = dirname(__FILE__);
        
        // Cargar configuración si no está cargada
        if (!defined('RATE_LIMIT_REQUESTS')) {
            require_once $basePath . '/../config/security.php';
        }
        
        // Cargar clases de seguridad
        require_once $basePath . '/SecurityManager.php';
        require_once $basePath . '/SecurityMiddleware.php';
    }
    
    /**
     * Configurar entorno seguro
     */
    private static function configureSecureEnvironment() {
        // Configurar manejo de errores seguro
        ini_set('display_errors', defined('APP_ENV') && APP_ENV === 'development' ? 1 : 0);
        ini_set('log_errors', 1);
        
        // Configurar límites de ejecución
        set_time_limit(30);
        ini_set('memory_limit', '128M');
        
        // Configurar sesiones seguras (ya se hace en security.php, pero por seguridad)
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isHTTPS() ? 1 : 0);
            ini_set('session.use_strict_mode', 1);
            session_name('CRM_LIGEROS_SESSION');
        }
    }
    
    /**
     * Verificar si el sistema está correctamente inicializado
     */
    public static function isInitialized() {
        return self::$initialized;
    }
    
    /**
     * Obtener información del estado de seguridad
     */
    public static function getSecurityStatus() {
        return [
            'initialized' => self::$initialized,
            'middleware_active' => class_exists('SecurityMiddleware') && SecurityMiddleware::isActive(),
            'security_manager_loaded' => class_exists('SecurityManager'),
            'config_loaded' => defined('RATE_LIMIT_REQUESTS'),
            'session_secure' => ini_get('session.cookie_httponly') == 1,
            'https' => isHTTPS(),
            'error_reporting' => ini_get('display_errors')
        ];
    }
}

/**
 * Función de utilidad para inicializar seguridad fácilmente
 */
function initSecurity() {
    SecurityBootstrap::initialize();
}

// ============================================================================
// AUTO-INICIALIZACIÓN (opcional)
// ============================================================================

// Si se define AUTO_INIT_SECURITY, inicializar automáticamente
if (defined('AUTO_INIT_SECURITY') && AUTO_INIT_SECURITY === true) {
    SecurityBootstrap::initialize();
}
?>