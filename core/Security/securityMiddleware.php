<?php
/**
 * Middleware de Seguridad
 */

class SecurityMiddleware {
    
    // ========================================================================
    // CONFIGURACIONES DEL MIDDLEWARE
    // ========================================================================
    
    // Rutas que no requieren autenticación
    private static $publicRoutes = [
        'auth/login',
        'auth/register', 
        'qr/scan',
        'test'  // Para testing
    ];
    
    // Rutas que no requieren validación CSRF
    private static $csrfExemptRoutes = [
        'auth/login',
        'qr/scan'
    ];
    
    // ========================================================================
    // MÉTODO PRINCIPAL DEL MIDDLEWARE
    // ========================================================================
    
    /**
     * Ejecutar todas las verificaciones de seguridad
     * Este método se debe llamar al inicio de cada request
     */
    public static function handle() {
        try {
            // 1. Configurar headers de seguridad básicos
            self::applySecurityHeaders();
            
            // 2. Obtener información del request
            $ip = SecurityManager::getRealIPAddress();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $method = $_SERVER['REQUEST_METHOD'];
            $uri = $_SERVER['REQUEST_URI'];
            
            // 3. Detectar y bloquear patrones de ataque
            if (self::detectMaliciousRequest()) {
                self::blockRequest('MALICIOUS_PATTERN_DETECTED', 'Patrón de ataque detectado');
                return;
            }
            
            // 4. Rate limiting general por IP
            if (!self::checkGeneralRateLimit($ip)) {
                self::blockRequest('RATE_LIMIT_EXCEEDED', 'Límite de requests excedido', $ip);
                return;
            }
            
            // 5. Protección específica para endpoints de login
            if (self::isLoginEndpoint($uri) && !self::checkLoginRateLimit($ip)) {
                self::blockRequest('LOGIN_RATE_LIMIT_EXCEEDED', 'Demasiados intentos de login', $ip);
                return;
            }
            
            // 6. Verificar protección contra brute force en login
            if (self::isLoginEndpoint($uri) && !SecurityManager::checkBruteForce($ip)) {
                self::blockRequest('BRUTE_FORCE_BLOCKED', 'IP bloqueada por intentos fallidos', $ip);
                return;
            }
            
            // 7. Validación CSRF para requests POST/PUT/DELETE
            if (self::requiresCSRFValidation($method, $uri)) {
                if (!self::validateCSRFToken()) {
                    self::blockRequest('CSRF_VALIDATION_FAILED', 'Token CSRF inválido o faltante', $ip);
                    return;
                }
            }
            
            // 8. Verificar restricciones de IP para administradores
            if (self::isAdminEndpoint($uri) && !isAdminIPAllowed($ip)) {
                self::blockRequest('ADMIN_IP_BLOCKED', 'IP no autorizada para administración', $ip);
                return;
            }
            
            // 9. Log del request si es necesario
            self::logRequestIfNeeded($method, $uri, $ip);
            
        } catch (Exception $e) {
            // En caso de error en el middleware, log y continuar
            SecurityManager::logSecurityEvent('MIDDLEWARE_ERROR', 'Error en middleware de seguridad', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // ========================================================================
    // APLICACIÓN DE HEADERS DE SEGURIDAD
    // ========================================================================
    
    /**
     * Aplicar headers de seguridad en cada response
     */
    private static function applySecurityHeaders() {
        // Aplicar headers básicos
        SecurityManager::setSecurityHeaders();
        SecurityManager::setCORSHeaders();
        
        // Headers adicionales específicos del middleware
        header('X-Middleware-Security: active');
        header('X-Request-ID: ' . uniqid('req_', true));
    }
    
    // ========================================================================
    // DETECCIÓN DE REQUESTS MALICIOSOS
    // ========================================================================
    
    /**
     * Detectar patrones maliciosos en el request
     */
    private static function detectMaliciousRequest() {
        // Verificar URL
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (detectAttackPatterns($uri)) {
            return true;
        }
        
        // Verificar parámetros GET
        foreach ($_GET as $key => $value) {
            if (detectAttackPatterns($key) || detectAttackPatterns($value)) {
                return true;
            }
        }
        
        // Verificar parámetros POST
        foreach ($_POST as $key => $value) {
            if (is_string($value) && (detectAttackPatterns($key) || detectAttackPatterns($value))) {
                return true;
            }
        }
        
        // Verificar headers sospechosos (User-Agent deshabilitado temporalmente)
        $suspiciousHeaders = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'];
        foreach ($suspiciousHeaders as $header) {
            $value = $_SERVER[$header] ?? '';
            if (!empty($value) && detectAttackPatterns($value)) {
                return true;
            }
        }

        // Verificar User-Agent solo para patrones realmente peligrosos
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!empty($userAgent)) {
            // Solo detectar User-Agents claramente maliciosos
            $dangerousUA = [
                'sqlmap', 'nikto', 'nessus', 'burp', 'dirbuster', 
                'masscan', 'nmap', 'wget', 'curl'
            ];
            
            $userAgentLower = strtolower($userAgent);
            foreach ($dangerousUA as $danger) {
                if (strpos($userAgentLower, $danger) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    // ========================================================================
    // RATE LIMITING
    // ========================================================================
    
    /**
     * Verificar rate limiting general
     */
    private static function checkGeneralRateLimit($ip) {
        return SecurityManager::checkRateLimit($ip, RATE_LIMIT_REQUESTS, RATE_LIMIT_TIME_WINDOW);
    }
    
    /**
     * Verificar rate limiting específico para login
     */
    private static function checkLoginRateLimit($ip) {
        return SecurityManager::checkRateLimit($ip . '_login', RATE_LIMIT_LOGIN, RATE_LIMIT_LOGIN_WINDOW);
    }
    
    // ========================================================================
    // VALIDACIÓN CSRF
    // ========================================================================
    
    /**
     * Verificar si el request requiere validación CSRF
     */
    private static function requiresCSRFValidation($method, $uri) {
        // Solo para métodos que modifican datos
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return false;
        }
        
        // Verificar si es una ruta exenta
        $route = self::extractRoute($uri);
        return !in_array($route, self::$csrfExemptRoutes);
    }
    
    /**
     * Validar token CSRF
     */
    private static function validateCSRFToken() {
        // Buscar token en headers o POST data
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                 $_POST['csrf_token'] ?? 
                 $_GET['csrf_token'] ?? '';
        
        if (empty($token)) {
            return false;
        }
        
        return SecurityManager::validateCSRFToken($token);
    }
    
    // ========================================================================
    // IDENTIFICACIÓN DE ENDPOINTS
    // ========================================================================
    
    /**
     * Verificar si es un endpoint de login
     */
    private static function isLoginEndpoint($uri) {
        $route = self::extractRoute($uri);
        return in_array($route, ['auth/login', 'login']);
    }
    
    /**
     * Verificar si es un endpoint de administración
     */
    private static function isAdminEndpoint($uri) {
        $route = self::extractRoute($uri);
        return strpos($route, 'admin/') === 0 ||
               strpos($route, 'users/') === 0 ||
               in_array($route, ['config', 'logs', 'security']);
    }
    
    /**
     * Extraer ruta del URI
     */
    private static function extractRoute($uri) {
        // Remover parámetros de query
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remover prefijos comunes
        $path = ltrim($path, '/');
        $path = preg_replace('/^(api\/|app\/)/', '', $path);
        
        return $path;
    }
    
    // ========================================================================
    // BLOQUEO DE REQUESTS
    // ========================================================================
    
    /**
     * Bloquear request y enviar respuesta de error
     */
    private static function blockRequest($type, $message, $ip = null) {
        $ip = $ip ?? SecurityManager::getRealIPAddress();
        
        // Log del bloqueo
        SecurityManager::logSecurityEvent($type, $message, [
            'ip' => $ip,
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        // Determinar código de respuesta según el tipo
        $statusCode = self::getStatusCodeForBlockType($type);
        
        // Enviar respuesta
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => false,
            'error' => $message,
            'code' => $type,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    /**
     * Obtener código de estado HTTP según el tipo de bloqueo
     */
    private static function getStatusCodeForBlockType($type) {
        switch ($type) {
            case 'RATE_LIMIT_EXCEEDED':
            case 'LOGIN_RATE_LIMIT_EXCEEDED':
            case 'BRUTE_FORCE_BLOCKED':
                return 429; // Too Many Requests
                
            case 'CSRF_VALIDATION_FAILED':
            case 'ADMIN_IP_BLOCKED':
                return 403; // Forbidden
                
            case 'MALICIOUS_PATTERN_DETECTED':
                return 400; // Bad Request
                
            default:
                return 403; // Forbidden por defecto
        }
    }
    
    // ========================================================================
    // LOGGING CONDICIONAL
    // ========================================================================
    
    /**
     * Log del request si es necesario
     */
    private static function logRequestIfNeeded($method, $uri, $ip) {
        // Solo log requests importantes o sospechosos
        $shouldLog = false;
        
        // Log todos los requests POST/PUT/DELETE
        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $shouldLog = true;
        }
        
        // Log requests a endpoints administrativos
        if (self::isAdminEndpoint($uri)) {
            $shouldLog = true;
        }
        
        // Log requests con parámetros sospechosos
        if (count($_GET) > 5 || count($_POST) > 10) {
            $shouldLog = true;
        }
        
        if ($shouldLog) {
            SecurityManager::logSecurityEvent('REQUEST_LOG', 'Request registrado', [
                'method' => $method,
                'uri' => $uri,
                'ip' => $ip,
                'get_params' => count($_GET),
                'post_params' => count($_POST)
            ]);
        }
    }
    
    // ========================================================================
    // MÉTODOS DE UTILIDAD
    // ========================================================================
    
    /**
     * Verificar si el middleware está activo
     */
    public static function isActive() {
        return true;
    }
    
    /**
     * Obtener estadísticas del middleware
     */
    public static function getStats() {
        return [
            'public_routes' => count(self::$publicRoutes),
            'csrf_exempt_routes' => count(self::$csrfExemptRoutes),
            'rate_limit_requests' => RATE_LIMIT_REQUESTS,
            'rate_limit_window' => RATE_LIMIT_TIME_WINDOW,
            'max_login_attempts' => MAX_LOGIN_ATTEMPTS,
            'lockout_time' => LOCKOUT_TIME
        ];
    }
    
    /**
     * Agregar ruta pública (para testing)
     */
    public static function addPublicRoute($route) {
        if (!in_array($route, self::$publicRoutes)) {
            self::$publicRoutes[] = $route;
        }
    }
}
?>