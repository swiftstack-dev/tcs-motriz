<?php
/**
 * TCS MOTRIZ - Portal de Autenticación Unificado (OWASP A07 / ISO 27001 A.9)
 */

define('TCS_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/DataStore.php';
require_once __DIR__ . '/core/Auth.php';

send_security_headers();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        $error = 'Error de seguridad en la sesión. Por favor recargue la página.';
    } else {
        $email = Security::sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $error = 'Por favor complete todos los campos requeridos.';
        } else {
            if (Auth::login($email, $password)) {
                header('Location: index.php?view=dashboard');
                exit;
            } else {
                $error = 'Credenciales no reconocidas o cuenta temporalmente bloqueada por seguridad.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Seguro | <?= Security::e(APP_TITLE) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at center, #0d1a33 0%, #060a14 100%); min-height: 100vh;">

<div style="width: 100%; max-width: 440px; padding: 20px;">
    <div style="background: var(--bg-card); border: 1px solid #1c2e56; border-radius: var(--radius-lg); padding: 36px 30px; box-shadow: var(--shadow-modal); text-align: center;">
        <img src="assets/img/logo-tcs.png" alt="TCS Motriz" style="height: 52px; object-fit: contain; margin-bottom: 16px;">
        <h1 style="font-size: 18px; font-weight: 800; color: #fff; letter-spacing: 0.5px;">Acceso al Sistema Técnico</h1>
        <p style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; margin-bottom: 24px;">Servicio TCS Motriz • Portal de Clientes y Administración</p>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; font-size: 12px; padding: 10px; border-radius: var(--radius-sm); margin-bottom: 20px; text-align: left;">
                ⚠️ <?= Security::e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" style="text-align: left;">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Correo Electrónico Autorizado</label>
                <input type="email" name="email" class="form-control" required placeholder="ejemplo@servicio-tcsmotriz.com.mx" autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña de Seguridad</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••••••">
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                    Autenticarse en el Sistema
                </button>
            </div>
        </form>

        <div style="margin-top: 26px; border-top: 1px solid var(--border-subtle); padding-top: 18px; font-size: 12px; color: var(--text-muted);">
            <div style="margin-bottom: 8px; font-weight: 600;">Accesos de Demostración Rápida:</div>
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <a href="index.php?action=switch_role&role=admin" class="btn btn-sm btn-dark" style="justify-content: center;">
                    Ingresar como Administrador TCS
                </a>
                <a href="index.php?action=switch_role&role=tecnico" class="btn btn-sm btn-dark" style="justify-content: center;">
                    Ingresar como Técnico Especialista
                </a>
                <a href="index.php?action=switch_role&role=cliente" class="btn btn-sm btn-dark" style="justify-content: center;">
                    Ingresar como Cliente (Ford Interlomas)
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
