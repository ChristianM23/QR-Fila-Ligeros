<?php
/**
 * Punto de Entrada Principal - CRM Ligeros v2.0
 * Este archivo reemplaza al index.php anterior y coordina toda la aplicación
 */

// Configurar zona horaria
date_default_timezone_set('Europe/Madrid');

// Configurar manejo de errores básico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir ruta base del proyecto
define('PROJECT_ROOT', dirname(__DIR__));

try {
    // Cargar la clase Application
    require_once PROJECT_ROOT . '/core/Application.php';
    
    // Crear e inicializar aplicación
    $app = new Application(PROJECT_ROOT);
    
    // Ejecutar en modo compatibilidad (mantiene funcionalidad existente)
    $app->run();
    
} catch (Exception $e) {
    // Manejo de errores críticos
    error_log('Critical Application Error: ' . $e->getMessage());
    
    // Mostrar error amigable
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - CRM Ligeros</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
                padding: 20px;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-container {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                text-align: center;
                max-width: 500px;
                width: 100%;
            }
            .error-icon {
                font-size: 48px;
                margin-bottom: 20px;
            }
            .error-title {
                color: #dc3545;
                margin-bottom: 15px;
                font-size: 24px;
            }
            .error-message {
                color: #6c757d;
                margin-bottom: 25px;
                line-height: 1.5;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #0056b3;
            }
            .debug-info {
                margin-top: 20px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 5px;
                text-align: left;
                font-family: monospace;
                font-size: 12px;
                color: #666;
                border: 1px solid #dee2e6;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h1 class="error-title">Error del Sistema</h1>
            <p class="error-message">
                Lo sentimos, ha ocurrido un error interno durante la inicialización del sistema.
                <br>Por favor, verifica la configuración o contacta al administrador.
            </p>
            
            <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
                <div class="debug-info">
                    <strong>Información de Debug:</strong><br>
                    <?= htmlspecialchars($e->getMessage()) ?><br><br>
                    <strong>Archivo:</strong> <?= $e->getFile() ?><br>
                    <strong>Línea:</strong> <?= $e->getLine() ?>
                </div>
            <?php endif; ?>
            
            <a href="/" class="btn">🔄 Reintentar</a>
            <a href="/login.php" class="btn" style="background: #28a745;">🔐 Login Directo</a>
        </div>
    </body>
    </html>
    <?php
}
?>