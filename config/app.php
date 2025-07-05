<?php
/**
 * Configuración Principal de la Aplicación
 * CRM Ligeros v2.0
 */

return [
    // Información de la aplicación
    'name' => 'CRM Ligeros',
    'version' => '2.0.0',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    'timezone' => 'Europe/Madrid',
    
    // URLs
    'url' => $_ENV['APP_URL'] ?? 'http://crm-ligeros.test',
    'api_url' => ($_ENV['APP_URL'] ?? 'http://crm-ligeros.test') . '/api',
    
    // Rutas de directorios  
    'paths' => [
        'app' => __DIR__ . '/../app',
        'core' => __DIR__ . '/../core', 
        'public' => __DIR__ . '/../public',
        'storage' => __DIR__ . '/../storage',
        'config' => __DIR__,
        'database' => __DIR__ . '/../database',
    ],
    
    // Configuración de base de datos (mantenemos compatibilidad)
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_DATABASE'] ?? 'crm-ligeros',
        'user' => $_ENV['DB_USERNAME'] ?? 'root',
        'pass' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],
    
    // Seguridad (mantenemos las constantes existentes)
    'security' => [
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'ligeros_1873_fila_CRM_secret',
        'jwt_expire' => $_ENV['JWT_EXPIRE'] ?? 3600,
        'rate_limit_requests' => $_ENV['RATE_LIMIT_REQUESTS'] ?? 100,
        'rate_limit_window' => $_ENV['RATE_LIMIT_WINDOW'] ?? 3600,
        'max_login_attempts' => $_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5,
        'lockout_time' => $_ENV['LOCKOUT_TIME'] ?? 900,
    ]
];
?>