<?php
/**
 * Página de Login Principal
 * Usa el patrón MVC con controlador dedicado
 */

// Verificar si estamos en el nuevo sistema
if (!defined('CRM_BOOTSTRAP_LOADED')) {
    // Cargar bootstrap de transición
    require_once __DIR__ . '/config/bootstrap.php';
}

// ============================================================================
// INICIALIZACIÓN Y DEPENDENCIAS
// ============================================================================

// Cargar configuración nueva si existe, sino la antigua
if (file_exists(__DIR__ . '/core/Application.php')) {
    require_once __DIR__ . '/core/Application.php';
    $app = new Application(__DIR__);
    $app->initializeLegacySystem();
} else {
    require_once __DIR__ . '/config/config.php';
}

// Incluir clases de seguridad
require_once __DIR__ . '/security/SecurityManager.php';
require_once __DIR__ . '/security/AuthManager.php';
require_once __DIR__ . '/security/SecurityMiddleware.php';

// Incluir el controlador de login
require_once __DIR__ . '/controllers/LoginController.php';

// ============================================================================
// MIDDLEWARE DE SEGURIDAD
// ============================================================================

// Aplicar middleware de seguridad antes de procesar
SecurityMiddleware::handle();

// ============================================================================
// CONTROLADOR DE LOGIN
// ============================================================================

try {
    // Crear instancia del controlador
    $loginController = new LoginController();
    
    // Delegar toda la lógica al controlador
    $loginController->handleRequest();
    
} catch (Exception $e) {
    // Log crítico si el controlador falla
    error_log('Error crítico en login.php: ' . $e->getMessage());
    
    // Mostrar página de error genérica
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Error - <?php echo APP_NAME; ?></title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
            .error-container { max-width: 500px; margin: 0 auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .error-icon { font-size: 48px; margin-bottom: 20px; }
            .error-title { color: #dc3545; margin-bottom: 15px; }
            .error-message { color: #6c757d; margin-bottom: 25px; }
            .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
            .btn:hover { background: #0056b3; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h1 class="error-title">Error del Sistema</h1>
            <p class="error-message">Lo sentimos, ha ocurrido un error interno. Por favor, inténtalo más tarde.</p>
            <a href="<?php echo BASE_URL; ?>" class="btn">Volver al Inicio</a>
        </div>
    </body>
    </html>
    <?php
}
?>