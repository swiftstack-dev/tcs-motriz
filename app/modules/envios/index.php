<?php
/**
 * TCS MOTRIZ - Módulo de Guías de Envío y Despachos Logísticos
 * Trazabilidad de paquetería (DHL, FedEx, Estafeta, Paquetexpress, TCS Directo),
 * remisiones de salida, control de entrega y kárdex de refacciones en tránsito
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$guias = DataStore::getGuiasEnvio();
$inventario = DataStore::getInventario();
$matrices = DataStore::getMatrices();
$sucursales = DataStore::getSucursales();
$talleres = DataStore::getTalleres();
$usuarios = DataStore::getUsuarios();
$tecnicos = array_values(array_filter($usuarios, fn($u) => $u['rol'] === 'tecnico' || $u['rol'] === 'admin'));

$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();

$activeTab = Security::sanitizeString($_GET['tab'] ?? 'activas');
$msg = Security::sanitizeString($_GET['msg'] ?? '');

$guiasEnRuta = array_values(array_filter($guias, fn($g) => $g['estado'] === 'en_transito'));
$guiasEnPrep = array_values(array_filter($guias, fn($g) => $g['estado'] === 'preparacion'));
$guiasEntregadas = array_values(array_filter($guias, fn($g) => $g['estado'] === 'entregado'));
$guiasIncidencia = array_values(array_filter($guias, fn($g) => $g['estado'] === 'incidencia'));

$totalFletes = array_sum(array_column($guias, 'costo_flete'));
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Guías de Envío y Logística de Refacciones</h1>
        <p>Trazabilidad perimetral de despachos desde Almacén Central hacia sucursales, agencias y técnicos en campo</p>
    </div>

    <div class="header-action-buttons">
        <a href="index.php?action=exportar_csv&tipo=envios" class="btn btn-dark" title="Exportar reporte de envíos y costos a Excel / CSV">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Exportar Guías (CSV)</span>
        </a>

        <?php if ($isAdmin || $isTech): ?>
        <button class="btn btn-primary" onclick="openModal('modal-crear-guia')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <span>+ Despachar Nuevo Paquete</span>
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- MENSAJES DE NOTIFICACIÓN -->
<?php if ($msg === 'guia_creada'): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--accent-emerald); color: #34d399; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>🚚</span> Guía de envío registrada exitosamente. Las piezas han sido descontadas del almacén central.
    </div>
<?php elseif ($msg === 'estado_actualizado'): ?>
    <div style="background: rgba(14, 165, 233, 0.15); border: 1px solid var(--accent-cyan); color: #38bdf8; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>📍</span> Estado de la guía actualizado. Evento registrado en la trazabilidad del paquete.
    </div>
<?php elseif ($msg === 'guia_eliminada'): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--accent-rose); color: #f87171; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>🗑️</span> Registro de guía cancelado y asentado en bitácora de auditoría.
    </div>
<?php endif; ?>

<!-- KPI CARDS DE LOGÍSTICA -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="kpi-card kpi-cyan">
        <div class="kpi-number"><?= count($guiasEnRuta) ?></div>
        <div class="kpi-label">Paquetes en Ruta</div>
        <div class="kpi-subtext">En tránsito con transportista</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-number"><?= count($guiasEnPrep) ?></div>
        <div class="kpi-label">En Preparación</div>
        <div class="kpi-subtext">Empaque en Almacén Central</div>
    </div>

    <div class="kpi-card kpi-emerald">
        <div class="kpi-number"><?= count($guiasEntregadas) ?></div>
        <div class="kpi-label">Entregas Concluidas</div>
        <div class="kpi-subtext">Con acuse de recepción</div>
    </div>

    <div class="kpi-card kpi-rose">
        <div class="kpi-number">$<?= number_format($totalFletes, 2) ?></div>
        <div class="kpi-label">Gasto en Fletes (MXN)</div>
        <div class="kpi-subtext">Logística y paqueterías</div>
    </div>
</div>

<!-- BARRA DE PESTAÑAS -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px; overflow-x: auto;">
    <button type="button" class="tab-btn <?= ($activeTab === 'activas' ? 'active' : '') ?>" onclick="showTab('tab-guias-activas', this)">
        🚚 Guías en Proceso (<?= count($guiasEnRuta) + count($guiasEnPrep) + count($guiasIncidencia) ?>)
    </button>
    <button type="button" class="tab-btn <?= ($activeTab === 'entregadas' ? 'active' : '') ?>" onclick="showTab('tab-guias-entregadas', this)">
        ✅ Historial Entregados (<?= count($guiasEntregadas) ?>)
    </button>
</div>

<!-- =========================================================================
     PESTAÑA 1: GUÍAS ACTIVAS Y EN RUTA
     ========================================================================= -->
<div id="tab-guias-activas" class="tab-content-panel" style="display: <?= ($activeTab === 'activas' ? 'block' : 'none') ?>;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Monitoreo de Despachos Activos y Paquetes en Tránsito</span>
            </div>
            <div style="width: 280px;">
                <input type="text" id="table-search-input" class="form-control" placeholder="🔍 Filtrar por folio, transportista, destino..." />
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Folio / Carrier</th>
                        <th>Transportista</th>
                        <th>Destino / Receptor</th>
                        <th>Piezas Empacadas</th>
                        <th>Fecha Despacho</th>
                        <th>Entrega Estimada</th>
                        <th>Estado Envío</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $activas = array_filter($guias, fn($g) => $g['estado'] !== 'entregado');
                    ?>
                    <?php if (empty($activas)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                No hay paquetes pendientes de entrega en este momento. Todos los envíos han sido entregados con éxito.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($activas as $guia): ?>
                        <?php
                            $piezasTotal = 0;
                            foreach ($guia['piezas'] ?? [] as $pz) {
                                $piezasTotal += (int)($pz['cantidad'] ?? 1);
                            }
                        ?>
                        <tr>
                            <td>
                                <span class="code-badge"><?= Security::e($guia['folio_guia']) ?></span>
                                <div style="font-size: 11px; font-family: var(--font-mono); color: var(--accent-cyan-light); margin-top: 2px;">
                                    <?= Security::e($guia['no_guia_carrier']) ?>
                                </div>
                            </td>
                            <td>
                                <strong style="color: #fff; font-size: 13px;"><?= Security::e($guia['transportista']) ?></strong>
                                <?php if (!empty($guia['tracking_url']) && strpos($guia['tracking_url'], 'http') === 0): ?>
                                    <div style="margin-top: 2px;">
                                        <a href="<?= Security::e($guia['tracking_url']) ?>" target="_blank" rel="noopener noreferrer" style="font-size: 11px; color: var(--accent-cyan); display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                            <span>Rastreo Web</span>
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: #fff;"><?= Security::e($guia['destinatario_nombre']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    <?= Security::e($guia['destino_etiqueta']) ?>
                                </div>
                                <div style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">
                                    📍 <?= Security::e($guia['direccion_entrega']) ?>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 12px; font-weight: 700; color: #fff;">
                                    <?= count($guia['piezas'] ?? []) ?> productos (<?= $piezasTotal ?> u.)
                                </span>
                                <div style="font-size: 11px; color: var(--text-secondary);">
                                    <?php 
                                        $descPz = array_map(fn($p) => $p['cantidad'] . 'x ' . $p['codigo_parte'], array_slice($guia['piezas'] ?? [], 0, 2));
                                        echo Security::e(implode(', ', $descPz));
                                        if (count($guia['piezas'] ?? []) > 2) echo '...';
                                    ?>
                                </div>
                            </td>
                            <td style="font-size: 12px; color: var(--text-secondary);">
                                <?= Security::e($guia['fecha_despacho']) ?>
                            </td>
                            <td>
                                <strong style="font-size: 12px; color: var(--accent-amber);">
                                    <?= Security::e($guia['fecha_estimada_entrega']) ?>
                                </strong>
                            </td>
                            <td>
                                <?php if ($guia['estado'] === 'en_transito'): ?>
                                    <span class="badge-status status-observado" style="font-size: 10px; animation: pulse 2s infinite;">🔵 EN RUTA</span>
                                <?php elseif ($guia['estado'] === 'preparacion'): ?>
                                    <span class="badge-status status-pendiente" style="font-size: 10px;">🟡 EN PREPARACIÓN</span>
                                <?php elseif ($guia['estado'] === 'incidencia'): ?>
                                    <span class="badge-status status-fuera" style="font-size: 10px;">🔴 INCIDENCIA</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" class="btn btn-sm btn-dark" title="Ver Remisión de Salida" onclick='openVoucherGuia(<?= json_encode($guia, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        📄 Remisión
                                    </button>
                                    <?php if ($isAdmin || $isTech): ?>
                                    <button type="button" class="btn btn-sm btn-cyan" title="Actualizar estatus de entrega" onclick='openActualizarEstado(<?= json_encode($guia, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        🔄 Estado
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <button type="button" class="btn btn-sm btn-danger" title="Cancelar guía" onclick="confirmarEliminarGuia(<?= $guia['id'] ?>, '<?= Security::e($guia['folio_guia']) ?>')">
                                        🗑️
                                    </button>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     PESTAÑA 2: HISTORIAL DE ENTREGAS CONCLUIDAS
     ========================================================================= -->
<div id="tab-guias-entregadas" class="tab-content-panel" style="display: <?= ($activeTab === 'entregadas' ? 'block' : 'none') ?>;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Bitácora de Despachos Entregados y Acuses de Recepción</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Folio TCS</th>
                        <th>Transportista / Guía</th>
                        <th>Destino Final</th>
                        <th>Piezas Suministradas</th>
                        <th>Fecha Despacho</th>
                        <th>Fecha y Hora Entrega</th>
                        <th>Recibió en Sede</th>
                        <th style="text-align: right;">Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($guiasEntregadas)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                No hay entregas concluidas archivadas aún.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($guiasEntregadas as $guia): ?>
                        <tr>
                            <td>
                                <span class="code-badge"><?= Security::e($guia['folio_guia']) ?></span>
                            </td>
                            <td>
                                <strong style="color: #fff;"><?= Security::e($guia['transportista']) ?></strong>
                                <div style="font-size: 11px; font-family: var(--font-mono); color: var(--text-muted);">
                                    <?= Security::e($guia['no_guia_carrier']) ?>
                                </div>
                            </td>
                            <td>
                                <strong style="color: #fff;"><?= Security::e($guia['destinatario_nombre']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-secondary);">
                                    <?= Security::e($guia['destino_etiqueta']) ?>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: #fff; font-weight: 600;">
                                    <?= count($guia['piezas'] ?? []) ?> refacciones
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-secondary);">
                                <?= Security::e($guia['fecha_despacho']) ?>
                            </td>
                            <td>
                                <strong style="font-size: 12px; color: var(--accent-emerald);">
                                    <?= Security::e($guia['fecha_entrega_real'] ?? 'Concluido') ?>
                                </strong>
                            </td>
                            <td>
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #fff;">
                                    ✍️ <?= Security::e($guia['quien_recibio'] ?? 'Encargado de Bahía') ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-sm btn-dark" onclick='openVoucherGuia(<?= json_encode($guia, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                    📄 Ver Remisión
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 1: DESPACHAR NUEVO PAQUETE (ALTA DE GUÍA)
     ========================================================================= -->
<div id="modal-crear-guia" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 680px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Despachar Envío de Refacciones (Almacén Central)</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_guia_envio">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Transportista / Paquetería *</label>
                        <select name="transportista" class="form-control" required>
                            <option value="DHL Express">DHL Express (Aéreo / Urgente)</option>
                            <option value="FedEx Express">FedEx Express Nacional</option>
                            <option value="Estafeta Terrestre">Estafeta Terrestre</option>
                            <option value="Paquetexpress">Paquetexpress (Carga Pesada)</option>
                            <option value="Logística Directa TCS">Logística Directa TCS Motriz (Móvil)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Guía / Tracking Carrier *</label>
                        <input type="text" name="no_guia_carrier" class="form-control" required placeholder="Ej: 9842109823 / FDX-884920">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo de Destino *</label>
                        <select name="tipo_destino" id="guia-tipo-destino" class="form-control" required onchange="toggleGuiaDestino(this.value)">
                            <option value="sucursal">Sucursal de Cliente (Agencia)</option>
                            <option value="taller">Taller Mecánico Independiente</option>
                            <option value="tecnico">Técnico en Campo TCS Motriz</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Costo de Flete ($ MXN)</label>
                        <input type="number" step="0.01" name="costo_flete" class="form-control" value="0.00">
                    </div>
                </div>

                <!-- SELECTOR DESTINO SUCURSAL -->
                <div class="form-group" id="guia-group-sucursal">
                    <label class="form-label">Sucursal / Agencia Destino *</label>
                    <select name="id_sucursal" id="guia-id-sucursal" class="form-control" onchange="autoFillSucursalDestino(this)">
                        <option value="">-- Selecciona una sucursal --</option>
                        <?php foreach ($sucursales as $s): ?>
                            <?php 
                                $matName = 'Matriz';
                                foreach ($matrices as $m) {
                                    if ($m['id'] == $s['id_matriz']) { $matName = $m['razon_social']; break; }
                                }
                            ?>
                            <option value="<?= $s['id'] ?>" 
                                    data-nombre="<?= Security::e($s['gerente_servicio'] ?? $s['nombre']) ?>" 
                                    data-dir="<?= Security::e($s['direccion']) ?>" 
                                    data-tel="<?= Security::e($s['telefono']) ?>">
                                <?= Security::e($s['nombre']) ?> — (<?= Security::e($matName) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- SELECTOR DESTINO TALLER -->
                <div class="form-group" id="guia-group-taller" style="display: none;">
                    <label class="form-label">Taller Mecánico Destino *</label>
                    <select name="id_taller" id="guia-id-taller" class="form-control" onchange="autoFillTallerDestino(this)">
                        <option value="">-- Selecciona un taller --</option>
                        <?php foreach ($talleres as $t): ?>
                            <option value="<?= $t['id'] ?>" 
                                    data-nombre="<?= Security::e($t['gerente_servicio'] ?? $t['razon_social']) ?>" 
                                    data-dir="<?= Security::e($t['direccion']) ?>" 
                                    data-tel="<?= Security::e($t['telefono']) ?>">
                                <?= Security::e($t['razon_social']) ?> (RFC: <?= Security::e($t['rfc']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- SELECTOR DESTINO TÉCNICO -->
                <div class="form-group" id="guia-group-tecnico" style="display: none;">
                    <label class="form-label">Técnico Especialista en Campo *</label>
                    <select name="id_tecnico" id="guia-id-tecnico" class="form-control" onchange="autoFillTecnicoDestino(this)">
                        <option value="">-- Selecciona un técnico --</option>
                        <?php foreach ($tecnicos as $tc): ?>
                            <option value="<?= $tc['id'] ?>" 
                                    data-nombre="<?= Security::e($tc['nombre']) ?>" 
                                    data-dir="Atención en Campo - Móvil Bahía" 
                                    data-tel="<?= Security::e($tc['telefono']) ?>">
                                <?= Security::e($tc['nombre']) ?> (<?= Security::e($tc['telefono']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre del Contacto / Receptor *</label>
                        <input type="text" name="destinatario_nombre" id="guia-destinatario-nombre" class="form-control" required placeholder="Persona autorizada para recibir">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono de Contacto</label>
                        <input type="text" name="telefono_contacto" id="guia-destinatario-telefono" class="form-control" placeholder="Conmutador o móvil">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Dirección Completa de Entrega *</label>
                    <input type="text" name="direccion_entrega" id="guia-destinatario-direccion" class="form-control" required placeholder="Calle, número, colonia, CP y ciudad">
                </div>

                <!-- SELECTOR DE PIEZAS A EMPACAR -->
                <div style="background: rgba(14, 165, 233, 0.05); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--accent-cyan-light); text-transform: uppercase;">Refacciones Empacadas en el Paquete</span>
                        <button type="button" class="btn btn-sm btn-dark" onclick="agregarFilaPiezaEnvio()">+ Agregar Otra Pieza</button>
                    </div>

                    <div id="contenedor-piezas-envio">
                        <div class="fila-pieza-envio" style="display: flex; gap: 8px; margin-bottom: 8px;">
                            <select name="piezas_id[]" class="form-control" required style="flex: 3;">
                                <option value="">-- Elige refacción del almacén --</option>
                                <?php foreach ($inventario as $invItem): ?>
                                    <option value="<?= $invItem['id'] ?>">
                                        <?= Security::e($invItem['codigo_parte']) ?> — <?= Security::e($invItem['descripcion']) ?> (Stock: <?= (int)$invItem['stock_actual'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="piezas_cant[]" class="form-control" value="1" min="1" style="flex: 1;" placeholder="Cant.">
                        </div>
                    </div>

                    <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="descontar_stock" id="chk-descontar" value="1" checked style="accent-color: var(--accent-cyan);">
                        <label for="chk-descontar" style="font-size: 12px; color: var(--text-secondary); cursor: pointer;">
                            Descontar automáticamente las cantidades del Inventario Central al guardar la guía
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha de Despacho *</label>
                        <input type="date" name="fecha_despacho" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha Estimada de Llegada *</label>
                        <input type="date" name="fecha_estimada_entrega" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Instrucciones Especiales / Notas de Embarque</label>
                    <textarea name="notas" class="form-control" rows="2" placeholder="Ej: Entregar en mostrador de refacciones; preguntar por Jefe de Taller..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar Despacho y Generar Guía</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL 2: ACTUALIZAR ESTADO DE GUÍA
     ========================================================================= -->
<div id="modal-actualizar-estado-guia" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Actualizar Estado de Envío Logístico</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=actualizar_estado_guia">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="status-guia-id" value="">
            <div class="modal-body">
                <div style="margin-bottom: 16px; padding: 12px; background: rgba(255,255,255,0.04); border-radius: var(--radius-sm);">
                    <div style="font-size: 13px; font-weight: 700; color: #fff;" id="status-guia-folio"></div>
                    <div style="font-size: 12px; color: var(--text-secondary);" id="status-guia-carrier-info"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nuevo Estado del Paquete *</label>
                    <select name="estado" id="status-guia-select" class="form-control" required onchange="toggleReceptorField(this.value)">
                        <option value="preparacion">🟡 En Preparación (Almacén Central)</option>
                        <option value="en_transito">🔵 En Tránsito / En Ruta con Transportista</option>
                        <option value="entregado">🟢 Entregado y Recibido en Sucursal / Taller</option>
                        <option value="incidencia">🔴 Incidencia / Demora / Retenido</option>
                    </select>
                </div>

                <div class="form-group" id="group-quien-recibio" style="display: none;">
                    <label class="form-label">Nombre y Puesto de Quien Recibió el Paquete *</label>
                    <input type="text" name="quien_recibio" id="status-guia-recibio" class="form-control" placeholder="Ej: Marcos Sandoval (Jefe de Bahía)">
                </div>

                <div class="form-group">
                    <label class="form-label">Notas del Evento / Comentarios de Seguimiento</label>
                    <textarea name="notas" id="status-guia-notas" class="form-control" rows="3" placeholder="Ej: Paquete arribó a rampa; sellos intactos..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-cyan">Guardar Actualización</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL 3: REMISIÓN DE SALIDA / VOUCHER IMPRIMIBLE
     ========================================================================= -->
<div id="modal-voucher-guia" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Remisión de Salida y Comprobante de Despacho</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body" id="voucher-print-area" style="background: #0b1120; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 20px;">
            <!-- CABECERA DE REMISIÓN -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--accent-cyan); padding-bottom: 12px; margin-bottom: 16px;">
                <div>
                    <div style="font-size: 16px; font-weight: 800; color: #fff;">TCS MOTRIZ S.A. DE C.V.</div>
                    <div style="font-size: 11px; color: var(--text-secondary);">Almacén Central y Despacho Logístico Industrial</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; font-weight: 800; color: var(--accent-cyan);" id="voucher-folio"></div>
                    <div style="font-size: 11px; color: var(--text-muted);" id="voucher-fecha"></div>
                </div>
            </div>

            <!-- DATOS DE TRANSPORTE Y DESTINO -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; font-size: 12px;">
                <div style="background: rgba(255,255,255,0.03); padding: 10px; border-radius: 4px;">
                    <div style="font-weight: 700; color: var(--accent-cyan-light); margin-bottom: 4px;">DATOS DEL TRANSPORTISTA</div>
                    <div><strong>Carrier:</strong> <span id="voucher-carrier"></span></div>
                    <div><strong>Guía Carrier:</strong> <span id="voucher-carrier-no" style="font-family: var(--font-mono); color: #fff;"></span></div>
                    <div><strong>Estado:</strong> <span id="voucher-estado" class="badge-status status-operativo" style="font-size: 9px;"></span></div>
                </div>

                <div style="background: rgba(255,255,255,0.03); padding: 10px; border-radius: 4px;">
                    <div style="font-weight: 700; color: var(--accent-cyan-light); margin-bottom: 4px;">DESTINO Y RECEPTOR</div>
                    <div><strong>Destinatario:</strong> <span id="voucher-destinatario" style="color: #fff;"></span></div>
                    <div><strong>Dirección:</strong> <span id="voucher-direccion"></span></div>
                    <div><strong>Contacto:</strong> <span id="voucher-telefono"></span></div>
                </div>
            </div>

            <!-- TABLA DE REFACCIONES DESPACHADAS -->
            <div style="margin-bottom: 16px;">
                <div style="font-size: 11px; font-weight: 700; color: var(--accent-cyan-light); text-transform: uppercase; margin-bottom: 6px;">Refacciones y Contenido del Paquete:</div>
                <table class="data-table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th style="text-align: center;">Cantidad</th>
                            <th>Unidad</th>
                        </tr>
                    </thead>
                    <tbody id="voucher-piezas-body">
                    </tbody>
                </table>
            </div>

            <!-- NOTAS Y FIRMAS -->
            <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 20px;" id="voucher-notas-box">
                <strong>Instrucciones de Entrega:</strong> <span id="voucher-notas"></span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; text-align: center; border-top: 1px dashed var(--border-subtle); padding-top: 16px; margin-top: 20px;">
                <div>
                    <div style="height: 40px;"></div>
                    <div style="border-top: 1px solid var(--text-muted); font-size: 11px; color: var(--text-secondary); padding-top: 4px;">
                        Despachador Responsable<br><strong style="color: #fff;">Almacén Central TCS Motriz</strong>
                    </div>
                </div>
                <div>
                    <div style="height: 40px; display: flex; align-items: flex-end; justify-content: center; font-size: 12px; color: #34d399;" id="voucher-recibio-firmante"></div>
                    <div style="border-top: 1px solid var(--text-muted); font-size: 11px; color: var(--text-secondary); padding-top: 4px;">
                        Firma y Nombre de Quien Recibe<br><strong style="color: #fff;">Sucursal / Taller Destino</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-dark" data-close-modal>Cerrar</button>
            <button type="button" class="btn btn-primary" onclick="imprimirRemision()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Imprimir Remisión</span>
            </button>
        </div>
    </div>
</div>

<script>
function toggleGuiaDestino(tipo) {
    document.getElementById('guia-group-sucursal').style.display = (tipo === 'sucursal') ? 'block' : 'none';
    document.getElementById('guia-group-taller').style.display = (tipo === 'taller') ? 'block' : 'none';
    document.getElementById('guia-group-tecnico').style.display = (tipo === 'tecnico') ? 'block' : 'none';
}

function autoFillSucursalDestino(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.nombre) {
        document.getElementById('guia-destinatario-nombre').value = opt.dataset.nombre || '';
        document.getElementById('guia-destinatario-direccion').value = opt.dataset.dir || '';
        document.getElementById('guia-destinatario-telefono').value = opt.dataset.tel || '';
    }
}

function autoFillTallerDestino(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.nombre) {
        document.getElementById('guia-destinatario-nombre').value = opt.dataset.nombre || '';
        document.getElementById('guia-destinatario-direccion').value = opt.dataset.dir || '';
        document.getElementById('guia-destinatario-telefono').value = opt.dataset.tel || '';
    }
}

function autoFillTecnicoDestino(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.nombre) {
        document.getElementById('guia-destinatario-nombre').value = opt.dataset.nombre || '';
        document.getElementById('guia-destinatario-direccion').value = opt.dataset.dir || '';
        document.getElementById('guia-destinatario-telefono').value = opt.dataset.tel || '';
    }
}

function agregarFilaPiezaEnvio() {
    const cont = document.getElementById('contenedor-piezas-envio');
    const template = cont.firstElementChild.cloneNode(true);
    template.querySelector('select').value = '';
    template.querySelector('input').value = '1';
    cont.appendChild(template);
}

function toggleReceptorField(estado) {
    const group = document.getElementById('group-quien-recibio');
    if (estado === 'entregado') {
        group.style.display = 'block';
        document.getElementById('status-guia-recibio').setAttribute('required', 'required');
    } else {
        group.style.display = 'none';
        document.getElementById('status-guia-recibio').removeAttribute('required');
    }
}

function openActualizarEstado(guia) {
    document.getElementById('status-guia-id').value = guia.id;
    document.getElementById('status-guia-folio').innerText = `${guia.folio_guia} (${guia.transportista})`;
    document.getElementById('status-guia-carrier-info').innerText = `Guía Carrier: ${guia.no_guia_carrier} | Destino: ${guia.destinatario_nombre}`;
    document.getElementById('status-guia-select').value = guia.estado;
    toggleReceptorField(guia.estado);
    document.getElementById('status-guia-recibio').value = guia.quien_recibio || '';
    document.getElementById('status-guia-notas').value = '';
    openModal('modal-actualizar-estado-guia');
}

function openVoucherGuia(guia) {
    document.getElementById('voucher-folio').innerText = guia.folio_guia;
    document.getElementById('voucher-fecha').innerText = `Despacho: ${guia.fecha_despacho}`;
    document.getElementById('voucher-carrier').innerText = guia.transportista;
    document.getElementById('voucher-carrier-no').innerText = guia.no_guia_carrier;
    document.getElementById('voucher-estado').innerText = guia.estado.toUpperCase();
    document.getElementById('voucher-destinatario').innerText = `${guia.destinatario_nombre} (${guia.destino_etiqueta})`;
    document.getElementById('voucher-direccion').innerText = guia.direccion_entrega;
    document.getElementById('voucher-telefono').innerText = guia.telefono_contacto || 'S/N';
    document.getElementById('voucher-notas').innerText = guia.notas || 'Sin observaciones de empaque.';

    const tbody = document.getElementById('voucher-piezas-body');
    tbody.innerHTML = '';
    (guia.piezas || []).forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="code-badge">${p.codigo_parte || 'N/A'}</span></td>
            <td><strong>${p.descripcion || ''}</strong></td>
            <td style="text-align: center; font-weight: bold; font-family: monospace;">${p.cantidad}</td>
            <td>${p.unidad_medida || 'Pza'}</td>
        `;
        tbody.appendChild(tr);
    });

    const recibioBox = document.getElementById('voucher-recibio-firmante');
    if (guia.quien_recibio) {
        recibioBox.innerText = `✍️ Recibido por: ${guia.quien_recibio}`;
    } else {
        recibioBox.innerText = '';
    }

    openModal('modal-voucher-guia');
}

function imprimirRemision() {
    window.print();
}

function confirmarEliminarGuia(id, folio) {
    if (confirm(`¿Está seguro de eliminar y cancelar la guía de envío "${folio}"? Esta acción no se puede deshacer.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=eliminar_guia_envio';

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
