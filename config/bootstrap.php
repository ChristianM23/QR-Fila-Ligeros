<?php
/**
 * Bootstrap de Transición - Maneja compatibilidad entre sistema antiguo y nuevo
 */

// Evitar ejecución directa
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    die('Acceso directo no permitido');
}

// Solo cargar si no se ha cargado ya
if (!defined('CRM_BOOTSTRAP_LOADED')) {
    define('CRM_BOOTSTRAP_LOADED', true);
    
    // Determinar si estamos en el nuevo sistema o el antiguo
    $isNewSystem = defined('PROJECT_ROOT');
    
    if ($isNewSystem) {
        // Nuevo sistema - usar rutas nuevas
        $basePath = PROJECT_ROOT;
        $securityPath = $basePath . '/core/Security/';
    } else {
        // Sistema antiguo - usar rutas antiguas
        $basePath = dirname(__DIR__);
        $securityPath = $basePath . '/security/';
    }
    
    // Cargar archivos de seguridad desde la ubicación correcta
    $securityFiles = [
        'SecurityManager.php',
        'SecurityBootstrap.php'
    ];
    
    foreach ($securityFiles as $file) {
        $newPath = $basePath . '/core/Security/' . $file;
        $oldPath = $basePath . '/security/' . $file;
        
        if (file_exists($newPath)) {
            require_once $newPath;
        } elseif (file_exists($oldPath)) {
            require_once $oldPath;
        }
    }
    
    // Definir constantes solo si no existen
    if (!defined('LOG_PATH')) {
        define('LOG_PATH', $basePath . '/storage/logs/');
    }
    if (!defined('QR_PATH')) {
        define('QR_PATH', $basePath . '/storage/qr-codes/');
    }
    if (!defined('UPLOAD_PATH')) {
        define('UPLOAD_PATH', $basePath . '/public/uploads/');
    }
}