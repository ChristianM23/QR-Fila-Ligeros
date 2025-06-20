<?php
/**
 * Script de configuración inicial para CRM Ligeros
 * Ejecutar una sola vez después de clonar el repositorio
 */

echo "<h1>🚀 Configuración inicial CRM Ligeros</h1>";

// Directorios que necesitan ser creados
$directories = [
    'src/qr-codes',
    'logs', 
    'uploads',
    'temp',
    'cache'
];

// Archivos de configuración
$configFiles = [
    'config/config.php' => 'config/config.local.php.example'
];

echo "<h2>📁 Creando directorios necesarios...</h2>";

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p>✅ Directorio creado: <strong>$dir</strong></p>";
            
            // Crear archivo .gitkeep
            $gitkeepPath = $dir . '/.gitkeep';
            $gitkeepContent = "# Este archivo mantiene el directorio en Git\n# Los archivos de $dir no se suben al repositorio\n";
            
            if (file_put_contents($gitkeepPath, $gitkeepContent)) {
                echo "<p>📝 Archivo .gitkeep creado en $dir</p>";
            }
        } else {
            echo "<p>❌ Error al crear directorio: <strong>$dir</strong></p>";
        }
    } else {
        echo "<p>ℹ️ Directorio ya existe: <strong>$dir</strong></p>";
    }
}

echo "<h2>⚙️ Configurando archivos de configuración...</h2>";

// Verificar si config.php existe
if (!file_exists('config/config.php')) {
    if (file_exists('config/config.local.php.example')) {
        if (copy('config/config.local.php.example', 'config/config.php')) {
            echo "<p>✅ Archivo config.php creado desde plantilla</p>";
            echo "<p>⚠️ <strong>IMPORTANTE:</strong> Revisa y ajusta config/config.php según tu entorno</p>";
        } else {
            echo "<p>❌ Error al copiar archivo de configuración</p>";
        }
    } else {
        echo "<p>⚠️ No se encontró archivo de plantilla config.local.php.example</p>";
    }
} else {
    echo "<p>ℹ️ Archivo config.php ya existe</p>";
}

echo "<h2>🔐 Verificando permisos...</h2>";

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p>✅ Directorio <strong>$dir</strong> tiene permisos de escritura</p>";
        } else {
            echo "<p>⚠️ Directorio <strong>$dir</strong> NO tiene permisos de escritura</p>";
            echo "<p>🛠️ Ejecuta: <code>chmod 755 $dir</code></p>";
        }
    }
}

echo "<h2>📋 Verificando dependencias...</h2>";

// Verificar extensiones PHP necesarias
$requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'json', 'curl'];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p>✅ Extensión PHP <strong>$ext</strong> está disponible</p>";
    } else {
        echo "<p>❌ Extensión PHP <strong>$ext</strong> NO está disponible</p>";
    }
}

echo "<h2>🗄️ Verificando base de datos...</h2>";

if (file_exists('config/config.php')) {
    require_once 'config/config.php';
    
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<p>✅ Conexión a MySQL exitosa</p>";
        
        // Verificar si la base de datos existe
        $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Base de datos <strong>" . DB_NAME . "</strong> existe</p>";
            
            // Conectar a la base de datos específica
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Verificar tablas
            $tables = ['users', 'members', 'attendance_log', 'user_sessions'];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo "<p>✅ Tabla <strong>$table</strong> existe</p>";
                } else {
                    echo "<p>⚠️ Tabla <strong>$table</strong> NO existe</p>";
                }
            }
            
        } else {
            echo "<p>⚠️ Base de datos <strong>" . DB_NAME . "</strong> NO existe</p>";
            echo "<p>🛠️ Necesitas ejecutar el script SQL de configuración</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>⚠️ Archivo config.php no encontrado</p>";
}

echo "<h2>📝 Próximos pasos:</h2>";
echo "<ol>";
echo "<li>Revisar y ajustar <strong>config/config.php</strong> con tu configuración local</li>";
echo "<li>Crear la base de datos si no existe</li>";
echo "<li>Ejecutar <strong>sql/database_setup.sql</strong> para crear las tablas</li>";
echo "<li>Verificar permisos de directorios si hay errores</li>";
echo "<li>Ejecutar <strong>test.php</strong> para verificar que todo funciona</li>";
echo "</ol>";

echo "<hr>";
echo "<p>🎉 <strong>Configuración inicial completada!</strong></p>";
echo "<p>🔗 <a href='test.php'>Ejecutar pruebas del sistema</a></p>";
?>