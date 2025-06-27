<?php
// Test básico para verificar que PHP funciona
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test Básico - CRM Ligeros</h1>";

echo "<h2>📋 Información del servidor:</h2>";
echo "<p>✅ PHP Version: " . PHP_VERSION . "</p>";
echo "<p>✅ Fecha y hora: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>✅ Directorio actual: " . __DIR__ . "</p>";

echo "<h2>📁 Verificar archivos de configuración:</h2>";

// Verificar si existe config.php
if (file_exists('config/config.php')) {
    echo "<p>✅ config/config.php: Existe</p>";
    
    // Intentar incluir el archivo
    try {
        require_once 'config/config.php';
        echo "<p>✅ config/config.php: Cargado correctamente</p>";
        
        // Mostrar algunas constantes definidas
        if (defined('DB_HOST')) {
            echo "<p>✅ DB_HOST: " . DB_HOST . "</p>";
        }
        if (defined('DB_NAME')) {
            echo "<p>✅ DB_NAME: " . DB_NAME . "</p>";
        }
        if (defined('BASE_URL')) {
            echo "<p>✅ BASE_URL: " . BASE_URL . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error al cargar config.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ config/config.php: NO existe</p>";
    echo "<p>🛠️ Necesitas crear este archivo primero</p>";
}

echo "<h2>📂 Verificar directorios:</h2>";

$dirs = ['config', 'src', 'logs', 'security'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "<p>✅ Directorio <strong>$dir</strong>: Existe</p>";
    } else {
        echo "<p>❌ Directorio <strong>$dir</strong>: NO existe</p>";
        echo "<p>🛠️ Crear con: <code>mkdir $dir</code></p>";
    }
}

echo "<h2>🗄️ Test de conexión MySQL (sin seleccionar base de datos):</h2>";

if (defined('DB_HOST') && defined('DB_USER')) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "<p>✅ Conexión a MySQL: OK</p>";
        
        // Verificar si la base de datos existe
        if (defined('DB_NAME')) {
            $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
            if ($stmt->rowCount() > 0) {
                echo "<p>✅ Base de datos <strong>" . DB_NAME . "</strong>: Existe</p>";
            } else {
                echo "<p>❌ Base de datos <strong>" . DB_NAME . "</strong>: NO existe</p>";
                echo "<p>🛠️ Crear con: <code>CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></p>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<p>❌ Error de conexión MySQL: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>⚠️ Constantes de base de datos no definidas</p>";
}

echo "<h2>🔧 Extensiones PHP necesarias:</h2>";

$extensions = ['pdo', 'pdo_mysql', 'gd', 'json', 'curl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p>✅ <strong>$ext</strong>: Disponible</p>";
    } else {
        echo "<p>❌ <strong>$ext</strong>: NO disponible</p>";
    }
}

echo "<hr>";
echo "<h2>📝 Instrucciones:</h2>";
echo "<ol>";
echo "<li>Si ves errores arriba, síguelos en ordem</li>";
echo "<li>Crea la base de datos <strong>crm_ligeros</strong> en phpMyAdmin</li>";
echo "<li>Asegúrate de que todos los directorios existan</li>";
echo "<li>Una vez solucionado, prueba <strong>test.php</strong></li>";
echo "</ol>";
?>