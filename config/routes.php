<?php
/**
 * Rutas del Sistema Moderno - CRM Ligeros v2.0
 * Sistema completamente nuevo, sin legacy
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

// Página principal - dashboard
$router->get('/', 'DashboardController@index');

// Dashboard principal
$router->get('/dashboard', 'DashboardController@index');

// ============================================================================
// RUTAS DE MIEMBROS
// ============================================================================

// Listar miembros
$router->get('/members', 'MemberController@index');

// Crear miembro
$router->get('/members/create', 'MemberController@create');
$router->post('/members', 'MemberController@store');

// Ver miembro específico
$router->get('/members/{id}', 'MemberController@show');

// Editar miembro
$router->get('/members/{id}/edit', 'MemberController@edit');
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

// Ver evento específico
$router->get('/events/{id}', 'EventController@show');

// Editar evento
$router->get('/events/{id}/edit', 'EventController@edit');
$router->put('/events/{id}', 'EventController@update');

// ============================================================================
// RUTAS DE QR Y ASISTENCIA
// ============================================================================

// Mostrar QR de miembro
$router->get('/qr/{id}', 'QRController@show');

// Página de escaneo
$router->get('/scan', 'QRController@scan');

// Procesar escaneo
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
$router->get('/admin/users/{id}/edit', 'AdminController@editUser');
$router->put('/admin/users/{id}', 'AdminController@updateUser');
$router->delete('/admin/users/{id}', 'AdminController@destroyUser');

// Configuración del sistema
$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/settings', 'AdminController@updateSettings');

// Logs y seguridad
$router->get('/admin/logs', 'AdminController@logs');
$router->get('/admin/security', 'AdminController@security');

// ============================================================================
// RUTAS DE API (para mantener compatibilidad)
// ============================================================================

// Las rutas de API siguen funcionando a través de api/index.php
// Estas rutas están aquí solo como referencia

// ============================================================================
// RUTAS DE DESARROLLO Y DEBUG
// ============================================================================

if (defined('APP_ENV') && APP_ENV === 'development') {
    
    // Debug de rutas registradas
    $router->get('/debug/routes', function() use ($router) {
        echo "<!DOCTYPE html>";
        echo "<html lang='es'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        echo "<title>Debug - Rutas del Sistema</title>";
        echo "<style>";
        echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }";
        echo "h1 { color: #333; }";
        echo "table { width: 100%; border-collapse: collapse; background: white; }";
        echo "th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }";
        echo "th { background: #667eea; color: white; }";
        echo "tr:nth-child(even) { background: #f2f2f2; }";
        echo ".method { font-weight: bold; padding: 4px 8px; border-radius: 4px; color: white; }";
        echo ".GET { background: #28a745; }";
        echo ".POST { background: #007bff; }";
        echo ".PUT { background: #ffc107; color: #333; }";
        echo ".DELETE { background: #dc3545; }";
        echo ".back-link { display: inline-block; margin: 20px 0; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; }";
        echo "</style>";
        echo "</head>";
        echo "<body>";
        
        echo "<h1>🔍 Rutas del Sistema CRM Ligeros</h1>";
        echo "<p><strong>Total de rutas registradas:</strong> " . count($router->getRoutes()) . "</p>";
        
        echo "<table>";
        echo "<tr><th>Método</th><th>Ruta</th><th>Controlador</th><th>Descripción</th></tr>";
        
        $routeDescriptions = [
            '/' => 'Página principal (Dashboard)',
            '/login' => 'Iniciar sesión',
            '/logout' => 'Cerrar sesión',
            '/dashboard' => 'Panel de control principal',
            '/members' => 'Listar todos los miembros',
            '/members/create' => 'Formulario crear miembro',
            '/members/{id}' => 'Ver miembro específico',
            '/events' => 'Listar eventos',
            '/events/create' => 'Formulario crear evento',
            '/qr/{id}' => 'Código QR del miembro',
            '/scan' => 'Escanear código QR',
            '/admin' => 'Panel de administración',
            '/admin/users' => 'Gestión de usuarios',
            '/admin/settings' => 'Configuración del sistema'
        ];
        
        foreach ($router->getRoutes() as $route) {
            $description = $routeDescriptions[$route['path']] ?? 'Funcionalidad del sistema';
            echo "<tr>";
            echo "<td><span class='method {$route['method']}'>{$route['method']}</span></td>";
            echo "<td><code>{$route['path']}</code></td>";
            echo "<td>{$route['handler']}</td>";
            echo "<td>{$description}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<a href='/' class='back-link'>← Volver al Dashboard</a>";
        echo "<a href='/login' class='back-link'>🔐 Ir al Login</a>";
        
        echo "</body>";
        echo "</html>";
    });
    
    // Test del sistema
    $router->get('/debug/system', function() {
        echo "<!DOCTYPE html>";
        echo "<html lang='es'>";
        echo "<head><meta charset='UTF-8'><title>Test del Sistema</title></head>";
        echo "<body style='font-family: Arial, sans-serif; margin: 40px; background: #f8f9fa;'>";
        echo "<h1>🧪 Test del Sistema CRM Ligeros</h1>";
        
        echo "<h2>✅ Estado de Servicios:</h2>";
        echo "<ul>";
        
        try {
            $router = app('router');
            echo "<li>✅ Router: Funcionando</li>";
        } catch (Exception $e) {
            echo "<li>❌ Router: Error - " . $e->getMessage() . "</li>";
        }
        
        try {
            $db = app('db');
            echo "<li>" . ($db ? "✅" : "❌") . " Base de Datos: " . ($db ? "Conectada" : "Sin conexión") . "</li>";
        } catch (Exception $e) {
            echo "<li>❌ Base de Datos: Error - " . $e->getMessage() . "</li>";
        }
        
        try {
            $auth = app('auth');
            echo "<li>✅ AuthManager: Disponible</li>";
        } catch (Exception $e) {
            echo "<li>❌ AuthManager: Error - " . $e->getMessage() . "</li>";
        }
        
        echo "</ul>";
        
        echo "<h2>📊 Información del Sistema:</h2>";
        echo "<ul>";
        echo "<li><strong>Aplicación:</strong> " . (defined('APP_NAME') ? APP_NAME : 'N/A') . "</li>";
        echo "<li><strong>Versión:</strong> " . (defined('APP_VERSION') ? APP_VERSION : 'N/A') . "</li>";
        echo "<li><strong>Entorno:</strong> " . (defined('APP_ENV') ? APP_ENV : 'N/A') . "</li>";
        echo "<li><strong>PHP:</strong> " . PHP_VERSION . "</li>";
        echo "<li><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</li>";
        echo "</ul>";
        
        echo "<p><a href='/debug/routes' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>🔍 Ver Rutas</a></p>";
        echo "<p><a href='/' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>🏠 Ir al Dashboard</a></p>";
        
        echo "</body>";
        echo "</html>";
    });
}
?>