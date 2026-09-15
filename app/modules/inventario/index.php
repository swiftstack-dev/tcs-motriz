<?php
/**
 * TCS MOTRIZ - Módulo Integral de Inventario de Refacciones, Kárdex y Valuación
 * Control de almacén, existencias, reorden automático y bitácora de movimientos
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$inventario = DataStore::getInventario();
$proveedores = DataStore::getProveedores();
$movimientos = DataStore::getMovimientosInventario();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();

$activeTab = Security::sanitizeString($_GET['tab'] ?? 'existencias');
$msg = Security::sanitizeString($_GET['msg'] ?? '');

$itemsBajoStock = array_filter($inventario, fn($i) => ($i['stock_actual'] <= $i['stock_minimo']));
$totalUnidades = array_sum(array_column($inventario, 'stock_actual'));

$valorTotalAlmacen = 0;
$categoriasValuacion = [];

foreach ($inventario as $item) {
    $subtotal = (float)$item['costo_unitario'] * (int)$item['stock_actual'];
    $valorTotalAlmacen += $subtotal;
    $cat = $item['categoria'] ?? 'General';
    if (!isset($categoriasValuacion[$cat])) {
        $categoriasValuacion[$cat] = [
            'categoria' => $cat,
            'items'     => 0,
            'unidades'  => 0,
            'valor'     => 0.0
        ];
    }
    $categoriasValuacion[$cat]['items']++;
    $categoriasValuacion[$cat]['unidades'] += (int)$item['stock_actual'];
    $categoriasValuacion[$cat]['valor'] += $subtotal;
}
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Inventario de Refacciones y Almacén Central</h1>
        <p>Control de componentes certificados para elevadores, fluidos ISO 32, cables de ecualización y kárdex</p>
    </div>

    <div class="header-action-buttons">
        <div style="display: flex; gap: 8px;">
            <a href="index.php?action=exportar_csv&tipo=inventario" class="btn btn-dark" title="Exportar Catálogo de Refacciones a Excel / CSV">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Exportar Catálogo</span>
            </a>
            <a href="index.php?action=exportar_csv&tipo=movimientos_inventario" class="btn btn-dark" title="Exportar Kárdex de Movimientos a Excel / CSV">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>Exportar Kárdex</span>
            </a>
        </div>

        <?php if ($isAdmin || $isTech): ?>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-cyan" onclick="openModal('modal-movimiento-stock')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                <span>Registrar Movimiento</span>
            </button>
            <button class="btn btn-primary" onclick="openModal('modal-crear-refaccion')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ Nueva Refacción</span>
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MENSAJES DE NOTIFICACIÓN DE ACCIÓN -->
<?php if ($msg === 'refaccion_creada'): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--accent-emerald); color: #34d399; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>✅</span> Refacción registrada exitosamente en el catálogo central de almacén.
    </div>
<?php elseif ($msg === 'refaccion_actualizada'): ?>
    <div style="background: rgba(14, 165, 233, 0.15); border: 1px solid var(--accent-cyan); color: #38bdf8; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>ℹ️</span> Datos de la refacción y parámetros de stock actualizados correctamente.
    </div>
<?php elseif ($msg === 'refaccion_eliminada'): ?>
    <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid var(--accent-rose); color: #f87171; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>🗑️</span> Refacción eliminada del inventario y registrada en la bitácora de auditoría.
    </div>
<?php elseif ($msg === 'stock_actualizado'): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--accent-emerald); color: #34d399; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span>📦</span> Movimiento de existencias registrado exitosamente en el Kárdex de almacén.
    </div>
<?php endif; ?>

<!-- ALERTA SI HAY STOCK CRÍTICO -->
<?php if (!empty($itemsBajoStock)): ?>
<div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 24px;">⚠️</span>
        <div>
            <div style="font-weight: 700; color: #f87171; font-size: 14px;">Alerta de Reabastecimiento Crítico (<?= count($itemsBajoStock) ?> componentes)</div>
            <div style="font-size: 12px; color: var(--text-secondary);">Existen piezas que han caído por debajo del umbral mínimo de seguridad operativa para bahías de servicio.</div>
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="index.php?view=proveedores" class="btn btn-sm btn-danger">Contactar Proveedores</a>
        <a href="index.php?view=envios" class="btn btn-sm btn-dark">Ver Guías en Tránsito</a>
    </div>
</div>
<?php endif; ?>

<!-- KPI CARDS DE INVENTARIO -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="kpi-card kpi-cyan">
        <div class="kpi-number"><?= count($inventario) ?></div>
        <div class="kpi-label">Refacciones Catalogadas</div>
        <div class="kpi-subtext">Catálogo Multimarca</div>
    </div>

    <div class="kpi-card <?= count($itemsBajoStock) > 0 ? 'kpi-rose' : 'kpi-emerald' ?>">
        <div class="kpi-number"><?= count($itemsBajoStock) ?></div>
        <div class="kpi-label">En Stock Crítico</div>
        <div class="kpi-subtext"><?= count($itemsBajoStock) > 0 ? 'Requieren compra urgente' : 'Niveles de stock óptimos' ?></div>
    </div>

    <div class="kpi-card kpi-emerald">
        <div class="kpi-number"><?= number_format($totalUnidades) ?></div>
        <div class="kpi-label">Unidades Físicas</div>
        <div class="kpi-subtext">Existencias en Almacén</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-number">$<?= number_format($valorTotalAlmacen, 2) ?></div>
        <div class="kpi-label">Valuación Total (MXN)</div>
        <div class="kpi-subtext">Activo Fijo en Insumos</div>
    </div>
</div>

<!-- BARRA DE PESTAÑAS -->
<div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px; overflow-x: auto;">
    <button type="button" class="tab-btn <?= ($activeTab === 'existencias' ? 'active' : '') ?>" onclick="showTab('tab-existencias', this)">
        📦 Catálogo de Existencias (<?= count($inventario) ?>)
    </button>
    <button type="button" class="tab-btn <?= ($activeTab === 'movimientos' ? 'active' : '') ?>" onclick="showTab('tab-movimientos', this)">
        📋 Kárdex y Movimientos (<?= count($movimientos) ?>)
    </button>
    <button type="button" class="tab-btn <?= ($activeTab === 'valuacion' ? 'active' : '') ?>" onclick="showTab('tab-valuacion', this)">
        💰 Valuación por Categoría (<?= count($categoriasValuacion) ?>)
    </button>
</div>

<!-- =========================================================================
     PESTAÑA 1: CATÁLOGO DE EXISTENCIAS
     ========================================================================= -->
<div id="tab-existencias" class="tab-content-panel" style="display: <?= ($activeTab === 'existencias' ? 'block' : 'none') ?>;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                <span>Control de Piezas y Existencias Físicas</span>
            </div>
            <div style="width: 280px;">
                <input type="text" id="table-search-input" class="form-control" placeholder="🔍 Buscar por código, nombre o rack..." />
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código / SKU</th>
                        <th>Descripción y Estado</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Mínimo</th>
                        <th>Unidad</th>
                        <th>Costo Unitario</th>
                        <th>Proveedor Habitual</th>
                        <th>Ubicación Rack</th>
                        <?php if ($isAdmin || $isTech): ?>
                            <th style="text-align: right;">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventario as $item): ?>
                        <?php 
                            $isCritico = ($item['stock_actual'] <= $item['stock_minimo']); 
                            $pctStock = $item['stock_minimo'] > 0 ? min(100, round(($item['stock_actual'] / ($item['stock_minimo'] * 2)) * 100)) : 100;
                        ?>
                        <tr>
                            <td>
                                <span class="code-badge"><?= Security::e($item['codigo_parte']) ?></span>
                            </td>
                            <td>
                                <strong style="color: #fff; font-size: 13px;"><?= Security::e($item['descripcion']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted); display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                                    <span>Condición: <strong style="color: var(--accent-cyan);"><?= ucfirst(Security::e($item['estado_pieza'] ?? 'nuevo')) ?></strong></span>
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: var(--text-secondary); background: rgba(255,255,255,0.05); padding: 2px 8px; border-radius: 4px;">
                                    <?= Security::e($item['categoria']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 15px; font-weight: 800; font-family: var(--font-mono); color: <?= $isCritico ? 'var(--accent-rose)' : 'var(--accent-emerald)' ?>;">
                                        <?= (int)$item['stock_actual'] ?>
                                    </span>
                                    <?php if ($isCritico): ?>
                                        <span class="badge-status status-fuera" style="font-size: 9px; padding: 2px 6px;">REORDEN</span>
                                    <?php endif; ?>
                                </div>
                                <div style="width: 70px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 4px; overflow: hidden;">
                                    <div style="height: 100%; width: <?= $pctStock ?>%; background: <?= $isCritico ? '#ef4444' : '#10b981' ?>;"></div>
                                </div>
                            </td>
                            <td style="color: var(--text-muted); font-family: var(--font-mono); font-size: 13px;">
                                <?= (int)$item['stock_minimo'] ?>
                            </td>
                            <td style="font-size: 12px; color: var(--text-secondary);">
                                <?= Security::e($item['unidad_medida']) ?>
                            </td>
                            <td style="font-family: var(--font-mono); font-weight: 700; color: #fff;">
                                $<?= number_format((float)$item['costo_unitario'], 2) ?>
                            </td>
                            <td style="font-size: 11px;">
                                <?php if (!empty($item['id_proveedor'])): ?>
                                    <a href="index.php?view=proveedores&proveedor_id=<?= (int)$item['id_proveedor'] ?>" style="color: var(--accent-cyan-light); text-decoration: none;">
                                        <?= Security::e($item['proveedor_nombre']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">Sin proveedor</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 11px; color: var(--text-secondary);">
                                <span style="display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <?= Security::e($item['ubicacion_estante'] ?? 'Almacén Central') ?>
                                </span>
                            </td>
                            <?php if ($isAdmin || $isTech): ?>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" class="btn btn-sm btn-dark" title="Ajustar existencias" onclick="openAjustarStock(<?= $item['id'] ?>, '<?= Security::e($item['codigo_parte']) ?>', <?= (int)$item['stock_actual'] ?>)">
                                        ⚖️ Ajustar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-cyan" title="Editar refacción" onclick='openEditarRefaccion(<?= json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        ✏️ Editar
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <button type="button" class="btn btn-sm btn-danger" title="Eliminar refacción" onclick="confirmarEliminarRefaccion(<?= $item['id'] ?>, '<?= Security::e($item['codigo_parte']) ?>')">
                                        🗑️
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     PESTAÑA 2: KÁRDEX Y BITÁCORA DE MOVIMIENTOS
     ========================================================================= -->
<div id="tab-movimientos" class="tab-content-panel" style="display: <?= ($activeTab === 'movimientos' ? 'block' : 'none') ?>;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>Kárdex Oficial: Entradas, Salidas y Ajustes de Almacén</span>
            </div>
            <button class="btn btn-sm btn-cyan" onclick="openModal('modal-movimiento-stock')">
                + Registrar Nuevo Movimiento
            </button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Folio / ID</th>
                        <th>Fecha / Hora</th>
                        <th>Refacción</th>
                        <th>Tipo Movimiento</th>
                        <th>Cantidad</th>
                        <th>Stock Resultante</th>
                        <th>Motivo / Justificación</th>
                        <th>Referencia / Guía</th>
                        <th>Autorizado Por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td>
                                <span class="code-badge">MOV-<?= str_pad((string)$mov['id'], 4, '0', STR_PAD_LEFT) ?></span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-secondary);">
                                <?= Security::e($mov['created_at']) ?>
                            </td>
                            <td>
                                <strong style="color: #fff;"><?= Security::e($mov['codigo_parte']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= Security::e($mov['descripcion'] ?? '') ?></div>
                            </td>
                            <td>
                                <?php if ($mov['tipo_movimiento'] === 'entrada'): ?>
                                    <span class="badge-status status-operativo">⬆️ ENTRADA</span>
                                <?php elseif ($mov['tipo_movimiento'] === 'salida'): ?>
                                    <span class="badge-status status-fuera">⬇️ SALIDA</span>
                                <?php else: ?>
                                    <span class="badge-status status-observado">🔄 AJUSTE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size: 14px; font-family: var(--font-mono); color: <?= $mov['tipo_movimiento'] === 'entrada' ? 'var(--accent-emerald)' : ($mov['tipo_movimiento'] === 'salida' ? 'var(--accent-rose)' : 'var(--accent-cyan)') ?>;">
                                    <?= $mov['tipo_movimiento'] === 'entrada' ? '+' : ($mov['tipo_movimiento'] === 'salida' ? '-' : '') ?><?= (int)$mov['cantidad'] ?>
                                </strong>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 12px;">
                                <span style="color: var(--text-muted);"><?= (int)($mov['stock_anterior'] ?? 0) ?></span>
                                <span style="color: var(--text-muted);"> ➔ </span>
                                <strong style="color: #fff;"><?= (int)$mov['stock_nuevo'] ?></strong>
                            </td>
                            <td style="font-size: 12px; color: var(--text-secondary);">
                                <?= Security::e($mov['motivo']) ?>
                            </td>
                            <td>
                                <span style="font-family: var(--font-mono); font-size: 11px; background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px;">
                                    <?= Security::e($mov['referencia'] ?? 'S/R') ?>
                                </span>
                            </td>
                            <td style="font-size: 12px;">
                                👤 <?= Security::e($mov['usuario_nombre'] ?? 'Administrador') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     PESTAÑA 3: VALUACIÓN FINANCIERA POR CATEGORÍA
     ========================================================================= -->
<div id="tab-valuacion" class="tab-content-panel" style="display: <?= ($activeTab === 'valuacion' ? 'block' : 'none') ?>;">
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Desglose de Activo Financiero e Inversión por Categoría de Insumo</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Categoría de Insumo</th>
                        <th>Refacciones Catalogadas</th>
                        <th>Unidades Físicas</th>
                        <th>Valor Total en Almacén (MXN)</th>
                        <th>% Participación en Almacén</th>
                        <th>Estrategia de Rotación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoriasValuacion as $catRow): ?>
                        <?php 
                            $pctValor = $valorTotalAlmacen > 0 ? round(($catRow['valor'] / $valorTotalAlmacen) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>
                                <strong style="color: #fff; font-size: 13px;"><?= Security::e($catRow['categoria']) ?></strong>
                            </td>
                            <td><?= (int)$catRow['items'] ?> refacciones</td>
                            <td><?= number_format((int)$catRow['unidades']) ?> unidades</td>
                            <td>
                                <strong style="font-family: var(--font-mono); font-size: 14px; color: var(--accent-emerald);">
                                    $<?= number_format((float)$catRow['valor'], 2) ?>
                                </strong>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-family: var(--font-mono); font-size: 12px;"><?= $pctValor ?>%</span>
                                    <div style="flex: 1; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; min-width: 60px;">
                                        <div style="height: 100%; width: <?= $pctValor ?>%; background: #38bdf8;"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 11px; color: var(--text-secondary);">
                                <?= $pctValor > 30 ? 'Alta inversión — Control FIFO estricto' : 'Rotación continua conforme a servicios preventivos' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: rgba(14, 165, 233, 0.08); font-weight: 700;">
                        <td>TOTAL GLOBAL ALMACÉN</td>
                        <td><?= count($inventario) ?> refacciones</td>
                        <td><?= number_format($totalUnidades) ?> unidades</td>
                        <td style="font-family: var(--font-mono); font-size: 15px; color: var(--accent-cyan);">$<?= number_format($valorTotalAlmacen, 2) ?></td>
                        <td>100%</td>
                        <td>Auditoría ISO 27001</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODALES OPERATIVOS DE ALMACÉN
     ========================================================================= -->

<!-- MODAL 1: REGISTRAR NUEVA REFACCIÓN -->
<div id="modal-crear-refaccion" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Registrar Nueva Refacción en Catálogo Central</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_refaccion">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Código de Parte / SKU *</label>
                        <input type="text" name="codigo_parte" class="form-control" required placeholder="Ej: OIL-ISO32-19L" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría *</label>
                        <input type="text" name="categoria" class="form-control" required placeholder="Ej: Fluidos Hidráulicos">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción de la Pieza o Insumo *</label>
                    <input type="text" name="descripcion" class="form-control" required placeholder="Ej: Cubeta Aceite Hidráulico Anti-Desgaste ISO 32 (19L)">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stock Inicial en Almacén *</label>
                        <input type="number" name="stock_actual" class="form-control" required value="10" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Mínimo de Alerta (Punto de Reorden) *</label>
                        <input type="number" name="stock_minimo" class="form-control" required value="3" min="1">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unidad de Medida *</label>
                        <select name="unidad_medida" class="form-control" required>
                            <option value="Pza">Pieza (Pza)</option>
                            <option value="Juego">Juego / Set</option>
                            <option value="Kit">Kit de Servicio</option>
                            <option value="Cubeta">Cubeta (19L)</option>
                            <option value="Lt">Litro (Lt)</option>
                            <option value="Gal">Galón</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Costo Unitario ($ MXN) *</label>
                        <input type="number" step="0.01" name="costo_unitario" class="form-control" required value="500.00">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Proveedor Habitual</label>
                        <select name="id_proveedor" class="form-control">
                            <option value="">Sin proveedor asignado</option>
                            <?php foreach ($proveedores as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= Security::e($p['razon_social']) ?> (RFC: <?= Security::e($p['rfc']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ubicación Física en Almacén</label>
                        <input type="text" name="ubicacion_estante" class="form-control" placeholder="Ej: Pasillo A - Estante 2">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar en Almacén Central</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: EDITAR REFACCIÓN -->
<div id="modal-editar-refaccion" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Editar Ficha de Refacción</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=editar_refaccion">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit-ref-id" value="">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Código de Parte / SKU *</label>
                        <input type="text" name="codigo_parte" id="edit-ref-codigo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría *</label>
                        <input type="text" name="categoria" id="edit-ref-categoria" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción de la Pieza o Insumo *</label>
                    <input type="text" name="descripcion" id="edit-ref-descripcion" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stock Mínimo de Alerta *</label>
                        <input type="number" name="stock_minimo" id="edit-ref-stock-minimo" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unidad de Medida *</label>
                        <input type="text" name="unidad_medida" id="edit-ref-unidad" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Costo Unitario ($ MXN) *</label>
                        <input type="number" step="0.01" name="costo_unitario" id="edit-ref-costo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Proveedor Habitual</label>
                        <select name="id_proveedor" id="edit-ref-proveedor" class="form-control">
                            <option value="">Sin proveedor asignado</option>
                            <?php foreach ($proveedores as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= Security::e($p['razon_social']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Ubicación Física en Almacén</label>
                    <input type="text" name="ubicacion_estante" id="edit-ref-estante" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-cyan">Guardar Cambios de Refacción</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: REGISTRAR MOVIMIENTO / AJUSTE DE STOCK (KÁRDEX) -->
<div id="modal-movimiento-stock" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Registrar Movimiento de Almacén (Kárdex)</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=registrar_movimiento_stock">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Seleccionar Refacción del Catálogo *</label>
                    <select name="id_refaccion" id="mov-id-refaccion" class="form-control" required onchange="actualizarStockActualLabel(this)">
                        <option value="">-- Elige una pieza --</option>
                        <?php foreach ($inventario as $invItem): ?>
                            <option value="<?= $invItem['id'] ?>" data-stock="<?= (int)$invItem['stock_actual'] ?>">
                                <?= Security::e($invItem['codigo_parte']) ?> — <?= Security::e($invItem['descripcion']) ?> (Stock actual: <?= (int)$invItem['stock_actual'] ?> <?= Security::e($invItem['unidad_medida']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo de Movimiento *</label>
                        <select name="tipo_movimiento" id="mov-tipo" class="form-control" required>
                            <option value="entrada">🟢 ENTRADA (Reabastecimiento / Compra)</option>
                            <option value="salida">🔴 SALIDA (Consumo en Servicio / Despacho)</option>
                            <option value="ajuste">🔄 AJUSTE (Conciliación física de inventario)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cantidad a Mover / Ajustar *</label>
                        <input type="number" name="cantidad" id="mov-cantidad" class="form-control" required min="1" value="1">
                        <div id="mov-stock-hint" style="font-size: 11px; color: var(--text-muted); margin-top: 4px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Motivo o Justificación del Movimiento *</label>
                    <input type="text" name="motivo" class="form-control" required placeholder="Ej: Factura de proveedor #9014 / Instalación en rampa Ford Interlomas">
                </div>

                <div class="form-group">
                    <label class="form-label">Documento o Referencia de Respaldo</label>
                    <input type="text" name="referencia" class="form-control" placeholder="Ej: FAC-9014, ORD-2026-041, GUIA-TCS-001">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Asentar en Kárdex</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditarRefaccion(item) {
    document.getElementById('edit-ref-id').value = item.id || '';
    document.getElementById('edit-ref-codigo').value = item.codigo_parte || '';
    document.getElementById('edit-ref-categoria').value = item.categoria || '';
    document.getElementById('edit-ref-descripcion').value = item.descripcion || '';
    document.getElementById('edit-ref-stock-minimo').value = item.stock_minimo || 1;
    document.getElementById('edit-ref-unidad').value = item.unidad_medida || 'Pza';
    document.getElementById('edit-ref-costo').value = item.costo_unitario || 0;
    document.getElementById('edit-ref-proveedor').value = item.id_proveedor || '';
    document.getElementById('edit-ref-estante').value = item.ubicacion_estante || '';
    openModal('modal-editar-refaccion');
}

function openAjustarStock(refId, codigo, stockActual) {
    const sel = document.getElementById('mov-id-refaccion');
    if (sel) {
        sel.value = refId;
        actualizarStockActualLabel(sel);
    }
    openModal('modal-movimiento-stock');
}

function actualizarStockActualLabel(selectEl) {
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const hint = document.getElementById('mov-stock-hint');
    if (selectedOpt && selectedOpt.dataset.stock) {
        hint.innerText = `Existencia actual en almacén: ${selectedOpt.dataset.stock} unidades.`;
    } else {
        hint.innerText = '';
    }
}

function confirmarEliminarRefaccion(id, codigo) {
    if (confirm(`¿Está seguro de eliminar del catálogo la refacción "${codigo}"? Esta acción se asentará en la bitácora de auditoría.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=eliminar_refaccion';

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
