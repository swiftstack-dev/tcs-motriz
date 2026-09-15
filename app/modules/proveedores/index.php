<?php
/**
 * TCS MOTRIZ - Módulo de Registro y Directorio de Proveedores Automotrices
 * Gestión integral de fabricantes, distribuidores de refacciones, créditos y contacto
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$proveedores = DataStore::getProveedores();
$inventario = DataStore::getInventario();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();

$msg = Security::sanitizeString($_GET['msg'] ?? '');
$provFiltroId = !empty($_GET['proveedor_id']) ? Security::sanitizeInt($_GET['proveedor_id']) : null;

// Métricas de proveedores
$totalProveedores = count($proveedores);
$conCredito = count(array_filter($proveedores, fn($p) => ((int)$p['dias_credito'] > 0)));
$totalRefaccionesSuministradas = count(array_filter($inventario, fn($i) => !empty($i['id_proveedor'])));
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Directorio y Registro de Proveedores Autorizados</h1>
        <p>Alianzas con fabricantes de elevadores, importadores de refacciones y distribuidores de fluidos</p>
    </div>

    <div class="header-action-buttons">
        <a href="index.php?action=exportar_csv&tipo=proveedores" class="btn btn-dark" title="Descargar directorio en formato Excel / CSV">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Exportar Directorio</span>
        </a>

        <?php if ($isAdmin || $isTech): ?>
        <button class="btn btn-primary" onclick="openModal('modal-crear-proveedor')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Nuevo Proveedor</span>
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- MENSAJES DE NOTIFICACIÓN DE ACCIONES -->
<?php if ($msg === 'proveedor_creado'): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--accent-emerald); color: #34d399; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>✅</span> Proveedor dado de alta exitosamente en el directorio oficial de TCS Motriz.
    </div>
<?php elseif ($msg === 'proveedor_actualizado'): ?>
    <div style="background: rgba(14, 165, 233, 0.15); border: 1px solid var(--accent-cyan); color: #38bdf8; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>ℹ️</span> Información del proveedor, condiciones de crédito y contacto actualizados.
    </div>
<?php elseif ($msg === 'proveedor_eliminado'): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--accent-rose); color: #f87171; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>🗑️</span> Proveedor eliminado del catálogo y registrado en la bitácora de auditoría.
    </div>
<?php endif; ?>

<!-- KPI CARDS DE PROVEEDORES -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="kpi-card kpi-cyan">
        <div class="kpi-number"><?= $totalProveedores ?></div>
        <div class="kpi-label">Proveedores Calificados</div>
        <div class="kpi-subtext">Directorio Activo</div>
    </div>

    <div class="kpi-card kpi-emerald">
        <div class="kpi-number"><?= $totalRefaccionesSuministradas ?></div>
        <div class="kpi-label">Refacciones Asignadas</div>
        <div class="kpi-subtext">Piezas en Catálogo Central</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-number"><?= $conCredito ?></div>
        <div class="kpi-label">Líneas de Crédito</div>
        <div class="kpi-subtext">Plazos de 15 a 45 días</div>
    </div>

    <div class="kpi-card kpi-rose">
        <div class="kpi-number">98.4%</div>
        <div class="kpi-label">Cumplimiento en Entregas</div>
        <div class="kpi-subtext">SLA de Proveedores Certificados</div>
    </div>
</div>

<!-- BUSCADOR EN VIVO DE PROVEEDORES -->
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
    <div style="flex: 1; max-width: 400px;">
        <input type="text" id="search-proveedor-input" class="form-control" placeholder="🔍 Filtrar por razón social, contacto, especialidad o RFC..." />
    </div>
    <div style="font-size: 13px; color: var(--text-secondary);">
        Mostrando <strong style="color: #fff;"><?= count($proveedores) ?></strong> distribuidores autorizados
    </div>
</div>

<!-- GRID DE PROVEEDORES -->
<div class="equipos-grid" id="proveedores-grid">
    <?php foreach ($proveedores as $prov): ?>
        <?php 
            $misPiezas = array_filter($inventario, fn($i) => ($i['id_proveedor'] ?? 0) == $prov['id']);
            $cleanPhone = preg_replace('/[^0-9]/', '', $prov['telefono']);
            if (strlen($cleanPhone) === 10) {
                $cleanPhone = '521' . $cleanPhone;
            } elseif (strlen($cleanPhone) < 10) {
                $cleanPhone = '52155' . $cleanPhone;
            }
            $whatsappMsg = urlencode("Hola {$prov['contacto']}, le contacto del departamento de Compras y Almacén de TCS Motriz para solicitar cotización y tiempos de entrega de refacciones para elevadores automotrices.");
        ?>
        <div class="equipo-card proveedor-card-item">
            <div>
                <div class="equipo-card-header">
                    <span class="code-badge"><?= Security::e($prov['rfc']) ?></span>
                    <span class="badge-status status-operativo" style="font-size: 10px;">
                        <?= Security::e($prov['categoria']) ?>
                    </span>
                </div>

                <h3 class="equipo-title" style="margin-bottom: 12px;"><?= Security::e($prov['razon_social']) ?></h3>

                <div class="equipo-meta-list" style="margin-bottom: 14px;">
                    <div>
                        <strong>👤 Asesor / Contacto:</strong> 
                        <span style="color: #fff;"><?= Security::e($prov['contacto']) ?></span>
                    </div>
                    <div>
                        <strong>📞 Conmutador / Tel:</strong> 
                        <a href="tel:<?= Security::e($prov['telefono']) ?>" style="color: var(--accent-cyan-light); text-decoration: none; font-weight: 600;">
                            <?= Security::e($prov['telefono']) ?>
                        </a>
                    </div>
                    <div>
                        <strong>✉️ Correo Oficial:</strong> 
                        <a href="mailto:<?= Security::e($prov['email']) ?>?subject=Cotizacion%20TCS%20Motriz" style="color: var(--accent-cyan-light); text-decoration: none;">
                            <?= Security::e($prov['email']) ?>
                        </a>
                    </div>
                    <div>
                        <strong>💳 Crédito Comercial:</strong> 
                        <span style="color: var(--accent-emerald); font-weight: 700;"><?= (int)$prov['dias_credito'] ?> días hábiles</span>
                    </div>
                    <div>
                        <strong>📦 Refacciones Suministradas:</strong> 
                        <span style="color: #fff; font-weight: 700;"><?= count($misPiezas) ?> ítems</span> en catálogo
                    </div>
                </div>

                <!-- LISTADO DE PIEZAS SUMINISTRADAS (CHIPS) -->
                <?php if (!empty($misPiezas)): ?>
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border-subtle);">
                    <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Catálogo Habitual:</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                        <?php foreach (array_slice($misPiezas, 0, 3) as $pz): ?>
                            <span style="font-size: 11px; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 2px 6px; border-radius: 4px;">
                                <?= Security::e($pz['codigo_parte']) ?>
                            </span>
                        <?php endforeach; ?>
                        <?php if (count($misPiezas) > 3): ?>
                            <span style="font-size: 10px; color: var(--text-muted); align-self: center;">+<?= count($misPiezas) - 3 ?> más</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="equipo-card-actions" style="margin-top: 18px; border-top: 1px solid var(--border-subtle); padding-top: 14px; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; gap: 6px; width: 100%;">
                    <a href="https://api.whatsapp.com/send?phone=<?= $cleanPhone ?>&text=<?= $whatsappMsg ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm" style="flex: 1; justify-content: center; background: #059669; color: #fff; border: 1px solid #10b981;" title="Chatear con el proveedor por WhatsApp">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                    <a href="mailto:<?= Security::e($prov['email']) ?>?subject=Solicitud%20de%20Cotizacion%20-%20TCS%20Motriz" class="btn btn-sm btn-cyan" style="flex: 1; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span>Cotizar</span>
                    </a>
                </div>

                <?php if ($isAdmin || $isTech): ?>
                <div style="display: flex; gap: 6px; width: 100%; justify-content: flex-end;">
                    <button type="button" class="btn btn-sm btn-dark" style="flex: 1; justify-content: center;" onclick='openEditarProveedor(<?= json_encode($prov, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                        ✏️ Editar
                    </button>
                    <?php if ($isAdmin): ?>
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmarEliminarProveedor(<?= $prov['id'] ?>, '<?= Security::e($prov['razon_social']) ?>')">
                        🗑️
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- =========================================================================
     MODALES OPERATIVOS DE PROVEEDORES
     ========================================================================= -->

<!-- MODAL 1: REGISTRAR NUEVO PROVEEDOR -->
<div id="modal-crear-proveedor" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Registrar Nuevo Proveedor Autorizado</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_proveedor">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Razón Social *</label>
                        <input type="text" name="razon_social" class="form-control" required placeholder="Ej: Rotary Lift de México S.A. de C.V.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC Fiscal *</label>
                        <input type="text" name="rfc" class="form-control" required placeholder="Ej: RLM990115-99A" style="text-transform: uppercase;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre del Asesor / Contacto *</label>
                        <input type="text" name="contacto" class="form-control" required placeholder="Ej: Ing. Arturo Salgado">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría / Especialidad *</label>
                        <input type="text" name="categoria" class="form-control" required placeholder="Ej: Elevadores y Rampas">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono Directo / WhatsApp *</label>
                        <input type="text" name="telefono" class="form-control" required placeholder="Ej: 55-4000-8800">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico Oficial *</label>
                        <input type="email" name="email" class="form-control" required placeholder="ventas@proveedor.mx">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Días de Crédito Comercial</label>
                    <input type="number" name="dias_credito" class="form-control" value="30" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar en Directorio</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: EDITAR PROVEEDOR -->
<div id="modal-editar-proveedor" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Editar Datos de Proveedor</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=editar_proveedor">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit-prov-id" value="">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Razón Social *</label>
                        <input type="text" name="razon_social" id="edit-prov-razon" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC Fiscal *</label>
                        <input type="text" name="rfc" id="edit-prov-rfc" class="form-control" required style="text-transform: uppercase;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre del Asesor / Contacto *</label>
                        <input type="text" name="contacto" id="edit-prov-contacto" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría / Especialidad *</label>
                        <input type="text" name="categoria" id="edit-prov-categoria" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono Directo / WhatsApp *</label>
                        <input type="text" name="telefono" id="edit-prov-telefono" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico Oficial *</label>
                        <input type="email" name="email" id="edit-prov-email" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Días de Crédito Comercial</label>
                    <input type="number" name="dias_credito" id="edit-prov-credito" class="form-control" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-cyan">Guardar Cambios de Proveedor</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('search-proveedor-input');
    if (input) {
        input.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.proveedor-card-item').forEach(card => {
                const text = card.innerText.toLowerCase();
                card.style.display = text.includes(term) ? 'flex' : 'none';
            });
        });
    }
});

function openEditarProveedor(prov) {
    document.getElementById('edit-prov-id').value = prov.id || '';
    document.getElementById('edit-prov-razon').value = prov.razon_social || '';
    document.getElementById('edit-prov-rfc').value = prov.rfc || '';
    document.getElementById('edit-prov-contacto').value = prov.contacto || '';
    document.getElementById('edit-prov-categoria').value = prov.categoria || '';
    document.getElementById('edit-prov-telefono').value = prov.telefono || '';
    document.getElementById('edit-prov-email').value = prov.email || '';
    document.getElementById('edit-prov-credito').value = prov.dias_credito || 0;
    openModal('modal-editar-proveedor');
}

function confirmarEliminarProveedor(id, razon) {
    if (confirm(`¿Está seguro de eliminar al proveedor "${razon}" del directorio de compras? Esta acción se asentará en la bitácora de auditoría.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=eliminar_proveedor';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = 'csrf_token';
        csrf.value = '<?= csrf_token() ?>';
        form.appendChild(csrf);

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;
        form.appendChild(inputId);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
