<?php
// Test de diagnóstico simple
echo "<h1>🔍 Test de Diagnóstico - CRM Ligeros</h1>";

// Mostrar errores PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>📋 Información básica:</h2>";
echo "<p>✅ PHP Version: " . PHP_VERSION . "</p>";
echo "<p>✅ Servidor: " . $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible' . "</p>";
echo "<p>✅ Directorio actual: " . __DIR__ . "</p>";
echo "<p>✅ Usuario servidor: " . get_current_user() . "</p>";

echo "<h2>📁 Verificación de archivos:</h2>";

// Lista de archivos que deberían existir
$requiredFiles = [
    'config/config.php',
    'security/SecurityManager.php',
    'config/security.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<p>✅ Archivo <strong>$file</strong>: Existe</p>";
    } else {
        echo "<p>❌ Archivo <strong>$file</strong>: NO existe</p>";
        echo "<p style='margin-left: 20px; color: red;'>🚨 Este archivo es necesario</p>";
    }
}

echo "<h2>📂 Verificación de directorios:</h2>";

$requiredDirs = [
    'config',
    'security', 
    'logs',
    'src',
    'src/qr-codes'
];

foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        echo "<p>✅ Directorio <strong>$dir</strong>: Existe</p>";
        if (is_writable($dir)) {
            echo "<p style='margin-left: 20px; color: green;'>✅ Escribible</p>";
        } else {
            echo "<p style='margin-left: 20px; color: orange;'>⚠️ No escribible</p>";
        }
    } else {
        echo "<p>❌ Directorio <strong>$dir</strong>: NO existe</p>";
        echo "<p style='margin-left: 20px; color: red;'>🚨 Crear con: mkdir $dir</p>";
    }
}

echo "<h2>🔧 Test de inclusión de archivos:</h2>";

// Test básico de config
if (file_exists('config/config.php')) {
    try {
        require_once 'config/config.php';
        echo "<p>✅ config/config.php: Cargado correctamente</p>";
        
        // Verificar constantes
        $constants = ['DB_HOST', 'DB_NAME', 'BASE_URL', 'LOG_PATH', 'QR_PATH'];
        foreach ($constants as $const) {
            if (defined($const)) {
                echo "<p style='margin-left: 20px;'>✅ $const: " . constant($const) . "</p>";
            } else {
                echo "<p style='margin-left: 20px;'>❌ $const: No definida</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error cargando config.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ config/config.php: No encontrado</p>";
}

// Test de SecurityManager
if (file_exists('security/SecurityManager.php')) {
    try {
        require_once 'security/SecurityManager.php';
        echo "<p>✅ SecurityManager.php: Cargado correctamente</p>";
        
        if (class_exists('SecurityManager')) {
            echo "<p style='margin-left: 20px;'>✅ Clase SecurityManager: Existe</p>";
        } else {
            echo "<p style='margin-left: 20px;'>❌ Clase SecurityManager: No existe</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error cargando SecurityManager.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ SecurityManager.php: No encontrado</p>";
}

echo "<h2>🔍 Variables de servidor importantes:</h2>";
$serverVars = ['REQUEST_URI', 'HTTP_HOST', 'DOCUMENT_ROOT', 'SERVER_NAME'];
foreach ($serverVars as $var) {
    echo "<p><strong>$var:</strong> " . ($_SERVER[$var] ?? 'No definida') . "</p>";
}

echo "<h2>📝 Log de errores PHP:</h2>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo "<p>📄 Archivo de errores: <code>$errorLog</code></p>";
    $errors = file_get_contents($errorLog);
    $lastErrors = array_slice(explode("\n", $errors), -10);
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars(implode("\n", $lastErrors));
    echo "</pre>";
} else {
    echo "<p>⚠️ No se encontró archivo de log de errores PHP</p>";
}

echo "<hr>";
echo "<h2>🚀 Instrucciones:</h2>";
echo "<p>Si ves errores arriba:</p>";
echo "<ol>";
echo "<li>Crea los directorios faltantes</li>";
echo "<li>Verifica que los archivos config/config.php y security/SecurityManager.php existan</li>";
echo "<li>Revisa los logs de error de Laragon</li>";
echo "</ol>";
?>