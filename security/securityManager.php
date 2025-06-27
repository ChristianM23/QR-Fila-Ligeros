<?php
/**
 * Sistema de Seguridad Integral
 */

class SecurityManager {
    
    /**
     * Configurar headers de seguridad mejorados
     */
    public static function setSecurityHeaders() {
        // Prevenir clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy básico
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com; img-src \'self\' data:; font-src \'self\' cdnjs.cloudflare.com;');
        
        // Strict Transport Security (solo en HTTPS)
        if (self::isHTTPS()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Feature Policy
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
    
    /**
     * Configurar CORS de manera segura
     */
    public static function setCORSHeaders() {
        $allowedOrigins = [
            'http://localhost',
            'http://crm-ligeros.test',
            'https://crm-ligeros.test',
            'http://localhost:3000'
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 horas
        
        // Manejar preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    /**
     * Validación y sanitización de datos
     */
    public static function sanitizeInput($data, $type = 'string') {
        if (is_array($data)) {
            return array_map(function($item) use ($type) {
                return self::sanitizeInput($item, $type);
            }, $data);
        }
        
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
                return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validar datos
     */
    public static function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Required check
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "El campo $field es requerido";
                continue;
            }
            
            if (empty($value)) continue;
            
            // Type validation
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
                        $errors[$field] = "El campo $field debe ser un DNI válido";
                    }
                    break;
            }
            
            // Length validation
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = "El campo $field debe tener al menos {$rule['min_length']} caracteres";
            }
            
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = "El campo $field no puede tener más de {$rule['max_length']} caracteres";
            }
            
            // Pattern validation
            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = "El campo $field no tiene el formato correcto";
            }
        }
        
        return $errors;
    }
    
    /**
     * Rate Limiting
     */
    public static function checkRateLimit($identifier, $maxRequests = 60, $timeWindow = 3600) {
        $cacheFile = LOG_PATH . "rate_limit_" . md5($identifier) . ".json";
        
        $now = time();
        $requests = [];
        
        // Leer intentos previos
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && isset($data['requests'])) {
                $requests = $data['requests'];
            }
        }
        
        // Filtrar requests dentro del tiempo límite
        $requests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });
        
        // Verificar límite
        if (count($requests) >= $maxRequests) {
            return false;
        }
        
        // Agregar nuevo request
        $requests[] = $now;
        
        // Guardar en cache
        file_put_contents($cacheFile, json_encode(['requests' => $requests]));
        
        return true;
    }
    
    /**
     * Protección CSRF
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
    
    public static function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Logging de seguridad
     */
    public static function logSecurityEvent($type, $message, $data = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'ip' => self::getRealIPAddress(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => $data
        ];
        
        $logFile = LOG_PATH . 'security_' . date('Y-m') . '.log';
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Obtener IP real del usuario
     */
    public static function getRealIPAddress() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                  'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
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
    
    /**
     * Generar hash seguro para passwords
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
    }
    
    /**
     * Verificar password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Protección contra ataques de fuerza bruta
     */
    public static function checkBruteForce($identifier, $maxAttempts = 5, $lockoutTime = 900) {
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
}
