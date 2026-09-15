<?php
/**
 * TCS MOTRIZ - Módulo de Autogestión Integral de Clientes, Sucursales y Accesos
 * 100% Autogestionable por el Administrador sin intervención técnica
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$currentUser = Auth::user();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();

$matrices = DataStore::getMatrices();
$talleres = DataStore::getTalleres();
$sucursales = DataStore::getSucursales();
$equipos = DataStore::getEquipos();
$usuariosClientes = DataStore::getUsuarios('cliente');

// Pestaña activa por parámetro GET o por defecto 'matrices'
$activeTab = Security::sanitizeString($_GET['tab'] ?? 'matrices');

// Mensajes de confirmación
$msg = Security::sanitizeString($_GET['msg'] ?? '');
$msgText = '';
if ($msg === 'matriz_creada') $msgText = 'Empresa Matriz registrada con éxito en el sistema.';
if ($msg === 'matriz_actualizada') $msgText = 'Datos de la Empresa Matriz actualizados correctamente.';
if ($msg === 'matriz_eliminada') $msgText = 'Empresa Matriz eliminada del registro.';
if ($msg === 'sucursal_creada') $msgText = 'Nueva sucursal vinculada con éxito.';
if ($msg === 'sucursal_actualizada') $msgText = 'Sucursal actualizada correctamente.';
if ($msg === 'sucursal_eliminada') $msgText = 'Sucursal eliminada del sistema.';
if ($msg === 'taller_creado') $msgText = 'Taller independiente registrado con éxito.';
if ($msg === 'taller_actualizado') $msgText = 'Datos del taller actualizados correctamente.';
if ($msg === 'taller_eliminado') $msgText = 'Taller eliminado del registro.';
if ($msg === 'usuario_creado') $msgText = 'Cuenta de acceso para cliente creada exitosamente.';
if ($msg === 'usuario_actualizado') $msgText = 'Acceso de cliente actualizado y credenciales renovadas.';
if ($msg === 'usuario_eliminado') $msgText = 'Cuenta de acceso revocada y eliminada.';
?>

<?php if (!empty($msgText)): ?>
<div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; padding: 12px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
    <div style="display: flex; align-items: center; gap: 8px;">
        <span>✔</span>
        <span><?= Security::e($msgText) ?></span>
    </div>
    <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: #34d399; font-size: 16px; cursor: pointer;">&times;</button>
</div>
<?php endif; ?>

<div class="view-header">
    <div class="view-title-group">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 28px; object-fit: contain;">
            <h1>Centro de Autogestión de Clientes & Sucursales</h1>
        </div>
        <p>Administración 100% autónoma de Empresas Matriz, Sucursales, Talleres y Cuentas de Acceso al Portal</p>
    </div>

    <?php if ($isAdmin || $isTech): ?>
    <div class="header-action-buttons">
        <a href="index.php?action=exportar_csv&tipo=clientes" class="btn btn-dark" title="Exportar directorio integral a Excel">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Exportar Directorio (Excel)</span>
        </a>
        <button class="btn btn-primary" onclick="openModal('modal-crear-matriz')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M10 11h2M10 15h2M14 11h2M14 15h2M18 11h2M18 15h2M9 3h6v4H9z"/></svg>
            <span>+ Alta Matriz</span>
        </button>
        <button class="btn btn-cyan" onclick="openModal('modal-vincular-sucursal')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            <span>+ Alta Sucursal</span>
        </button>
        <button class="btn btn-emerald" style="background:#059669;color:#fff;border-color:#047857;" onclick="openModal('modal-crear-usuario-cliente')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            <span>+ Alta Acceso Cliente</span>
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- KPI MÉTRICAS DE CONTROL COMERCIAL -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 24px;">
    <div class="kpi-card kpi-cyan">
        <div class="kpi-number"><?= count($matrices) ?></div>
        <div class="kpi-label">Empresas Matriz</div>
        <div class="kpi-subtext">Grupos Corporativos Multisede</div>
    </div>
    <div class="kpi-card kpi-emerald">
        <div class="kpi-number"><?= count($sucursales) ?></div>
        <div class="kpi-label">Red de Sucursales</div>
        <div class="kpi-subtext">Agencias y Bahías Vinculadas</div>
    </div>
    <div class="kpi-card kpi-blue">
        <div class="kpi-number"><?= count($talleres) ?></div>
        <div class="kpi-label">Talleres Únicos</div>
        <div class="kpi-subtext">Establecimientos Independientes</div>
    </div>
    <div class="kpi-card kpi-amber">
        <div class="kpi-number"><?= count($usuariosClientes) ?></div>
        <div class="kpi-label">Accesos al Portal</div>
        <div class="kpi-subtext">Usuarios Clientes Habilitados</div>
    </div>
</div>

<!-- NAVEGACIÓN POR PESTAÑAS (TABS) -->
<div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border-subtle); margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px;">
    <button type="button" class="tab-btn <?= ($activeTab === 'matrices' ? 'active' : '') ?>" onclick="showTab('tab-matrices', this)">
        🏢 Empresas Matriz & Sucursales (<?= count($matrices) ?>)
    </button>
    <button type="button" class="tab-btn <?= ($activeTab === 'talleres' ? 'active' : '') ?>" onclick="showTab('tab-talleres', this)">
        🔧 Talleres Independientes (<?= count($talleres) ?>)
    </button>
    <button type="button" class="tab-btn <?= ($activeTab === 'sucursales' ? 'active' : '') ?>" onclick="showTab('tab-sucursales', this)">
        📍 Directorio de Sucursales (<?= count($sucursales) ?>)
    </button>
    <button type="button" class="tab-btn <?= ($activeTab === 'usuarios' ? 'active' : '') ?>" onclick="showTab('tab-usuarios', this)">
        👥 Cuentas de Acceso al Portal (<?= count($usuariosClientes) ?>)
    </button>
</div>

<!-- =========================================================================
     PESTAÑA 1: EMPRESAS MATRIZ Y RED DE SUCURSALES
     ========================================================================= -->
<div id="tab-matrices" class="tab-content-panel" style="<?= ($activeTab === 'matrices' ? 'display:block;' : 'display:none;') ?>">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-block; width: 10px; height: 10px; background: var(--accent-emerald); border-radius: 2px;"></span>
            Estructura Corporativa: Empresa Matriz $\rightarrow$ Red de Sucursales
        </h2>
        <?php if ($isAdmin || $isTech): ?>
        <button class="btn btn-sm btn-primary" onclick="openModal('modal-crear-matriz')">+ Registrar Nueva Empresa Matriz</button>
        <?php endif; ?>
    </div>

    <?php if (empty($matrices)): ?>
        <div class="panel-card" style="text-align: center; color: var(--text-muted); padding: 40px;">
            No hay empresas matrices registradas. Haga clic en "+ Alta Matriz" para agregar la primera.
        </div>
    <?php else: ?>
        <?php foreach ($matrices as $mat): ?>
            <?php 
                $sucsMatriz = DataStore::getSucursales($mat['id']);
                $sucIds = array_column($sucsMatriz, 'id');
                $eqCountMat = count(array_filter($equipos, fn($e) => in_array($e['id_sucursal'] ?? 0, $sucIds)));
                $userCountMat = count(array_filter($usuariosClientes, fn($u) => ($u['id_matriz'] ?? 0) == $mat['id'] || in_array($u['id_sucursal'] ?? 0, $sucIds)));
            ?>
            <div class="tree-card" style="margin-bottom: 24px;">
                <!-- CABECERA DE LA MATRIZ -->
                <div class="tree-matriz-header" style="background: #0d1a38; border-bottom: 1px solid var(--border-subtle); padding: 18px 20px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <h3 style="font-size: 16px; font-weight: 800; color: #fff;"><?= Security::e($mat['razon_social']) ?></h3>
                            <span class="badge-status status-operativo">EMPRESA MATRIZ</span>
                            <span class="code-badge" style="background: #1e293b; color: #94a3b8;"><?= count($sucsMatriz) ?> Sucursales</span>
                            <span class="code-badge" style="background: #1e293b; color: #38bdf8;"><?= $eqCountMat ?> Elevadores</span>
                            <span class="code-badge" style="background: #1e293b; color: #34d399;"><?= $userCountMat ?> Cuentas Portal</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px; display: flex; gap: 20px; flex-wrap: wrap;">
                            <span><strong>RFC:</strong> <span style="font-family: var(--font-mono); color: #fff;"><?= Security::e($mat['rfc']) ?></span></span>
                            <span><strong>Domicilio Fiscal:</strong> <?= Security::e($mat['direccion']) ?></span>
                            <span><strong>Teléfono:</strong> <?= Security::e($mat['telefono']) ?></span>
                            <span><strong>Email Oficial:</strong> <?= Security::e($mat['email']) ?></span>
                        </div>
                    </div>

                    <?php if ($isAdmin || $isTech): ?>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button class="btn btn-sm btn-dark" onclick='openEditarMatriz(<?= json_encode($mat, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                            ✏️ Editar
                        </button>
                        <button class="btn btn-sm btn-cyan" onclick="openModalVincularConMatriz(<?= $mat['id'] ?>)">
                            + Sucursal
                        </button>
                        <?php if ($isAdmin): ?>
                        <button class="btn btn-sm btn-danger" onclick="confirmarEliminar('eliminar_matriz', <?= $mat['id'] ?>, '<?= Security::e(addslashes($mat['razon_social'])) ?>')">
                            🗑️
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- SUCURSALES HIJAS -->
                <div class="branches-grid" style="padding: 16px 20px;">
                    <?php if (empty($sucsMatriz)): ?>
                        <div style="color: var(--text-muted); font-size: 13px; padding: 10px;">
                            No hay sucursales registradas para esta matriz. Haga clic en "+ Sucursal" para vincular una sede.
                        </div>
                    <?php else: ?>
                        <?php foreach ($sucsMatriz as $suc): ?>
                            <?php 
                                $eqCount = count(array_filter($equipos, fn($e) => ($e['id_sucursal'] ?? 0) == $suc['id']));
                                $userSuc = array_filter($usuariosClientes, fn($u) => ($u['id_sucursal'] ?? 0) == $suc['id']);
                            ?>
                            <div class="branch-subcard" style="background: #081226; border: 1px solid #1c2e56;">
                                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #16284a; padding-bottom: 8px;">
                                    <h4 style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                        <?= Security::e($suc['nombre']) ?>
                                    </h4>
                                    <div style="display: flex; gap: 4px;">
                                        <span class="nav-badge" style="background: #0284c7; color: #fff;"><?= $eqCount ?> Rampas</span>
                                    </div>
                                </div>

                                <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.6; margin-top: 10px;">
                                    <div>📍 <?= Security::e($suc['direccion']) ?></div>
                                    <div>📞 <?= Security::e($suc['telefono']) ?></div>
                                    <?php if (!empty($suc['gerente_servicio'])): ?>
                                        <div>👤 <strong>Gerente Servicio:</strong> <?= Security::e($suc['gerente_servicio']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($suc['jefe_taller'])): ?>
                                        <div>🔧 <strong>Jefe Taller:</strong> <?= Security::e($suc['jefe_taller']) ?></div>
                                    <?php endif; ?>
                                    <div style="margin-top: 4px; font-size: 11px; color: #38bdf8;">
                                        👥 <strong>Accesos al Portal:</strong> <?= count($userSuc) ?> cuentas activas
                                    </div>
                                </div>

                                <div style="margin-top: 14px; padding-top: 8px; border-top: 1px solid #16284a; display: flex; justify-content: space-between; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <div style="display: flex; gap: 6px;">
                                        <a href="index.php?view=equipos&sucursal_id=<?= $suc['id'] ?>" class="btn btn-sm btn-dark" style="font-size: 11px;">
                                            Ver Rampas (<?= $eqCount ?>)
                                        </a>
                                        <?php if ($isAdmin || $isTech): ?>
                                        <button class="btn btn-sm btn-cyan" onclick='openEditarSucursal(<?= json_encode($suc, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="font-size: 11px;" title="Editar datos de sucursal">
                                            ✏️
                                        </button>
                                        <?php if ($isAdmin): ?>
                                        <button class="btn btn-sm btn-danger" onclick="confirmarEliminar('eliminar_sucursal', <?= $suc['id'] ?>, '<?= Security::e(addslashes($suc['nombre'])) ?>')" style="font-size: 11px;" title="Eliminar sucursal">
                                            🗑️
                                        </button>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($isAdmin || $isTech): ?>
                                    <button class="btn btn-sm btn-primary" onclick="openModalCrearEquipoConSucursal(<?= $suc['id'] ?>)" style="font-size: 11px;">
                                        + Agregar Rampa
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- =========================================================================
     PESTAÑA 2: TALLERES INDEPENDIENTES (LOCAL ÚNICO)
     ========================================================================= -->
<div id="tab-talleres" class="tab-content-panel" style="<?= ($activeTab === 'talleres' ? 'display:block;' : 'display:none;') ?>">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-block; width: 10px; height: 10px; background: var(--accent-cyan-light); border-radius: 2px;"></span>
            Talleres Mecánicos y Centros de Servicio Independientes (Local Único)
        </h2>
        <?php if ($isAdmin || $isTech): ?>
        <button class="btn btn-sm btn-primary" onclick="openModal('modal-crear-taller')">+ Registrar Taller Independiente</button>
        <?php endif; ?>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        <?php if (empty($talleres)): ?>
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px;" class="panel-card">
                No hay talleres independientes registrados.
            </div>
        <?php else: ?>
            <?php foreach ($talleres as $tal): ?>
                <?php 
                    $eqCount = count(array_filter($equipos, fn($e) => ($e['id_taller'] ?? 0) == $tal['id']));
                    $userTal = array_filter($usuariosClientes, fn($u) => ($u['id_taller'] ?? 0) == $tal['id']);
                ?>
                <div class="panel-card" style="background: #091326; border: 1px solid var(--border-subtle);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #16284a; padding-bottom: 12px; margin-bottom: 12px;">
                        <div>
                            <h3 style="font-size: 15px; font-weight: 800; color: #fff;"><?= Security::e($tal['razon_social']) ?></h3>
                            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">
                                RFC: <span style="font-family: var(--font-mono); color: #fff;"><?= Security::e($tal['rfc']) ?></span>
                            </div>
                        </div>
                        <span class="badge-status status-observado" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border-color: rgba(14, 165, 233, 0.3);">
                            TALLER ÚNICO
                        </span>
                    </div>

                    <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.7;">
                        <div>📍 <strong>Ubicación:</strong> <?= Security::e($tal['direccion']) ?></div>
                        <div>📞 <strong>Teléfono:</strong> <?= Security::e($tal['telefono']) ?></div>
                        <div>✉️ <strong>Email:</strong> <?= Security::e($tal['email']) ?></div>
                        <?php if (!empty($tal['gerente_servicio'])): ?>
                            <div>👤 <strong>Gerente de Servicio:</strong> <?= Security::e($tal['gerente_servicio']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($tal['jefe_taller'])): ?>
                            <div>🔧 <strong>Jefe de Taller:</strong> <?= Security::e($tal['jefe_taller']) ?></div>
                        <?php endif; ?>
                        <div style="margin-top: 6px; padding: 6px 10px; background: #060e1e; border-radius: 4px; font-size: 11px; display: flex; justify-content: space-between;">
                            <span>Rampas Asignadas: <strong><?= $eqCount ?></strong></span>
                            <span>Accesos Portal: <strong><?= count($userTal) ?></strong></span>
                        </div>
                    </div>

                    <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #16284a; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 6px;">
                            <a href="index.php?view=equipos&taller_id=<?= $tal['id'] ?>" class="btn btn-sm btn-dark" style="font-size: 11px;">
                                Ver Equipos (<?= $eqCount ?>)
                            </a>
                            <?php if ($isAdmin || $isTech): ?>
                            <button class="btn btn-sm btn-cyan" onclick='openEditarTaller(<?= json_encode($tal, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="font-size: 11px;" title="Editar taller">
                                ✏️
                            </button>
                            <?php if ($isAdmin): ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmarEliminar('eliminar_taller', <?= $tal['id'] ?>, '<?= Security::e(addslashes($tal['razon_social'])) ?>')" style="font-size: 11px;" title="Eliminar taller">
                                🗑️
                            </button>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($isAdmin || $isTech): ?>
                        <button class="btn btn-sm btn-primary" onclick="openModalCrearEquipoConTaller(<?= $tal['id'] ?>)" style="font-size: 11px;">
                            + Agregar Rampa
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- =========================================================================
     PESTAÑA 3: DIRECTORIO GENERAL DE SUCURSALES (CONSOLIDADO)
     ========================================================================= -->
<div id="tab-sucursales" class="tab-content-panel" style="<?= ($activeTab === 'sucursales' ? 'display:block;' : 'display:none;') ?>">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-block; width: 10px; height: 10px; background: #38bdf8; border-radius: 2px;"></span>
            Directorio Nacional de Sucursales y Agencias
        </h2>
        <?php if ($isAdmin || $isTech): ?>
        <button class="btn btn-sm btn-cyan" onclick="openModal('modal-vincular-sucursal')">+ Vincular Nueva Sucursal</button>
        <?php endif; ?>
    </div>

    <div class="panel-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sucursal / Bahía</th>
                        <th>Empresa Matriz</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Responsables en Bahía</th>
                        <th>Equipos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sucursales)): ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">No hay sucursales registradas.</td></tr>
                    <?php else: ?>
                        <?php 
                        $matsById = array_column($matrices, null, 'id');
                        foreach ($sucursales as $s): 
                            $matName = $matsById[$s['id_matriz']]['razon_social'] ?? 'Matriz';
                            $eqCount = count(array_filter($equipos, fn($e) => ($e['id_sucursal'] ?? 0) == $s['id']));
                        ?>
                        <tr>
                            <td><strong><?= Security::e($s['nombre']) ?></strong></td>
                            <td><span class="code-badge"><?= Security::e($matName) ?></span></td>
                            <td style="font-size: 12px; color: var(--text-secondary);"><?= Security::e($s['direccion']) ?></td>
                            <td style="font-size: 12px;"><?= Security::e($s['telefono']) ?></td>
                            <td style="font-size: 11px;">
                                <div>Gerente: <?= Security::e($s['gerente_servicio'] ?? 'N/A') ?></div>
                                <div>Jefe Taller: <?= Security::e($s['jefe_taller'] ?? 'N/A') ?></div>
                            </td>
                            <td>
                                <a href="index.php?view=equipos&sucursal_id=<?= $s['id'] ?>" class="nav-badge" style="background: #0284c7; color: #fff; text-decoration: none;">
                                    <?= $eqCount ?> Elevadores
                                </a>
                            </td>
                            <td>
                                <?php if ($isAdmin || $isTech): ?>
                                <div style="display: flex; gap: 6px;">
                                    <button class="btn btn-sm btn-dark" onclick='openEditarSucursal(<?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Editar sucursal">
                                        ✏️
                                    </button>
                                    <?php if ($isAdmin): ?>
                                    <button class="btn btn-sm btn-danger" onclick="confirmarEliminar('eliminar_sucursal', <?= $s['id'] ?>, '<?= Security::e(addslashes($s['nombre'])) ?>')" title="Eliminar">
                                        🗑️
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span style="font-size: 11px; color: var(--text-muted);">Solo lectura</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     PESTAÑA 4: USUARIOS Y ACCESOS DE CLIENTES AL PORTAL
     ========================================================================= -->
<div id="tab-usuarios" class="tab-content-panel" style="<?= ($activeTab === 'usuarios' ? 'display:block;' : 'display:none;') ?>">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-block; width: 10px; height: 10px; background: #10b981; border-radius: 2px;"></span>
            Cuentas y Accesos de Clientes al Portal (Autogestión de Credenciales)
        </h2>
        <?php if ($isAdmin): ?>
        <button class="btn btn-sm btn-emerald" style="background:#059669;color:#fff;border-color:#047857;" onclick="openModal('modal-crear-usuario-cliente')">
            + Dar de Alta Nuevo Acceso de Cliente
        </button>
        <?php endif; ?>
    </div>

    <div class="panel-card">
        <div style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.25); border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; font-size: 12px; color: #38bdf8;">
            ℹ️ <strong>Autogestión Perimetral:</strong> Cada usuario cliente tiene acceso restringido exclusivamente a las rampas, órdenes y reportes de su sucursal o taller asignado (Prevención IDOR OWASP A01).
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre y Apellido</th>
                        <th>Email (Login)</th>
                        <th>Teléfono</th>
                        <th>Empresa / Sede Asignada</th>
                        <th>Rol</th>
                        <th>Acciones de Administrador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuariosClientes)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">No hay cuentas de clientes registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuariosClientes as $u): ?>
                        <tr>
                            <td>
                                <strong><?= Security::e($u['nombre']) ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);"><?= Security::e($u['alias']) ?></div>
                            </td>
                            <td>
                                <span style="font-family: var(--font-mono); color: #fff; font-size: 12px;"><?= Security::e($u['email']) ?></span>
                            </td>
                            <td style="font-size: 12px;"><?= Security::e($u['telefono'] ?? 'N/A') ?></td>
                            <td>
                                <span class="code-badge" style="background: #0f274a; color: #38bdf8; border: 1px solid #1e4976;">
                                    📍 <?= Security::e($u['entidad_nombre']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status status-operativo" style="font-size: 10px;">CLIENTE PORTAL</span>
                            </td>
                            <td>
                                <?php if ($isAdmin): ?>
                                <div style="display: flex; gap: 6px; align-items: center;">
                                    <button class="btn btn-sm btn-dark" onclick='openEditarUsuario(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Editar usuario / Cambiar contraseña">
                                        ✏️ Editar / Clave
                                    </button>
                                    <a href="index.php?action=switch_role&role=cliente" class="btn btn-sm btn-cyan" title="Simular la sesión de cliente para pruebas" style="font-size: 11px;">
                                        👁️ Probar Vista
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmarEliminar('eliminar_usuario_cliente', <?= $u['id'] ?>, '<?= Security::e(addslashes($u['nombre'])) ?>')" title="Eliminar acceso">
                                        🗑️
                                    </button>
                                </div>
                                <?php else: ?>
                                <span style="font-size: 11px; color: var(--text-muted);">Solo administrador</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODALES PARA AUTOGESTIÓN COMPLETA (EDICIÓN Y ALTA)
     ========================================================================= -->

<!-- MODAL: EDITAR EMPRESA MATRIZ -->
<div id="modal-editar-matriz" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Editar Empresa Matriz</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=editar_matriz">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit-matriz-id" value="" />
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Razón Social *</label>
                        <input type="text" name="razon_social" id="edit-matriz-razon" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC Fiscal *</label>
                        <input type="text" name="rfc" id="edit-matriz-rfc" class="form-control" required />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Dirección Fiscal *</label>
                        <input type="text" name="direccion" id="edit-matriz-direccion" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono Oficial *</label>
                        <input type="text" name="telefono" id="edit-matriz-telefono" class="form-control" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Correo Electrónico Oficial *</label>
                    <input type="email" name="email" id="edit-matriz-email" class="form-control" required />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios de Matriz</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDITAR SUCURSAL -->
<div id="modal-editar-sucursal" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Editar Sucursal / Agencia</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=editar_sucursal">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit-sucursal-id" value="" />
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Empresa Matriz Vinculada *</label>
                    <select name="id_matriz" id="edit-sucursal-matriz" class="form-control" required>
                        <?php foreach ($matrices as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= Security::e($m['razon_social']) ?> (RFC: <?= Security::e($m['rfc']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre de la Sucursal *</label>
                        <input type="text" name="nombre" id="edit-sucursal-nombre" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono *</label>
                        <input type="text" name="telefono" id="edit-sucursal-telefono" class="form-control" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección Completa *</label>
                    <input type="text" name="direccion" id="edit-sucursal-direccion" class="form-control" required />
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gerente de Servicio</label>
                        <input type="text" name="gerente_servicio" id="edit-sucursal-gerente" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jefe de Taller / Bahía</label>
                        <input type="text" name="jefe_taller" id="edit-sucursal-jefe" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-cyan">Guardar Cambios de Sucursal</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDITAR TALLER INDEPENDIENTE -->
<div id="modal-editar-taller" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Editar Taller Independiente</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=editar_taller">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit-taller-id" value="" />
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Razón Social *</label>
                        <input type="text" name="razon_social" id="edit-taller-razon" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC *</label>
                        <input type="text" name="rfc" id="edit-taller-rfc" class="form-control" required />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono *</label>
                        <input type="text" name="telefono" id="edit-taller-telefono" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" name="email" id="edit-taller-email" class="form-control" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección Completa *</label>
                    <input type="text" name="direccion" id="edit-taller-direccion" class="form-control" required />
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gerente de Servicio</label>
                        <input type="text" name="gerente_servicio" id="edit-taller-gerente" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jefe de Taller</label>
                        <input type="text" name="jefe_taller" id="edit-taller-jefe" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios de Taller</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: CREAR USUARIO / ACCESO CLIENTE -->
<div id="modal-crear-usuario-cliente" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Dar de Alta Cuenta de Cliente para el Portal</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_usuario_cliente">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre Completo del Responsable *</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Lic. Rodrigo Mendoza" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alias o Cargo *</label>
                        <input type="text" name="alias" class="form-control" required placeholder="Ej: Gerente de Servicio" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico (Será su Usuario) *</label>
                        <input type="email" name="email" class="form-control" required placeholder="rmendoza@agencia.com" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña de Acceso Temporal *</label>
                        <input type="text" name="password" class="form-control" required value="Tcs_<?= rand(1000, 9999) ?>!" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono Móvil (WhatsApp)</label>
                        <input type="text" name="telefono" class="form-control" placeholder="55-1234-5678" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC / Identificación</label>
                        <input type="text" name="rfc" class="form-control" placeholder="MENR800101-XXX" />
                    </div>
                </div>

                <div style="background: #070e1e; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 14px; margin-top: 10px;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label class="form-label">Tipo de Entidad a Vincular *</label>
                        <select name="destino_tipo" class="form-control" id="crear-user-destino-tipo" onchange="toggleUserDestino(this.value, 'crear')">
                            <option value="sucursal">Sucursal de Empresa Matriz</option>
                            <option value="taller">Taller Independiente</option>
                        </select>
                    </div>

                    <div class="form-group" id="crear-user-group-sucursal">
                        <label class="form-label">Seleccionar Sucursal de la Matriz *</label>
                        <select name="id_sucursal" class="form-control">
                            <?php foreach ($sucursales as $s): ?>
                                <?php $matName = $matsById[$s['id_matriz']]['razon_social'] ?? 'Matriz'; ?>
                                <option value="<?= $s['id'] ?>"><?= Security::e($s['nombre']) ?> — (<?= Security::e($matName) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="crear-user-group-taller" style="display: none;">
                        <label class="form-label">Seleccionar Taller Independiente *</label>
                        <select name="id_taller" class="form-control">
                            <?php foreach ($talleres as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= Security::e($t['razon_social']) ?> (RFC: <?= Security::e($t['rfc']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-emerald" style="background:#059669;color:#fff;border-color:#047857;">Crear Cuenta de Cliente</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDITAR USUARIO / CAMBIAR CONTRASEÑA -->
<div id="modal-editar-usuario-cliente" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Editar Cuenta de Acceso & Restablecer Clave</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=editar_usuario_cliente">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit-user-id" value="" />
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" name="nombre" id="edit-user-nombre" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alias / Cargo *</label>
                        <input type="text" name="alias" id="edit-user-alias" class="form-control" required />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico (Login) *</label>
                        <input type="email" name="email" id="edit-user-email" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nueva Contraseña (Dejar en blanco para no cambiar)</label>
                        <input type="text" name="password" id="edit-user-password" class="form-control" placeholder="Nueva clave segura..." />
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono Móvil</label>
                        <input type="text" name="telefono" id="edit-user-telefono" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC</label>
                        <input type="text" name="rfc" id="edit-user-rfc" class="form-control" />
                    </div>
                </div>

                <div style="background: #070e1e; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 14px; margin-top: 10px;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label class="form-label">Tipo de Entidad Asignada *</label>
                        <select name="destino_tipo" class="form-control" id="edit-user-destino-tipo" onchange="toggleUserDestino(this.value, 'edit')">
                            <option value="sucursal">Sucursal de Empresa Matriz</option>
                            <option value="taller">Taller Independiente</option>
                        </select>
                    </div>

                    <div class="form-group" id="edit-user-group-sucursal">
                        <label class="form-label">Sucursal Asignada *</label>
                        <select name="id_sucursal" id="edit-user-id-sucursal" class="form-control">
                            <?php foreach ($sucursales as $s): ?>
                                <?php $matName = $matsById[$s['id_matriz']]['razon_social'] ?? 'Matriz'; ?>
                                <option value="<?= $s['id'] ?>"><?= Security::e($s['nombre']) ?> — (<?= Security::e($matName) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="edit-user-group-taller" style="display: none;">
                        <label class="form-label">Taller Asignado *</label>
                        <select name="id_taller" id="edit-user-id-taller" class="form-control">
                            <?php foreach ($talleres as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= Security::e($t['razon_social']) ?> (RFC: <?= Security::e($t['rfc']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios de Cuenta</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(tabId, btn) {
    document.querySelectorAll('.tab-content-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    const panel = document.getElementById(tabId);
    if (panel) panel.style.display = 'block';
    if (btn) btn.classList.add('active');
}

function openEditarMatriz(mat) {
    document.getElementById('edit-matriz-id').value = mat.id || '';
    document.getElementById('edit-matriz-razon').value = mat.razon_social || '';
    document.getElementById('edit-matriz-rfc').value = mat.rfc || '';
    document.getElementById('edit-matriz-direccion').value = mat.direccion || '';
    document.getElementById('edit-matriz-telefono').value = mat.telefono || '';
    document.getElementById('edit-matriz-email').value = mat.email || '';
    openModal('modal-editar-matriz');
}

function openEditarSucursal(suc) {
    document.getElementById('edit-sucursal-id').value = suc.id || '';
    document.getElementById('edit-sucursal-matriz').value = suc.id_matriz || '';
    document.getElementById('edit-sucursal-nombre').value = suc.nombre || '';
    document.getElementById('edit-sucursal-telefono').value = suc.telefono || '';
    document.getElementById('edit-sucursal-direccion').value = suc.direccion || '';
    document.getElementById('edit-sucursal-gerente').value = suc.gerente_servicio || '';
    document.getElementById('edit-sucursal-jefe').value = suc.jefe_taller || '';
    openModal('modal-editar-sucursal');
}

function openEditarTaller(tal) {
    document.getElementById('edit-taller-id').value = tal.id || '';
    document.getElementById('edit-taller-razon').value = tal.razon_social || '';
    document.getElementById('edit-taller-rfc').value = tal.rfc || '';
    document.getElementById('edit-taller-telefono').value = tal.telefono || '';
    document.getElementById('edit-taller-email').value = tal.email || '';
    document.getElementById('edit-taller-direccion').value = tal.direccion || '';
    document.getElementById('edit-taller-gerente').value = tal.gerente_servicio || '';
    document.getElementById('edit-taller-jefe').value = tal.jefe_taller || '';
    openModal('modal-editar-taller');
}

function openEditarUsuario(u) {
    document.getElementById('edit-user-id').value = u.id || '';
    document.getElementById('edit-user-nombre').value = u.nombre || '';
    document.getElementById('edit-user-alias').value = u.alias || '';
    document.getElementById('edit-user-email').value = u.email || '';
    document.getElementById('edit-user-password').value = '';
    document.getElementById('edit-user-telefono').value = u.telefono || '';
    document.getElementById('edit-user-rfc').value = u.rfc || '';

    const isTaller = Boolean(u.id_taller && !u.id_sucursal);
    const destinoSelect = document.getElementById('edit-user-destino-tipo');
    destinoSelect.value = isTaller ? 'taller' : 'sucursal';
    toggleUserDestino(destinoSelect.value, 'edit');

    if (isTaller) {
        document.getElementById('edit-user-id-taller').value = u.id_taller || '';
    } else {
        document.getElementById('edit-user-id-sucursal').value = u.id_sucursal || '';
    }

    openModal('modal-editar-usuario-cliente');
}

function toggleUserDestino(val, prefix) {
    const groupSuc = document.getElementById(prefix + '-user-group-sucursal');
    const groupTal = document.getElementById(prefix + '-user-group-taller');
    if (val === 'taller') {
        if (groupSuc) groupSuc.style.display = 'none';
        if (groupTal) groupTal.style.display = 'block';
    } else {
        if (groupSuc) groupSuc.style.display = 'block';
        if (groupTal) groupTal.style.display = 'none';
    }
}

function openModalVincularConMatriz(matrizId) {
    const sel = document.querySelector('#modal-vincular-sucursal select[name="id_matriz"]');
    if (sel) sel.value = matrizId;
    openModal('modal-vincular-sucursal');
}

function openModalCrearEquipoConSucursal(sucursalId) {
    const destSel = document.getElementById('select-destino-tipo');
    if (destSel) {
        destSel.value = 'sucursal';
        toggleDestinoFields('sucursal');
    }
    const sucSel = document.querySelector('#modal-crear-equipo select[name="id_sucursal"]');
    if (sucSel) sucSel.value = sucursalId;
    openModal('modal-crear-equipo');
}

function openModalCrearEquipoConTaller(tallerId) {
    const destSel = document.getElementById('select-destino-tipo');
    if (destSel) {
        destSel.value = 'taller';
        toggleDestinoFields('taller');
    }
    const talSel = document.querySelector('#modal-crear-equipo select[name="id_taller"]');
    if (talSel) talSel.value = tallerId;
    openModal('modal-crear-equipo');
}

function confirmarEliminar(action, id, nombre) {
    if (confirm(`¿Está seguro de eliminar el registro "${nombre}"? Esta acción no se puede deshacer.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=' + action;

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

<style>
.tab-btn {
    background: transparent;
    border: 1px solid var(--border-subtle);
    color: var(--text-secondary);
    padding: 10px 18px;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all var(--transition-fast);
    white-space: nowrap;
}
.tab-btn:hover {
    color: #fff;
    border-color: var(--accent-cyan);
}
.tab-btn.active {
    background: rgba(14, 165, 233, 0.15);
    color: #38bdf8;
    border-color: #38bdf8;
    box-shadow: 0 0 12px rgba(56, 189, 248, 0.2);
}
</style>
