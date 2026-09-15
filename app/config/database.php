<?php
/**
 * TCS MOTRIZ - Configuración de Conexión a Base de Datos MySQL (Hostinger)
 * Configurado con los accesos oficiales provistos.
 */

if (!defined('TCS_ACCESS')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso directo prohibido.');
}

// Parámetros de Hostinger MySQL Oficiales
define('DB_HOST', getenv('DB_HOST') ?: 'srv1264.hstgr.io'); // Host remoto o 'localhost' en producción
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'u666149057_TCS');
define('DB_USER', getenv('DB_USER') ?: 'u666149057_spartangray');
define('DB_PASS', getenv('DB_PASS') ?: 'p6Z@9DSFX_K7Pxb**-*-*');
define('DB_CHARSET', 'utf8mb4');

/**
 * Administrador de Conexión PDO Singleton
 */
class Database {
    private static ?PDO $instance = null;
    private static bool $isFallbackMode = false;
    private static ?string $connectionError = null;
    private static string $activeHost = DB_HOST;

    public static function getConnection(): ?PDO {
        if (self::$instance === null && !self::$isFallbackMode) {
            $hostsToTry = [DB_HOST, 'localhost', '193.203.166.105'];
            $connected = false;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 3,
            ];

            foreach ($hostsToTry as $host) {
                try {
                    $dsn = sprintf(
                        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                        $host,
                        DB_PORT,
                        DB_NAME,
                        DB_CHARSET
                    );
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                    self::$activeHost = $host;
                    $connected = true;
                    self::$isFallbackMode = false;
                    break;
                } catch (PDOException $e) {
                    self::$connectionError = $e->getMessage();
                }
            }

            if (!$connected) {
                self::$isFallbackMode = true;
                error_log("[TCS Database] No se pudo conectar a MySQL (" . self::$connectionError . "). Activando almacenamiento de respaldo.");
            }
        }
        return self::$instance;
    }

    public static function isFallback(): bool {
        if (self::$instance !== null) {
            return false;
        }
        if (self::$isFallbackMode) {
            return true;
        }
        self::getConnection();
        return self::$instance === null;
    }

    public static function getActiveHost(): string {
        return self::$activeHost;
    }

    public static function getError(): ?string {
        return self::$connectionError;
    }
}
