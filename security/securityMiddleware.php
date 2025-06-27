<?php
/**
 * Middleware de Seguridad
 */
class SecurityMiddleware {
    
    public static function handle() {
        // Configurar headers de seguridad
        SecurityManager::setSecurityHeaders();
        SecurityManager::setCORSHeaders();
        
        // Rate limiting por IP
        $ip = SecurityManager::getRealIPAddress();
        if (!SecurityManager::checkRateLimit($ip, 100, 3600)) { // 100 requests por hora
            SecurityManager::logSecurityEvent('RATE_LIMIT_EXCEEDED', 'Rate limit exceeded', ['ip' => $ip]);
            http_response_code(429);
            echo json_encode(['error' => 'Demasiadas solicitudes. Intenta más tarde.']);
            exit();
        }
        
        // Protección contra ataques de fuerza bruta en login
        if ($_SERVER['REQUEST_URI'] === '/api/auth/login' && !SecurityManager::checkBruteForce($ip)) {
            SecurityManager::logSecurityEvent('BRUTE_FORCE_BLOCKED', 'Brute force attempt blocked', ['ip' => $ip]);
            http_response_code(429);
            echo json_encode(['error' => 'Cuenta bloqueada temporalmente por intentos fallidos.']);
            exit();
        }
        
        // Validar CSRF token en requests POST/PUT/DELETE
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
            if (!SecurityManager::validateCSRFToken($csrfToken)) {
                SecurityManager::logSecurityEvent('CSRF_VALIDATION_FAILED', 'CSRF token validation failed', ['ip' => $ip]);
                http_response_code(403);
                echo json_encode(['error' => 'Token CSRF inválido.']);
                exit();
            }
        }
    }
}