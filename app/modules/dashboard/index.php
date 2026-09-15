<?php
/**
 * TCS MOTRIZ - Dashboard Principal de Telemetría y Operaciones
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$stats = DataStore::getStats();
$currentUser = Auth::user();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();
$isClient = Auth::isClient();

// Filtro de órdenes según rol
$filterSucursal = $isClient ? ($currentUser['id_sucursal'] ?? null) : null;
$filterTaller = $isClient ? ($currentUser['id_taller'] ?? null) : null;
$ordenes = DataStore::getOrdenes($filterSucursal, $filterTaller);
$equipos = DataStore::getEquipos($filterSucursal, $filterTaller);
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Panel de Control General</h1>
        <p>Monitoreo operativo de equipamiento automotriz, rampas y servicio a talleres</p>
    </div>

    <div class="header-action-buttons">
        <?php if ($isAdmin || $isTech): ?>
            <button class="btn btn-primary" onclick="openModal('modal-crear-matriz')">
                <span>+ Crear Matriz</span>
            </button>
            <button class="btn btn-cyan" onclick="openModal('modal-vincular-sucursal')">
                <span>Vincular Sucursal</span>
            </button>
            <button class="btn btn-dark" onclick="openModal('modal-crear-taller')">
                <span>+ Taller</span>
            </button>
            <button class="btn btn-success" onclick="openModal('modal-crear-equipo')">
                <span>+ Equipo</span>
            </button>
        <?php else: ?>
            <button class="btn btn-primary" onclick="openModal('modal-solicitar-servicio')">
                <span>+ Solicitar Servicio a Equipo</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- DIAGRAMA DE FLUJO JERÁRQUICO -->
<div class="flow-diagram-banner">
    <div class="flow-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="M14 9h7"/><path d="M14 15h7"/></svg>
        <span>Estructura del Sistema (Diagramas de Flujo)</span>
    </div>
    <div class="flow-pills">
        <span class="flow-pill active">Matriz</span>
        <span class="flow-arrow">→</span>
        <span class="flow-pill active">Sucursal</span>
        <span class="flow-arrow">/</span>
        <span class="flow-pill active">Taller</span>
        <span class="flow-arrow">→</span>
        <span class="flow-pill active">Rampas / Equipos</span>
    </div>
</div>

<!-- KPI CARDS -->
<div class="kpi-grid">
    <div class="kpi-card kpi-cyan">
        <div class="kpi-number"><?= $stats['ubicaciones_activas'] ?></div>
        <div class="kpi-label">Ubicaciones Activas</div>
        <div class="kpi-subtext">(Matrices / Talleres)</div>
    </div>

    <div class="kpi-card kpi-emerald">
        <div class="kpi-number"><?= count($equipos) ?></div>
        <div class="kpi-label">Rampas & Equipos</div>
        <div class="kpi-subtext">Registrados en Operación</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-number"><?= $stats['servicios_pendientes'] ?></div>
        <div class="kpi-label">Servicios Solicitados</div>
        <div class="kpi-subtext">Pendientes / En Proceso</div>
    </div>

    <div class="kpi-card kpi-emerald">
        <div class="kpi-number"><?= $stats['mantenimientos_mes'] ?></div>
        <div class="kpi-label">Mantenimientos Concluidos</div>
        <div class="kpi-subtext">Certificados este Mes</div>
    </div>
</div>

<!-- DUAL GRID: SOLICITUDES + ESTADO OPERATIVO -->
<div class="dashboard-dual-grid">
    <!-- PANEL IZQUIERDO: SOLICITUDES RECIENTES -->
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>Solicitudes de Servicio Recientes</span>
            </div>
            <a href="index.php?view=ordenes" class="btn btn-sm btn-dark">Ver Todas</a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Equipo / Rampa</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ordenes)): ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No hay solicitudes registradas para esta sede.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($ordenes, 0, 5) as $ord): ?>
                            <tr>
                                <td><span class="code-badge"><?= Security::e($ord['folio']) ?></span></td>
                                <td>
                                    <strong><?= Security::e($ord['equipo_nombre']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= Security::e($ord['equipo_codigo']) ?></div>
                                </td>
                                <td><?= Security::e($ord['ubicacion']) ?></td>
                                <td>
                                    <?php if ($ord['estado'] === 'en_proceso'): ?>
                                        <span class="badge-status status-en_proceso">En Proceso</span>
                                    <?php elseif ($ord['estado'] === 'concluido'): ?>
                                        <span class="badge-status status-concluido">Concluido</span>
                                    <?php else: ?>
                                        <span class="badge-status status-observado">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isAdmin || $isTech): ?>
                                        <?php if ($ord['estado'] !== 'concluido'): ?>
                                            <a href="index.php?view=reportes&accion=nuevo&orden_id=<?= $ord['id'] ?>" class="btn btn-sm btn-success">Atender / Reporte</a>
                                        <?php else: ?>
                                            <span style="font-size: 11px; color: var(--accent-emerald);">✔ Registrada</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="index.php?view=ordenes" class="btn btn-sm btn-dark">Consultar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PANEL DERECHO: ESTADO OPERATIVO DE RAMPAS -->
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                <span>Estado Operativo de Rampas</span>
            </div>
            <span class="badge-status status-operativo"><?= $stats['disponibilidad_pct'] ?>% DISPONIBILIDAD</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 10px;">
            <!-- Barras de Salud -->
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                    <span style="color: var(--accent-emerald); font-weight: 600;">● Operativo 100%</span>
                    <span><?= $stats['operativos'] ?> de <?= $stats['equipos_registrados'] ?></span>
                </div>
                <div style="height: 8px; background: #12213d; border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; width: <?= ($stats['equipos_registrados'] > 0 ? ($stats['operativos']/$stats['equipos_registrados'])*100 : 0) ?>%; background: var(--accent-emerald);"></div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                    <span style="color: var(--accent-amber); font-weight: 600;">● Requiere Mantenimiento / Atención</span>
                    <span><?= $stats['observados'] ?> de <?= $stats['equipos_registrados'] ?></span>
                </div>
                <div style="height: 8px; background: #12213d; border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; width: <?= ($stats['equipos_registrados'] > 0 ? ($stats['observados']/$stats['equipos_registrados'])*100 : 0) ?>%; background: var(--accent-amber);"></div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                    <span style="color: var(--accent-rose); font-weight: 600;">● Fuera de Servicio (Crítico)</span>
                    <span><?= $stats['fuera_servicio'] ?> de <?= $stats['equipos_registrados'] ?></span>
                </div>
                <div style="height: 8px; background: #12213d; border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; width: <?= ($stats['equipos_registrados'] > 0 ? ($stats['fuera_servicio']/$stats['equipos_registrados'])*100 : 0) ?>%; background: var(--accent-rose);"></div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border-subtle); display: flex; gap: 8px;">
                <a href="index.php?view=equipos" class="btn btn-sm btn-dark" style="flex: 1; justify-content: center;">Ver Catálogo</a>
                <a href="index.php?view=reportes" class="btn btn-sm btn-cyan" style="flex: 1; justify-content: center;">Histórico Informes</a>
            </div>
        </div>
    </div>
</div>
