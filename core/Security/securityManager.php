<?php
/**
 * Clase principal de seguridad
 */

class SecurityManager {
    
    // ========================================================================
    // CONFIGURACIÓN DE HEADERS DE SEGURIDAD
    // ========================================================================
    
    /**
     * Configurar headers de seguridad HTTP
     * Establece headers para prevenir ataques comunes
     */
    public static function setSecurityHeaders() {
        // Prevenir clickjacking (evita que la página se cargue en un iframe)
        header('X-Frame-Options: DENY');
        
        // Prevenir MIME type sniffing (fuerza a usar el Content-Type correcto)
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection (activa el filtro XSS del navegador)
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy (controla qué información se envía en el header Referer)
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy básico
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com; img-src \'self\' data:; font-src \'self\' cdnjs.cloudflare.com;');
        
        // Strict Transport Security (solo en HTTPS)
        if (self::isHTTPS()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Permissions Policy (controla qué APIs pueden usar)
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
    
    // ========================================================================
    // CONFIGURACIÓN CORS
    // ========================================================================
    
    /**
     * Configurar CORS de manera segura
     * Solo permite orígenes específicos definidos en la configuración
     */
    public static function setCORSHeaders() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Verificar si el origen está permitido
        if (isOriginAllowed($origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 horas
        
        // Manejar preflight requests (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    // ========================================================================
    // SANITIZACIÓN Y VALIDACIÓN DE DATOS
    // ========================================================================
    
    /**
     * Sanitizar datos de entrada
     * Limpia los datos según el tipo especificado
     */
    public static function sanitizeInput($data, $type = 'string') {
        // Si es un array, sanitizar cada elemento
        if (is_array($data)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $data);
        }
        
        // Sanitizar según el tipo
        switch ($type) {
            case 'email':
                return filter_var($data, FILTER_SANITIZE_EMAIL);
                
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'url':
                return filter_var($data, FILTER_SANITIZE_URL);
                
            case 'string':
            default:
                // Limpiar HTML, espacios y caracteres especiales
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validar datos según reglas específicas
     * Retorna un array con errores si los hay
     */
    public static function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Verificar si es requerido
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "El campo $field es requerido";
                continue;
            }
            
            // Si está vacío y no es requerido, continuar
            if (empty($value)) continue;
            
            // Validación por tipo
            switch ($rule['type'] ?? 'string') {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "El campo $field debe ser un email válido";
                    }
                    break;
                    
                case 'int':
                    if (!filter_var($value, FILTER_VALIDATE_INT)) {
                        $errors[$field] = "El campo $field debe ser un número entero";
                    }
                    break;
                    
                case 'float':
                    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
                        $errors[$field] = "El campo $field debe ser un número decimal";
                    }
                    break;
                    
                case 'phone':
                    if (!preg_match('/^[+]?[0-9\s\-\(\)]{9,20}$/', $value)) {
                        $errors[$field] = "El campo $field debe ser un teléfono válido";
                    }
                    break;
                    
                case 'dni':
                    if (!preg_match('/^[0-9]{8}[A-Z]$/', $value)) {
                        $errors[$field] = "El campo $field debe ser un DNI válido (8 números + letra)";
                    }
                    break;
            }
            
            // Validación de longitud mínima
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = "El campo $field debe tener al menos {$rule['min_length']} caracteres";
            }
            
            // Validación de longitud máxima
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = "El campo $field no puede tener más de {$rule['max_length']} caracteres";
            }
            
            // Validación con patrón personalizado
            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = "El campo $field no tiene el formato correcto";
            }
        }
        
        return $errors;
    }
    
    // ========================================================================
    // RATE LIMITING
    // ========================================================================
    
    /**
     * Verificar rate limiting por IP
     * Controla cuántas peticiones puede hacer una IP en un período de tiempo
     */
    public static function checkRateLimit($identifier, $maxRequests = null, $timeWindow = null) {
        // Usar valores por defecto si no se especifican
        $maxRequests = $maxRequests ?? RATE_LIMIT_REQUESTS;
        $timeWindow = $timeWindow ?? RATE_LIMIT_TIME_WINDOW;
        
        $cacheFile = LOG_PATH . "rate_limit_" . md5($identifier) . ".json";
        
        $now = time();
        $requests = [];
        
        // Leer intentos previos del archivo
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && isset($data['requests'])) {
                $requests = $data['requests'];
            }
        }
        
        // Filtrar requests que están dentro del tiempo límite
        $requests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });
        
        // Verificar si se ha excedido el límite
        if (count($requests) >= $maxRequests) {
            return false;
        }
        
        // Agregar nuevo request
        $requests[] = $now;
        
        // Guardar en archivo cache
        file_put_contents($cacheFile, json_encode(['requests' => $requests]));
        
        return true;
    }
    
    // ========================================================================
    // PROTECCIÓN CSRF
    // ========================================================================
    
    /**
     * Generar token CSRF
     * Crea un token único para proteger contra ataques CSRF
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validar token CSRF
     * Verifica que el token enviado coincida con el de la sesión
     */
    public static function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // ========================================================================
    // GESTIÓN DE PASSWORDS
    // ========================================================================
    
    /**
     * Crear hash seguro de password
     * Usa Argon2ID para máxima seguridad
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iteraciones
            'threads' => 3          // 3 hilos
        ]);
    }
    
    /**
     * Verificar password contra hash
     * Verifica de forma segura sin revelar timing
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // ========================================================================
    // PROTECCIÓN CONTRA BRUTE FORCE
    // ========================================================================
    
    /**
     * Verificar si una IP está bloqueada por brute force
     */
    public static function checkBruteForce($identifier, $maxAttempts = null, $lockoutTime = null) {
        $maxAttempts = $maxAttempts ?? MAX_LOGIN_ATTEMPTS;
        $lockoutTime = $lockoutTime ?? LOCKOUT_TIME;
        
        $cacheFile = LOG_PATH . "bruteforce_" . md5($identifier) . ".json";
        
        $now = time();
        $attempts = [];
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && isset($data['attempts'])) {
                $attempts = $data['attempts'];
            }
        }
        
        // Filtrar intentos dentro del tiempo de bloqueo
        $attempts = array_filter($attempts, function($timestamp) use ($now, $lockoutTime) {
            return ($now - $timestamp) < $lockoutTime;
        });
        
        return count($attempts) < $maxAttempts;
    }
    
    /**
     * Registrar intento fallido de login
     */
    public static function recordFailedAttempt($identifier) {
        $cacheFile = LOG_PATH . "bruteforce_" . md5($identifier) . ".json";
        
        $attempts = [];
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && isset($data['attempts'])) {
                $attempts = $data['attempts'];
            }
        }
        
        $attempts[] = time();
        file_put_contents($cacheFile, json_encode(['attempts' => $attempts]));
    }
    
    // ========================================================================
    // LOGGING DE SEGURIDAD
    // ========================================================================
    
    /**
     * Registrar evento de seguridad
     * Guarda logs de eventos importantes para auditoría
     */
    public static function logSecurityEvent($type, $message, $data = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'ip' => self::getRealIPAddress(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => LOG_SENSITIVE_DATA ? $data : []
        ];
        
        $logFile = LOG_PATH . 'security_' . date('Y-m') . '.log';
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    // ========================================================================
    // UTILIDADES
    // ========================================================================
    
    /**
     * Obtener la IP real del usuario
     * Considera proxies y load balancers
     */
    public static function getRealIPAddress() {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Proxy estándar
            'HTTP_X_FORWARDED',          // Proxy alternativo
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Forwarded estándar
            'HTTP_FORWARDED',            // Forwarded
            'REMOTE_ADDR'                // IP directa
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                // Verificar que sea una IP válida y pública
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Detectar si estamos en HTTPS
     */
    private static function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
}
?>