<?php
/**
 * Configuración de Seguridad
 */

// Configuración de Rate Limiting
define('RATE_LIMIT_REQUESTS', 100);        // Requests por hora por IP
define('RATE_LIMIT_TIME_WINDOW', 3600);    // 1 hora en segundos
define('RATE_LIMIT_LOGIN', 5);             // Intentos de login por IP
define('RATE_LIMIT_LOGIN_WINDOW', 900);    // 15 minutos

// Configuración de Brute Force Protection
define('MAX_LOGIN_ATTEMPTS', 5);           // Intentos máximos de login
define('LOCKOUT_TIME', 900);               // 15 minutos de bloqueo

// Configuración de Passwords
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_UPPERCASE', true);
define('REQUIRE_LOWERCASE', true);
define('REQUIRE_NUMBERS', true);
define('REQUIRE_SPECIAL_CHARS', true);

// Configuración de JWT
define('JWT_ALGORITHM', 'HS256');
define('JWT_REFRESH_EXPIRE', 604800);      // 7 días para refresh token

// IPs permitidas para administración (opcional)
define('ADMIN_ALLOWED_IPS', [
    '127.0.0.1',
    '::1',
    // Agregar IPs específicas si es necesario
]);

// Dominios permitidos para CORS
define('ALLOWED_ORIGINS', [
    'http://localhost',
    'http://crm-ligeros.test',
    'http://localhost:3000',
    'https://crm-ligeros.test' // Para producción
]);

// Configuración de archivos
define('MAX_UPLOAD_SIZE', 5242880);        // 5MB máximo por archivo
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'application/pdf'
]);

// Configuración de logs de seguridad
define('SECURITY_LOG_RETENTION', 90);      // Días que se mantienen los logs
define('LOG_SENSITIVE_DATA', false);       // No loggear datos sensibles

/**
 * Funciones de validación de seguridad
 */

/**
 * Validar fortaleza de contraseña
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $errors[] = "La contraseña debe tener al menos " . MIN_PASSWORD_LENGTH . " caracteres";
    }
    
    if (REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una letra mayúscula";
    }
    
    if (REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una letra minúscula";
    }
    
    if (REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un número";
    }
    
    if (REQUIRE_SPECIAL_CHARS && !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un carácter especial";
    }
    
    // Verificar contraseñas comunes
    $commonPasswords = [
        'password', '123456', '123456789', 'qwerty', 'abc123',
        'password123', 'admin', 'letmein', 'welcome', 'monkey'
    ];
    
    if (in_array(strtolower($password), $commonPasswords)) {
        $errors[] = "La contraseña es demasiado común";
    }
    
    return $errors;
}

/**
 * Verificar si una IP está en la lista de IPs permitidas para admin
 */
function isAdminIPAllowed($ip) {
    if (empty(ADMIN_ALLOWED_IPS)) {
        return true; // Si no hay restricciones de IP
    }
    
    return in_array($ip, ADMIN_ALLOWED_IPS);
}

/**
 * Generar un token seguro
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Validar que un origen está permitido para CORS
 */
function isOriginAllowed($origin) {
    return in_array($origin, ALLOWED_ORIGINS);
}

/**
 * Limpiar logs antiguos
 */
function cleanupOldLogs() {
    $logDir = LOG_PATH;
    $retentionDays = SECURITY_LOG_RETENTION;
    
    if (!is_dir($logDir)) {
        return;
    }
    
    $files = glob($logDir . '*.log');
    $cutoffTime = time() - ($retentionDays * 24 * 60 * 60);
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoffTime) {
            unlink($file);
        }
    }
}

/**
 * Detectar patrones de ataque en requests
 */
function detectAttackPatterns($input) {
    $patterns = [
        'sql_injection' => '/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b|\btruncate\b)/i',
        'xss' => '/(<script|javascript:|onload=|onerror=|onclick=)/i',
        'path_traversal' => '/(\.\.\/|\.\.\\\|%2e%2e%2f|%2e%2e%5c)/i',
        'command_injection' => '/(\b(nc|netcat|wget|curl|ping|nslookup)\b|[;&|`])/i'
    ];
    
    foreach ($patterns as $type => $pattern) {
        if (preg_match($pattern, $input)) {
            SecurityManager::logSecurityEvent('ATTACK_DETECTED', "Detected $type pattern", [
                'pattern' => $type,
                'input' => substr($input, 0, 200) // Solo log primeros 200 chars
            ]);
            return $type;
        }
    }
    
    return false;
}

/**
 * Configuración de sesiones seguras
 */
function configureSecureSessions() {
    // Configuración de cookies seguras
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isHTTPS() ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    
    // Regenerar ID de sesión periódicamente
    ini_set('session.gc_maxlifetime', 3600); // 1 hora
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    // Configurar nombre de sesión personalizado
    session_name('CRM_LIGEROS_SESSION');
}

/**
 * Detectar si estamos en HTTPS
 */
function isHTTPS() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
           $_SERVER['SERVER_PORT'] == 443 ||
           (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

// Configurar sesiones seguras al cargar este archivo
configureSecureSessions();

// Limpiar logs antiguos (ejecutar ocasionalmente)
if (random_int(1, 100) === 1) {
    cleanupOldLogs();
}
?>