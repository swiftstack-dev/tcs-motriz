<?php
/**
 * TCS MOTRIZ - Configuración General del Sistema
 * Versión 1.0.0 - Entorno Hostinger / PHP 8.x
 */

// Evitar acceso directo
if (!defined('TCS_ACCESS')) {
    define('TCS_ACCESS', true);
}

// Configuración horaria y localización
date_default_timezone_set('America/Mexico_City');
setlocale(LC_ALL, 'es_MX.UTF-8', 'es_ES.UTF-8', 'spanish');

// Constantes de la Aplicación
define('APP_NAME', 'TCS Motriz');
define('APP_TITLE', 'TCS Motriz | Plataforma de Gestión Técnica y Telemetría');
define('APP_SUBTITLE', 'Manejo de Reparaciones, Inventario y Salud de Elevadores');
define('APP_DOMAIN', 'servicio-tcsmotriz.com.mx');
define('APP_VERSION', '1.0.0-STABLE');
define('APP_ENV', getenv('APP_ENV') ?: 'production'); // 'development' o 'production'

// Rutas del Sistema
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('CORE_PATH', BASE_PATH . '/core');
define('MODULES_PATH', BASE_PATH . '/modules');
define('LOGS_PATH', BASE_PATH . '/logs');
define('ASSETS_PATH', BASE_PATH . '/assets');

// Configuración de Sesión
define('SESSION_LIFETIME', 3600 * 8); // 8 Horas laborales
define('SESSION_NAME', 'TCS_SESSION_ID');

// Contacto y Soporte
define('SUPPORT_EMAIL', 'soporte@servicio-tcsmotriz.com.mx');
define('SUPPORT_PHONE', '55-8000-4277');
