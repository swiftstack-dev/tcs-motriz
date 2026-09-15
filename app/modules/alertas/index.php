<?php
/**
 * TCS MOTRIZ - Centro de Alertas Técnicas, Monitoreo de Fallas y Mantenimientos Preventivos
 * Notificaciones en Tiempo Real y Mitigación de Paros Catastróficos en Bahía
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$currentUser = Auth::user();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();
$isClient = Auth::isClient();

// Filtro seguro contra IDOR para clientes
$filterSucursal = $isClient ? ($currentUser['id_sucursal'] ?? null) : null;
$filterTaller = $isClient ? ($currentUser['id_taller'] ?? null) : null;

$alertas = DataStore::getAlertas($filterSucursal, $filterTaller);

// Conteo por severidad
$criticas = array_filter($alertas, fn($a) => $a['severidad'] === 'critica');
$altas = array_filter($alertas, fn($a) => $a['severidad'] === 'alta');
$medias = array_filter($alertas, fn($a) => $a['severidad'] === 'media');
$mantenimientos = array_filter($alertas, fn($a) => in_array($a['tipo'], ['mantenimiento_vencido', 'mantenimiento_proximo']));
$fallas = array_filter($alertas, fn($a) => in_array($a['tipo'], ['falla_critica', 'desviacion_tolerancia']));
?>

<div class="view-header">
    <div class="view-title-group">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 26px; animation: pulse-ring 2s infinite;">🔔</span>
            <div>
                <h1>Centro de Alertas Técnicas y Monitoreo Preventivo</h1>
                <p>Detección anticipada de fallas, elevadores fuera de servicio y vencimientos de mantenimiento NOM/OSHA</p>
            </div>
        </div>
    </div>

    <div class="header-action-buttons">
        <a href="index.php?action=exportar_csv&tipo=alertas" class="btn btn-dark">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Exportar Alertas CSV</span>
        </a>
        <?php if ($isAdmin || $isTech): ?>
            <a href="index.php?view=reportes&accion=nuevo" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Despachar Peritaje / Reporte</span>
            </a>
        <?php else: ?>
            <button class="btn btn-primary" onclick="openModal('modal-solicitar-servicio')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Reportar Falla Urgente</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- KPIS EJECUTIVOS DE ALERTA -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="kpi-card kpi-rose">
        <div class="kpi-number"><?= count($criticas) ?></div>
        <div class="kpi-label">Alertas Críticas / Bloqueos</div>
        <div class="kpi-subtext">Requieren atención inmediata</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-number"><?= count($mantenimientos) ?></div>
        <div class="kpi-label">Mantenimientos Próximos / Vencidos</div>
        <div class="kpi-subtext">Ciclos normativos a renovar</div>
    </div>

    <div class="kpi-card kpi-cyan">
        <div class="kpi-number"><?= count($altas) ?></div>
        <div class="kpi-label">Desviaciones y Órdenes</div>
        <div class="kpi-subtext">Tolerancias o servicios en curso</div>
    </div>

    <div class="kpi-card kpi-emerald">
        <div class="kpi-number"><?= count($alertas) ?></div>
        <div class="kpi-label">Total Notificaciones Activas</div>
        <div class="kpi-subtext">Supervisadas por telemetría</div>
    </div>
</div>

<!-- FILTROS DE ALERTA -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;">
    <span style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">Filtrar por:</span>
    <button class="btn btn-sm btn-dark btn-filter-alert active" onclick="filtrarAlertas('todas', this)">Todas (<?= count($alertas) ?>)</button>
    <button class="btn btn-sm btn-dark btn-filter-alert" onclick="filtrarAlertas('critica', this)">🔴 Críticas (<?= count($criticas) ?>)</button>
    <button class="btn btn-sm btn-dark btn-filter-alert" onclick="filtrarAlertas('mantenimiento', this)">🟡 Mantenimientos (<?= count($mantenimientos) ?>)</button>
    <button class="btn btn-sm btn-dark btn-filter-alert" onclick="filtrarAlertas('falla', this)">⚙️ Desviaciones (<?= count($fallas) ?>)</button>
</div>

<!-- LISTADO DE ALERTAS ACTIVAS -->
<div class="alerts-container" style="display: flex; flex-direction: column; gap: 14px;">
    <?php if (empty($alertas)): ?>
        <div class="panel-card" style="text-align: center; padding: 40px;">
            <span style="font-size: 40px;">✅</span>
            <h3 style="color: #fff; margin-top: 10px;">Sin Alertas Pendientes</h3>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">Todos los elevadores y maquinaria operan dentro de los estándares de seguridad y con mantenimientos al corriente.</p>
        </div>
    <?php else: ?>
        <?php foreach ($alertas as $alt): ?>
            <?php
            $isCrit = ($alt['severidad'] === 'critica');
            $isAlta = ($alt['severidad'] === 'alta');
            $borderCol = $isCrit ? '#e11d48' : ($isAlta ? '#f59e0b' : '#0284c7');
            $bgCol = $isCrit ? 'rgba(225, 29, 72, 0.07)' : ($isAlta ? 'rgba(245, 158, 11, 0.06)' : 'rgba(2, 132, 199, 0.05)');
            $categoryClass = in_array($alt['tipo'], ['mantenimiento_vencido', 'mantenimiento_proximo']) ? 'tipo-mantenimiento' : (in_array($alt['tipo'], ['falla_critica', 'desviacion_tolerancia']) ? 'tipo-falla' : 'tipo-otro');
            ?>
            <div class="alert-item-card <?= $categoryClass ?>" data-severidad="<?= $alt['severidad'] ?>" style="background: <?= $bgCol ?>; border: 1px solid <?= $borderCol ?>; border-radius: var(--radius-md); padding: 18px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; transition: transform var(--transition-fast);">
                <div style="display: flex; gap: 16px; align-items: flex-start; max-width: 750px;">
                    <div style="font-size: 24px; padding-top: 2px;">
                        <?= $isCrit ? '🚨' : ($isAlta ? '⚠️' : 'ℹ️') ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                            <span class="badge-status <?= $isCrit ? 'status-fuera_servicio' : ($isAlta ? 'status-observado' : 'status-operativo') ?>" style="font-size: 10px;">
                                <?= $alt['badge'] ?>
                            </span>
                            <?php if (!empty($alt['codigo_equipo'])): ?>
                                <span class="code-badge"><?= Security::e($alt['codigo_equipo']) ?></span>
                            <?php endif; ?>
                            <span style="font-size: 11px; color: var(--text-muted);">📍 <?= Security::e($alt['ubicacion']) ?></span>
                            <span style="font-size: 11px; color: var(--text-secondary);">• Fecha: <?= Security::e($alt['fecha_deteccion']) ?></span>
                        </div>

                        <h3 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 4px;">
                            <?= Security::e($alt['titulo']) ?>
                        </h3>
                        <p style="font-size: 13px; color: #cbd5e1; line-height: 1.5; margin-bottom: 8px;">
                            <?= Security::e($alt['mensaje']) ?>
                        </p>
                        <div style="font-size: 11px; color: var(--accent-cyan-light);">
                            <strong>Recomendación Técnica:</strong> <?= Security::e($alt['accion_sugerida']) ?>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="<?= $alt['accion_url'] ?>" class="btn btn-sm <?= $isCrit ? 'btn-primary' : ($isAlta ? 'btn-cyan' : 'btn-dark') ?>">
                        <span><?= $alt['accion_texto'] ?></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function filtrarAlertas(filtro, btn) {
    document.querySelectorAll('.btn-filter-alert').forEach(b => b.classList.remove('active', 'btn-primary'));
    btn.classList.add('active');
    
    document.querySelectorAll('.alert-item-card').forEach(card => {
        if (filtro === 'todas') {
            card.style.display = 'flex';
        } else if (filtro === 'critica') {
            card.style.display = (card.getAttribute('data-severidad') === 'critica') ? 'flex' : 'none';
        } else if (filtro === 'mantenimiento') {
            card.style.display = card.classList.contains('tipo-mantenimiento') ? 'flex' : 'none';
        } else if (filtro === 'falla') {
            card.style.display = card.classList.contains('tipo-falla') ? 'flex' : 'none';
        }
    });
}
</script>
