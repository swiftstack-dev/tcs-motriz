<?php
/**
 * TCS MOTRIZ - Cierre Seguro de Sesión (OWASP A07)
 */

define('TCS_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/DataStore.php';
require_once __DIR__ . '/core/Auth.php';

Auth::logout();
header('Location: login.php?msg=sesion_cerrada');
exit;
