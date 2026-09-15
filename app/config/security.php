<?php
/**
 * TCS MOTRIZ - Configuración de Seguridad Perimetral y OWASP / ISO 27001
 */

if (!defined('TCS_ACCESS')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso directo prohibido.');
}

// Configuración endurecida de directivas PHP para producción
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', LOGS_PATH . '/php_errors.log');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

// Configuración de cookies seguras de sesión (OWASP A07)
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    session_name(SESSION_NAME);
    
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    
    session_start();
}

// Inicialización de Token CSRF (OWASP A01 / A05)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Validar Token CSRF
 */
function validate_csrf_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtener campo oculto de formulario con CSRF
 */
function csrf_field() {
    $token = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Obtener valor en cadena del token CSRF
 */
function csrf_token() {
    return htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Enviar cabeceras HTTP de seguridad adicionales
 */
function send_security_headers() {
    if (!headers_sent()) {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
    }
}
