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
                                <?php if ($isAdmin || $isTech): ?>
                                    <?php if ($ord['estado'] !== 'concluido'): ?>
                                        <div style="display: flex; gap: 6px;">
                                            <a href="index.php?view=reportes&accion=nuevo&orden_id=<?= $ord['id'] ?>" class="btn btn-sm btn-success" title="Atender y capturar reporte">
                                                Atender / Reporte
                                            </a>
                                            <form method="POST" action="index.php?action=actualizar_orden_estado" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="orden_id" value="<?= $ord['id'] ?>">
                                                <input type="hidden" name="nuevo_estado" value="concluido">
                                                <button type="submit" class="btn btn-sm btn-dark" title="Marcar como Concluido">✔</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: var(--accent-emerald);">✔ Concluido</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-dark" onclick="alert('Descripción: <?= addslashes($ord['descripcion_falla']) ?>')">
                                        Detalle
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
