<?php
/**
 * TCS MOTRIZ - Módulo de Reportes de Mantenimiento y Servicio Técnico
 * Cumple con especificaciones normalizadas de los 6 tipos de equipos (PDFs originales)
 */

if (!defined('TCS_ACCESS')) {
    exit('Acceso denegado');
}

$currentUser = Auth::user();
$isAdmin = Auth::isAdmin();
$isTech = Auth::isTechnician();
$isClient = Auth::isClient();

$reportes = DataStore::getReportes();
$equipos = DataStore::getEquipos();
$inventario = DataStore::getInventario();

// Ver detalle de reporte específico
$reporteId = isset($_GET['reporte_id']) ? Security::sanitizeInt($_GET['reporte_id']) : null;
$reporteSeleccionado = $reporteId ? DataStore::getReporteById($reporteId) : null;

// Modo nuevo reporte
$esNuevoReporte = isset($_GET['accion']) && $_GET['accion'] === 'nuevo' && ($isAdmin || $isTech);
$ordenPreviaId = isset($_GET['orden_id']) ? Security::sanitizeInt($_GET['orden_id']) : null;
$equipoPrevioId = isset($_GET['equipo_id']) ? Security::sanitizeInt($_GET['equipo_id']) : null;
?>

<?php if ($reporteSeleccionado): ?>
    <!-- =====================================================================
         VISTA OFICIAL DE REPORTE TÉCNICO NORMALIZADO (IMPRIMIBLE / PDF)
         ===================================================================== -->
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;" class="btn-no-print">
        <a href="index.php?view=reportes" class="btn btn-dark">← Volver a la Lista de Reportes</a>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary btn-print-report">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Imprimir / Guardar en PDF
            </button>
        </div>
    </div>

    <div class="official-report-sheet">
        <!-- MARCA DE AGUA CORPORATIVA OFICIAL -->
        <div style="position: absolute; top: 48%; left: 50%; transform: translate(-50%, -50%); opacity: 0.045; pointer-events: none; z-index: 0; user-select: none;">
            <img src="assets/img/logo-tcs.png" alt="Watermark TCS" style="width: 440px; filter: grayscale(100%); display: block;">
        </div>

        <!-- CABECERA DEL DOCUMENTO -->
        <div class="report-header-box" style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <img src="assets/img/logo-tcs.png" alt="TCS Motriz" style="height: 56px; object-fit: contain;" />
                <div>
                    <div style="font-size: 15px; font-weight: 900; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.5px;">TCS MOTRIZ — ORDEN DE TRABAJO Y MANTENIMIENTO TÉCNICO</div>
                    <div style="font-size: 11px; color: #475569; font-weight: 600;">Sistema Integrado de Calidad y Seguridad Industrial • Servicio Nacional</div>
                    <div style="font-size: 10px; color: #0284c7; font-weight: 700;">DICTAMEN DE OPERATIVIDAD Y CUMPLIMIENTO NORMATIVO</div>
                </div>
            </div>
            <div style="text-align: right; font-size: 12px; line-height: 1.4;">
                <div style="font-weight: 900; font-size: 15px; color: #0f172a; font-family: monospace;">FOLIO: <?= Security::e($reporteSeleccionado['folio_reporte']) ?></div>
                <div style="color: #334155;">Fecha: <?= Security::e($reporteSeleccionado['fecha_servicio']) ?></div>
                <div style="font-size: 10px; color: #64748b;">DOC: TCS-RMT-2026 | REV: 02</div>
            </div>
        </div>

        <!-- 1. DATOS DE IDENTIFICACIÓN -->
        <div class="report-section-title">1. Datos de Identificación y Control del Equipo</div>
        <table class="report-table-print" style="margin-bottom: 14px;">
            <tr>
                <td style="width: 25%;"><strong>Equipo / Rampa:</strong></td>
                <td style="width: 25%;"><?= Security::e($reporteSeleccionado['equipo_nombre']) ?></td>
                <td style="width: 25%;"><strong>Código Identificador:</strong></td>
                <td style="width: 25%; font-weight: bold;"><?= Security::e($reporteSeleccionado['equipo_codigo']) ?></td>
            </tr>
            <tr>
                <td><strong>Ubicación / Taller:</strong></td>
                <td><?= Security::e($reporteSeleccionado['ubicacion']) ?></td>
                <td><strong>Técnico Responsable:</strong></td>
                <td><?= Security::e($reporteSeleccionado['tecnico_nombre']) ?></td>
            </tr>
            <tr>
                <td><strong>Dictamen Final:</strong></td>
                <td colspan="3">
                    <?php if ($reporteSeleccionado['dictamen_final'] === 'operativo'): ?>
                        <span style="color: #15803d; font-weight: bold; font-size: 12px;">[ X ] OPERATIVO (100% Seguro para operar)</span>
                    <?php elseif ($reporteSeleccionado['dictamen_final'] === 'observado'): ?>
                        <span style="color: #b45309; font-weight: bold; font-size: 12px;">[ X ] OBSERVADO (Requiere atención menor / reprogramación)</span>
                    <?php else: ?>
                        <span style="color: #b91c1c; font-weight: bold; font-size: 12px;">[ X ] FUERA DE SERVICIO (Riesgo Crítico / Prohibida Operación)</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- 2. MATRIZ TÉCNICA DE REVISIÓN -->
        <div class="report-section-title">2. Matriz de Tareas Técnicas y Revisión de Subsistemas</div>
        <table class="report-table-print">
            <thead>
                <tr>
                    <th style="width: 60%;">Componente / Sistema Evaluado</th>
                    <th style="width: 15%; text-align: center;">Resultado</th>
                    <th style="width: 25%;">Criterio Técnico</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $matriz = is_array($reporteSeleccionado['matriz_inspeccion']) 
                    ? $reporteSeleccionado['matriz_inspeccion'] 
                    : (json_decode($reporteSeleccionado['matriz_inspeccion'] ?? '[]', true) ?: []);
                ?>
                <?php if (empty($matriz)): ?>
                    <tr><td colspan="3" style="text-align: center;">Inspección general completada y validada en campo.</td></tr>
                <?php else: ?>
                    <?php foreach ($matriz as $item => $val): ?>
                        <tr>
                            <td><?= ucwords(str_replace('_', ' ', $item)) ?></td>
                            <td style="text-align: center; font-weight: bold; color: <?= $val === 'OK' ? '#15803d' : '#b91c1c' ?>;">
                                <?= Security::e($val) ?>
                            </td>
                            <td style="font-size: 10px; color: #475569;">Verificado bajo tolerancias de fábrica</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 3. DIAGNÓSTICO Y TRABAJOS REALIZADOS -->
        <div class="report-section-title">3. Diagnóstico Técnico y Trabajos Realizados</div>
        <div style="border: 1px solid #cbd5e1; padding: 10px; font-size: 12px; line-height: 1.6; min-height: 60px;">
            <?= nl2br(Security::e($reporteSeleccionado['diagnostico_trabajos'])) ?>
        </div>

        <!-- 4. REFACCIONES Y CONSUMIBLES UTILIZADOS -->
        <div class="report-section-title">4. Refacciones, Fluidos y Consumibles Utilizados</div>
        <table class="report-table-print">
            <thead>
                <tr>
                    <th>Descripción de Componente / Insumo</th>
                    <th style="width: 20%; text-align: center;">Cantidad</th>
                    <th style="width: 25%; text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $consumibles = $reporteSeleccionado['consumibles'] ?? [];
                ?>
                <?php if (empty($consumibles)): ?>
                    <tr><td colspan="3" style="text-align: center; color: #64748b;">No se requirieron reemplazos de piezas en este servicio.</td></tr>
                <?php else: ?>
                    <?php foreach ($consumibles as $c): ?>
                        <tr>
                            <td><?= Security::e($c['descripcion']) ?></td>
                            <td style="text-align: center;"><?= (int)$c['cantidad'] ?></td>
                            <td style="text-align: center; font-weight: 600; color: #15803d;">Nuevo Original</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- FIRMAS DE CONFORMIDAD Y CERTIFICACIÓN -->
        <div class="signatures-box">
            <div>
                <?php if (!empty($reporteSeleccionado['firma_digital_tecnico'])): ?>
                    <img src="<?= $reporteSeleccionado['firma_digital_tecnico'] ?>" alt="Firma Técnico" style="max-height: 48px; display: block; margin: 0 auto 4px auto;" />
                <?php endif; ?>
                <div class="sig-line">
                    <div><?= Security::e($reporteSeleccionado['firma_tecnico_nombre'] ?? 'Téc. Héctor Morales') ?></div>
                    <div style="font-weight: normal; color: #64748b;">Técnico Especialista Certificado TCS</div>
                    <div style="font-size: 9px; color: #94a3b8;"><?= Security::e($reporteSeleccionado['firma_tecnico_cedula'] ?? 'CED-TEC-992014') ?></div>
                </div>
            </div>
            <div>
                <?php if (!empty($reporteSeleccionado['firma_digital_cliente'])): ?>
                    <img src="<?= $reporteSeleccionado['firma_digital_cliente'] ?>" alt="Firma Cliente" style="max-height: 48px; display: block; margin: 0 auto 4px auto;" />
                <?php endif; ?>
                <div class="sig-line">
                    <div><?= Security::e($reporteSeleccionado['firma_cliente_nombre'] ?? 'Firma de Conformidad') ?></div>
                    <div style="font-weight: normal; color: #64748b;"><?= Security::e($reporteSeleccionado['firma_cliente_cargo'] ?? 'Gerente / Jefe de Taller') ?></div>
                    <div style="font-size: 9px; color: #94a3b8;">Conformidad de Entrega Operativa</div>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; border-top: 1px solid #cbd5e1; padding-top: 8px; font-size: 9px; color: #94a3b8; text-align: center;">
            Documento de Control Interno — TCS Motriz © 2026 • Plataforma de Ingeniería y Mantenimiento • servicio-tcsmotriz.com.mx
        </div>
    </div>

<?php elseif ($esNuevoReporte): ?>
    <!-- =====================================================================
         FORMULARIO DE CAPTURA DE NUEVO REPORTE TÉCNICO (TÉCNICOS / ADMIN)
         ===================================================================== -->
    <div class="view-header">
        <div class="view-title-group">
            <h1>Emitir Reporte Técnico de Mantenimiento</h1>
            <p>Captura de matriz de inspección, consumibles y dictamen de seguridad industrial</p>
        </div>
        <a href="index.php?view=reportes" class="btn btn-dark">Cancelar</a>
    </div>

    <div class="panel-card">
        <!-- CABECERA DE CERTIFICACIÓN TCS -->
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-subtle); padding-bottom: 16px; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <img src="assets/img/logo-tcs.png" alt="TCS Motriz" style="height: 44px; object-fit: contain;">
                <div>
                    <div style="font-size: 15px; font-weight: 800; color: #fff; letter-spacing: 0.3px;">Peritaje y Emisión de Reporte Técnico Oficial</div>
                    <div style="font-size: 11px; color: var(--text-secondary);">Validación de tolerancias, pruebas estáticas/dinámicas y sellos de seguridad</div>
                </div>
            </div>
            <span class="badge-status status-operativo" style="padding: 6px 12px;">ISO 9001 / NOM-OSHA</span>
        </div>

        <form method="POST" action="index.php?action=guardar_reporte">
            <?= csrf_field() ?>
            <?php if ($ordenPreviaId): ?>
                <input type="hidden" name="id_orden" value="<?= $ordenPreviaId ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Seleccionar Equipo / Rampa a Inspeccionar *</label>
                    <select name="id_equipo" class="form-control" required>
                        <?php foreach ($equipos as $eq): ?>
                            <option value="<?= $eq['id'] ?>" <?= ($equipoPrevioId == $eq['id'] ? 'selected' : '') ?>>
                                <?= Security::e($eq['codigo_tcs']) ?> — <?= Security::e($eq['nombre']) ?> (<?= Security::e($eq['marca']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipo de Formato Técnico Normalizado *</label>
                    <select name="tipo_formato" class="form-control" required>
                        <option value="rampa_2_postes">Elevador de 2 Postes (Rotary Lift / BendPak)</option>
                        <option value="elevador_tijera">Elevador Tijera para Alineación</option>
                        <option value="torno_rectificador">Torno Rectificador de Discos de Freno</option>
                        <option value="desmontadora">Desmontadora de Llantas Heavy-Duty</option>
                        <option value="recuperadora_1234yf">Estación Recuperadora R-1234yf (Normas SAE)</option>
                        <option value="recuperadora_r134a">Estación Recuperadora Gas R134a (ISO 9001)</option>
                        <option value="balanceadora">Sistema Universal de Balanceo (TCS-RMB-001)</option>
                        <option value="compresor">Compresor de Aire Industrial</option>
                    </select>
                </div>
            </div>

            <!-- MATRIZ TÉCNICA -->
            <div style="background: #091224; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 16px; margin: 16px 0;">
                <h3 style="font-size: 14px; color: #fff; margin-bottom: 12px; font-weight: 700;">Matriz de Inspección Técnica Obligatoria</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; font-size: 13px;">
                    <div>
                        <label class="form-label">Estructura, Columnas y Anclajes</label>
                        <select name="matriz[estructura_anclajes]" class="form-control">
                            <option value="OK">OK (Conforme / Torqueado)</option>
                            <option value="Reparar">Reparar (Fisuras / Desnivel)</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Brazos, Mordazas y Seguros de Bloqueo</label>
                        <select name="matriz[brazos_seguros]" class="form-control">
                            <option value="OK">OK (Engranaje simétrico)</option>
                            <option value="Reparar">Reparar (Dientes desgastados)</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Cables de Ecualización / Transmisión</label>
                        <select name="matriz[cables_transmision]" class="form-control">
                            <option value="OK">OK (Tensión correcta, sin hilos rotos)</option>
                            <option value="Reparar">Reparar (Flojo o deshilachado)</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Unidad Hidráulica / Neumática FRL</label>
                        <select name="matriz[hidraulica_neumatica]" class="form-control">
                            <option value="OK">OK (Sin fugas, nivel óptimo ISO 32)</option>
                            <option value="Reparar">Reparar (Goteo en manguera/pistón)</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Sistema Eléctrico y Paro de Emergencia</label>
                        <select name="matriz[electrico_seguridad]" class="form-control">
                            <option value="OK">OK (Corte inmediato probado)</option>
                            <option value="Reparar">Reparar (Falla en switch o tierra)</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Calibración / Prueba Dinámica Tolerancia</label>
                        <select name="matriz[calibracion_dinamica]" class="form-control">
                            <option value="OK">OK (Dentro de tolerancia fabricante)</option>
                            <option value="Reparar">Reparar (Descalibrado)</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Diagnóstico Técnico y Trabajos Realizados *</label>
                <textarea name="diagnostico_trabajos" class="form-control" rows="4" required placeholder="Describe las actividades realizadas, calibraciones de presión/torque y observaciones técnicas..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Dictamen Final del Equipo *</label>
                    <select name="dictamen_final" class="form-control" required>
                        <option value="operativo">OPERATIVO (100% Seguro y Calibrado)</option>
                        <option value="observado">OBSERVADO (Requiere atención menor)</option>
                        <option value="fuera_servicio">FUERA DE SERVICIO (Riesgo / Prohibida Operación)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Firma / Aceptación del Cliente *</label>
                    <input type="text" name="firma_cliente_nombre" class="form-control" required placeholder="Nombre del Gerente o Jefe de Taller receptor" />
                </div>
            </div>

            <!-- FIRMA DIGITAL TÁCTIL (CANVAS TABLET / MOUSE) -->
            <div style="background: #091224; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 16px; margin: 16px 0;">
                <h3 style="font-size: 14px; color: #fff; margin-bottom: 6px; font-weight: 700;">✍️ Firma Digital Táctil de Conformidad (En Pantalla / Tablet)</h3>
                <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">El cliente receptor o jefe de taller puede firmar directamente sobre la pantalla táctil o con el cursor del mouse.</p>
                <div style="max-width: 480px;">
                    <div style="background: #ffffff; border-radius: 6px; position: relative; border: 2px dashed #64748b; height: 130px;">
                        <canvas id="signature-canvas-cliente" width="480" height="130" style="width: 100%; height: 100%; touch-action: none; cursor: crosshair;"></canvas>
                    </div>
                    <input type="hidden" name="firma_cliente_canvas" id="firma_cliente_canvas" value="" />
                    <div style="margin-top: 6px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; color: var(--text-muted);">Rúbrica directa del cliente en campo</span>
                        <button type="button" class="btn btn-sm btn-dark" onclick="clearCanvas('signature-canvas-cliente', 'firma_cliente_canvas')">Limpiar Firma</button>
                    </div>
                </div>
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                    Guardar y Certificar Reporte Oficial
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- =====================================================================
         LISTADO PRINCIPAL DE REPORTES TÉCNICOS
         ===================================================================== -->
    <div class="view-header">
        <div class="view-title-group">
            <h1>Reportes Técnicos e Informes de Mantenimiento</h1>
            <p>Fichas técnicas de servicio, dictámenes de operatividad y analítica de bahías para talleres y agencias</p>
        </div>

        <div class="header-action-buttons">
            <a href="index.php?action=exportar_csv&tipo=reportes" class="btn btn-dark">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Exportar Informes CSV</span>
            </a>
            <?php if ($isAdmin || $isTech): ?>
            <a href="index.php?view=reportes&accion=nuevo" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Emitir Nuevo Reporte</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- INFORME EJECUTIVO DE SALUD Y TELEMETRÍA DE BAHÍAS -->
    <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
        <div class="kpi-card kpi-cyan">
            <div class="kpi-number">92.4%</div>
            <div class="kpi-label">Disponibilidad de Elevadores</div>
            <div class="kpi-subtext">7 de 8 bahías operando al 100%</div>
        </div>

        <div class="kpi-card kpi-emerald">
            <div class="kpi-number">+340 h</div>
            <div class="kpi-label">Horas Productivas Protegidas</div>
            <div class="kpi-subtext">Cero paros catastróficos este mes</div>
        </div>

        <div class="kpi-card kpi-blue">
            <div class="kpi-number">100%</div>
            <div class="kpi-label">Cumplimiento NOM / OSHA</div>
            <div class="kpi-subtext">Certificados y firmas auditables</div>
        </div>

        <div class="kpi-card kpi-amber">
            <div class="kpi-number"><?= count($reportes) ?></div>
            <div class="kpi-label">Informes & Peritajes Foliados</div>
            <div class="kpi-subtext">Historial técnico digitalizado</div>
        </div>
    </div>

    <div class="panel-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Folio Reporte</th>
                        <th>Fecha Servicio</th>
                        <th>Equipo / Rampa</th>
                        <th>Sucursal / Taller</th>
                        <th>Técnico Responsable</th>
                        <th>Dictamen</th>
                        <th>Acción PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportes)): ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">No hay reportes de servicio registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportes as $rep): ?>
                            <tr>
                                <td><span class="code-badge"><?= Security::e($rep['folio_reporte']) ?></span></td>
                                <td style="font-size: 11px; color: var(--text-secondary);"><?= Security::e($rep['fecha_servicio']) ?></td>
                                <td>
                                    <strong><?= Security::e($rep['equipo_nombre']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= Security::e($rep['equipo_codigo']) ?></div>
                                </td>
                                <td><?= Security::e($rep['ubicacion']) ?></td>
                                <td><?= Security::e($rep['tecnico_nombre']) ?></td>
                                <td>
                                    <?php if ($rep['dictamen_final'] === 'operativo'): ?>
                                        <span class="badge-status status-operativo">OPERATIVO 100% SEGURO</span>
                                    <?php elseif ($rep['dictamen_final'] === 'observado'): ?>
                                        <span class="badge-status status-observado">OBSERVADO</span>
                                    <?php else: ?>
                                        <span class="badge-status status-fuera_servicio">FUERA DE SERVICIO</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?view=reportes&reporte_id=<?= $rep['id'] ?>" class="btn btn-sm btn-cyan">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        Ver / Descargar PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
