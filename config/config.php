<?php
/**
 * Configuración Principal
 */

// ============================================================================
// CONFIGURACIÓN DE ENTORNO
// ============================================================================
define('APP_ENV', 'development'); // Cambiar a 'production' en producción
define('APP_VERSION', '1.0.0');
define('APP_NAME', 'CRM Ligeros');

// ============================================================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'crm-ligeros');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================================
// CONFIGURACIÓN DE URLs
// ============================================================================
define('BASE_URL', 'http://crm-ligeros.test/');
define('API_URL', BASE_URL . 'api/');
define('VIEWS_URL', BASE_URL . 'views/');
define('ADMIN_URL', BASE_URL . 'admin/');

// ============================================================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================================================
define('JWT_SECRET', 'ligeros_1873_fila_CRM_' . hash('sha256', 'secret_key_change_in_production'));
define('JWT_EXPIRE', 3600); // 1 hora
define('AUTO_INIT_SECURITY', true); // Inicializar seguridad automáticamente

// ============================================================================
// CONFIGURACIÓN DE ARCHIVOS Y DIRECTORIOS
// ============================================================================
define('SRC_PATH', __DIR__ . '/../src/');
define('QR_PATH', SRC_PATH . 'qr-codes/');
define('LOG_PATH', __DIR__ . '/../logs/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('CACHE_PATH', __DIR__ . '/../cache/');

// ============================================================================
// CONFIGURACIÓN DE ZONA HORARIA
// ============================================================================
date_default_timezone_set('Europe/Madrid');

// ============================================================================
// CONFIGURACIÓN DE ERRORES (SEGÚN ENTORNO)
// ============================================================================
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// ============================================================================
// HEADERS DE SEGURIDAD BÁSICOS
// ============================================================================
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// ============================================================================
// CARGAR CONFIGURACIÓN DE SEGURIDAD
// ============================================================================
require_once __DIR__ . '/security.php';

// ============================================================================
// CARGAR SISTEMA DE SEGURIDAD
// ============================================================================
require_once __DIR__ . '/../security/SecurityBootstrap.php';

// ============================================================================
// FUNCIONES DE UTILIDAD
// ============================================================================

/**
 * Función para debug (solo en desarrollo)
 */
function debug($data, $die = false) {
    if (APP_ENV === 'development') {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
        if ($die) die();
    }
}

/**
 * Función para obtener URL base
 */
function getBaseUrl() {
    return BASE_URL;
}

/**
 * Función para obtener URL de API
 */
function getApiUrl() {
    return API_URL;
}

/**
 * Función para log de aplicación
 */
function logApp($message, $level = 'INFO') {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $logFile = LOG_PATH . 'app_' . date('Y-m') . '.log';
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Función para verificar si estamos en producción
 */
function isProduction() {
    return APP_ENV === 'production';
}

/**
 * Función para verificar si estamos en desarrollo
 */
function isDevelopment() {
    return APP_ENV === 'development';
}

// ============================================================================
// VERIFICACIONES INICIALES
// ============================================================================

// Verificar que los directorios existen
$requiredDirs = [
    LOG_PATH,
    QR_PATH,
    UPLOAD_PATH,
    CACHE_PATH
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ============================================================================
// INICIALIZACIÓN FINAL
// ============================================================================

// Log de inicialización
logApp('Aplicación inicializada - Versión ' . APP_VERSION);

// Mensaje de confirmación en desarrollo
if (isDevelopment()) {
    // Solo mostrar si se accede directamente a config.php
    if (basename($_SERVER['PHP_SELF']) === 'config.php') {
        echo "<h1>🚀 " . APP_NAME . " - Configuración Cargada</h1>";
        echo "<p>✅ Entorno: <strong>" . APP_ENV . "</strong></p>";
        echo "<p>✅ Versión: <strong>" . APP_VERSION . "</strong></p>";
        echo "<p>✅ Base URL: <strong>" . BASE_URL . "</strong></p>";
        echo "<p>✅ Seguridad: <strong>" . (AUTO_INIT_SECURITY ? 'Activada' : 'Manual') . "</strong></p>";
        echo "<p>✅ Zona horaria: <strong>" . date_default_timezone_get() . "</strong></p>";
        echo "<p>✅ Directorios creados correctamente</p>";
        
        // Mostrar estado de seguridad
        if (class_exists('SecurityBootstrap')) {
            $securityStatus = SecurityBootstrap::getSecurityStatus();
            echo "<h2>🔒 Estado de Seguridad</h2>";
            foreach ($securityStatus as $key => $value) {
                $status = $value ? '✅' : '❌';
                echo "<p>$status <strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> " . ($value ? 'OK' : 'No') . "</p>";
            }
        }
    }
}
?>