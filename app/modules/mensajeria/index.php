<?php
/**
 * TCS MOTRIZ - Mensajería Privada Directa
 * Canal perimetral seguro entre Administrador, Soporte Técnico, Técnicos y Clientes
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$currentUser = Auth::user();
$mensajes = DataStore::getMensajes();
$usuarios = DataStore::getUsuarios();

// Seleccionar contacto activo (por defecto Soporte Técnico o Admin)
$contactoActivoId = isset($_GET['contacto_id']) ? Security::sanitizeInt($_GET['contacto_id']) : (Auth::isAdmin() ? 4 : 2);
$contactoActivo = DataStore::getUsuarioById($contactoActivoId) ?? $usuarios[0];
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Mensajería Privada Directa</h1>
        <p>Canal de comunicación seguro entre Administrador, Soporte Técnico y Clientes</p>
    </div>
</div>

<div class="chat-container">
    <!-- LISTA DE CONTACTOS -->
    <div class="chat-contact-list">
        <div style="padding: 14px; border-bottom: 1px solid var(--border-subtle); font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
            Contactos del Sistema
        </div>
        <?php foreach ($usuarios as $u): ?>
            <?php if ($u['id'] != $currentUser['id']): ?>
                <a href="index.php?view=mensajeria&contacto_id=<?= $u['id'] ?>" class="contact-item <?= ($contactoActivoId == $u['id'] ? 'active' : '') ?>" style="text-decoration: none; color: inherit;">
                    <div style="position: relative;">
                        <div class="profile-avatar" style="width: 32px; height: 32px; font-size: 12px;">
                            <?= strtoupper(substr($u['nombre'], 0, 2)) ?>
                        </div>
                        <?php if ($u['is_online']): ?>
                            <div class="profile-status-dot"></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: #fff;"><?= Security::e($u['nombre']) ?></div>
                        <div style="font-size: 10px; color: var(--accent-cyan-light); text-transform: uppercase;">
                            <?= Security::e($u['alias'] ?? $u['rol']) ?>
                        </div>
                    </div>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- ÁREA DE CONVERSACIÓN -->
    <div class="chat-viewport">
        <div class="chat-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="profile-avatar" style="width: 36px; height: 36px;">
                    <?= strtoupper(substr($contactoActivo['nombre'], 0, 2)) ?>
                </div>
                <div>
                    <div style="font-size: 14px; font-weight: 700; color: #fff;"><?= Security::e($contactoActivo['nombre']) ?></div>
                    <div style="font-size: 11px; color: var(--accent-emerald);">● En línea • <?= strtoupper(Security::e($contactoActivo['alias'] ?? $contactoActivo['rol'])) ?></div>
                </div>
            </div>
            <span class="code-badge">CANAL SEGURO</span>
        </div>

        <div class="chat-messages">
            <?php foreach ($mensajes as $msg): ?>
                <?php $isMyMessage = ($msg['id_remitente'] == $currentUser['id']); ?>
                <div class="message-bubble <?= ($isMyMessage ? 'message-out' : 'message-in') ?>">
                    <div style="font-size: 10px; opacity: 0.8; margin-bottom: 3px;">
                        <?= Security::e($msg['remitente_nombre']) ?>
                    </div>
                    <div><?= Security::e($msg['mensaje']) ?></div>
                    <div class="message-time"><?= Security::e($msg['hora'] ?? '14:20') ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST" action="index.php?action=enviar_mensaje" class="chat-input-area">
            <?= csrf_field() ?>
            <input type="hidden" name="id_destinatario" value="<?= $contactoActivo['id'] ?>">
            <input type="text" name="mensaje" class="form-control" placeholder="Escribe un mensaje privado seguro..." required autocomplete="off">
            <button type="submit" class="btn btn-primary">
                <span>Enviar</span>
            </button>
        </form>
    </div>
</div>
