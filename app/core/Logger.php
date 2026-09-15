<?php
/**
 * TCS MOTRIZ - Módulo de Auditoría y Bitácora de Eventos (ISO 27001 A.12.4)
 */

if (!defined('TCS_ACCESS')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso directo prohibido.');
}

class Logger {
    private static string $logFile = LOGS_PATH . '/security.log';

    /**
     * Registrar un evento de auditoría de seguridad
     */
    public static function log(
        string $action,
        string $entity,
        ?string $entityId = null,
        array $details = [],
        ?int $userId = null,
        ?string $userRole = null
    ): void {
        $ip = Security::getClientIp();
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);
        $timestamp = date('Y-m-d H:i:s');

        // Determinar usuario actual si no se provee
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['user_role'] ?? 'guest';
        }

        $logEntry = [
            'timestamp'   => $timestamp,
            'user_id'     => $userId,
            'user_role'   => $userRole ?: 'guest',
            'action'      => strtoupper($action),
            'entity'      => $entity,
            'entity_id'   => $entityId,
            'ip'          => $ip,
            'user_agent'  => $userAgent,
            'details'     => $details
        ];

        // 1. Escritura en archivo físico protegido (Append Only)
        $line = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        @file_put_contents(self::$logFile, $line, FILE_APPEND | LOCK_EX);

        // 2. Registro en base de datos si hay conexión PDO
        try {
            $pdo = Database::getConnection();
            if ($pdo) {
                $stmt = $pdo->prepare("
                    INSERT INTO audit_logs (id_usuario, rol_usuario, accion, entidad, entidad_id, ip_address, user_agent, detalles)
                    VALUES (:uid, :rol, :accion, :entidad, :eid, :ip, :ua, :detalles)
                ");
                $stmt->execute([
                    ':uid'      => $userId,
                    ':rol'      => $userRole,
                    ':accion'   => strtoupper($action),
                    ':entidad'  => $entity,
                    ':eid'      => $entityId,
                    ':ip'       => $ip,
                    ':ua'       => $userAgent,
                    ':detalles' => json_encode($details, JSON_UNESCAPED_UNICODE)
                ]);
            }
        } catch (Exception $e) {
            // Silencioso para evitar romper el flujo del usuario si la BD está offline
        }
    }

    /**
     * Leer últimos logs de auditoría para la consola de administración
     */
    public static function getRecentLogs(int $limit = 50): array {
        $logs = [];
        if (file_exists(self::$logFile)) {
            $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines) {
                $reversed = array_reverse($lines);
                $slice = array_slice($reversed, 0, $limit);
                foreach ($slice as $line) {
                    $decoded = json_decode($line, true);
                    if ($decoded) {
                        $logs[] = $decoded;
                    }
                }
            }
        }
        return $logs;
    }
}
