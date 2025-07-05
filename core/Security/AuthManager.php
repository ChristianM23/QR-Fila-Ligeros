<?php
/**
 * AuthManager - Gestor Principal de Autenticación
 * Maneja todo el proceso de login, logout y validación de sesiones
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/SecurityManager.php';

class AuthManager {
    
    private static $pdo = null;
    
    /**
     * Inicializar conexión a base de datos
     */
    private static function initDB() {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_FOUND_ROWS => true
                ]);
            } catch (PDOException $e) {
                SecurityManager::logSecurityEvent('DB_CONNECTION_ERROR', 'Error de conexión: ' . $e->getMessage());
                throw new Exception('Error de conexión a la base de datos');
            }
        }
    }
    
    /**
     * Autenticar usuario con username/email y password
     * 
     * @param string $identifier Username o email
     * @param string $password Password en texto plano
     * @param string $userIP IP del usuario
     * @return array|false Datos del usuario o false si falla
     */
    public static function authenticate($identifier, $password, $userIP) {
        self::initDB();
        
        try {
            // Verificar si está bloqueado por brute force
            if (!SecurityManager::checkBruteForce($userIP)) {
                SecurityManager::logSecurityEvent('BRUTE_FORCE_BLOCKED', 'IP bloqueada por intentos fallidos', ['ip' => $userIP]);
                return ['error' => 'IP bloqueada por intentos fallidos. Inténtelo más tarde.'];
            }
            
            // Buscar usuario por username o email
            $stmt = self::$pdo->prepare("
                SELECT id, username, email, password_hash, user_level, is_active, created_at 
                FROM users 
                WHERE (username = :identifier OR email = :identifier) 
                AND is_active = 1
            ");
            $stmt->execute(['identifier' => $identifier]);
            $user = $stmt->fetch();
            
            if (!$user) {
                SecurityManager::recordFailedAttempt($userIP);
                SecurityManager::logSecurityEvent('LOGIN_FAILED', 'Usuario no encontrado: ' . $identifier, ['ip' => $userIP]);
                return ['error' => 'Credenciales incorrectas'];
            }
            
            // Verificar password
            if (!password_verify($password, $user['password_hash'])) {
                SecurityManager::recordFailedAttempt($userIP);
                SecurityManager::logSecurityEvent('LOGIN_FAILED', 'Password incorrecto para: ' . $identifier, ['ip' => $userIP]);
                return ['error' => 'Credenciales incorrectas'];
            }
            
            // Login exitoso - limpiar intentos fallidos no es necesario
            // (se limpian automáticamente por tiempo)
            
            // Crear sesión
            $sessionToken = self::createUserSession($user['id'], $userIP);
            
            if (!$sessionToken) {
                return ['error' => 'Error al crear la sesión'];
            }
            
            // Log de login exitoso
            SecurityManager::logSecurityEvent('LOGIN_SUCCESS', 'Login exitoso para: ' . $identifier, ['ip' => $userIP]);
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'user_level' => $user['user_level'],
                    'level_name' => self::getLevelName($user['user_level']),
                    'session_token' => $sessionToken
                ]
            ];
            
        } catch (Exception $e) {
            SecurityManager::logSecurityEvent('LOGIN_ERROR', 'Error en autenticación: ' . $e->getMessage(), ['ip' => $userIP]);
            return ['error' => 'Error interno del servidor'];
        }
    }
    
    /**
     * Crear sesión de usuario en base de datos
     * 
     * @param int $userId ID del usuario
     * @param string $userIP IP del usuario
     * @return string|false Token de sesión o false si falla
     */
    private static function createUserSession($userId, $userIP) {
        self::initDB();
        
        try {
            // Limpiar sesiones expiradas del usuario
            self::cleanupExpiredSessions($userId);
            
            // Generar token único
            $sessionToken = self::generateSecureToken();
            $expiresAt = date('Y-m-d H:i:s', time() + JWT_EXPIRE);
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Insertar nueva sesión
            $stmt = self::$pdo->prepare("
                INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent) 
                VALUES (:user_id, :token, :expires_at, :ip_address, :user_agent)
            ");
            
            $success = $stmt->execute([
                'user_id' => $userId,
                'token' => $sessionToken,
                'expires_at' => $expiresAt,
                'ip_address' => $userIP,
                'user_agent' => $userAgent
            ]);
            
            if ($success) {
                // Configurar sesión PHP
                self::startSecureSession();
                $_SESSION['user_id'] = $userId;
                $_SESSION['session_token'] = $sessionToken;
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = $userIP;
                
                return $sessionToken;
            }
            
            return false;
            
        } catch (Exception $e) {
            SecurityManager::logSecurityEvent('SESSION_CREATE_ERROR', 'Error creando sesión: ' . $e->getMessage(), ['ip' => $userIP]);
            return false;
        }
    }
    
    /**
     * Validar sesión activa
     * 
     * @return array|false Datos del usuario o false si sesión inválida
     */
    public static function validateSession() {
        self::initDB();
        self::startSecureSession();
        
        // Verificar si hay sesión PHP activa
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        $sessionToken = $_SESSION['session_token'];
        $currentIP = SecurityManager::getRealIPAddress();
        
        try {
            // Verificar sesión en base de datos
            $stmt = self::$pdo->prepare("
                SELECT u.id, u.username, u.email, u.user_level, u.is_active, s.expires_at, s.ip_address
                FROM users u
                INNER JOIN user_sessions s ON u.id = s.user_id
                WHERE u.id = :user_id AND s.session_token = :token AND u.is_active = 1
            ");
            
            $stmt->execute([
                'user_id' => $userId,
                'token' => $sessionToken
            ]);
            
            $session = $stmt->fetch();
            
            if (!$session) {
                self::destroySession();
                return false;
            }
            
            // Verificar si la sesión ha expirado
            if (strtotime($session['expires_at']) < time()) {
                self::destroySession();
                SecurityManager::logSecurityEvent('SESSION_EXPIRED', 'Sesión expirada para usuario: ' . $session['username'], ['ip' => $currentIP]);
                return false;
            }
            
            // Verificar IP (opcional, comentar si causas problemas)
            if ($session['ip_address'] !== $currentIP) {
                SecurityManager::logSecurityEvent('IP_MISMATCH', 'IP diferente en sesión para usuario: ' . $session['username'], ['ip' => $currentIP, 'session_ip' => $session['ip_address']]);
                // Opcional: puedes decidir si destruir la sesión o solo loggear
                // self::destroySession();
                // return false;
            }
            
            return [
                'id' => $session['id'],
                'username' => $session['username'],
                'email' => $session['email'],
                'user_level' => $session['user_level'],
                'level_name' => self::getLevelName($session['user_level']),
                'ip_address' => $session['ip_address']
            ];
            
        } catch (Exception $e) {
            SecurityManager::logSecurityEvent('SESSION_VALIDATION_ERROR', 'Error validando sesión: ' . $e->getMessage(), ['ip' => $currentIP]);
            return false;
        }
    }
    
    /**
     * Cerrar sesión del usuario
     */
    public static function logout() {
        self::initDB();
        self::startSecureSession();
        
        $currentIP = SecurityManager::getRealIPAddress();
        
        try {
            // Eliminar sesión de la base de datos
            if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
                $stmt = self::$pdo->prepare("
                    DELETE FROM user_sessions 
                    WHERE user_id = :user_id AND session_token = :token
                ");
                
                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'token' => $_SESSION['session_token']
                ]);
                
                SecurityManager::logSecurityEvent('LOGOUT_SUCCESS', 'Logout exitoso para usuario ID: ' . $_SESSION['user_id'], ['ip' => $currentIP]);
            }
            
        } catch (Exception $e) {
            SecurityManager::logSecurityEvent('LOGOUT_ERROR', 'Error en logout: ' . $e->getMessage(), ['ip' => $currentIP]);
        }
        
        // Destruir sesión PHP
        self::destroySession();
    }
    
    /**
     * Verificar si el usuario tiene el nivel mínimo requerido
     * 
     * @param int $requiredLevel Nivel mínimo requerido
     * @param array $user Datos del usuario (opcional, se obtiene de sesión si no se proporciona)
     * @return bool True si tiene permisos, false si no
     */
    public static function hasPermission($requiredLevel, $user = null) {
        if ($user === null) {
            $user = self::validateSession();
        }
        
        if (!$user) {
            return false;
        }
        
        return $user['user_level'] >= $requiredLevel;
    }
    
    /**
     * Obtener nombre del nivel de usuario
     * 
     * @param int $level Nivel numérico
     * @return string Nombre del nivel
     */
    public static function getLevelName($level) {
        $levels = [
            1 => 'Usuario',
            2 => 'Vocal',
            3 => 'Cop',
            4 => 'Tresorer',
            5 => 'Vice Secretari',
            6 => 'Secretari',
            7 => 'Darrer Tro',
            8 => 'Primer Tro',
            9 => 'Admin',
            10 => 'Superadmin'
        ];
        
        return $levels[$level] ?? 'Desconocido';
    }
    
    /**
     * Generar token seguro para sesiones
     * 
     * @return string Token seguro
     */
    private static function generateSecureToken() {
        return bin2hex(random_bytes(32)) . '_' . time();
    }
    
    /**
     * Iniciar sesión PHP segura
     */
    private static function startSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Destruir sesión PHP completamente
     */
    private static function destroySession() {
        self::startSecureSession();
        
        // Limpiar variables de sesión
        $_SESSION = [];
        
        // Destruir cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir sesión
        session_destroy();
    }
    
    /**
     * Limpiar sesiones expiradas de un usuario
     * 
     * @param int $userId ID del usuario
     */
    private static function cleanupExpiredSessions($userId) {
        self::initDB();
        
        try {
            $stmt = self::$pdo->prepare("
                DELETE FROM user_sessions 
                WHERE user_id = :user_id AND expires_at < NOW()
            ");
            $stmt->execute(['user_id' => $userId]);
            
        } catch (Exception $e) {
            SecurityManager::logSecurityEvent('SESSION_CLEANUP_ERROR', 'Error limpiando sesiones: ' . $e->getMessage());
        }
    }
    
    /**
     * Crear nuevo usuario (solo para administradores)
     * 
     * @param array $userData Datos del usuario
     * @param array $currentUser Usuario actual (debe ser admin)
     * @return array Resultado de la operación
     */
    public static function createUser($userData, $currentUser) {
        self::initDB();
        
        // Verificar permisos (solo admin o superadmin pueden crear usuarios)
        if (!$currentUser || $currentUser['user_level'] < 9) {
            return ['error' => 'No tiene permisos para crear usuarios'];
        }
        
        try {
            // Validar datos
            $requiredFields = ['username', 'email', 'password', 'user_level'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    return ['error' => 'El campo ' . $field . ' es requerido'];
                }
            }
            
            // Validar password usando la función existente
            $passwordErrors = validatePasswordStrength($userData['password']);
            if (!empty($passwordErrors)) {
                return ['error' => 'La contraseña no cumple los requisitos: ' . implode(', ', $passwordErrors)];
            }
            
            // Verificar que el usuario no exista
            $stmt = self::$pdo->prepare("
                SELECT id FROM users 
                WHERE username = :username OR email = :email
            ");
            $stmt->execute([
                'username' => $userData['username'],
                'email' => $userData['email']
            ]);
            
            if ($stmt->fetch()) {
                return ['error' => 'El usuario o email ya existe'];
            }
            
            // Crear usuario
            $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            $stmt = self::$pdo->prepare("
                INSERT INTO users (username, email, password_hash, user_level, is_active) 
                VALUES (:username, :email, :password_hash, :user_level, 1)
            ");
            
            $success = $stmt->execute([
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password_hash' => $passwordHash,
                'user_level' => $userData['user_level']
            ]);
            
            if ($success) {
                SecurityManager::logSecurityEvent('USER_CREATED', 'Usuario creado: ' . $userData['username'], ['ip' => SecurityManager::getRealIPAddress()]);
                return ['success' => true, 'message' => 'Usuario creado exitosamente'];
            }
            
            return ['error' => 'Error al crear el usuario'];
            
        } catch (Exception $e) {
            SecurityManager::logSecurityEvent('USER_CREATE_ERROR', 'Error creando usuario: ' . $e->getMessage(), ['ip' => SecurityManager::getRealIPAddress()]);
            return ['error' => 'Error interno del servidor'];
        }
    }
}
?>