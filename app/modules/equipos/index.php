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

    <!-- MATRIZ DE DESGASTE MECÁNICO Y VIDA ÚTIL PREDICTIVA (MEJORA 4) -->
    <?php
    $desgaste = DataStore::getDesgastePredictivo($equipoDetalle);
    $alertaDesgaste = ($desgaste['cables']['porcentaje'] >= 75 || $desgaste['fluido']['porcentaje'] >= 75 || $desgaste['gomas']['porcentaje'] >= 75);
    ?>
    <div style="background: #081022; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 18px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">⏳</span>
                <div>
                    <div style="font-size: 14px; font-weight: 800; color: #fff;">Telemetría de Ciclos & Desgaste Mecánico Predictivo</div>
                    <div style="font-size: 11px; color: var(--text-secondary);">Algoritmo de fatiga basado en <?= number_format($desgaste['horas_acumuladas']) ?> hrs efectivas de izaje industrial</div>
                </div>
            </div>
            <span class="code-badge" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3); font-size: 12px; padding: 4px 10px;">
                Norma ANSI / ALI ALCTV
            </span>
        </div>

        <?php if ($alertaDesgaste): ?>
            <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #fbbf24; display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span>
                <div><strong>Alerta Preventiva Temprana:</strong> Rampa <?= Security::e($equipoDetalle['codigo_tcs']) ?> al <?= $desgaste['cables']['porcentaje'] ?>% de ciclo en cables de ecualización. Programar recambio para evitar paros en bahía.</div>
            </div>
        <?php endif; ?>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <!-- 1. CABLES DE ECUALIZACIÓN -->
            <?php
            $c = $desgaste['cables'];
            $barColor = $c['porcentaje'] >= 90 ? '#ef4444' : ($c['porcentaje'] >= 75 ? '#f59e0b' : '#10b981');
            ?>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px;">
                    <span style="color: #fff; font-weight: 700;">1. <?= $c['nombre'] ?></span>
                    <span style="color: <?= $barColor ?>; font-weight: 800; font-family: var(--font-mono);"><?= $c['porcentaje'] ?>% Consumido (<?= $c['horas_restantes'] ?> hrs restantes)</span>
                </div>
                <div style="height: 8px; background: #1e293b; border-radius: 4px; overflow: hidden;">
                    <div style="width: <?= $c['porcentaje'] ?>%; height: 100%; background: <?= $barColor ?>; transition: width 0.4s ease;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                    <span><?= $c['norma'] ?></span>
                    <span style="color: <?= $barColor ?>;"><?= $c['recomendacion'] ?></span>
                </div>
            </div>

            <!-- 2. FLUIDO HIDRÁULICO -->
            <?php
            $f = $desgaste['fluido'];
            $barColorF = $f['porcentaje'] >= 90 ? '#ef4444' : ($f['porcentaje'] >= 75 ? '#f59e0b' : '#10b981');
            ?>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px;">
                    <span style="color: #fff; font-weight: 700;">2. <?= $f['nombre'] ?></span>
                    <span style="color: <?= $barColorF ?>; font-weight: 800; font-family: var(--font-mono);"><?= $f['porcentaje'] ?>% Consumido (<?= $f['horas_restantes'] ?> hrs restantes)</span>
                </div>
                <div style="height: 8px; background: #1e293b; border-radius: 4px; overflow: hidden;">
                    <div style="width: <?= $f['porcentaje'] ?>%; height: 100%; background: <?= $barColorF ?>; transition: width 0.4s ease;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                    <span><?= $f['norma'] ?></span>
                    <span style="color: <?= $barColorF ?>;"><?= $f['recomendacion'] ?></span>
                </div>
            </div>

            <!-- 3. ALMOHADILLAS DE GOMA -->
            <?php
            $g = $desgaste['gomas'];
            $barColorG = $g['porcentaje'] >= 90 ? '#ef4444' : ($g['porcentaje'] >= 75 ? '#f59e0b' : '#10b981');
            ?>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px;">
                    <span style="color: #fff; font-weight: 700;">3. <?= $g['nombre'] ?></span>
                    <span style="color: <?= $barColorG ?>; font-weight: 800; font-family: var(--font-mono);"><?= $g['porcentaje'] ?>% Consumido (<?= $g['horas_restantes'] ?> hrs restantes)</span>
                </div>
                <div style="height: 8px; background: #1e293b; border-radius: 4px; overflow: hidden;">
                    <div style="width: <?= $g['porcentaje'] ?>%; height: 100%; background: <?= $barColorG ?>; transition: width 0.4s ease;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                    <span><?= $g['norma'] ?></span>
                    <span style="color: <?= $barColorG ?>;"><?= $g['recomendacion'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; border-top: 1px solid #1c2e56; padding-top: 14px; align-items: center;">
        <?php
        $waEqMsg = "Hola, comparto expediente de la rampa " . $equipoDetalle['nombre'] . " (" . $equipoDetalle['codigo_tcs'] . "): Estado " . strtoupper($equipoDetalle['estado_salud']) . ", horas de uso: " . $equipoDetalle['horas_uso'] . " hrs. Desgaste en cables: " . $desgaste['cables']['porcentaje'] . "%. Soporte TCS Motriz: 55-8000-4277.";
        $waEqUrl = "https://api.whatsapp.com/send?text=" . urlencode($waEqMsg);
        ?>
        <a href="<?= $waEqUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-emerald" style="background:#16a34a;color:#fff;border-color:#15803d;display:inline-flex;align-items:center;gap:6px;" title="Compartir Expediente y Telemetría vía WhatsApp">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            <span>WhatsApp</span>
        </a>
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
                    <?php
                    $waCardMsg = "Reporte de rampa " . $eq['nombre'] . " (" . $eq['codigo_tcs'] . "): Estado " . strtoupper($eq['estado_salud']) . " en " . ($eq['ubicacion_nombre'] ?? 'Taller') . ". Contactar a TCS Motriz: 55-8000-4277.";
                    $waCardUrl = "https://api.whatsapp.com/send?text=" . urlencode($waCardMsg);
                    ?>
                    <a href="<?= $waCardUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-emerald" style="background:#16a34a;color:#fff;border-color:#15803d;padding:4px 7px;display:inline-flex;align-items:center;" title="Notificar vía WhatsApp">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.652zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    </a>
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
