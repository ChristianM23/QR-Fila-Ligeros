<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login' ?> - <?= $appName ?? 'CRM Ligeros' ?></title>
    
    <!-- Meta tags de seguridad -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo y header -->
            <div class="login-header">
                <div class="logo">
                    <h1><?= $appName ?? 'CRM Ligeros' ?></h1>
                </div>
                <p class="subtitle">Sistema de Gestión de Miembros v2.0</p>
                <h2>Iniciar Sesión</h2>
            </div>
            
            <!-- Alertas y mensajes -->
            <div class="alerts-container">
                <?php if (isset($messages['error'])): ?>
                    <div class="alert alert-error" role="alert">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <strong>Error:</strong> <?= htmlspecialchars($messages['error']) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($messages['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <div class="alert-icon">✅</div>
                        <div class="alert-content">
                            <strong>Éxito:</strong> <?= htmlspecialchars($messages['success']) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($messages['warning'])): ?>
                    <div class="alert alert-warning" role="alert">
                        <div class="alert-icon">⏰</div>
                        <div class="alert-content">
                            <strong>Atención:</strong> <?= htmlspecialchars($messages['warning']) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($messages['info'])): ?>
                    <div class="alert alert-info" role="alert">
                        <div class="alert-icon">ℹ️</div>
                        <div class="alert-content">
                            <strong>Información:</strong> <?= htmlspecialchars($messages['info']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulario de login -->
            <form method="POST" action="/login" class="login-form" novalidate autocomplete="on">
                <!-- Token CSRF oculto -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                
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
                            value="<?= htmlspecialchars($form_data['identifier'] ?? '') ?>"
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
                            <li><strong>Sistema actualizado:</strong> Nueva arquitectura MVC</li>
                            <li><strong>Seguridad mejorada:</strong> Protección multicapa activa</li>
                            <li><strong>Sesiones seguras:</strong> Tokens únicos y cifrado</li>
                            <li><strong>Monitoreo:</strong> Todas las actividades son registradas</li>
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
                <p><strong><?= $appName ?? 'CRM Ligeros' ?></strong> v<?= $appVersion ?? '2.0.0' ?></p>
                <p>Sistema moderno de gestión de miembros</p>
            </div>
            <div class="environment-info">
                <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
                    <p><small>🔧 Modo desarrollo - Arquitectura MVC</small></p>
                <?php endif; ?>
                <p><small>© <?= date('Y') ?> - Asociación Ligeros</small></p>
            </div>
        </div>
    </div>
    
    <!-- Scripts JavaScript -->
    <script src="/assets/js/auth.js"></script>
    
    <!-- Script inline para configuración -->
    <script>
        // Configuración del sistema de login
        window.CRMConfig = {
            baseUrl: '<?= defined('BASE_URL') ? BASE_URL : '/' ?>',
            appName: '<?= addslashes($appName ?? 'CRM Ligeros') ?>',
            csrfToken: '<?= htmlspecialchars($csrf_token ?? '') ?>'
        };
        
        // Inicializar sistema de login cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('.login-form');
            
            if (loginForm && typeof LoginManager !== 'undefined') {
                LoginManager.init(loginForm, window.CRMConfig);
            } else {
                // Fallback simple si LoginManager no está disponible
                console.log('LoginManager no disponible - usando funcionalidad básica');
                
                // Toggle password visibility
                const toggleButtons = document.querySelectorAll('.toggle-password');
                toggleButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('data-target');
                        const passwordField = document.getElementById(targetId);
                        const showText = this.querySelector('.show-text');
                        const hideText = this.querySelector('.hide-text');
                        
                        if (passwordField.type === 'password') {
                            passwordField.type = 'text';
                            showText.style.display = 'none';
                            hideText.style.display = 'inline';
                        } else {
                            passwordField.type = 'password';
                            hideText.style.display = 'none';
                            showText.style.display = 'inline';
                        }
                    });
                });
                
                // Validación básica del formulario
                const form = document.querySelector('.login-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const identifier = document.getElementById('identifier').value.trim();
                        const password = document.getElementById('password').value;
                        
                        if (!identifier || !password) {
                            e.preventDefault();
                            alert('Por favor, completa todos los campos');
                            return false;
                        }
                        
                        // Mostrar estado de carga
                        const submitButton = document.getElementById('loginButton');
                        if (submitButton) {
                            submitButton.disabled = true;
                            const btnContent = submitButton.querySelector('.btn-content');
                            const btnLoading = submitButton.querySelector('.btn-loading');
                            
                            if (btnContent) btnContent.style.display = 'none';
                            if (btnLoading) btnLoading.style.display = 'flex';
                        }
                    });
                }
            }
            
            // Auto-dismiss alerts después de 10 segundos
            const alerts = document.querySelectorAll('.alert-success, .alert-warning, .alert-info');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 300);
                }, 10000);
            });
            
            // Focus en el primer campo
            const firstInput = document.getElementById('identifier');
            if (firstInput && !firstInput.value) {
                firstInput.focus();
            }
        });
        
        // Limpiar campos sensibles al salir
        window.addEventListener('beforeunload', function() {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.value = '';
            }
        });
        
        // Manejar errores de carga de recursos
        window.addEventListener('error', function(e) {
            if (e.target.tagName === 'SCRIPT' || e.target.tagName === 'LINK') {
                console.warn('Recurso no cargado:', e.target.src || e.target.href);
            }
        });
    </script>
</body>
</html>