<?php
echo "<h1>🔍 Debug Simple</h1>";

try {
    require_once 'core/Application.php';
    echo "<p>✅ Application.php cargado</p>";
    
    $app = new Application(__DIR__);
    echo "<p>✅ Application creada</p>";
    
    echo "<p><strong>Constantes definidas:</strong></p>";
    echo "<ul>";
    echo "<li>PROJECT_ROOT: " . (defined('PROJECT_ROOT') ? PROJECT_ROOT : 'NO') . "</li>";
    echo "<li>APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'NO') . "</li>";
    echo "<li>APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NO') . "</li>";
    echo "</ul>";
    
    // Test del router
    require_once 'core/Http/Router.php';
    $router = new Router();
    echo "<p>✅ Router creado</p>";
    
    // Test de ruta simple
    $router->get('/test-simple', function() {
        return "¡Ruta funcionando!";
    });
    
    echo "<p>✅ Ruta de test añadida</p>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<p><a href='/'>Volver al inicio</a></p>";
?>