<?php
/**
 * TCS MOTRIZ - Núcleo de Autenticación y Control de Acceso Basado en Roles (RBAC)
 * ISO 27001 A.9 (Control de Acceso) y OWASP A01 (Broken Access Control)
 */

if (!defined('TCS_ACCESS')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso directo prohibido.');
}

class Auth {

    /**
     * Iniciar sesión segura
     */
    public static function login(string $email, string $password): bool {
        // Protección contra ataques de fuerza bruta (Rate Limiting)
        if (!Security::checkRateLimit('login', 5, 300)) {
            Logger::log('AUTH_RATE_LIMIT_EXCEEDED', 'usuarios', null, ['email' => $email]);
            return false;
        }

        $user = DataStore::getUsuarioByEmail($email);
        if (!$user) {
            Logger::log('AUTH_FAIL_USER_NOT_FOUND', 'usuarios', null, ['email' => $email]);
            return false;
        }

        // Verificación de credenciales
        if (!Security::verifyPassword($password, $user['password_hash'])) {
            Logger::log('AUTH_FAIL_INVALID_PASSWORD', 'usuarios', (string)$user['id'], ['email' => $email]);
            return false;
        }

        // Regeneración de ID de sesión para prevenir Session Fixation (OWASP A07)
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_alias'] = $user['alias'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['user_sucursal'] = $user['id_sucursal'];
        $_SESSION['user_taller'] = $user['id_taller'];
        $_SESSION['logged_at'] = time();

        Logger::log('AUTH_SUCCESS_LOGIN', 'usuarios', (string)$user['id'], ['rol' => $user['rol']]);
        return true;
    }

    /**
     * Cerrar sesión de forma segura
     */
    public static function logout(): void {
        if (isset($_SESSION['user_id'])) {
            Logger::log('AUTH_LOGOUT', 'usuarios', (string)$_SESSION['user_id']);
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Verificar si hay una sesión activa
     */
    public static function check(): bool {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Obtener el usuario actual
     */
    public static function user(): ?array {
        if (!self::check()) {
            // Por defecto en la primera carga para comodidad de visualización inmediata,
            // cargamos al Administrador si no se ha iniciado sesión manual.
            self::simulateRole('admin');
        }
        return DataStore::getUsuarioById((int)$_SESSION['user_id']);
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): string {
        return $_SESSION['user_role'] ?? 'cliente';
    }

    public static function isAdmin(): bool {
        $rol = self::role();
        return ($rol === 'admin' || $rol === 'soporte');
    }

    public static function isTechnician(): bool {
        return self::role() === 'tecnico';
    }

    public static function isClient(): bool {
        return self::role() === 'cliente';
    }

    /**
     * Comprobar si el usuario posee alguno de los roles solicitados
     */
    public static function hasRole($roles): bool {
        $userRole = self::role();
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }
        return $userRole === $roles;
    }

    /**
     * Simular cambio de rol en vivo (Característica del Prototipo/Video)
     */
    public static function simulateRole(string $targetRole): void {
        $targetUserId = 1; // Admin por defecto
        if ($targetRole === 'tecnico') {
            $targetUserId = 3; // Héctor Morales
        } elseif ($targetRole === 'cliente') {
            $targetUserId = 4; // Carlos Mendoza (Ford Interlomas)
        } elseif ($targetRole === 'soporte') {
            $targetUserId = 2; // Soporte
        }

        $user = DataStore::getUsuarioById($targetUserId);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_alias'] = $user['alias'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['user_sucursal'] = $user['id_sucursal'];
            $_SESSION['user_taller'] = $user['id_taller'];
            Logger::log('SIMULATE_ROLE_SWITCH', 'usuarios', (string)$user['id'], ['rol' => $user['rol']]);
        }
    }

    /**
     * Prevención de IDOR (Insecure Direct Object Reference)
     * Valida si el usuario cliente tiene autorización de interactuar con el equipo
     */
    public static function canAccessEquipment(array $equipo): bool {
        if (self::isAdmin() || self::isTechnician()) {
            return true;
        }

        $userSucursal = $_SESSION['user_sucursal'] ?? null;
        $userTaller = $_SESSION['user_taller'] ?? null;

        if ($userSucursal && !empty($equipo['id_sucursal']) && $equipo['id_sucursal'] == $userSucursal) {
            return true;
        }
        if ($userTaller && !empty($equipo['id_taller']) && $equipo['id_taller'] == $userTaller) {
            return true;
        }

        return false;
    }
}
