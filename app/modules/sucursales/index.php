<?php
/**
 * TCS MOTRIZ - Módulo de Registro de Sucursales y Talleres
 * Arquitectura Jerárquica: Matriz -> Sucursales -> Equipos / Taller -> Equipos
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$matrices = DataStore::getMatrices();
$talleres = DataStore::getTalleres();
$equipos = DataStore::getEquipos();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();
?>

<div class="view-header">
    <div class="view-title-group">
        <h1>Registro de Sucursales y Talleres</h1>
        <p>Gestión multi-sedes de Empresas Matriz (Sucursales) y Talleres Independientes (Local Único)</p>
    </div>

    <?php if ($isAdmin || $isTech): ?>
    <div class="header-action-buttons">
        <button class="btn btn-primary" onclick="openModal('modal-crear-matriz')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M10 11h2M10 15h2M14 11h2M14 15h2M18 11h2M18 15h2M9 3h6v4H9z"/></svg>
            <span>Registrar Matriz (Empresa)</span>
        </button>
        <button class="btn btn-cyan" onclick="openModal('modal-vincular-sucursal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            <span>Vincular Sucursal a Matriz</span>
        </button>
        <button class="btn btn-dark" onclick="openModal('modal-crear-taller')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            <span>Registrar Taller Único</span>
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- SECCIÓN DE EMPRESAS MATRIZ Y SUCURSALES -->
<div style="margin-bottom: 30px;">
    <h2 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <span style="display: inline-block; width: 10px; height: 10px; background: var(--accent-emerald); border-radius: 2px;"></span>
        Empresas Matriz con Red de Sucursales
    </h2>

    <?php foreach ($matrices as $mat): ?>
        <?php 
            $sucursales = DataStore::getSucursales($mat['id']);
        ?>
        <div class="tree-card">
            <!-- CABECERA DE MATRIZ -->
            <div class="tree-matriz-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: #fff;"><?= Security::e($mat['razon_social']) ?></h3>
                        <span class="badge-status status-operativo">EMPRESA MATRIZ</span>
                    </div>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px; display: flex; gap: 20px; flex-wrap: wrap;">
                        <span><strong>RFC:</strong> <?= Security::e($mat['rfc']) ?></span>
                        <span><strong>Fiscal:</strong> <?= Security::e($mat['direccion']) ?></span>
                        <span><strong>Tel:</strong> <?= Security::e($mat['telefono']) ?></span>
                        <span><strong>Email:</strong> <?= Security::e($mat['email']) ?></span>
                    </div>
                </div>
                <?php if ($isAdmin || $isTech): ?>
                <button class="btn btn-sm btn-cyan" onclick="openModal('modal-vincular-sucursal')">
                    + Vincular Sucursal
                </button>
                <?php endif; ?>
            </div>

            <!-- SUCURSALES HIJAS -->
            <div class="branches-grid">
                <?php if (empty($sucursales)): ?>
                    <div style="color: var(--text-muted); font-size: 13px;">No hay sucursales vinculadas a esta matriz aún.</div>
                <?php else: ?>
                    <?php foreach ($sucursales as $suc): ?>
                        <?php 
                            $eqCount = count(array_filter($equipos, fn($e) => ($e['id_sucursal'] ?? 0) == $suc['id']));
                        ?>
                        <div class="branch-subcard">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <h4 style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    <?= Security::e($suc['nombre']) ?>
                                </h4>
                                <span class="nav-badge"><?= $eqCount ?> Equipos</span>
                            </div>

                            <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.6; margin-top: 10px;">
                                <div>📍 <?= Security::e($suc['direccion']) ?></div>
                                <div>📞 <?= Security::e($suc['telefono']) ?></div>
                                <?php if (!empty($suc['gerente_servicio'])): ?>
                                    <div>👤 Gerente Serv.: <?= Security::e($suc['gerente_servicio']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($suc['jefe_taller'])): ?>
                                    <div>🔧 Jefe Taller: <?= Security::e($suc['jefe_taller']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div style="margin-top: 12px; padding-top: 8px; border-top: 1px solid #16284a; display: flex; justify-content: space-between; align-items: center;">
                                <a href="index.php?view=equipos&sucursal_id=<?= $suc['id'] ?>" class="btn btn-sm btn-dark" style="font-size: 11px;">Ver Rampas (<?= $eqCount ?>)</a>
                                <?php if ($isAdmin || $isTech): ?>
                                    <button class="btn btn-sm btn-primary" onclick="openModal('modal-crear-equipo')" style="font-size: 11px;">+ Equipo</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- SECCIÓN DE TALLERES INDEPENDIENTES (LOCAL ÚNICO) -->
<div>
    <h2 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
        <span style="display: inline-block; width: 10px; height: 10px; background: var(--accent-cyan); border-radius: 2px;"></span>
        Talleres Independientes (Local Único)
    </h2>

    <?php foreach ($talleres as $tal): ?>
        <?php 
            $eqCount = count(array_filter($equipos, fn($e) => ($e['id_taller'] ?? 0) == $tal['id']));
        ?>
        <div class="tree-card">
            <div class="tree-taller-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 style="font-size: 15px; font-weight: 700; color: #fff;"><?= Security::e($tal['razon_social']) ?></h3>
                        <span class="badge-status status-en_proceso">TALLER INDEPENDIENTE</span>
                    </div>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px; display: flex; gap: 20px; flex-wrap: wrap;">
                        <span><strong>RFC:</strong> <?= Security::e($tal['rfc']) ?></span>
                        <span><strong>Dirección:</strong> <?= Security::e($tal['direccion']) ?></span>
                        <span><strong>Tel:</strong> <?= Security::e($tal['telefono']) ?></span>
                        <span><strong>Jefe Taller:</strong> <?= Security::e($tal['jefe_taller'] ?? 'N/A') ?></span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="index.php?view=equipos&taller_id=<?= $tal['id'] ?>" class="btn btn-sm btn-dark">Ver Equipos (<?= $eqCount ?>)</a>
                    <?php if ($isAdmin || $isTech): ?>
                        <button class="btn btn-sm btn-success" onclick="openModal('modal-crear-equipo')">+ Equipos</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
