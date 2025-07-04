<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo htmlspecialchars($config['app_name']); ?></title>
    
    <!-- Meta tags de seguridad -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="<?php echo $config['base_url']; ?>assets/css/login.css">
    
    <!-- Precargar recursos críticos -->
    <link rel="preload" href="<?php echo $config['base_url']; ?>assets/css/login.css" as="style">
    <link rel="preload" href="<?php echo $config['base_url']; ?>assets/js/login.js" as="script">
    
    <!-- Favicon (opcional) -->
    <link rel="icon" href="<?php echo $config['base_url']; ?>assets/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo y header -->
            <div class="login-header">
                <div class="logo">
                    <h1><?php echo htmlspecialchars($config['app_name']); ?></h1>
                </div>
                <p class="subtitle">Sistema de Gestión de Miembros</p>
                <h2>Iniciar Sesión</h2>
            </div>
            
            <!-- Alertas y mensajes -->
            <div class="alerts-container">
                <?php if (isset($messages['error'])): ?>
                    <div class="alert alert-error" role="alert">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <strong>Error:</strong> <?php echo htmlspecialchars($messages['error']); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($messages['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <div class="alert-icon">✅</div>
                        <div class="alert-content">
                            <strong>Éxito:</strong> <?php echo htmlspecialchars($messages['success']); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($messages['warning'])): ?>
                    <div class="alert alert-warning" role="alert">
                        <div class="alert-icon">⏰</div>
                        <div class="alert-content">
                            <strong>Atención:</strong> <?php echo htmlspecialchars($messages['warning']); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($messages['info'])): ?>
                    <div class="alert alert-info" role="alert">
                        <div class="alert-icon">ℹ️</div>
                        <div class="alert-content">
                            <strong>Información:</strong> <?php echo htmlspecialchars($messages['info']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulario de login -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="login-form" novalidate autocomplete="on">
                <!-- Token CSRF oculto -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <!-- Campo de usuario/email -->
                <div class="form-group">
                    <label for="identifier" class="form-label">
                        <span class="label-text">Usuario o Email</span>
                        <span class="label-required">*</span>
                    </label>
                    <div class="input-container">
                        <input 
                            type="text" 
                            id="identifier" 
                            name="identifier" 
                            value="<?php echo htmlspecialchars($form_data['identifier']); ?>"
                            required 
                            autocomplete="username"
                            class="form-control"
                            placeholder="Introduce tu usuario o email"
                            maxlength="100"
                            autofocus
                        >
                        <div class="input-icon">👤</div>
                    </div>
                    <div class="form-help">
                        Puedes usar tu nombre de usuario o dirección de email.
                    </div>
                </div>
                
                <!-- Campo de contraseña -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <span class="label-text">Contraseña</span>
                        <span class="label-required">*</span>
                    </label>
                    <div class="input-container">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            class="form-control"
                            placeholder="Introduce tu contraseña"
                            maxlength="255"
                        >
                        <button type="button" class="toggle-password" data-target="password" tabindex="-1">
                            <span class="show-text">👁️</span>
                            <span class="hide-text" style="display: none;">🙈</span>
                        </button>
                    </div>
                </div>
                
                <!-- Checkbox recordarme -->
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="remember_me" name="remember_me" class="checkbox-input">
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">Recordarme por 30 días</span>
                    </label>
                    <div class="form-help">
                        Mantener la sesión activa en este dispositivo.
                    </div>
                </div>
                
                <!-- Botón de envío -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-login" id="loginButton">
                        <span class="btn-content">
                            <span class="btn-icon">🔐</span>
                            <span class="btn-text">Iniciar Sesión</span>
                        </span>
                        <span class="btn-loading" style="display: none;">
                            <span class="spinner"></span>
                            <span class="loading-text">Verificando...</span>
                        </span>
                    </button>
                </div>
            </form>
            
            <!-- Información de seguridad -->
            <div class="security-info">
                <details class="security-details">
                    <summary>🛡️ Información de Seguridad</summary>
                    <div class="security-content">
                        <ul>
                            <li><strong>Intentos permitidos:</strong> Máximo <?php echo $config['max_attempts']; ?> intentos por IP</li>
                            <li><strong>Bloqueo temporal:</strong> <?php echo round($config['lockout_time'] / 60); ?> minutos tras intentos fallidos</li>
                            <li><strong>Protección activa:</strong> Todas las actividades quedan registradas</li>
                            <li><strong>Sesión segura:</strong> Conexión protegida con tokens únicos</li>
                        </ul>
                    </div>
                </details>
            </div>
            
            <!-- Enlaces adicionales -->
            <div class="login-footer">
                <div class="help-links">
                    <p><small>¿Problemas para acceder? Contacta con un administrador de la asociación.</small></p>
                </div>
            </div>
        </div>
        
        <!-- Información del sistema -->
        <div class="system-info">
            <div class="app-info">
                <p><strong><?php echo htmlspecialchars($config['app_name']); ?></strong> v<?php echo $config['app_version']; ?></p>
                <p>Sistema seguro de gestión de miembros</p>
            </div>
            <div class="environment-info">
                <?php if (isDevelopment()): ?>
                    <p><small>🔧 Modo desarrollo activo</small></p>
                <?php endif; ?>
                <p><small>© <?php echo date('Y'); ?> - Asociación Ligeros</small></p>
            </div>
        </div>
    </div>
    
    <!-- Scripts JavaScript -->
    <script src="<?php echo $config['base_url']; ?>assets/js/login.js"></script>
    
    <!-- Script inline para configuración -->
    <script>
        // Configuración del sistema de login
        window.CRMConfig = {
            baseUrl: '<?php echo $config['base_url']; ?>',
            maxAttempts: <?php echo $config['max_attempts']; ?>,
            lockoutTime: <?php echo $config['lockout_time']; ?>,
            appName: '<?php echo addslashes($config['app_name']); ?>',
            csrfToken: '<?php echo $csrf_token; ?>'
        };
        
        // Inicializar sistema de login cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('.login-form');
            
            if (loginForm && typeof LoginManager !== 'undefined') {
                LoginManager.init(loginForm, window.CRMConfig);
            }
            
            // Auto-dismiss alerts después de 10 segundos
            const alerts = document.querySelectorAll('.alert-success, .alert-warning');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 10000);
            });
        });
        
        // Limpiar campos sensibles al salir
        window.addEventListener('beforeunload', function() {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.value = '';
            }
        });
    </script>
</body>
</html>