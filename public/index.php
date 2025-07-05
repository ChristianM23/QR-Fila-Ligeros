<?php
/**
 * Debug del Router - CRM Ligeros v2.0
 */

echo "<h1>🔍 Debug del Index.php</h1>";
echo "<p><strong>Este archivo se está ejecutando correctamente</strong></p>";

// Mostrar información del request
echo "<h2>📊 Información del Request:</h2>";
echo "<ul>";
echo "<li><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</li>";
echo "<li><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</li>";
echo "<li><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</li>";
echo "<li><strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'N/A') . "</li>";
echo "<li><strong>QUERY_STRING:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'N/A') . "</li>";
echo "</ul>";

echo "<h2>🏗️ Cargando Application:</h2>";

// Configurar zona horaria y errores
date_default_timezone_set('Europe/Madrid');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir PROJECT_ROOT
define('PROJECT_ROOT', dirname(__DIR__));
echo "<p>✅ PROJECT_ROOT definido como: " . PROJECT_ROOT . "</p>";

try {
    // Cargar Application
    $appFile = PROJECT_ROOT . '/core/Application.php';
    echo "<p>📁 Intentando cargar: " . $appFile . "</p>";
    
    if (!file_exists($appFile)) {
        throw new Exception("Archivo Application.php no encontrado en: " . $appFile);
    }
    
    require_once $appFile;
    echo "<p>✅ Application.php cargado correctamente</p>";
    
    // Crear Application
    $app = new Application(PROJECT_ROOT);
    echo "<p>✅ Application instanciada correctamente</p>";
    
    // Verificar constantes
    echo "<h3>🔧 Constantes definidas:</h3>";
    echo "<ul>";
    echo "<li>APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'NO DEFINIDA') . "</li>";
    echo "<li>APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NO DEFINIDA') . "</li>";
    echo "</ul>";
    
    // Test del router
    echo "<h3>🛣️ Test del Router:</h3>";
    
    $router = $app->get('router');
    echo "<p>✅ Router obtenido del servicio</p>";
    
    $routesFile = PROJECT_ROOT . '/config/routes.php';
    echo "<p>📁 Cargando rutas desde: " . $routesFile . "</p>";
    
    if (!file_exists($routesFile)) {
        throw new Exception("Archivo routes.php no encontrado");
    }
    
    $router->loadRoutes($routesFile);
    echo "<p>✅ Rutas cargadas - Total: " . count($router->getRoutes()) . "</p>";
    
    // Mostrar algunas rutas
    echo "<h4>Rutas disponibles:</h4>";
    echo "<ul>";
    foreach (array_slice($router->getRoutes(), 0, 5) as $route) {
        echo "<li>{$route['method']} {$route['path']} → {$route['handler']}</li>";
    }
    echo "</ul>";
    
    echo "<h3>🎯 Intentando resolver ruta actual:</h3>";
    echo "<p>Ruta solicitada: " . ($_SERVER['REQUEST_URI'] ?? '/') . "</p>";
    
    // Intentar resolver la ruta
    try {
        $route = $router->resolve();
        echo "<p>✅ Ruta resuelta correctamente</p>";
        echo "<p>Handler: " . $route->getHandler() . "</p>";
        
        // Intentar ejecutar
        echo "<h3>⚡ Ejecutando handler:</h3>";
        $result = $route->handle();
        echo "<p>✅ Handler ejecutado</p>";
        
        if (is_string($result)) {
            echo "<div style='border: 2px solid green; padding: 10px; margin: 10px 0;'>";
            echo "<h4>📋 Resultado del Handler:</h4>";
            echo $result;
            echo "</div>";
        }
        
    } catch (RouteNotFoundException $e) {
        echo "<p>❌ Ruta no encontrada: " . $e->getMessage() . "</p>";
        
        echo "<h4>🔍 Debug de rutas:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Método</th><th>Ruta</th><th>Pattern</th></tr>";
        foreach ($router->getRoutes() as $route) {
            echo "<tr>";
            echo "<td>{$route['method']}</td>";
            echo "<td>{$route['path']}</td>";
            echo "<td>" . ($route['pattern'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<p>❌ Error ejecutando handler: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error Critical:</h2>";
    echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow: auto;'>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}

echo "<h2>🧪 Tests Manuales:</h2>";
echo "<p><a href='/login'>🔐 Test /login</a></p>";
echo "<p><a href='/debug/routes'>🔍 Test /debug/routes</a></p>";
echo "<p><a href='/'>🏠 Test /</a></p>";
echo "<p><a href='/assets/css/app.css'>📄 Test asset CSS</a></p>";
?>