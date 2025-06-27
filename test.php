<?php
require_once 'config/config.php';

echo "<h1>Test de configuración CRM Asociación</h1>";

// Test de PHP
echo "<h2>✅ PHP Version: " . PHP_VERSION . "</h2>";

// Test de conexión a BD
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<h2>✅ Conexión a MariaDB: OK</h2>";
    echo "<p>Base de datos: " . DB_NAME . "</p>";
    
    // Test de tablas
    $tables = ['users', 'members', 'attendance_log', 'user_sessions'];
    foreach($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if($stmt->rowCount() > 0) {
            echo "<p>✅ Tabla '$table': Existe</p>";
        } else {
            echo "<p>❌ Tabla '$table': No existe</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "<h2>❌ Error de BD: " . $e->getMessage() . "</h2>";
}

// Test de directorios
$dirs = ['src/qr-codes', 'logs'];
foreach($dirs as $dir) {
    if(is_dir($dir) && is_writable($dir)) {
        echo "<p>✅ Directorio '$dir': OK y escribible</p>";
    } else {
        echo "<p>❌ Directorio '$dir': No existe o no es escribible</p>";
    }
}

echo "<h2>🚀 URLs del proyecto:</h2>";
echo "<ul>";
echo "<li><a href='" . BASE_URL . "'>Base URL</a></li>";
echo "<li><a href='" . API_URL . "'>API URL</a></li>";
echo "<li><a href='" . VIEWS_URL . "'>Frontend URL</a></li>";
echo "</ul>";
?>