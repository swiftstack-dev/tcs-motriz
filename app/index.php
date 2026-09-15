<?php
/**
 * TCS MOTRIZ - Plataforma de Gestión Técnica y Telemetría Industrial
 * Enrutador Principal, Manejo de Acciones y Renderizado del Layout
 */

define('TCS_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/core/DataStore.php';
require_once __DIR__ . '/core/Auth.php';

// Enviar cabeceras HTTP perimetrales de seguridad (OWASP A05)
send_security_headers();

// Obtener usuario autenticado o simulación
$currentUser = Auth::user();

// =========================================================================
// MANEJADOR DE ACCIONES (POST / GET)
// =========================================================================
$action = $_GET['action'] ?? null;
$view = Security::sanitizeString($_GET['view'] ?? 'dashboard');

// Manejo de cambio de rol simulado (Para pruebas del cliente y demostración)
if ($action === 'switch_role') {
    $targetRole = Security::sanitizeString($_GET['role'] ?? 'admin');
    Auth::simulateRole($targetRole);
    header('Location: index.php?view=' . urlencode($view));
    exit;
}

// Exportación industrial de datos a CSV/Excel (Compatible con UTF-8 BOM e ISO 27001)
if ($action === 'exportar_csv') {
    $tipo = Security::sanitizeString($_GET['tipo'] ?? 'inventario');
    $filename = "tcs_motriz_{$tipo}_" . date('Ymd_His') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM para Excel

    if ($tipo === 'inventario') {
        fputcsv($out, ['Código Parte', 'Descripción', 'Categoría', 'Stock Actual', 'Stock Mínimo', 'Unidad Medida', 'Costo Unitario (MXN)', 'Proveedor']);
        $inv = DataStore::getInventario();
        foreach ($inv as $i) {
            fputcsv($out, [
                $i['codigo_parte'],
                $i['descripcion'],
                $i['categoria'],
                $i['stock_actual'],
                $i['stock_minimo'],
                $i['unidad_medida'],
                number_format($i['costo_unitario'], 2),
                $i['proveedor_nombre'] ?? 'N/A'
            ]);
        }
    } elseif ($tipo === 'ordenes') {
        fputcsv($out, ['Folio Orden', 'Fecha Solicitud', 'Equipo', 'Ubicación Bahía', 'Tipo Servicio', 'Prioridad', 'Estado', 'Solicitante']);
        $ords = DataStore::getOrdenes();
        foreach ($ords as $o) {
            fputcsv($out, [
                $o['folio_orden'],
                $o['fecha_solicitud'],
                $o['equipo_nombre'] ?? 'N/A',
                $o['ubicacion_bahia'] ?? 'N/A',
                $o['tipo_servicio'],
                $o['prioridad'],
                $o['estado'],
                $o['solicitante_nombre'] ?? 'N/A'
            ]);
        }
    } elseif ($tipo === 'equipos') {
        fputcsv($out, ['Código TCS', 'Nombre', 'Marca', 'Modelo', 'No. Serie', 'Capacidad', 'Ubicación Bahía', 'Estado Salud', 'Horas Operación']);
        $eqs = DataStore::getEquipos();
        foreach ($eqs as $e) {
            fputcsv($out, [
                $e['codigo_tcs'],
                $e['nombre'],
                $e['marca'],
                $e['modelo'],
                $e['numero_serie'],
                $e['capacidad'] ?? 'N/A',
                $e['ubicacion_bahia'] ?? 'N/A',
                $e['estado_salud'],
                $e['horas_uso'] ?? 0
            ]);
        }
    } elseif ($tipo === 'alertas') {
        fputcsv($out, ['ID Alerta', 'Severidad', 'Equipo / Código', 'Ubicación', 'Título', 'Mensaje', 'Fecha Detección', 'Acción Sugerida']);
        $alts = DataStore::getAlertas();
        foreach ($alts as $a) {
            fputcsv($out, [
                $a['id'],
                strtoupper($a['severidad']),
                $a['codigo_equipo'] ?? 'N/A',
                $a['ubicacion'] ?? 'N/A',
                $a['titulo'],
                $a['mensaje'],
                $a['fecha_deteccion'],
                $a['accion_sugerida']
            ]);
        }
    } elseif ($tipo === 'ordenes_facturacion') {
        fputcsv($out, [
            'Folio Orden',
            'Fecha Conclusión',
            'Cliente / Matriz',
            'Sucursal / Bahía',
            'Equipo / Identificador',
            'Tipo de Servicio',
            'Horas Mano Obra',
            'Tarifa Hora (MXN)',
            'Total Mano Obra (MXN)',
            'Refacciones / Materiales',
            'Costo Refacciones (MXN)',
            'Subtotal (MXN)',
            'IVA 16% (MXN)',
            'Total Facturable (MXN)',
            'Estatus de Cobro'
        ]);
        $ords = DataStore::getOrdenes();
        foreach ($ords as $o) {
            $isConcluido = ($o['estado'] === 'concluido');
            $horasMO = $isConcluido ? 4.5 : 2.0;
            $tarifa = 650.00;
            $subtotalMO = $horasMO * $tarifa;
            $costoRefac = $isConcluido ? 4180.00 : 0.00;
            $subtotal = $subtotalMO + $costoRefac;
            $iva = $subtotal * 0.16;
            $total = $subtotal + $iva;
            fputcsv($out, [
                $o['folio'],
                substr($o['fecha_solicitud'], 0, 10),
                'Grupo Automotriz Premier S.A. de C.V.',
                $o['ubicacion'] ?? 'Bahía General',
                ($o['equipo_nombre'] ?? 'Elevador') . ' [' . ($o['equipo_codigo'] ?? '') . ']',
                ucwords($o['tipo_servicio']),
                $horasMO,
                number_format($tarifa, 2, '.', ''),
                number_format($subtotalMO, 2, '.', ''),
                $isConcluido ? 'Juego Cables Ecualización + Fluido ISO 32' : 'Diagnóstico inicial',
                number_format($costoRefac, 2, '.', ''),
                number_format($subtotal, 2, '.', ''),
                number_format($iva, 2, '.', ''),
                number_format($total, 2, '.', ''),
                $isConcluido ? 'CONCILIADO - LISTO PARA FACTURAR' : 'EN EJECUCIÓN'
            ]);
        }
    } elseif ($tipo === 'clientes') {
        fputcsv($out, ['Tipo Entidad', 'Razón Social / Nombre', 'RFC', 'Dirección / Ubicación', 'Teléfono', 'Email', 'Gerente de Servicio', 'Jefe de Taller', 'Sucursales / Bahías', 'Equipos']);
        $mats = DataStore::getMatrices();
        $sucs = DataStore::getSucursales();
        $tals = DataStore::getTalleres();
        $eqs = DataStore::getEquipos();

        foreach ($mats as $m) {
            $misSucs = array_filter($sucs, fn($s) => ($s['id_matriz'] ?? 0) == $m['id']);
            $nombresSucs = implode(' | ', array_column($misSucs, 'nombre'));
            $eqCount = count(array_filter($eqs, fn($e) => in_array($e['id_sucursal'] ?? 0, array_column($misSucs, 'id'))));
            fputcsv($out, ['Empresa Matriz', $m['razon_social'], $m['rfc'], $m['direccion'], $m['telefono'], $m['email'], 'N/A', 'N/A', count($misSucs) . " sucursales: $nombresSucs", $eqCount]);
        }

        foreach ($sucs as $s) {
            $eqCount = count(array_filter($eqs, fn($e) => ($e['id_sucursal'] ?? 0) == $s['id']));
            fputcsv($out, ['Sucursal', $s['nombre'], 'N/A', $s['direccion'], $s['telefono'], 'N/A', $s['gerente_servicio'] ?? 'N/A', $s['jefe_taller'] ?? 'N/A', 'Bahía Local', $eqCount]);
        }

        foreach ($tals as $t) {
            $eqCount = count(array_filter($eqs, fn($e) => ($e['id_taller'] ?? 0) == $t['id']));
            fputcsv($out, ['Taller Independiente', $t['razon_social'], $t['rfc'], $t['direccion'], $t['telefono'], $t['email'], $t['gerente_servicio'] ?? 'N/A', $t['jefe_taller'] ?? 'N/A', 'Local Único', $eqCount]);
        }
    }
    fclose($out);
    exit;
}

// Procesar peticiones POST protegidas con CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        Logger::log('CSRF_VALIDATION_FAILED', 'security', null, ['action' => $action]);
        die('<div style="background:#ef4444;color:#fff;padding:20px;font-family:sans-serif;text-align:center;">Error de Seguridad: Token CSRF Inválido o Expirado. Regrese e intente de nuevo.</div>');
    }

    try {
        switch ($action) {
            case 'crear_matriz':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                $matrizId = DataStore::createMatriz([
                    'razon_social' => Security::sanitizeString($_POST['razon_social']),
                    'rfc'          => Security::sanitizeString($_POST['rfc']),
                    'direccion'    => Security::sanitizeString($_POST['direccion']),
                    'telefono'     => Security::sanitizeString($_POST['telefono']),
                    'email'        => Security::sanitizeEmail($_POST['email']),
                    'logo_url'     => 'assets/img/matriz.png'
                ]);

                // Si viene primera sucursal incluida
                if (!empty($_POST['sucursal_nombre'])) {
                    DataStore::linkSucursal([
                        'id_matriz'        => $matrizId,
                        'nombre'           => Security::sanitizeString($_POST['sucursal_nombre']),
                        'direccion'        => Security::sanitizeString($_POST['sucursal_direccion']),
                        'telefono'         => Security::sanitizeString($_POST['sucursal_telefono']),
                        'gerente_servicio' => Security::sanitizeString($_POST['sucursal_gerente'] ?? null),
                        'jefe_taller'      => Security::sanitizeString($_POST['sucursal_jefe'] ?? null)
                    ]);
                }
                header('Location: index.php?view=clientes&msg=matriz_creada');
                exit;

            case 'editar_matriz':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                $id = Security::sanitizeInt($_POST['id']);
                DataStore::updateMatriz($id, [
                    'razon_social' => Security::sanitizeString($_POST['razon_social']),
                    'rfc'          => Security::sanitizeString($_POST['rfc']),
                    'direccion'    => Security::sanitizeString($_POST['direccion']),
                    'telefono'     => Security::sanitizeString($_POST['telefono']),
                    'email'        => Security::sanitizeEmail($_POST['email'])
                ]);
                header('Location: index.php?view=clientes&msg=matriz_actualizada');
                exit;

            case 'eliminar_matriz':
                if (!Auth::isAdmin()) die('No autorizado');
                $id = Security::sanitizeInt($_POST['id']);
                DataStore::deleteMatriz($id);
                header('Location: index.php?view=clientes&msg=matriz_eliminada');
                exit;

            case 'vincular_sucursal':
            case 'crear_sucursal':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                DataStore::linkSucursal([
                    'id_matriz'        => Security::sanitizeInt($_POST['id_matriz']),
                    'nombre'           => Security::sanitizeString($_POST['nombre']),
                    'direccion'        => Security::sanitizeString($_POST['direccion']),
                    'telefono'         => Security::sanitizeString($_POST['telefono']),
                    'gerente_servicio' => Security::sanitizeString($_POST['gerente_servicio'] ?? null),
                    'jefe_taller'      => Security::sanitizeString($_POST['jefe_taller'] ?? null)
                ]);
                header('Location: index.php?view=clientes&tab=sucursales&msg=sucursal_creada');
                exit;

            case 'editar_sucursal':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                $id = Security::sanitizeInt($_POST['id']);
                DataStore::updateSucursal($id, [
                    'id_matriz'        => Security::sanitizeInt($_POST['id_matriz']),
                    'nombre'           => Security::sanitizeString($_POST['nombre']),
                    'direccion'        => Security::sanitizeString($_POST['direccion']),
                    'telefono'         => Security::sanitizeString($_POST['telefono']),
                    'gerente_servicio' => Security::sanitizeString($_POST['gerente_servicio'] ?? null),
                    'jefe_taller'      => Security::sanitizeString($_POST['jefe_taller'] ?? null)
                ]);
                header('Location: index.php?view=clientes&tab=sucursales&msg=sucursal_actualizada');
                exit;

            case 'eliminar_sucursal':
                if (!Auth::isAdmin()) die('No autorizado');
                $id = Security::sanitizeInt($_POST['id']);
                DataStore::deleteSucursal($id);
                header('Location: index.php?view=clientes&tab=sucursales&msg=sucursal_eliminada');
                exit;

            case 'crear_taller':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                DataStore::createTaller([
                    'razon_social'     => Security::sanitizeString($_POST['razon_social']),
                    'rfc'              => Security::sanitizeString($_POST['rfc']),
                    'direccion'        => Security::sanitizeString($_POST['direccion']),
                    'telefono'         => Security::sanitizeString($_POST['telefono']),
                    'email'            => Security::sanitizeEmail($_POST['email']),
                    'gerente_servicio' => Security::sanitizeString($_POST['gerente_servicio'] ?? null),
                    'jefe_taller'      => Security::sanitizeString($_POST['jefe_taller'] ?? null)
                ]);
                header('Location: index.php?view=clientes&tab=talleres&msg=taller_creado');
                exit;

            case 'editar_taller':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                $id = Security::sanitizeInt($_POST['id']);
                DataStore::updateTaller($id, [
                    'razon_social'     => Security::sanitizeString($_POST['razon_social']),
                    'rfc'              => Security::sanitizeString($_POST['rfc']),
                    'direccion'        => Security::sanitizeString($_POST['direccion']),
                    'telefono'         => Security::sanitizeString($_POST['telefono']),
                    'email'            => Security::sanitizeEmail($_POST['email']),
                    'gerente_servicio' => Security::sanitizeString($_POST['gerente_servicio'] ?? null),
                    'jefe_taller'      => Security::sanitizeString($_POST['jefe_taller'] ?? null)
                ]);
                header('Location: index.php?view=clientes&tab=talleres&msg=taller_actualizado');
                exit;

            case 'eliminar_taller':
                if (!Auth::isAdmin()) die('No autorizado');
                $id = Security::sanitizeInt($_POST['id']);
                DataStore::deleteTaller($id);
                header('Location: index.php?view=clientes&tab=talleres&msg=taller_eliminado');
                exit;

            case 'crear_usuario_cliente':
                if (!Auth::isAdmin()) die('No autorizado');
                $destino = Security::sanitizeString($_POST['destino_tipo'] ?? 'sucursal');
                $idMatriz = null;
                $idSucursal = null;
                $idTaller = null;
                if ($destino === 'sucursal') {
                    $idSucursal = Security::sanitizeInt($_POST['id_sucursal']);
                    $suc = DataStore::getSucursalById($idSucursal);
                    $idMatriz = $suc['id_matriz'] ?? null;
                } else {
                    $idTaller = Security::sanitizeInt($_POST['id_taller']);
                }

                DataStore::createUsuario([
                    'nombre'     => Security::sanitizeString($_POST['nombre']),
                    'alias'      => Security::sanitizeString($_POST['alias'] ?? $_POST['nombre']),
                    'email'      => Security::sanitizeEmail($_POST['email']),
                    'telefono'   => Security::sanitizeString($_POST['telefono'] ?? ''),
                    'rfc'        => Security::sanitizeString($_POST['rfc'] ?? ''),
                    'rol'        => 'cliente',
                    'id_matriz'  => $idMatriz,
                    'id_sucursal'=> $idSucursal,
                    'id_taller'  => $idTaller,
                    'password'   => !empty($_POST['password']) ? $_POST['password'] : 'tcs2026',
                    'foto_url'   => 'assets/img/avatar-cliente1.png'
                ]);
                header('Location: index.php?view=clientes&tab=usuarios&msg=usuario_creado');
                exit;

            case 'editar_usuario_cliente':
                if (!Auth::isAdmin()) die('No autorizado');
                $id = Security::sanitizeInt($_POST['id']);
                $destino = Security::sanitizeString($_POST['destino_tipo'] ?? 'sucursal');
                $idMatriz = null;
                $idSucursal = null;
                $idTaller = null;
                if ($destino === 'sucursal') {
                    $idSucursal = Security::sanitizeInt($_POST['id_sucursal']);
                    $suc = DataStore::getSucursalById($idSucursal);
                    $idMatriz = $suc['id_matriz'] ?? null;
                } else {
                    $idTaller = Security::sanitizeInt($_POST['id_taller']);
                }

                $updateData = [
                    'nombre'     => Security::sanitizeString($_POST['nombre']),
                    'alias'      => Security::sanitizeString($_POST['alias'] ?? $_POST['nombre']),
                    'email'      => Security::sanitizeEmail($_POST['email']),
                    'telefono'   => Security::sanitizeString($_POST['telefono'] ?? ''),
                    'rfc'        => Security::sanitizeString($_POST['rfc'] ?? ''),
                    'id_matriz'  => $idMatriz,
                    'id_sucursal'=> $idSucursal,
                    'id_taller'  => $idTaller
                ];
                if (!empty($_POST['password'])) {
                    $updateData['password'] = $_POST['password'];
                }
                DataStore::updateUsuario($id, $updateData);
                header('Location: index.php?view=clientes&tab=usuarios&msg=usuario_actualizado');
                exit;

            case 'eliminar_usuario_cliente':
                if (!Auth::isAdmin()) die('No autorizado');
                $id = Security::sanitizeInt($_POST['id']);
                DataStore::deleteUsuario($id);
                header('Location: index.php?view=clientes&tab=usuarios&msg=usuario_eliminado');
                exit;

            case 'crear_equipo':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                $destino = Security::sanitizeString($_POST['destino_tipo'] ?? 'sucursal');
                $idSucursal = ($destino === 'sucursal') ? Security::sanitizeInt($_POST['id_sucursal']) : null;
                $idTaller = ($destino === 'taller') ? Security::sanitizeInt($_POST['id_taller']) : null;

                DataStore::createEquipo([
                    'nombre'          => Security::sanitizeString($_POST['nombre']),
                    'categoria'       => Security::sanitizeString($_POST['categoria']),
                    'marca'           => Security::sanitizeString($_POST['marca']),
                    'modelo'          => Security::sanitizeString($_POST['modelo']),
                    'numero_serie'    => Security::sanitizeString($_POST['numero_serie']),
                    'capacidad'       => Security::sanitizeString($_POST['capacidad']),
                    'ubicacion_bahia' => Security::sanitizeString($_POST['ubicacion_bahia']),
                    'id_sucursal'     => $idSucursal,
                    'id_taller'       => $idTaller,
                    'estado_salud'    => Security::sanitizeString($_POST['estado_salud'] ?? 'operativo'),
                    'horas_uso'       => Security::sanitizeInt($_POST['horas_uso'] ?? 0),
                    'ultimo_mantenimiento'  => date('Y-m-d'),
                    'proximo_mantenimiento' => date('Y-m-d', strtotime('+3 months')),
                    'notas_tecnicas'  => Security::sanitizeString($_POST['notas_tecnicas'] ?? '')
                ]);
                header('Location: index.php?view=equipos&msg=equipo_creado');
                exit;

            case 'solicitar_servicio':
                DataStore::createOrden([
                    'id_equipo'           => Security::sanitizeInt($_POST['id_equipo']),
                    'id_usuario_solicita' => $currentUser['id'],
                    'tipo_servicio'       => Security::sanitizeString($_POST['tipo_servicio']),
                    'prioridad'           => Security::sanitizeString($_POST['prioridad']),
                    'descripcion_falla'   => Security::sanitizeString($_POST['descripcion_falla'])
                ]);
                header('Location: index.php?view=ordenes&msg=orden_creada');
                exit;

            case 'actualizar_orden_estado':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                $ordenId = Security::sanitizeInt($_POST['orden_id']);
                $nuevoEstado = Security::sanitizeString($_POST['nuevo_estado']);
                DataStore::updateOrdenStatus($ordenId, $nuevoEstado);
                header('Location: index.php?view=ordenes&msg=orden_actualizada');
                exit;

            case 'guardar_reporte':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                $matrizData = $_POST['matriz'] ?? [];
                $cleanMatriz = [];
                foreach ($matrizData as $k => $v) {
                    $cleanMatriz[Security::sanitizeString($k)] = Security::sanitizeString($v);
                }

                // Procesamiento seguro de evidencias fotográficas (Antes y Después)
                $uploadDir = BASE_PATH . '/uploads/evidencias/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                $fotoAntesPath = null;
                $fotoDespuesPath = null;

                if (!empty($_FILES['foto_antes']['name']) && $_FILES['foto_antes']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['foto_antes']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowedExts)) {
                        $filename = 'antes_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($_FILES['foto_antes']['tmp_name'], $uploadDir . $filename)) {
                            $fotoAntesPath = 'uploads/evidencias/' . $filename;
                        }
                    }
                }

                if (!empty($_FILES['foto_despues']['name']) && $_FILES['foto_despues']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['foto_despues']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $allowedExts)) {
                        $filename = 'despues_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        if (move_uploaded_file($_FILES['foto_despues']['tmp_name'], $uploadDir . $filename)) {
                            $fotoDespuesPath = 'uploads/evidencias/' . $filename;
                        }
                    }
                }

                $nuevoRepId = DataStore::createReporte([
                    'id_orden'             => !empty($_POST['id_orden']) ? Security::sanitizeInt($_POST['id_orden']) : null,
                    'id_equipo'            => Security::sanitizeInt($_POST['id_equipo']),
                    'id_tecnico'           => $currentUser['id'],
                    'fecha_servicio'       => date('Y-m-d'),
                    'tipo_formato'         => Security::sanitizeString($_POST['tipo_formato']),
                    'matriz_inspeccion'    => $cleanMatriz,
                    'dictamen_final'       => Security::sanitizeString($_POST['dictamen_final']),
                    'diagnostico_trabajos' => Security::sanitizeString($_POST['diagnostico_trabajos']),
                    'firma_tecnico_nombre' => $currentUser['nombre'],
                    'firma_tecnico_cedula' => $currentUser['rfc'] ?? 'CED-TEC-992014',
                    'firma_cliente_nombre' => Security::sanitizeString($_POST['firma_cliente_nombre']),
                    'firma_cliente_cargo'  => 'Jefe de Bahía / Cliente',
                    'firma_digital_tecnico'=> !empty($_POST['firma_tecnico_canvas']) ? $_POST['firma_tecnico_canvas'] : null,
                    'firma_digital_cliente'=> !empty($_POST['firma_cliente_canvas']) ? $_POST['firma_cliente_canvas'] : null,
                    'foto_antes'           => $fotoAntesPath,
                    'foto_despues'         => $fotoDespuesPath,
                    'consumibles'          => [
                        ['descripcion' => 'Insumo de Mantenimiento Preventivo', 'cantidad' => 1]
                    ]
                ]);
                header('Location: index.php?view=reportes&reporte_id=' . $nuevoRepId . '&msg=reporte_creado');
                exit;

            case 'guardar_checklist':
                $idEquipo = Security::sanitizeInt($_POST['id_equipo']);
                $resultado = Security::sanitizeString($_POST['resultado'] ?? 'aprobado');
                $observaciones = Security::sanitizeString($_POST['observaciones'] ?? '');
                $items = $_POST['items'] ?? [];
                
                DataStore::createChecklist([
                    'id_equipo' => $idEquipo,
                    'id_usuario' => $currentUser['id'],
                    'fecha' => date('Y-m-d H:i:s'),
                    'resultado' => $resultado,
                    'items_json' => json_encode($items),
                    'observaciones' => $observaciones
                ]);

                if ($resultado === 'fallo_critico') {
                    DataStore::updateEquipoHealth($idEquipo, 'fuera_servicio');
                    DataStore::createOrden([
                        'id_equipo' => $idEquipo,
                        'id_usuario_solicita' => $currentUser['id'],
                        'tipo_servicio' => 'correctivo_urgente',
                        'prioridad' => 'critica',
                        'descripcion_falla' => 'FAILSAFE ACTIVADO POR CHECKLIST PRE-OPERATIVO: ' . $observaciones
                    ]);
                    header('Location: index.php?view=equipos&detalle_id=' . $idEquipo . '&msg=failsafe_activado');
                } else {
                    header('Location: index.php?view=equipos&detalle_id=' . $idEquipo . '&msg=checklist_ok');
                }
                exit;

            case 'crear_refaccion':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                DataStore::createRefaccion([
                    'codigo_parte'   => Security::sanitizeString($_POST['codigo_parte']),
                    'descripcion'    => Security::sanitizeString($_POST['descripcion']),
                    'categoria'      => Security::sanitizeString($_POST['categoria']),
                    'stock_actual'   => Security::sanitizeInt($_POST['stock_actual']),
                    'stock_minimo'   => Security::sanitizeInt($_POST['stock_minimo']),
                    'unidad_medida'  => Security::sanitizeString($_POST['unidad_medida']),
                    'costo_unitario' => (float)$_POST['costo_unitario'],
                    'id_proveedor'   => !empty($_POST['id_proveedor']) ? Security::sanitizeInt($_POST['id_proveedor']) : null,
                    'estado_pieza'   => 'nuevo',
                    'ubicacion_estante' => Security::sanitizeString($_POST['ubicacion_estante'] ?? 'Almacén')
                ]);
                header('Location: index.php?view=inventario&msg=refaccion_creada');
                exit;

            case 'ajustar_stock':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                DataStore::updateStock(Security::sanitizeInt($_POST['id_refaccion']), Security::sanitizeInt($_POST['nuevo_stock']));
                header('Location: index.php?view=inventario&msg=stock_actualizado');
                exit;

            case 'crear_proveedor':
                if (!Auth::isAdmin() && !Auth::isTechnician()) die('No autorizado');
                DataStore::createProveedor([
                    'razon_social' => Security::sanitizeString($_POST['razon_social']),
                    'rfc'          => Security::sanitizeString($_POST['rfc']),
                    'contacto'     => Security::sanitizeString($_POST['contacto']),
                    'telefono'     => Security::sanitizeString($_POST['telefono']),
                    'email'        => Security::sanitizeEmail($_POST['email']),
                    'categoria'    => Security::sanitizeString($_POST['categoria']),
                    'dias_credito' => Security::sanitizeInt($_POST['dias_credito'] ?? 30)
                ]);
                header('Location: index.php?view=proveedores&msg=proveedor_creado');
                exit;

            case 'enviar_mensaje':
                DataStore::enviarMensaje(
                    $currentUser['id'],
                    Security::sanitizeInt($_POST['id_destinatario']),
                    Security::sanitizeString($_POST['mensaje'])
                );
                header('Location: index.php?view=mensajeria&contacto_id=' . Security::sanitizeInt($_POST['id_destinatario']));
                exit;
        }
    } catch (Exception $e) {
        Logger::log('APPLICATION_EXCEPTION', 'system', null, ['error' => $e->getMessage()]);
        die('<div style="background:#0f172a;color:#ef4444;padding:30px;font-family:sans-serif;text-align:center;">Ocurrió una excepción al procesar la solicitud. El evento ha sido registrado en la bitácora de seguridad.</div>');
    }
}

// Inclusión de módulos autorizados (Prevención LFI / Local File Inclusion)
$allowedViews = [
    'dashboard',
    'alertas',
    'clientes',
    'sucursales',
    'equipos',
    'ordenes',
    'reportes',
    'inventario',
    'proveedores',
    'mensajeria',
    'credenciales',
    'auditoria'
];

if (!in_array($view, $allowedViews, true)) {
    $view = 'dashboard';
}

$matrices = DataStore::getMatrices();
$sucursales = DataStore::getSucursales();
$talleres = DataStore::getTalleres();
$equipos = DataStore::getEquipos();
$globalAlertas = DataStore::getAlertas(Auth::isClient() ? ($currentUser['id_sucursal'] ?? null) : null, Auth::isClient() ? ($currentUser['id_taller'] ?? null) : null);
$critCount = count(array_filter($globalAlertas, fn($a) => $a['severidad'] === 'critica'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e(APP_TITLE) ?></title>
    <meta name="description" content="Plataforma de mantenimiento de elevadores automotrices, telemetría e inventario para talleres mecánicos y agencias.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-wrapper">
    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="assets/img/logo-tcs.png" alt="Logo TCS Motriz" class="sidebar-logo">
            <div>
                <div class="sidebar-brand-title">Servicio TCS Motriz</div>
                <div class="sidebar-brand-subtitle"><?= Security::e(APP_DOMAIN) ?></div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Menú Principal</div>

            <a href="index.php?view=dashboard" class="nav-item <?= ($view === 'dashboard' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </div>
            </a>

            <a href="index.php?view=alertas" class="nav-item <?= ($view === 'alertas' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span>Alertas de Servicio</span>
                </div>
                <?php if (count($globalAlertas) > 0): ?>
                    <span class="nav-badge" style="background: <?= $critCount > 0 ? '#e11d48' : '#f59e0b' ?>; color: #fff; font-weight: bold;"><?= count($globalAlertas) ?></span>
                <?php endif; ?>
            </a>

            <a href="index.php?view=clientes" class="nav-item <?= ($view === 'clientes' || $view === 'sucursales' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Clientes y Sucursales</span>
                </div>
                <span class="nav-badge"><?= count($matrices) + count($talleres) ?></span>
            </a>

            <a href="index.php?view=equipos" class="nav-item <?= ($view === 'equipos' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Equipos y Rampas</span>
                </div>
                <span class="nav-badge"><?= count($equipos) ?></span>
            </a>

            <a href="index.php?view=ordenes" class="nav-item <?= ($view === 'ordenes' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Órdenes de Servicio</span>
                </div>
                <span class="nav-badge" style="background:#78350f;color:#fde68a;">1</span>
            </a>

            <a href="index.php?view=reportes" class="nav-item <?= ($view === 'reportes' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <span>Reportes e Informes</span>
                </div>
            </a>

            <div class="nav-section-title">Almacén & Proveedores</div>

            <a href="index.php?view=inventario" class="nav-item <?= ($view === 'inventario' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    <span>Inventario Refacciones</span>
                </div>
            </a>

            <a href="index.php?view=proveedores" class="nav-item <?= ($view === 'proveedores' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Registro Proveedores</span>
                </div>
            </a>

            <div class="nav-section-title">Comunicación & Accesos</div>

            <a href="index.php?view=mensajeria" class="nav-item <?= ($view === 'mensajeria' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Mensajería Privada</span>
                </div>
                <span class="nav-badge" style="background:#065f46;color:#a7f3d0;">2</span>
            </a>

            <a href="index.php?view=credenciales" class="nav-item <?= ($view === 'credenciales' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Credenciales de Rol</span>
                </div>
            </a>

            <?php if (Auth::isAdmin()): ?>
            <a href="index.php?view=auditoria" class="nav-item <?= ($view === 'auditoria' ? 'active' : '') ?>">
                <div class="nav-label-group">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Auditoría ISO 27001</span>
                </div>
            </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <span>TCS MOTRIZ © 2026</span>
            <span style="color: var(--accent-emerald);">v1.0.0</span>
        </div>
    </aside>

    <!-- ÁREA PRINCIPAL DE CONTENIDO -->
    <main class="main-viewport">
        <!-- BARRA SUPERIOR -->
        <header class="top-navbar">
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="landing.php" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; padding: 5px 12px; background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(14, 165, 233, 0.25); border-radius: 6px; transition: all var(--transition-fast);" title="Abrir Landing Page Oficial de TCS Motriz">
                    <img src="assets/img/logo-tcs.png" alt="TCS Motriz" style="height: 22px; object-fit: contain;">
                    <span style="font-size: 11px; font-weight: 700; color: #38bdf8; letter-spacing: 0.5px;">SITIO PÚBLICO ↗</span>
                </a>
            </div>

            <div class="role-simulator-bar">
                <span class="role-sim-title">Simular Rol:</span>
                <button class="btn-sim-role <?= (Auth::isAdmin() ? 'active' : '') ?>" data-role="admin">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Admin TCS Motriz
                </button>
                <button class="btn-sim-role <?= (Auth::isTechnician() ? 'active role-tecnico' : '') ?>" data-role="tecnico">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Técnico
                </button>
                <button class="btn-sim-role <?= (Auth::isClient() ? 'active role-cliente' : '') ?>" data-role="cliente">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    Usuario (Agencia)
                </button>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="index.php?view=alertas" style="position: relative; display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; background: rgba(225, 29, 72, 0.12); border: 1px solid rgba(225, 29, 72, 0.35); border-radius: 8px; color: #f43f5e; text-decoration: none;" title="Centro de Alertas Técnicas">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <?php if (count($globalAlertas) > 0): ?>
                        <span style="position: absolute; top: -5px; right: -5px; background: #e11d48; color: #fff; font-size: 10px; font-weight: 900; border-radius: 10px; padding: 1px 6px; box-shadow: 0 0 8px rgba(225,29,72,0.9);"><?= count($globalAlertas) ?></span>
                    <?php endif; ?>
                </a>

                <div class="user-profile-badge">
                <div class="profile-avatar">
                    <?= strtoupper(substr($currentUser['nombre'], 0, 2)) ?>
                    <div class="profile-status-dot"></div>
                </div>
                <div class="profile-info">
                    <div class="profile-name"><?= Security::e($currentUser['nombre']) ?></div>
                    <div class="profile-role-tag"><?= Security::e($currentUser['alias'] ?? $currentUser['rol']) ?></div>
                </div>
            </div>
        </header>

        <!-- CONTENEDOR DE VISTA ACTIVA -->
        <div class="view-content">
            <?php
            $viewPath = MODULES_PATH . '/' . $view . '/index.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                include MODULES_PATH . '/dashboard/index.php';
            }
            ?>
        </div>
    </main>
</div>

<!-- =====================================================================
     MODALES GLOBALES DE ACCIÓN
     ===================================================================== -->

<!-- MODAL 1: CREAR MATRIZ -->
<div id="modal-crear-matriz" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Registrar Empresa Matriz y Primera Sucursal</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_matriz">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="font-size: 11px; font-weight: 700; color: var(--accent-cyan-light); text-transform: uppercase; margin-bottom: 10px;">Paso 1: Información de la Matriz</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Razón Social *</label>
                        <input type="text" name="razon_social" class="form-control" required placeholder="Ej: Grupo Automotriz Premier S.A. de C.V.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC Fiscal *</label>
                        <input type="text" name="rfc" class="form-control" required placeholder="Ej: GAP880215-AB1">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Dirección Fiscal *</label>
                        <input type="text" name="direccion" class="form-control" required placeholder="Av. de las Industrias 500, CDMX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono Matriz *</label>
                        <input type="text" name="telefono" class="form-control" required placeholder="55-5555-0000">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Correo Electrónico Oficial *</label>
                    <input type="email" name="email" class="form-control" required placeholder="contacto@empresa.com">
                </div>

                <div style="font-size: 11px; font-weight: 700; color: var(--accent-cyan-light); text-transform: uppercase; margin: 20px 0 10px 0; border-top: 1px solid var(--border-subtle); padding-top: 14px;">Paso 2: Datos de la Primera Sucursal</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre de Sucursal *</label>
                        <input type="text" name="sucursal_nombre" class="form-control" required placeholder="Ej: Sucursal Ford Interlomas">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono Sucursal *</label>
                        <input type="text" name="sucursal_telefono" class="form-control" required placeholder="55-5555-1111">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección de la Sucursal *</label>
                    <input type="text" name="sucursal_direccion" class="form-control" required placeholder="Vía Magna 12, Interlomas">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gerente de Servicio (Opcional)</label>
                        <input type="text" name="sucursal_gerente" class="form-control" placeholder="Ing. Roberto Garza">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jefe de Taller (Opcional)</label>
                        <input type="text" name="sucursal_jefe" class="form-control" placeholder="Carlos Mendoza">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar Matriz & Sucursal</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: VINCULAR SUCURSAL A MATRIZ EXISTENTE -->
<div id="modal-vincular-sucursal" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Vincular Nueva Sucursal a Matriz Existente</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=vincular_sucursal">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Seleccionar Empresa Matriz Existente *</label>
                    <select name="id_matriz" class="form-control" required>
                        <?php foreach ($matrices as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= Security::e($m['razon_social']) ?> (RFC: <?= Security::e($m['rfc']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre de Nueva Sucursal *</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Sucursal Nissan Santa Fe">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono Sucursal *</label>
                        <input type="text" name="telefono" class="form-control" required placeholder="55-5555-2222">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Dirección de Sucursal *</label>
                    <input type="text" name="direccion" class="form-control" required placeholder="Vasco de Quiroga 3800, Santa Fe">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gerente de Servicio (Opcional)</label>
                        <input type="text" name="gerente_servicio" class="form-control" placeholder="Lic. Elena Torres">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jefe de Taller (Opcional)</label>
                        <input type="text" name="jefe_taller" class="form-control" placeholder="Jorge Morales">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-cyan">Vincular Sucursal</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: REGISTRAR TALLER ÚNICO -->
<div id="modal-crear-taller" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Registrar Taller Independiente (Local Único)</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_taller">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Razón Social del Taller *</label>
                        <input type="text" name="razon_social" class="form-control" required placeholder="Ej: Taller Electromecánico Ramírez">
                    </div>
                    <div class="form-group">
                        <label class="form-label">RFC *</label>
                        <input type="text" name="rfc" class="form-control" required placeholder="Ej: EMIR950412-5NB">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Teléfono Directo *</label>
                        <input type="text" name="telefono" class="form-control" required placeholder="55-5555-9911">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" name="email" class="form-control" required placeholder="contacto@taller.com">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Dirección Completa *</label>
                    <input type="text" name="direccion" class="form-control" required placeholder="Av. Patriotismo 120, Escandón">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gerente de Servicio (Opcional)</label>
                        <input type="text" name="gerente_servicio" class="form-control" placeholder="Ing. Esteban Ramírez">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jefe de Taller (Opcional)</label>
                        <input type="text" name="jefe_taller" class="form-control" placeholder="Roberto Solís">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar Taller</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: REGISTRAR EQUIPO O RAMPA -->
<div id="modal-crear-equipo" class="modal-backdrop">
    <div class="modal-dialog modal-dialog-large">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Registrar Equipo o Rampa Elevadora</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=crear_equipo">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo de Asignación *</label>
                        <select name="destino_tipo" class="form-control" id="select-destino-tipo" onchange="toggleDestinoFields(this.value)">
                            <option value="sucursal">Sucursal de Matriz</option>
                            <option value="taller">Taller Independiente</option>
                        </select>
                    </div>

                    <div class="form-group" id="group-sucursal">
                        <label class="form-label">Seleccionar Sucursal *</label>
                        <select name="id_sucursal" class="form-control">
                            <?php foreach ($sucursales as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= Security::e($s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="group-taller" style="display: none;">
                        <label class="form-label">Seleccionar Taller Independiente *</label>
                        <select name="id_taller" class="form-control">
                            <?php foreach ($talleres as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= Security::e($t['razon_social']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre del Equipo / Rampa *</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Rampa Hidráulica de 2 Postes (4.5 Ton)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría Técnica *</label>
                        <select name="categoria" class="form-control" required>
                            <option value="rampa_2_postes">Rampa de 2 Postes (Rotary Lift / BendPak)</option>
                            <option value="elevador_tijera">Elevador Tijera para Alineación</option>
                            <option value="torno_rectificador">Torno Rectificador de Discos</option>
                            <option value="desmontadora">Desmontadora de Llantas</option>
                            <option value="recuperadora_1234yf">Estación R-1234yf (A2L)</option>
                            <option value="recuperadora_r134a">Estación Gas R134a</option>
                            <option value="balanceadora">Balanceadora de Neumáticos</option>
                            <option value="compresor">Compresor de Aire Industrial</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Marca / Fabricante *</label>
                        <input type="text" name="marca" class="form-control" required placeholder="Ej: BendPak, Rotary Lift, Kaeser">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Modelo *</label>
                        <input type="text" name="modelo" class="form-control" required placeholder="Ej: XPR-10S, SPO10">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Número de Serie Físico *</label>
                        <input type="text" name="numero_serie" class="form-control" required placeholder="Ej: SN-9948201-MX">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacidad Nominal de Carga *</label>
                        <input type="text" name="capacidad" class="form-control" required placeholder="Ej: 10,000 lbs (4.5 Ton)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bahía / Ubicación en Taller *</label>
                        <input type="text" name="ubicacion_bahia" class="form-control" required placeholder="Ej: Bahía 1 (Mantenimiento Rápido)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado Operativo Inicial *</label>
                        <select name="estado_salud" class="form-control">
                            <option value="operativo">Operativo (Excelente / Seguro)</option>
                            <option value="observado">Requiere Atención Menor</option>
                            <option value="fuera_servicio">Fuera de Servicio (Inhabilitado)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar Equipo & Generar QR</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 5: NUEVA SOLICITUD DE SERVICIO (CLIENTE O ADMIN) -->
<div id="modal-solicitar-servicio" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/img/logo-tcs.png" alt="TCS" style="height: 24px; object-fit: contain;">
                <div class="modal-title">Nueva Solicitud de Servicio a Equipo</div>
            </div>
            <button class="modal-close" data-close-modal>&times;</button>
        </div>
        <form method="POST" action="index.php?action=solicitar_servicio">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Seleccionar Equipo de su Sucursal *</label>
                    <select name="id_equipo" class="form-control" required>
                        <?php 
                        $userSucursal = Auth::isClient() ? ($currentUser['id_sucursal'] ?? null) : null;
                        $userTaller = Auth::isClient() ? ($currentUser['id_taller'] ?? null) : null;
                        $allowedEquipos = DataStore::getEquipos($userSucursal, $userTaller);
                        ?>
                        <?php foreach ($allowedEquipos as $eq): ?>
                            <option value="<?= $eq['id'] ?>">
                                <?= Security::e($eq['codigo_tcs']) ?> — <?= Security::e($eq['nombre']) ?> (<?= Security::e($eq['ubicacion_nombre']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo de Servicio *</label>
                        <select name="tipo_servicio" class="form-control" required>
                            <option value="correctivo">Reparación Correctiva (Falla detectada)</option>
                            <option value="preventivo">Mantenimiento Preventivo Periódico</option>
                            <option value="calibracion">Calibración / Nivelación de Precisión</option>
                            <option value="urgente">Reparación Urgente / Emergencia</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nivel de Prioridad *</label>
                        <select name="prioridad" class="form-control" required>
                            <option value="normal">Normal (Programado)</option>
                            <option value="alta">Alta (Atención en 24 hrs)</option>
                            <option value="critica">Crítica (Elevador detenido / Riesgo)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción Detallada de la Falla o Solicitud *</label>
                    <textarea name="descripcion_falla" class="form-control" rows="4" required placeholder="Describe los síntomas: ruidos anormales, pérdida de presión, fuga de aceite, desfasamiento de cables..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-close-modal>Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar Solicitud de Servicio</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.TCS_CSRF = '<?= Security::e($_SESSION['csrf_token'] ?? '') ?>';
    function toggleDestinoFields(tipo) {
        document.getElementById('group-sucursal').style.display = (tipo === 'sucursal') ? 'block' : 'none';
        document.getElementById('group-taller').style.display = (tipo === 'taller') ? 'block' : 'none';
    }
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
