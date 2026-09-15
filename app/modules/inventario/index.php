<?php
/**
 * TCS MOTRIZ - Módulo de Inventario de Refacciones y Consumibles
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$inventario = DataStore::getInventario();
$proveedores = DataStore::getProveedores();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();

$itemsBajoStock = array_filter($inventario, fn($i) => ($i['stock_actual'] <= $i['stock_minimo']));
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Inventario de Refacciones y Consumibles</h1>
        <p>Almacén central de componentes para elevadores, cables de ecualización, fluidos ISO 32 y sellos</p>
    </div>

    <?php if ($isAdmin || $isTech): ?>
    <div class="header-action-buttons">
        <button class="btn btn-primary" onclick="openModal('modal-crear-refaccion')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Registrar Nueva Refacción</span>
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- ALERT BANNER SI HAY STOCK CRÍTICO -->
<?php if (!empty($itemsBajoStock)): ?>
<div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 20px;">⚠️</span>
        <div>
            <div style="font-weight: 700; color: #f87171; font-size: 13px;">Alerta de Reabastecimiento Crítico</div>
            <div style="font-size: 12px; color: var(--text-secondary);">Hay <?= count($itemsBajoStock) ?> componentes que han alcanzado o superado su punto mínimo de reorden.</div>
        </div>
    </div>
    <a href="index.php?view=proveedores" class="btn btn-sm btn-danger">Contactar Proveedores</a>
</div>
<?php endif; ?>

<!-- KPI DE INVENTARIO -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="kpi-card kpi-cyan">
        <div class="kpi-number"><?= count($inventario) ?></div>
        <div class="kpi-label">Ítems Catalogados</div>
        <div class="kpi-subtext">Piezas y Consumibles</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-number"><?= count($itemsBajoStock) ?></div>
        <div class="kpi-label">En Stock Crítico</div>
        <div class="kpi-subtext">Por debajo del mínimo</div>
    </div>

    <div class="kpi-card kpi-emerald">
        <div class="kpi-number"><?= array_sum(array_column($inventario, 'stock_actual')) ?></div>
        <div class="kpi-label">Unidades Totales</div>
        <div class="kpi-subtext">En Almacén Central</div>
    </div>

    <div class="kpi-card kpi-rose">
        <div class="kpi-number"><?= count($proveedores) ?></div>
        <div class="kpi-label">Proveedores Activos</div>
        <div class="kpi-subtext">Distribuidores Autorizados</div>
    </div>
</div>

<!-- TABLA DE INVENTARIO -->
<div class="panel-card">
    <div class="panel-header">
        <div class="panel-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            <span>Existencias de Almacén</span>
        </div>
        <div style="width: 250px;">
            <input type="text" id="table-search-input" class="form-control" placeholder="🔍 Filtrar componentes..." />
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Código Parte</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Stock Actual</th>
                    <th>Mínimo</th>
                    <th>Unidad</th>
                    <th>Costo Unitario</th>
                    <th>Proveedor</th>
                    <th>Ubicación</th>
                    <?php if ($isAdmin || $isTech): ?><th>Acción</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventario as $item): ?>
                    <tr>
                        <td><span class="code-badge"><?= Security::e($item['codigo_parte']) ?></span></td>
                        <td>
                            <strong><?= Security::e($item['descripcion']) ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);">Estado: <?= ucfirst(Security::e($item['estado_pieza'])) ?></div>
                        </td>
                        <td><?= Security::e($item['categoria']) ?></td>
                        <td>
                            <strong style="font-size: 14px; font-family: var(--font-mono); color: <?= ($item['stock_actual'] <= $item['stock_minimo'] ? 'var(--accent-rose)' : 'var(--accent-emerald)') ?>;">
                                <?= (int)$item['stock_actual'] ?>
                            </strong>
                        </td>
                        <td style="color: var(--text-muted);"><?= (int)$item['stock_minimo'] ?></td>
                        <td><?= Security::e($item['unidad_medida']) ?></td>
                        <td>$<?= number_format((float)$item['costo_unitario'], 2) ?></td>
                        <td style="font-size: 11px;"><?= Security::e($item['proveedor_nombre']) ?></td>
                        <td style="font-size: 11px; color: var(--text-secondary);"><?= Security::e($item['ubicacion_estante'] ?? 'Almacén') ?></td>
                        <?php if ($isAdmin || $isTech): ?>
                        <td>
                            <form method="POST" action="index.php?action=ajustar_stock" style="display: flex; gap: 4px; align-items: center;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_refaccion" value="<?= $item['id'] ?>">
                                <input type="number" name="nuevo_stock" value="<?= $item['stock_actual'] ?>" style="width: 60px; padding: 4px; font-size: 12px; background: #0a1324; border: 1px solid #1c2e56; color: #fff; border-radius: 4px;">
                                <button type="submit" class="btn btn-sm btn-dark" title="Guardar stock">💾</button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL CREAR REFACCIÓN -->
<div id="modal-crear-refaccion" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-title">Registrar Nueva Refacción en Inventario</div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_refaccion">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Código de Parte / SKU *</label>
                        <input type="text" name="codigo_parte" class="form-control" required placeholder="Ej: OIL-ISO32-19L">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría *</label>
                        <input type="text" name="categoria" class="form-control" required placeholder="Ej: Fluidos Hidráulicos">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción de la Pieza o Insumo *</label>
                    <input type="text" name="descripcion" class="form-control" required placeholder="Ej: Juego de 4 Almohadillas de Goma Rotary">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stock Inicial *</label>
                        <input type="number" name="stock_actual" class="form-control" required value="10" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Mínimo de Alerta *</label>
                        <input type="number" name="stock_minimo" class="form-control" required value="3" min="1">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unidad de Medida *</label>
                        <input type="text" name="unidad_medida" class="form-control" required value="Pza">
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
                                <option value="<?= $p['id'] ?>"><?= Security::e($p['razon_social']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ubicación en Estantería</label>
                        <input type="text" name="ubicacion_estante" class="form-control" placeholder="Ej: Pasillo A - Gaveta 2">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar en Almacén</button>
            </div>
        </form>
    </div>
</div>
