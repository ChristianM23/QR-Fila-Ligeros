<?php
/**
 * Debug del Sistema de Seguridad
 * Usar temporalmente para diagnosticar problemas
 */

require_once 'config/security.php';

echo "<h1>🔍 Debug del Sistema de Seguridad</h1>";

// Información del request actual
echo "<h2>Request Actual:</h2>";
echo "<p><strong>URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
echo "<p><strong>Método:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</p>";
echo "<p><strong>IP:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</p>";

// Probar detectAttackPatterns con diferentes inputs
echo "<h2>Pruebas de Detección:</h2>";

$testInputs = [
    '/login',
    '/dashboard', 
    '/',
    '/api',
    $_SERVER['REQUEST_URI'] ?? '',
    'SELECT * FROM users',
    '<script>alert(1)</script>',
    '../../../etc/passwd'
];

foreach ($testInputs as $input) {
    $result = detectAttackPatterns($input);
    $status = $result ? "❌ DETECTADO ($result)" : "✅ SEGURO";
    echo "<p><code>" . htmlspecialchars($input) . "</code> → $status</p>";
}

// Probar parámetros GET y POST
echo "<h2>Parámetros del Request:</h2>";
echo "<h3>GET:</h3>";
foreach ($_GET as $key => $value) {
    $keyResult = detectAttackPatterns($key);
    $valueResult = detectAttackPatterns($value);
    echo "<p><strong>$key:</strong> " . htmlspecialchars($value);
    if ($keyResult || $valueResult) {
        echo " ❌ DETECTADO";
    } else {
        echo " ✅ OK";
    }
    echo "</p>";
}

echo "<h3>POST:</h3>";
foreach ($_POST as $key => $value) {
    $keyResult = detectAttackPatterns($key);
    $valueResult = is_string($value) ? detectAttackPatterns($value) : false;
    echo "<p><strong>$key:</strong> " . htmlspecialchars(is_string($value) ? $value : '[array]');
    if ($keyResult || $valueResult) {
        echo " ❌ DETECTADO";
    } else {
        echo " ✅ OK";
    }
    echo "</p>";
}

echo "<h2>Headers Sospechosos:</h2>";
$suspiciousHeaders = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_USER_AGENT'];
foreach ($suspiciousHeaders as $header) {
    $value = $_SERVER[$header] ?? 'N/A';
    $result = detectAttackPatterns($value);
    $status = $result ? "❌ DETECTADO ($result)" : "✅ OK";
    echo "<p><strong>$header:</strong> " . htmlspecialchars($value) . " → $status</p>";
}

echo "<h2>💡 Soluciones:</h2>";
echo "<ol>";
echo "<li>Si '/login' aparece como DETECTADO, modifica la función detectAttackPatterns en config/security.php</li>";
echo "<li>Si algún header aparece como problemático, ajusta las expresiones regulares</li>";
echo "<li>Verifica que no haya parámetros GET/POST malformados</li>";
echo "</ol>";

echo "<p><a href='/'>← Volver al inicio</a></p>";
?>