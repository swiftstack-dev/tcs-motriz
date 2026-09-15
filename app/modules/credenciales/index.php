<?php
/**
 * TCS MOTRIZ - Credenciales de Rol e Información
 * Directorio de identidades, perfiles y estados de presencia
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$usuarios = DataStore::getUsuarios();
$sucursales = array_column(DataStore::getSucursales(), null, 'id');
$talleres = array_column(DataStore::getTalleres(), null, 'id');
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Credenciales de Información y Roles</h1>
        <p>Listado de administradores, soporte técnico, técnicos y usuarios asignados</p>
    </div>
</div>

<div class="credenciales-grid">
    <?php foreach ($usuarios as $u): ?>
        <div class="credencial-card">
            <div class="credencial-avatar">
                <?= strtoupper(substr($u['nombre'], 0, 2)) ?>
            </div>

            <h3 class="credencial-name"><?= Security::e($u['nombre']) ?></h3>
            <div class="credencial-role"><?= Security::e($u['alias'] ?? $u['rol']) ?></div>

            <div class="credencial-meta">
                <div><strong>RFC:</strong> <?= Security::e($u['rfc'] ?? 'N/A') ?></div>
                <div><strong>Correo:</strong> <?= Security::e($u['email']) ?></div>
                <div><strong>Teléfono:</strong> <?= Security::e($u['telefono'] ?? 'N/A') ?></div>
                <?php if (!empty($u['id_sucursal']) && isset($sucursales[$u['id_sucursal']])): ?>
                    <div><strong>Asignación:</strong> <?= Security::e($sucursales[$u['id_sucursal']]['nombre']) ?></div>
                <?php elseif (!empty($u['id_taller']) && isset($talleres[$u['id_taller']])): ?>
                    <div><strong>Asignación:</strong> <?= Security::e($talleres[$u['id_taller']]['razon_social']) ?></div>
                <?php else: ?>
                    <div><strong>Asignación:</strong> Corporativo TCS Motriz</div>
                <?php endif; ?>
                <div>
                    <strong>Estatus:</strong> 
                    <?php if ($u['is_online']): ?>
                        <span style="color: var(--accent-emerald); font-weight: 600;">● Online</span>
                    <?php else: ?>
                        <span style="color: var(--text-muted);">○ Offline</span>
                    <?php endif; ?>
                </div>
            </div>

            <a href="index.php?view=mensajeria&contacto_id=<?= $u['id'] ?>" class="btn btn-sm btn-dark" style="width: 100%; justify-content: center;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Enviar Mensaje Privado
            </a>
        </div>
    <?php endforeach; ?>
</div>
