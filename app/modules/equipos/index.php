<?php
/**
 * TCS MOTRIZ - Catálogo de Equipos, Rampas y Estado de Salud
 * Control de Elevadores Automotrices y Prevención IDOR
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$currentUser = Auth::user();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();
$isClient = Auth::isClient();

// Filtro seguro contra IDOR para clientes
$filterSucursal = null;
$filterTaller = null;

if ($isClient) {
    $filterSucursal = $currentUser['id_sucursal'] ?? null;
    $filterTaller = $currentUser['id_taller'] ?? null;
} elseif (isset($_GET['sucursal_id'])) {
    $filterSucursal = Security::sanitizeInt($_GET['sucursal_id']);
} elseif (isset($_GET['taller_id'])) {
    $filterTaller = Security::sanitizeInt($_GET['taller_id']);
}

$equipos = DataStore::getEquipos($filterSucursal, $filterTaller);
$detalleId = isset($_GET['detalle_id']) ? Security::sanitizeInt($_GET['detalle_id']) : null;
$equipoDetalle = $detalleId ? DataStore::getEquipoById($detalleId) : null;
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Catálogo de Equipos y Rampas Elevadoras</h1>
        <p>Telemetría de salud operativa, capacidad de carga y fichas técnicas</p>
    </div>

    <div class="header-action-buttons">
        <a href="index.php?action=exportar_csv&tipo=equipos" class="btn btn-dark">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Exportar CSV / Excel</span>
        </a>
        <?php if ($isAdmin || $isTech): ?>
            <button class="btn btn-primary" onclick="openModal('modal-crear-equipo')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Registrar Nuevo Equipo</span>
            </button>
        <?php else: ?>
            <button class="btn btn-primary" onclick="openModal('modal-solicitar-servicio')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Solicitar Servicio a mi Equipo</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- BARRA DE BÚSQUEDA Y FILTRADO -->
<div style="display: flex; gap: 14px; margin-bottom: 24px; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 260px;">
        <input type="text" id="search-equipo-input" class="form-control" placeholder="🔍 Buscar por nombre, marca, modelo o serie..." />
    </div>
    <div style="width: 220px;">
        <select id="filter-salud-select" class="form-control">
            <option value="">Todos los Estados de Salud</option>
            <option value="operativo">Solo Operativos (100%)</option>
            <option value="observado">Requiere Atención</option>
            <option value="fuera_servicio">Fuera de Servicio (Crítico)</option>
        </select>
    </div>
</div>

<!-- MODAL DE EXPEDIENTE / DETALLE TÉCNICO COMPLETO (SI ESTÁ SELECCIONADO) -->
<?php if ($equipoDetalle): ?>
<div class="panel-card" style="margin-bottom: 30px; border-color: var(--accent-blue); background: #0c1833;">
    <div class="panel-header">
        <div class="panel-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span>Expediente Técnico Completo: <?= Security::e($equipoDetalle['nombre']) ?> (<?= Security::e($equipoDetalle['codigo_tcs']) ?>)</span>
        </div>
        <a href="index.php?view=equipos" class="btn btn-sm btn-dark">Cerrar Expediente</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; font-size: 13px;">
        <div><strong>Marca / Fabricante:</strong> <?= Security::e($equipoDetalle['marca']) ?></div>
        <div><strong>Modelo:</strong> <?= Security::e($equipoDetalle['modelo']) ?></div>
        <div><strong>Número de Serie:</strong> <span class="code-badge"><?= Security::e($equipoDetalle['numero_serie']) ?></span></div>
        <div><strong>Capacidad Nominal:</strong> <?= Security::e($equipoDetalle['capacidad'] ?? 'N/A') ?></div>
        <div><strong>Ubicación en Taller:</strong> <?= Security::e($equipoDetalle['ubicacion_bahia'] ?? 'N/A') ?></div>
        <div><strong>Horas de Operación:</strong> <?= (int)$equipoDetalle['horas_uso'] ?> hrs</div>
        <div><strong>Último Mantenimiento:</strong> <?= Security::e($equipoDetalle['ultimo_mantenimiento'] ?? 'N/A') ?></div>
        <div><strong>Próximo Mantenimiento:</strong> <?= Security::e($equipoDetalle['proximo_mantenimiento'] ?? 'N/A') ?></div>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; border-top: 1px solid #1c2e56; padding-top: 14px;">
        <button class="btn btn-sm btn-cyan" onclick="showQRModal('<?= Security::e($equipoDetalle['codigo_tcs']) ?>', '<?= Security::e($equipoDetalle['nombre']) ?>', '<?= Security::e($equipoDetalle['marca']) ?>', '<?= Security::e($equipoDetalle['numero_serie']) ?>', '<?= Security::e($equipoDetalle['ubicacion_bahia'] ?? '') ?>')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Imprimir Código QR de Bahía
        </button>
        <button class="btn btn-sm btn-primary" onclick="openModal('modal-solicitar-servicio')">
            Generar Solicitud de Servicio
        </button>
        <?php if ($isAdmin || $isTech): ?>
            <a href="index.php?view=reportes&accion=nuevo&equipo_id=<?= $equipoDetalle['id'] ?>" class="btn btn-sm btn-success">
                Emitir Reporte de Mantenimiento
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- GRID DE TARJETAS DE EQUIPOS -->
<div class="equipos-grid">
    <?php if (empty($equipos)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
            No hay elevadores o equipos asignados a esta consulta.
        </div>
    <?php else: ?>
        <?php foreach ($equipos as $eq): ?>
            <div class="equipo-card" data-salud="<?= $eq['estado_salud'] ?>">
                <div>
                    <div class="equipo-card-header">
                        <span class="code-badge"><?= Security::e($eq['codigo_tcs']) ?></span>
                        <?php if ($eq['estado_salud'] === 'operativo'): ?>
                            <span class="badge-status status-operativo">OPERATIVO</span>
                        <?php elseif ($eq['estado_salud'] === 'observado'): ?>
                            <span class="badge-status status-observado">REQUIERE ATENCIÓN</span>
                        <?php else: ?>
                            <span class="badge-status status-fuera_servicio">FUERA DE SERVICIO</span>
                        <?php endif; ?>
                    </div>

                    <h3 class="equipo-title"><?= Security::e($eq['nombre']) ?></h3>
                    <div style="font-size: 11px; color: var(--accent-cyan-light); margin-top: 2px;">
                        📍 <?= Security::e($eq['ubicacion_nombre']) ?>
                    </div>

                    <div class="equipo-meta-list">
                        <div><strong>Marca:</strong> <?= Security::e($eq['marca']) ?> | <strong>Modelo:</strong> <?= Security::e($eq['modelo']) ?></div>
                        <div><strong>Número de Serie:</strong> <span style="font-family: var(--font-mono); color: #fff;"><?= Security::e($eq['numero_serie']) ?></span></div>
                        <div><strong>Capacidad:</strong> <?= Security::e($eq['capacidad'] ?? 'Estándar') ?></div>
                        <div><strong>Bahía / Ubicación:</strong> <?= Security::e($eq['ubicacion_bahia'] ?? 'Bahía General') ?></div>
                        <div><strong>Último Mantto:</strong> <?= Security::e($eq['ultimo_mantenimiento'] ?? 'N/A') ?></div>
                    </div>
                </div>

                <div class="equipo-card-actions">
                    <button class="btn btn-sm btn-warning" onclick="openChecklistModal(<?= $eq['id'] ?>, '<?= Security::e($eq['codigo_tcs']) ?>', '<?= Security::e(addslashes($eq['nombre'])) ?>')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        Checklist 5m
                    </button>
                    <button class="btn btn-sm btn-dark" onclick="verExpedienteEquipo(<?= $eq['id'] ?>)">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        Expediente
                    </button>
                    <button class="btn btn-sm btn-cyan" onclick="showQRModal('<?= Security::e($eq['codigo_tcs']) ?>', '<?= Security::e($eq['nombre']) ?>', '<?= Security::e($eq['marca']) ?>', '<?= Security::e($eq['numero_serie']) ?>', '<?= Security::e($eq['ubicacion_bahia'] ?? '') ?>')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Imprimir QR
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- MODAL VISUALIZADOR DE CÓDIGO QR PARA PEGAR EN ELEVADOR -->
<div id="modal-qr-expediente" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 440px; text-align: center;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Etiqueta QR Certificada para Elevador</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; align-items: center;">
            <!-- PLACA INDUSTRIAL IMPRIMIBLE -->
            <div id="qr-printable-plate" style="background: #ffffff; color: #0f172a; border: 3px solid #0f172a; border-radius: 8px; padding: 18px 20px; width: 100%; max-width: 340px; box-shadow: 0 4px 14px rgba(0,0,0,0.3); text-align: center;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px; border-bottom: 2px solid #b91c1c; padding-bottom: 8px;">
                    <img src="assets/img/logo-tcs.png" alt="TCS Motriz" style="height: 38px; object-fit: contain;">
                    <div style="text-align: left;">
                        <div style="font-weight: 900; font-size: 13px; color: #b91c1c; line-height: 1.1;">TCS MOTRIZ</div>
                        <div style="font-size: 8px; font-weight: 700; color: #475569; letter-spacing: 0.5px;">INGENIERÍA & TELEMETRÍA</div>
                    </div>
                </div>

                <div id="qr-modal-canvas-box" style="padding: 6px; background: #fff; margin: 6px auto; display: inline-block;"></div>
                
                <div id="qr-modal-codigo" style="font-family: monospace; font-size: 17px; font-weight: 900; color: #0284c7; margin-top: 4px;">TCS-EQ-001</div>
                <div id="qr-modal-nombre" style="font-weight: 800; font-size: 12px; color: #0f172a; margin-top: 2px;"></div>
                <div id="qr-modal-meta" style="font-size: 10px; color: #475569; margin-top: 2px;"></div>

                <div style="font-size: 8px; color: #64748b; margin-top: 10px; border-top: 1px dashed #cbd5e1; padding-top: 6px; line-height: 1.3;">
                    Escanee con la cámara para telemetría, bitácora y checklist diario.<br>
                    <strong>Soporte 24/7: 55-8000-4277 • servicio-tcsmotriz.com.mx</strong>
                </div>
            </div>

            <div style="font-size: 11px; color: var(--text-muted); margin-top: 14px;">
                Adherir esta etiqueta plastificada en la columna de control del elevador.
            </div>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button class="btn btn-primary btn-print-report">Imprimir Etiqueta Industrial</button>
            <button class="btn btn-dark" data-close-modal>Cerrar</button>
        </div>
    </div>
</div>

<!-- MODAL CHECKLIST PRE-OPERATIVO DE 5 MINUTOS CON BLOQUEO FAILSAFE -->
<div id="modal-checklist-preoperativo" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 540px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">⚡ Checklist Pre-operativo de Seguridad (5 Min)</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=guardar_checklist">
            <?= csrf_field() ?>
            <input type="hidden" name="id_equipo" id="chk-equipo-id" value="" />
            
            <div class="modal-body">
                <div style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.25); border-radius: var(--radius-sm); padding: 10px 14px; margin-bottom: 14px;">
                    <div style="font-size: 11px; color: #38bdf8; font-weight: 700; text-transform: uppercase;">Equipo Seleccionado</div>
                    <div id="chk-equipo-label" style="font-weight: 700; font-size: 13px; color: #fff;"></div>
                </div>

                <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 14px;">
                    Verificación rápida obligatoria antes de operar la rampa. Si detectas un riesgo, el sistema aplicará un bloqueo de seguridad industrial (Failsafe).
                </p>

                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px; background: #070e1e; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-subtle); cursor: pointer;">
                        <input type="checkbox" name="items[seguros_trinquetes]" value="1" checked required />
                        <span>1. Trinquetes mecánicos engranan firmes y audibles en ascenso</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; background: #070e1e; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-subtle); cursor: pointer;">
                        <input type="checkbox" name="items[cables_poleas]" value="1" checked required />
                        <span>2. Cables de acero sin deshilachado y con tensión simétrica</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; background: #070e1e; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-subtle); cursor: pointer;">
                        <input type="checkbox" name="items[fugas_hidraulicas]" value="1" checked required />
                        <span>3. Sin fugas de aceite hidráulico en mangueras ni pistones</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; background: #070e1e; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-subtle); cursor: pointer;">
                        <input type="checkbox" name="items[paro_emergencia]" value="1" checked required />
                        <span>4. Botón de paro de emergencia y bajada manual operativos</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; background: #070e1e; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-subtle); cursor: pointer;">
                        <input type="checkbox" name="items[anclaje_brazos]" value="1" checked required />
                        <span>5. Columnas rígidamente ancladas y seguros de giro de brazos OK</span>
                    </label>
                </div>

                <div class="form-group" style="margin-top: 14px;">
                    <label class="form-label">Dictamen de la Inspección Rápida *</label>
                    <select name="resultado" class="form-control" required id="chk-resultado-select">
                        <option value="aprobado">🟢 Aprobado (Equipo seguro para operar la jornada)</option>
                        <option value="fallo_critico">🔴 Fallo Crítico Detectado (Bloquear equipo / Failsafe)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Observaciones del Operador / Técnico</label>
                    <textarea name="observaciones" class="form-control" rows="2" placeholder="Detalla cualquier ruido o anomalía..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar Inspección</button>
            </div>
        </form>
    </div>
</div>

<script>
function openChecklistModal(id, codigo, nombre) {
    document.getElementById('chk-equipo-id').value = id;
    document.getElementById('chk-equipo-label').innerText = `${codigo} — ${nombre}`;
    openModal('modal-checklist-preoperativo');
}
</script>
