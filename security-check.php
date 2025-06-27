<?php
/**
 * Test Final del Sistema de Seguridad Completo
 * final-security-test.php - Paso 1D
 */

require_once 'config/config.php';

echo "<h1>🔒 Test Final - Sistema de Seguridad Completo</h1>";

// ============================================================================
// Test 1: Verificación de Configuración
// ============================================================================
echo "<h2>⚙️ Test 1: Configuración del Sistema</h2>";

$configChecks = [
    'APP_ENV' => defined('APP_ENV'),
    'BASE_URL' => defined('BASE_URL'),
    'JWT_SECRET' => defined('JWT_SECRET'),
    'AUTO_INIT_SECURITY' => defined('AUTO_INIT_SECURITY'),
    'RATE_LIMIT_REQUESTS' => defined('RATE_LIMIT_REQUESTS'),
    'MIN_PASSWORD_LENGTH' => defined('MIN_PASSWORD_LENGTH')
];

foreach ($configChecks as $const => $exists) {
    if ($exists) {
        echo "<p>✅ <strong>$const:</strong> " . constant($const) . "</p>";
    } else {
        echo "<p>❌ <strong>$const:</strong> No definida</p>";
    }
}

// ============================================================================
// Test 2: Verificación de Clases de Seguridad
// ============================================================================
echo "<h2>🏗️ Test 2: Clases de Seguridad</h2>";

$securityClasses = [
    'SecurityManager' => 'Gestor principal de seguridad',
    'SecurityMiddleware' => 'Middleware de protección automática',
    'SecurityBootstrap' => 'Inicializador del sistema'
];

foreach ($securityClasses as $class => $description) {
    if (class_exists($class)) {
        echo "<p>✅ <strong>$class:</strong> $description</p>";
    } else {
        echo "<p>❌ <strong>$class:</strong> Clase no encontrada</p>";
    }
}

// ============================================================================
// Test 3: Estado del Sistema de Seguridad
// ============================================================================
echo "<h2>🛡️ Test 3: Estado del Sistema de Seguridad</h2>";

if (class_exists('SecurityBootstrap')) {
    $securityStatus = SecurityBootstrap::getSecurityStatus();
    
    foreach ($securityStatus as $component => $status) {
        $icon = $status ? '✅' : '❌';
        $statusText = $status ? 'Activo' : 'Inactivo';
        echo "<p>$icon <strong>" . ucfirst(str_replace('_', ' ', $component)) . ":</strong> $statusText</p>";
    }
} else {
    echo "<p>❌ SecurityBootstrap no disponible</p>";
}

// ============================================================================
// Test 4: Funcionalidades Core de Seguridad
// ============================================================================
echo "<h2>🔧 Test 4: Funcionalidades Core</h2>";

// Test de sanitización
$testInput = '<script>alert("XSS")</script>';
$sanitized = SecurityManager::sanitizeInput($testInput);
echo "<p><strong>Sanitización XSS:</strong></p>";
echo "<p style='margin-left:20px'>Input: <code>" . htmlspecialchars($testInput) . "</code></p>";
echo "<p style='margin-left:20px'>Output: <code>$sanitized</code></p>";
echo "<p style='margin-left:20px'>" . ($testInput !== $sanitized ? '✅ Sanitizado correctamente' : '❌ No sanitizado') . "</p>";

// Test de validación de password
$passwords = [
    '123' => 'Débil',
    'password' => 'Común',
    'MiPassSegura123!' => 'Fuerte'
];

echo "<p><strong>Validación de Passwords:</strong></p>";
foreach ($passwords as $pass => $expected) {
    $errors = validatePasswordStrength($pass);
    $status = empty($errors) ? '✅ Válida' : '❌ ' . count($errors) . ' errores';
    echo "<p style='margin-left:20px'><code>$pass</code> ($expected): $status</p>";
}

// Test de tokens CSRF
echo "<p><strong>Tokens CSRF:</strong></p>";
$token = SecurityManager::generateCSRFToken();
$isValid = SecurityManager::validateCSRFToken($token);
echo "<p style='margin-left:20px'>Token: <code>" . substr($token, 0, 16) . "...</code></p>";
echo "<p style='margin-left:20px'>Validación: " . ($isValid ? '✅ Válido' : '❌ Inválido') . "</p>";

// Test de hashing de passwords
echo "<p><strong>Hashing de Passwords:</strong></p>";
$testPassword = 'TestPassword123!';
$hash = SecurityManager::hashPassword($testPassword);
$verified = SecurityManager::verifyPassword($testPassword, $hash);
echo "<p style='margin-left:20px'>Password: <code>$testPassword</code></p>";
echo "<p style='margin-left:20px'>Hash: <code>" . substr($hash, 0, 30) . "...</code></p>";
echo "<p style='margin-left:20px'>Verificación: " . ($verified ? '✅ Correcta' : '❌ Falló') . "</p>";

// ============================================================================
// Test 5: Protecciones Automáticas
// ============================================================================
echo "<h2>🚨 Test 5: Protecciones Automáticas</h2>";

// Test de detección de patrones de ataque
$attackPatterns = [
    'SELECT * FROM users' => 'SQL Injection',
    '<script>alert(1)</script>' => 'XSS',
    '../../../etc/passwd' => 'Path Traversal'
];

echo "<p><strong>Detección de Ataques:</strong></p>";
foreach ($attackPatterns as $pattern => $type) {
    $detected = detectAttackPatterns($pattern);
    $status = $detected ? '✅ Detectado como ' . $detected : '❌ No detectado';
    echo "<p style='margin-left:20px'><strong>$type:</strong> $status</p>";
}

// Test de rate limiting
echo "<p><strong>Rate Limiting:</strong></p>";
$testIP = '192.168.1.100';
$rateLimitOK = SecurityManager::checkRateLimit($testIP, 5, 60);
echo "<p style='margin-left:20px'>IP Test: $testIP</p>";
echo "<p style='margin-left:20px'>Estado: " . ($rateLimitOK ? '✅ Dentro del límite' : '❌ Límite excedido') . "</p>";

// ============================================================================
// Test 6: Archivos y Directorios
// ============================================================================
echo "<h2>📁 Test 6: Estructura de Archivos</h2>";

$requiredFiles = [
    'config/config.php' => 'Configuración principal',
    'config/security.php' => 'Configuración de seguridad',
    'security/SecurityManager.php' => 'Gestor de seguridad',
    'security/SecurityMiddleware.php' => 'Middleware de seguridad',
    'security/SecurityBootstrap.php' => 'Inicializador',
    '.htaccess' => 'Configuración Apache'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p>✅ <strong>$file:</strong> $description ($size bytes)</p>";
    } else {
        echo "<p>❌ <strong>$file:</strong> Archivo no encontrado</p>";
    }
}

$requiredDirs = [
    'logs' => LOG_PATH,
    'security' => 'security/',
    'src/qr-codes' => QR_PATH
];

echo "<p><strong>Directorios:</strong></p>";
foreach ($requiredDirs as $name => $path) {
    if (is_dir($path)) {
        $writable = is_writable($path) ? '✅ Escribible' : '❌ No escribible';
        echo "<p style='margin-left:20px'>✅ <strong>$name:</strong> Existe - $writable</p>";
    } else {
        echo "<p style='margin-left:20px'>❌ <strong>$name:</strong> No existe</p>";
    }
}

// ============================================================================
// Test 7: Headers de Seguridad
// ============================================================================
echo "<h2>📋 Test 7: Headers de Seguridad</h2>";

$expectedHeaders = [
    'X-Frame-Options' => 'Protección clickjacking',
    'X-Content-Type-Options' => 'Prevención MIME sniffing',
    'X-XSS-Protection' => 'Protección XSS'
];

$headersFound = 0;
foreach (headers_list() as $header) {
    foreach ($expectedHeaders as $expectedHeader => $description) {
        if (stripos($header, $expectedHeader) === 0) {
            echo "<p>✅ <strong>$expectedHeader:</strong> $description</p>";
            echo "<p style='margin-left:20px; color:#666'>$header</p>";
            $headersFound++;
            break;
        }
    }
}

if ($headersFound >= 2) {
    echo "<p>✅ Headers de seguridad configurados correctamente</p>";
} else {
    echo "<p>⚠️ Algunos headers de seguridad pueden estar faltando</p>";
}

// ============================================================================
// Test 8: Logs de Seguridad
// ============================================================================
echo "<h2>📝 Test 8: Sistema de Logs</h2>";

// Crear un log de prueba
SecurityManager::logSecurityEvent('FINAL_TEST', 'Test completo del sistema de seguridad', [
    'version' => APP_VERSION,
    'timestamp' => time()
]);

$logFile = LOG_PATH . 'security_' . date('Y-m') . '.log';
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "<p>✅ <strong>Log de seguridad:</strong> Funcionando</p>";
    echo "<p style='margin-left:20px'>Archivo: <code>$logFile</code></p>";
    echo "<p style='margin-left:20px'>Tamaño: $logSize bytes</p>";
    
    // Mostrar última entrada
    $logContent = file_get_contents($logFile);
    $logLines = explode("\n", trim($logContent));
    $lastLog = end($logLines);
    
    if (!empty($lastLog)) {
        $logEntry = json_decode($lastLog, true);
        if ($logEntry) {
            echo "<p style='margin-left:20px'>Última entrada: {$logEntry['timestamp']} - {$logEntry['type']}</p>";
        }
    }
} else {
    echo "<p>❌ <strong>Log de seguridad:</strong> No se pudo crear</p>";
}
?>