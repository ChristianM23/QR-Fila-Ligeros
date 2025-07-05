<?php
/**
 * AuthController - Controlador de Autenticación
 * Maneja login, logout y registro
 */

namespace App\Controllers;

class AuthController extends BaseController {
    
    /**
     * Mostrar formulario de login
     */
    public function showLogin($request = null) {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        // Preparar datos para la vista
        $data = [
            'title' => 'Iniciar Sesión',
            'csrf_token' => $this->getCsrfToken(),
            'messages' => $this->getMessages(),
            'form_data' => $this->getFormData()
        ];
        
        return $this->view('auth.login', $data);
    }
    
    /**
     * Procesar formulario de login
     */
    public function processLogin($request = null) {
        try {
            $data = $this->getRequestData();
            
            // Validar datos
            $errors = $this->validate($data, [
                'identifier' => ['required' => true, 'max_length' => 100],
                'password' => ['required' => true, 'max_length' => 255]
            ]);
            
            if (!empty($errors)) {
                $this->setMessage('error', 'Por favor, completa todos los campos requeridos.');
                $this->redirect('/login');
            }
            
            // Sanitizar datos
            $identifier = $this->sanitize($data['identifier']);
            $password = $data['password'];
            
            // Intentar autenticación usando el servicio moderno
            $auth = app('auth');
            $userIP = $this->getRealIP();
            $result = $auth::authenticate($identifier, $password, $userIP);
            
            if (isset($result['success']) && $result['success']) {
                $this->setMessage('success', 'Bienvenido, ' . $result['user']['username']);
                $this->redirect('/dashboard');
            } else {
                $errorMessage = $result['error'] ?? 'Credenciales incorrectas';
                $this->setMessage('error', $errorMessage);
                $this->redirect('/login');
            }
        
        } catch (\Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->setMessage('error', 'Error interno del servidor');
            $this->redirect('/login');
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout($request = null) {
        try {
            if (class_exists('AuthManager')) {
                \AuthManager::logout();
            }
            
            // Limpiar sesión PHP completamente
            if (session_status() !== PHP_SESSION_NONE) {
                session_destroy();
            }
            
            $this->setMessage('success', 'Sesión cerrada correctamente');
            $this->redirect('/login');
            
        } catch (\Exception $e) {
            error_log('Error en logout: ' . $e->getMessage());
            $this->redirect('/login');
        }
    }
    
    /**
     * Obtener mensajes de la sesión
     */
    private function getMessages() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);
        
        return $messages;
    }
    
    /**
     * Establecer mensaje en la sesión
     */
    private function setMessage($type, $message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['messages'][$type] = $message;
    }
    
    /**
     * Obtener datos del formulario de la sesión
     */
    private function getFormData() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $data = $_SESSION['form_data'] ?? ['identifier' => ''];
        unset($_SESSION['form_data']);
        
        return $data;
    }
    
    /**
     * Guardar datos del formulario en la sesión
     */
    private function setFormData($data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Solo guardar datos no sensibles
        $_SESSION['form_data'] = [
            'identifier' => $data['identifier'] ?? ''
        ];
    }
    
    /**
     * Configurar datos de "recordarme"
     */
    private function setRememberMeData($identifier) {
        // Solo si el identifier no es un email (no guardar emails por privacidad)
        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['remember_username'] = $identifier;
        }
    }
    
    /**
     * Obtener IP real del usuario
     */
    private function getRealIP() {
        if (class_exists('SecurityManager')) {
            return \SecurityManager::getRealIPAddress();
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
?>