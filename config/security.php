<?php
/**
 * Configuración de Seguridad
 */

// ============================================================================
// CONFIGURACIÓN DE RATE LIMITING
// ============================================================================
define('RATE_LIMIT_REQUESTS', 100);        // Requests por hora por IP
define('RATE_LIMIT_TIME_WINDOW', 3600);    // 1 hora en segundos
define('RATE_LIMIT_LOGIN', 5);             // Intentos de login por IP
define('RATE_LIMIT_LOGIN_WINDOW', 900);    // 15 minutos

// ============================================================================
// CONFIGURACIÓN DE PROTECCIÓN BRUTE FORCE
// ============================================================================
define('MAX_LOGIN_ATTEMPTS', 5);           // Intentos máximos de login
define('LOCKOUT_TIME', 900);               // 15 minutos de bloqueo

// ============================================================================
// CONFIGURACIÓN DE PASSWORDS
// ============================================================================
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_UPPERCASE', true);
define('REQUIRE_LOWERCASE', true);
define('REQUIRE_NUMBERS', true);
define('REQUIRE_SPECIAL_CHARS', true);

// ============================================================================
// CONFIGURACIÓN DE JWT
// ============================================================================
define('JWT_ALGORITHM', 'HS256');
define('JWT_REFRESH_EXPIRE', 604800);      // 7 días para refresh token

// ============================================================================
// IPs PERMITIDAS PARA ADMINISTRACIÓN (OPCIONAL)
// ============================================================================
define('ADMIN_ALLOWED_IPS', [
    '127.0.0.1',
    '::1',
    // Agregar IPs específicas si es necesario para administradores
]);

// ============================================================================
// DOMINIOS PERMITIDOS PARA CORS
// ============================================================================
define('ALLOWED_ORIGINS', [
    'http://localhost',
    'http://crm-ligeros.test',
    'https://crm-ligeros.test', // Para producción
    'http://localhost:3000'     // Para desarrollo frontend
]);

// ============================================================================
// CONFIGURACIÓN DE ARCHIVOS
// ============================================================================
define('MAX_UPLOAD_SIZE', 5242880);        // 5MB máximo por archivo
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'application/pdf'
]);

// ============================================================================
// CONFIGURACIÓN DE LOGS DE SEGURIDAD
// ============================================================================
define('SECURITY_LOG_RETENTION', 90);      // Días que se mantienen los logs
define('LOG_SENSITIVE_DATA', false);       // No loggear datos sensibles

// ============================================================================
// FUNCIONES DE VALIDACIÓN DE SEGURIDAD
// ============================================================================

/**
 * Validar fortaleza de contraseña
 * Verifica que la contraseña cumpla con los requisitos de seguridad
 */
function validatePasswordStrength($password) {
    $errors = [];
    
    // Verificar longitud mínima
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $errors[] = "La contraseña debe tener al menos " . MIN_PASSWORD_LENGTH . " caracteres";
    }
    
    // Verificar mayúsculas
    if (REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una letra mayúscula";
    }
    
    // Verificar minúsculas
    if (REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una letra minúscula";
    }
    
    // Verificar números
    if (REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un número";
    }
    
    // Verificar caracteres especiales
    if (REQUIRE_SPECIAL_CHARS && !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un carácter especial";
    }
    
    // Verificar contraseñas comunes (lista básica)
    $commonPasswords = [
        'password', '123456', '123456789', 'qwerty', 'abc123',
        'password123', 'admin', 'welcome',
        'ligeros', 'asociacion', 'crm123'
    ];
    
    if (in_array(strtolower($password), $commonPasswords)) {
        $errors[] = "La contraseña es demasiado común, usa una más segura";
    }
    
    return $errors;
}

/**
 * Verificar si una IP está en la lista de IPs permitidas para admin
 */
function isAdminIPAllowed($ip) {
    // Si no hay restricciones de IP, permitir todas
    if (empty(ADMIN_ALLOWED_IPS)) {
        return true;
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
 * Detectar patrones de ataque en requests - VERSIÓN CORREGIDA
 * Patrones más específicos y menos falsos positivos
 */
function detectAttackPatterns($input) {
    // Lista blanca de rutas permitidas
    $allowedPaths = ['/login', '/dashboard', '/', '/api', '/logout', '/debug-security.php'];
    
    // Si es una ruta, verificar lista blanca primero
    if (strpos($input, '/') === 0) {
        $path = parse_url($input, PHP_URL_PATH);
        if (in_array($path, $allowedPaths)) {
            return false; // Permitir rutas específicas
        }
    }
    
    $patterns = [
        // SQL Injection - más específico
        'sql_injection' => '/\b(union\s+select|select\s+.*\s+from|insert\s+into|update\s+\w+\s+set|delete\s+from|drop\s+table|or\s+1\s*=\s*1)\b/i',
        
        // XSS - más específico  
        'xss' => '/<script[^>]*>|javascript\s*:|on\w+\s*=\s*["\'][^"\']*["\']>/i',
        
        // Path Traversal - más específico
        'path_traversal' => '/(\.\.\/){2,}|(\.\.\\\\){2,}|(%2e%2e%2f){2,}/i',
        
        // Command Injection - MUY específico para evitar falsos positivos
        'command_injection' => '/[\|;`]\s*(nc|netcat|wget|curl|cat|ls|rm|cp)\s+/i'
    ];
    
    foreach ($patterns as $type => $pattern) {
        if (preg_match($pattern, $input)) {
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

// ============================================================================
// INICIALIZACIÓN AUTOMÁTICA
// ============================================================================

// Configurar sesiones seguras al cargar este archivo
configureSecureSessions();

// Limpiar logs antiguos ocasionalmente (1% de probabilidad)
if (random_int(1, 100) === 1) {
    cleanupOldLogs();
}

// Mostrar mensaje de confirmación si se ejecuta directamente
if (basename($_SERVER['PHP_SELF']) === 'security.php') {
    echo "🔒 Configuración de seguridad cargada correctamente\n";
    echo "✅ Sesiones seguras configuradas\n";
    echo "✅ Funciones de validación disponibles\n";
}
?>