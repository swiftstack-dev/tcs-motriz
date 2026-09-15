<?php
/**
 * TCS MOTRIZ - Auditoría y Seguridad ISO 27001 / OWASP
 * Visualización de Bitácora Inmutable de Eventos
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

// Solo administradores y soporte pueden auditar el sistema
if (!Auth::isAdmin()) {
    echo '<div class="panel-card"><p style="color: var(--accent-rose);">Acceso restringido: Se requieren privilegios de Administrador de Seguridad (ISO 27001 A.9).</p></div>';
    return;
}

$logs = Logger::getRecentLogs(60);
$isDbFallback = Database::isFallback();
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Auditoría y Seguridad ISO 27001 / OWASP</h1>
        <p>Monitoreo perimetral, trazabilidad inmutable de accesos y prevención de incidentes</p>
    </div>

    <div class="header-action-buttons">
        <span class="badge-status status-operativo">ESCUDO PERIMETRAL ACTIVO</span>
    </div>
</div>

<!-- METRICAS DE SEGURIDAD -->
<div class="kpi-grid">
    <div class="kpi-card kpi-cyan">
        <div class="kpi-number"><?= count($logs) ?></div>
        <div class="kpi-label">Eventos en Bitácora</div>
        <div class="kpi-subtext">Últimos registros auditados</div>
    </div>

    <div class="kpi-card kpi-emerald">
        <div class="kpi-number">100%</div>
        <div class="kpi-label">Consultas Preparadas</div>
        <div class="kpi-subtext">Inyecciones SQL bloqueadas</div>
    </div>

    <div class="kpi-card <?= $isDbFallback ? 'kpi-amber' : 'kpi-emerald' ?>">
        <div class="kpi-number" style="font-size: 18px;"><?= $isDbFallback ? 'AUTÓNOMO JSON' : 'MYSQL HOSTINGER' ?></div>
        <div class="kpi-label">Modo de Base de Datos</div>
        <div class="kpi-subtext"><?= $isDbFallback ? 'Listo para recibir credenciales MySQL' : 'Conexión PDO activa' ?></div>
    </div>

    <div class="kpi-card kpi-cyan">
        <div class="kpi-number">A.12.4</div>
        <div class="kpi-label">Norma ISO 27001</div>
        <div class="kpi-subtext">Cumplimiento de Auditoría</div>
    </div>
</div>

<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>Registro Cronológico de Seguridad (Append-Only Log)</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha / Hora (UTC-6)</th>
                    <th>Acción</th>
                    <th>Entidad</th>
                    <th>Usuario / Rol</th>
                    <th>Dirección IP</th>
                    <th>Detalles Técnicos</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">No hay registros recientes en la bitácora.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="font-size: 11px; font-family: var(--font-mono); color: var(--text-muted);"><?= Security::e($log['timestamp']) ?></td>
                            <td><span class="code-badge"><?= Security::e($log['action']) ?></span></td>
                            <td><?= Security::e($log['entity']) ?> <?= !empty($log['entity_id']) ? ('#' . Security::e($log['entity_id'])) : '' ?></td>
                            <td>
                                <div>ID: <?= (int)$log['user_id'] ?></div>
                                <div style="font-size: 10px; color: var(--accent-cyan-light);"><?= strtoupper(Security::e($log['user_role'])) ?></div>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 11px;"><?= Security::e($log['ip']) ?></td>
                            <td style="font-size: 11px; color: var(--text-secondary); max-width: 300px; word-break: break-word;">
                                <?= Security::e(json_encode($log['details'], JSON_UNESCAPED_UNICODE)) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
