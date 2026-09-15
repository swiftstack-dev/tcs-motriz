<?php
/**
 * TCS MOTRIZ - Módulo de Solicitudes y Órdenes de Servicio
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$currentUser = Auth::user();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();
$isClient = Auth::isClient();

$filterSucursal = $isClient ? ($currentUser['id_sucursal'] ?? null) : null;
$filterTaller = $isClient ? ($currentUser['id_taller'] ?? null) : null;
$ordenes = DataStore::getOrdenes($filterSucursal, $filterTaller);
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Solicitudes y Órdenes de Servicio</h1>
        <p>Control integral de incidencias, reparaciones correctivas y mantenimientos programados</p>
    </div>

    <div class="header-action-buttons">
        <a href="index.php?action=exportar_csv&tipo=ordenes" class="btn btn-dark">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Exportar CSV / Excel</span>
        </a>
        <button class="btn btn-primary" onclick="openModal('modal-solicitar-servicio')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Nueva Solicitud de Servicio</span>
        </button>
    </div>
</div>

<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span>Bitácora de Órdenes de Servicio</span>
        </div>
        <div style="width: 250px;">
            <input type="text" id="table-search-input" class="form-control" placeholder="🔍 Filtrar órdenes..." />
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Ubicación / Sucursal</th>
                    <th>Equipo</th>
                    <th>Tipo / Prioridad</th>
                    <th>Solicitado por</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ordenes)): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">No hay órdenes registradas para esta ubicación.</td></tr>
                <?php else: ?>
                    <?php foreach ($ordenes as $ord): ?>
                        <tr>
                            <td><span class="code-badge"><?= Security::e($ord['folio']) ?></span></td>
                            <td style="font-size: 11px; color: var(--text-secondary);"><?= substr($ord['fecha_solicitud'], 0, 10) ?></td>
                            <td><?= Security::e($ord['ubicacion']) ?></td>
                            <td>
                                <strong><?= Security::e($ord['equipo_nombre']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= Security::e($ord['equipo_codigo']) ?></div>
                            </td>
                            <td>
                                <div style="text-transform: capitalize; font-weight: 600; color: #fff;">
                                    <?= Security::e($ord['tipo_servicio']) ?>
                                </div>
                                <div style="font-size: 11px; color: <?= $ord['prioridad'] === 'alta' || $ord['prioridad'] === 'critica' ? 'var(--accent-rose)' : 'var(--accent-cyan-light)' ?>;">
                                    Prioridad: <?= strtoupper(Security::e($ord['prioridad'])) ?>
                                </div>
                            </td>
                            <td>
                                <div><?= Security::e($ord['solicitante_nombre']) ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);">Asignado: <?= Security::e($ord['tecnico_nombre']) ?></div>
                            </td>
                            <td>
                                <?php if ($ord['estado'] === 'en_proceso'): ?>
                                    <span class="badge-status status-en_proceso">EN PROCESO</span>
                                <?php elseif ($ord['estado'] === 'concluido'): ?>
                                    <span class="badge-status status-concluido">CONCLUIDO</span>
                                <?php else: ?>
                                    <span class="badge-status status-observado">PENDIENTE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <button class="btn btn-sm btn-cyan" onclick='verFichaOrden(<?= htmlspecialchars(json_encode($ord), ENT_QUOTES, "UTF-8") ?>)' title="Ver / Imprimir Ficha Oficial de Orden">
                                        Ficha
                                    </button>
                                    <?php if ($isAdmin || $isTech): ?>
                                        <?php if ($ord['estado'] !== 'concluido'): ?>
                                            <a href="index.php?view=reportes&accion=nuevo&orden_id=<?= $ord['id'] ?>" class="btn btn-sm btn-success" title="Atender y capturar reporte">
                                                Atender
                                            </a>
                                            <form method="POST" action="index.php?action=actualizar_orden_estado" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="orden_id" value="<?= $ord['id'] ?>">
                                                <input type="hidden" name="nuevo_estado" value="concluido">
                                                <button type="submit" class="btn btn-sm btn-dark" title="Marcar como Concluido">✔</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size: 11px; color: var(--accent-emerald);">✔ Concluido</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL IMPRESIÓN DE FICHA DE ORDEN DE TRABAJO CON LOGO -->
<div id="modal-ver-orden" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Ficha Oficial de Orden de Servicio</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body">
            <div id="orden-print-sheet" style="background: #ffffff; color: #0f172a; padding: 24px; border-radius: 6px; font-family: Arial, sans-serif; position: relative; overflow: hidden; border: 1px solid #cbd5e1;">
                <!-- Watermark -->
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.04; pointer-events: none;">
                    <img src="assets/img/logo-tcs.png" alt="Watermark" style="width: 320px; filter: grayscale(100%);">
                </div>

                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #b91c1c; padding-bottom: 12px; margin-bottom: 16px; position: relative; z-index: 1;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="assets/img/logo-tcs.png" alt="TCS Motriz" style="height: 46px; object-fit: contain;">
                        <div>
                            <div style="font-weight: 900; font-size: 14px; color: #b91c1c;">TCS MOTRIZ — INGENIERÍA Y MANTENIMIENTO</div>
                            <div style="font-size: 10px; color: #475569;">Orden Oficial de Trabajo en Bahía • Red Nacional</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div id="ord-view-folio" style="font-family: monospace; font-weight: 900; font-size: 15px; color: #0f172a;"></div>
                        <div id="ord-view-fecha" style="font-size: 11px; color: #64748b;"></div>
                    </div>
                </div>

                <!-- Detalle -->
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 14px; position: relative; z-index: 1;">
                    <tr>
                        <td style="padding: 6px; border: 1px solid #cbd5e1; width: 25%; font-weight: bold; background: #f8fafc;">Ubicación:</td>
                        <td id="ord-view-ubicacion" style="padding: 6px; border: 1px solid #cbd5e1; width: 25%;"></td>
                        <td style="padding: 6px; border: 1px solid #cbd5e1; width: 25%; font-weight: bold; background: #f8fafc;">Equipo / Rampa:</td>
                        <td id="ord-view-equipo" style="padding: 6px; border: 1px solid #cbd5e1; width: 25%;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px; border: 1px solid #cbd5e1; font-weight: bold; background: #f8fafc;">Tipo de Servicio:</td>
                        <td id="ord-view-tipo" style="padding: 6px; border: 1px solid #cbd5e1;"></td>
                        <td style="padding: 6px; border: 1px solid #cbd5e1; font-weight: bold; background: #f8fafc;">Prioridad:</td>
                        <td id="ord-view-prioridad" style="padding: 6px; border: 1px solid #cbd5e1;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px; border: 1px solid #cbd5e1; font-weight: bold; background: #f8fafc;">Solicitado por:</td>
                        <td id="ord-view-solicitante" style="padding: 6px; border: 1px solid #cbd5e1;"></td>
                        <td style="padding: 6px; border: 1px solid #cbd5e1; font-weight: bold; background: #f8fafc;">Técnico Asignado:</td>
                        <td id="ord-view-tecnico" style="padding: 6px; border: 1px solid #cbd5e1;"></td>
                    </tr>
                </table>

                <div style="font-size: 11px; font-weight: bold; color: #1e293b; margin-bottom: 4px; position: relative; z-index: 1;">Descripción de la Falla o Trabajo Requerido:</div>
                <div id="ord-view-falla" style="border: 1px solid #cbd5e1; background: #f8fafc; padding: 10px; font-size: 12px; min-height: 48px; margin-bottom: 16px; position: relative; z-index: 1;"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 24px; text-align: center; font-size: 11px; position: relative; z-index: 1;">
                    <div style="border-top: 1px solid #94a3b8; padding-top: 6px;">
                        <strong id="ord-view-firma-solicita"></strong><br>
                        <span style="color: #64748b;">Firma Solicitante / Cliente</span>
                    </div>
                    <div style="border-top: 1px solid #94a3b8; padding-top: 6px;">
                        <strong id="ord-view-firma-tec"></strong><br>
                        <span style="color: #64748b;">Técnico Especialista TCS Motriz</span>
                    </div>
                </div>

                <div style="margin-top: 18px; border-top: 1px solid #e2e8f0; padding-top: 6px; font-size: 9px; color: #94a3b8; text-align: center;">
                    Documento de Control y Asignación Operativa — TCS Motriz © 2026 • servicio-tcsmotriz.com.mx
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary btn-print-report">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir Orden Oficial
            </button>
            <button class="btn btn-dark" data-close-modal>Cerrar</button>
        </div>
    </div>
</div>

<script>
function verFichaOrden(ord) {
    document.getElementById('ord-view-folio').innerText = ord.folio || ord.folio_orden || 'ORD-TCS';
    document.getElementById('ord-view-fecha').innerText = 'Fecha: ' + (ord.fecha_solicitud || '').substring(0, 10);
    document.getElementById('ord-view-ubicacion').innerText = ord.ubicacion || 'Bahía de Taller';
    document.getElementById('ord-view-equipo').innerText = (ord.equipo_nombre || '') + (ord.equipo_codigo ? ' (' + ord.equipo_codigo + ')' : '');
    document.getElementById('ord-view-tipo').innerText = (ord.tipo_servicio || 'Servicio').toUpperCase();
    document.getElementById('ord-view-prioridad').innerText = (ord.prioridad || 'Normal').toUpperCase();
    document.getElementById('ord-view-solicitante').innerText = ord.solicitante_nombre || 'Cliente';
    document.getElementById('ord-view-tecnico').innerText = ord.tecnico_nombre || 'Especialista Asignado';
    document.getElementById('ord-view-falla').innerText = ord.descripcion_falla || 'Sin observaciones adicionales reportadas.';
    document.getElementById('ord-view-firma-solicita').innerText = ord.solicitante_nombre || 'Firma de Conformidad';
    document.getElementById('ord-view-firma-tec').innerText = ord.tecnico_nombre || 'Téc. Héctor Morales';
    openModal('modal-ver-orden');
}
</script>
