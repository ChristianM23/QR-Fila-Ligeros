<?php
// Configuración para desarrollo local en Laragon
define('DB_HOST', 'localhost');
define('DB_NAME', 'crm-ligeros');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// URLs del proyecto
define('BASE_URL', 'http://localhost/crm_ligeros/');
define('API_URL', BASE_URL . 'api/');
define('VIEWS_URL', BASE_URL . 'views/');

// Configuración de seguridad
define('JWT_SECRET', 'ligeros_1873_fila_CRM');
define('JWT_EXPIRE', 3600); // 1 hora

// Configuración de archivos
define('SRC_PATH', __DIR__ . '/../src/');
define('QR_PATH', SRC_PATH . 'qr-codes/');
define('LOG_PATH', __DIR__ . '/../logs/');

// Timezone
date_default_timezone_set('Europe/Madrid');

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers de seguridad básicos
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
?>