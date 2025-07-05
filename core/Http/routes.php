<?php
/**
 * Definición de Rutas - CRM Ligeros
 * 
 * Formato: $router->method('path', 'Controller@method');
 */

// ============================================================================
// RUTAS DE AUTENTICACIÓN
// ============================================================================

// Login - mostrar formulario
$router->get('/login', 'AuthController@showLogin');

// Login - procesar formulario
$router->post('/login', 'AuthController@processLogin');

// Logout
$router->get('/logout', 'AuthController@logout');
$router->post('/logout', 'AuthController@logout');

// ============================================================================
// RUTAS PRINCIPALES
// ============================================================================

// Página principal - redirige a dashboard si está logueado
$router->get('/', 'AuthController@showLogin');

// Dashboard principal
$router->get('/dashboard', 'AuthController@showLogin');

// ============================================================================
// RUTAS DE MIEMBROS
// ============================================================================

// Listar miembros
$router->get('/members', 'MemberController@index');

// Mostrar formulario crear miembro
$router->get('/members/create', 'MemberController@create');

// Procesar crear miembro
$router->post('/members', 'MemberController@store');

// Ver miembro específico
$router->get('/members/{id}', 'MemberController@show');

// Mostrar formulario editar miembro
$router->get('/members/{id}/edit', 'MemberController@edit');

// Procesar editar miembro
$router->put('/members/{id}', 'MemberController@update');

// Eliminar miembro
$router->delete('/members/{id}', 'MemberController@destroy');

// ============================================================================
// RUTAS DE EVENTOS
// ============================================================================

// Listar eventos
$router->get('/events', 'EventController@index');

// Crear evento
$router->get('/events/create', 'EventController@create');
$router->post('/events', 'EventController@store');

// Ver evento
$router->get('/events/{id}', 'EventController@show');

// ============================================================================
// RUTAS DE QR
// ============================================================================

// Generar QR para miembro
$router->get('/qr/{id}', 'QRController@show');

// Escanear QR (registrar asistencia)
$router->get('/scan/{code}', 'QRController@scan');
$router->post('/scan', 'QRController@processScan');

// ============================================================================
// RUTAS DE ADMINISTRACIÓN
// ============================================================================

// Panel de administración
$router->get('/admin', 'AdminController@index');

// Gestión de usuarios
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/create', 'AdminController@createUser');
$router->post('/admin/users', 'AdminController@storeUser');

// Configuración del sistema
$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/settings', 'AdminController@updateSettings');

// Logs de seguridad
$router->get('/admin/logs', 'AdminController@logs');

// ============================================================================
// RUTAS DE API (mantener compatibilidad)
// ============================================================================

// Las rutas de API seguirán funcionando a través del archivo api/index.php
// por compatibilidad con el sistema existente

// ============================================================================
// RUTAS DE DESARROLLO (temporales)
// ============================================================================

if (defined('APP_ENV') && APP_ENV === 'development') {
    // Debug de rutas
    $router->get('/debug/routes', function() use ($router) {
        echo "<h1>🔍 Rutas Registradas</h1>";
        echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
        echo "<tr><th>Método</th><th>Ruta</th><th>Handler</th></tr>";
        
        foreach ($router->getRoutes() as $route) {
            echo "<tr>";
            echo "<td>{$route['method']}</td>";
            echo "<td>{$route['path']}</td>";
            echo "<td>{$route['handler']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><a href='/'>← Volver al inicio</a></p>";
    });
    
    // Información del sistema
    $router->get('/debug/info', 'DebugController@info');
}
?>