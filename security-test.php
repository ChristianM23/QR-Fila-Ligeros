<?php
/**
 * Test de Seguridad
 * Ejecutar para verificar que las medidas de seguridad están funcionando
 */

require_once 'config/config.php';
require_once 'config/security.php';
require_once 'security/SecurityManager.php';

echo "<h1>🔒 Test de Seguridad - CRM Ligeros</h1>";

// Inicializar middleware de seguridad
SecurityManager::setSecurityHeaders();

// Test 1: Headers de Seguridad
echo "<h2>📋 Test 1: Headers de Seguridad</h2>";
$requiredHeaders = [
    'X-Frame-Options',
    'X-Content-Type-Options', 
    'X-XSS-Protection',
    'Referrer-Policy',
    'Content-Security-Policy'
];

foreach ($requiredHeaders as $header) {
    $headerFound = false;
    foreach (headers_list() as $sentHeader) {
        if (stripos($sentHeader, $header) === 0) {
            echo "<p>✅ Header <strong>$header</strong>: Configurado</p>";
            echo "<p style='margin-left: 20px; color: #666;'>$sentHeader</p>";
            $headerFound = true;
            break;
        }
    }
    if (!$headerFound) {
        echo "<p>❌ Header <strong>$header</strong>: NO configurado</p>";
    }
}

// Test 2: Validación y Sanitización
echo "<h2>🧹 Test 2: Validación y Sanitización</h2>";

$testData = [
    'script_tag' => '<script>alert("XSS")</script>',
    'sql_injection' => "'; DROP TABLE users; --",
    'normal_text' => 'Texto normal',
    'email' => 'test@example.com',
    'phone' => '+34 123 456 789'
];

foreach ($testData as $type => $data) {
    $sanitized = SecurityManager::sanitizeInput($data);
    echo "<p><strong>$type:</strong></p>";
    echo "<p style='margin-left: 20px;'>Original: <code>" . htmlspecialchars($data) . "</code></p>";
    echo "<p style='margin-left: 20px;'>Sanitizado: <code>$sanitized</code></p>";
    
    if ($data !== $sanitized) {
        echo "<p style='margin-left: 20px; color: green;'>✅ Datos sanitizados correctamente</p>";
    } else {
        echo "<p style='margin-left: 20px; color: blue;'>ℹ️ Sin cambios necesarios</p>";
    }
}

// Test 3: Validación de Datos
echo "<h2>✔️ Test 3: Validación de Datos</h2>";

$testValidation = [
    'name' => 'Juan Pérez',
    'email' => 'invalid-email',
    'phone' => '123abc',
    'dni' => '12345678Z'
];

$validationRules = [
    'name' => ['required' => true, 'type' => 'string', 'min_length' => 2],
    'email' => ['required' => true, 'type' => 'email'],
    'phone' => ['required' => false, 'type' => 'phone'],
    'dni' => ['required' => false, 'type' => 'dni']
];

$validationErrors = SecurityManager::validateInput($testValidation, $validationRules);

if (empty($validationErrors)) {
    echo "<p>✅ Todos los datos son válidos</p>";
} else {
    echo "<p>⚠️ Se encontraron errores de validación:</p>";
    foreach ($validationErrors as $field => $error) {
        echo "<p style='margin-left: 20px; color: red;'>• $field: $error</p>";
    }
}

// Test 4: Rate Limiting
echo "<h2>⏱️ Test 4: Rate Limiting</h2>";

$testIP = SecurityManager::getRealIPAddress();
echo "<p>IP del cliente: <strong>$testIP</strong></p>";

if (SecurityManager::checkRateLimit($testIP, 10, 60)) {
    echo "<p>✅ Rate limit: OK (dentro del límite)</p>";
} else {
    echo "<p>❌ Rate limit: EXCEDIDO</p>";
}

// Test 5: Protección CSRF
echo "<h2>🛡️ Test 5: Protección CSRF</h2>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrfToken = SecurityManager::generateCSRFToken();
echo "<p>Token CSRF generado: <code>" . substr($csrfToken, 0, 16) . "...</code></p>";

if (SecurityManager::validateCSRFToken($csrfToken)) {
    echo "<p>✅ Validación CSRF: Token válido</p>";
} else {
    echo "<p>❌ Validación CSRF: Token inválido</p>";
}

// Test 6: Hashing de Passwords
echo "<h2>🔐 Test 6: Hashing de Passwords</h2>";

$testPassword = 'MiPasswordSegura123!';
$hashedPassword = SecurityManager::hashPassword($testPassword);

echo "<p>Password original: <code>$testPassword</code></p>";
echo "<p>Password hasheada: <code>" . substr($hashedPassword, 0, 30) . "...</code></p>";

if (SecurityManager::verifyPassword($testPassword, $hashedPassword)) {
    echo "<p>✅ Verificación de password: Correcta</p>";
} else {
    echo "<p>❌ Verificación de password: Falló</p>";
}

// Test 7: Validación de Fortaleza de Password
echo "<h2>💪 Test 7: Validación de Fortaleza de Password</h2>";

$testPasswords = [
    '123456' => 'Débil',
    'password' => 'Común',
    'MiPass123!' => 'Fuerte'
];

foreach ($testPasswords as $password => $expected) {
    $errors = validatePasswordStrength($password);
    echo "<p><strong>Password:</strong> <code>$password</code> (Esperado: $expected)</p>";
    
    if (empty($errors)) {
        echo "<p style='margin-left: 20px; color: green;'>✅ Password válida</p>";
    } else {
        echo "<p style='margin-left: 20px; color: red;'>❌ Errores encontrados:</p>";
        foreach ($errors as $error) {
            echo "<p style='margin-left: 40px;'>• $error</p>";
        }
    }
}

// Test 8: Detección de Patrones de Ataque
echo "<h2>🚨 Test 8: Detección de Patrones de Ataque</h2>";

$attackPatterns = [
    'SELECT * FROM users' => 'SQL Injection',
    '<script>alert(1)</script>' => 'XSS',
    '../../../etc/passwd' => 'Path Traversal',
    'system("rm -rf /")' => 'Command Injection'
];

foreach ($attackPatterns as $input => $attackType) {
    $detected = detectAttackPatterns($input);
    echo "<p><strong>$attackType:</strong> <code>" . htmlspecialchars($input) . "</code></p>";
    
    if ($detected) {
        echo "<p style='margin-left: 20px; color: red;'>🚨 Ataque detectado: $detected</p>";
    } else {
        echo "<p style='margin-left: 20px; color: green;'>✅ No se detectó como ataque</p>";
    }
}

// Test 9: Verificación de Directorios y Permisos
echo "<h2>📁 Test 9: Verificación de Directorios y Permisos</h2>";

$directories = [
    'logs' => LOG_PATH,
    'qr-codes' => QR_PATH,
    'config' => __DIR__ . '/config/'
];

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        echo "<p>✅ Directorio <strong>$name</strong>: Existe</p>";
        
        if (is_writable($path)) {
            echo "<p style='margin-left: 20px; color: green;'>✅ Permisos de escritura: OK</p>";
        } else {
            echo "<p style='margin-left: 20px; color: orange;'>⚠️ Permisos de escritura: Sin permisos</p>";
        }
    } else {
        echo "<p>❌ Directorio <strong>$name</strong>: NO existe</p>";
    }
}

// Test 10: Configuración de PHP
echo "<h2>⚙️ Test 10: Configuración de PHP</h2>";

$phpSettings = [
    'display_errors' => ['expected' => '1', 'security_note' => 'Solo en desarrollo'],
    'expose_php' => ['expected' => '0', 'security_note' => 'Ocultar versión PHP'],
    'allow_url_fopen' => ['expected' => '0', 'security_note' => 'Prevenir inclusión remota'],
    'session.cookie_httponly' => ['expected' => '1', 'security_note' => 'Cookies HTTP only'],
    'session.use_strict_mode' => ['expected' => '1', 'security_note' => 'Modo estricto de sesiones']
];

foreach ($phpSettings as $setting => $config) {
    $currentValue = ini_get($setting);
    $expected = $config['expected'];
    $note = $config['security_note'];
    
    echo "<p><strong>$setting:</strong> $currentValue (Esperado: $expected)</p>";
    echo "<p style='margin-left: 20px; color: #666; font-size: 0.9em;'>$note</p>";
    
    if ($currentValue == $expected) {
        echo "<p style='margin-left: 20px; color: green;'>✅ Configuración correcta</p>";
    } else {
        echo "<p style='margin-left: 20px; color: orange;'>⚠️ Revisar configuración</p>";
    }
}

// Test 11: Test de Logs
echo "<h2>📝 Test 11: Sistema de Logs</h2>";

try {
    SecurityManager::logSecurityEvent('TEST', 'Test de logging de seguridad', ['test' => true]);
    echo "<p>✅ Log de seguridad: Funcionando correctamente</p>";
    
    $logFile = LOG_PATH . 'security_' . date('Y-m') . '.log';
    if (file_exists($logFile)) {
        echo "<p>✅ Archivo de log creado: <code>$logFile</code></p>";
        $logSize = filesize($logFile);
        echo "<p style='margin-left: 20px;'>Tamaño: $logSize bytes</p>";
    } else {
        echo "<p>❌ Archivo de log no creado</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error en logging: " . $e->getMessage() . "</p>";
}

// Resumen final
echo "<hr>";
echo "<h2>📊 Resumen de Seguridad</h2>";
echo "<p>🔒 <strong>Estado general:</strong> Configuración de seguridad básica implementada</p>";
echo "<p>⚠️ <strong>Recomendaciones:</strong></p>";
echo "<ul>";
echo "<li>Verificar que todos los headers de seguridad se muestren correctamente</li>";
echo "<li>Asegurar que los directorios sensibles tengan permisos correctos</li>";
echo "<li>Revisar configuración PHP para producción</li>";
echo "<li>Implementar monitoreo de logs de seguridad</li>";
echo "<li>Realizar pruebas de penetración regulares</li>";
echo "</ul>";

echo "<h3>🚀 Próximos pasos:</h3>";
echo "<ul>";
echo "<li>✅ Capa de seguridad básica completada</li>";
echo "<li>🔄 Siguiente: Sistema de generación de QR</li>";
echo "</ul>";
?>