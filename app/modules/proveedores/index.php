<?php
/**
 * TCS MOTRIZ - Módulo de Registro y Directorio de Proveedores
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$proveedores = DataStore::getProveedores();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Registro de Proveedores Automotrices</h1>
        <p>Directorio calificado de fabricantes de elevadores, distribuidores de lubricantes e insumos</p>
    </div>

    <?php if ($isAdmin || $isTech): ?>
    <div class="header-action-buttons">
        <button class="btn btn-primary" onclick="openModal('modal-crear-proveedor')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Registrar Nuevo Proveedor</span>
        </button>
    </div>
    <?php endif; ?>
</div>

<div class="equipos-grid">
    <?php foreach ($proveedores as $prov): ?>
        <div class="equipo-card">
            <div>
                <div class="equipo-card-header">
                    <span class="code-badge"><?= Security::e($prov['rfc']) ?></span>
                    <span class="badge-status status-operativo"><?= Security::e($prov['categoria']) ?></span>
                </div>

                <h3 class="equipo-title"><?= Security::e($prov['razon_social']) ?></h3>

                <div class="equipo-meta-list">
                    <div><strong>👤 Contacto:</strong> <?= Security::e($prov['contacto']) ?></div>
                    <div><strong>📞 Teléfono:</strong> <a href="tel:<?= Security::e($prov['telefono']) ?>" style="color: var(--accent-cyan-light); text-decoration: none;"><?= Security::e($prov['telefono']) ?></a></div>
                    <div><strong>✉️ Correo:</strong> <a href="mailto:<?= Security::e($prov['email']) ?>" style="color: var(--accent-cyan-light); text-decoration: none;"><?= Security::e($prov['email']) ?></a></div>
                    <div><strong>💳 Días de Crédito:</strong> <?= (int)$prov['dias_credito'] ?> días</div>
                </div>
            </div>

            <div class="equipo-card-actions">
                <a href="mailto:<?= Security::e($prov['email']) ?>?subject=Cotizacion%20TCS%20Motriz" class="btn btn-sm btn-cyan" style="flex: 1; justify-content: center;">
                    Solicitar Cotización
                </a>
                <a href="tel:<?= Security::e($prov['telefono']) ?>" class="btn btn-sm btn-dark">
                    Llamar
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- MODAL REGISTRAR PROVEEDOR -->
<div id="modal-crear-proveedor" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-title">Registrar Nuevo Proveedor Autorizado</div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_proveedor">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Razón Social *</label>
                        <input type="text" name="razon_social" class="form-control" required placeholder="Ej: Kaeser Compresores de México S.A.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC *</label>
                        <input type="text" name="rfc" class="form-control" required placeholder="Ej: KCM980312-AA1">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre del Contacto / Asesor *</label>
                        <input type="text" name="contacto" class="form-control" required placeholder="Ej: Lic. Gerardo Ramos">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría de Especialidad *</label>
                        <input type="text" name="categoria" class="form-control" required placeholder="Ej: Compresores y Neumática">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono Directo *</label>
                        <input type="text" name="telefono" class="form-control" required placeholder="Ej: 55-4433-2211">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" name="email" class="form-control" required placeholder="ventas@proveedor.com">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Días de Crédito Otorgados</label>
                    <input type="number" name="dias_credito" class="form-control" value="30">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Dar de Alta Proveedor</button>
            </div>
        </form>
    </div>
</div>
