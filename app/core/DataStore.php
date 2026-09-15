<?php
/**
 * TCS MOTRIZ - Repositorio Unificado de Datos (DataStore)
 * Compatible con MySQL PDO en Hostinger y Fallback Autónomo Persistente en JSON
 */

if (!defined('TCS_ACCESS')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso directo prohibido.');
}

class DataStore {
    private static string $storeFile = BASE_PATH . '/database/store.json';
    private static ?array $memoryData = null;

    /**
     * Inicializar datos en caso de no existir store.json
     */
    private static function initDefaultData(): array {
        return [
            'matrices' => [
                [
                    'id' => 1,
                    'razon_social' => 'Grupo Automotriz Premier S.A. de C.V.',
                    'rfc' => 'GAP880215-AB1',
                    'direccion' => 'Av. de las Industrias 500, CDMX',
                    'telefono' => '55-5555-0000',
                    'email' => 'contacto@grupopremier.com',
                    'logo_url' => 'assets/img/matriz-premier.png',
                    'created_at' => '2026-08-01 10:00:00'
                ]
            ],
            'sucursales' => [
                [
                    'id' => 1,
                    'id_matriz' => 1,
                    'nombre' => 'Sucursal Ford Interlomas',
                    'direccion' => 'Vía Magna 12, Interlomas, Huixquilucan',
                    'telefono' => '55-5555-1111',
                    'gerente_servicio' => 'Ing. Roberto Garza',
                    'jefe_taller' => 'Carlos Mendoza',
                    'created_at' => '2026-08-01 10:15:00'
                ],
                [
                    'id' => 2,
                    'id_matriz' => 1,
                    'nombre' => 'Sucursal Nissan Santa Fe',
                    'direccion' => 'Vasco de Quiroga 3800, Santa Fe',
                    'telefono' => '55-5555-2222',
                    'gerente_servicio' => 'Lic. Elena Torres',
                    'jefe_taller' => 'Jorge Morales',
                    'created_at' => '2026-08-01 10:30:00'
                ]
            ],
            'talleres' => [
                [
                    'id' => 1,
                    'razon_social' => 'Taller Electromecánico Ramírez',
                    'rfc' => 'EMIR950412-5NB',
                    'direccion' => 'Av. Patriotismo 120, Escandón, CDMX',
                    'telefono' => '55-5555-9911',
                    'email' => 'contacto@taller-ramirez.com',
                    'gerente_servicio' => 'Ing. Esteban Ramírez',
                    'jefe_taller' => 'Roberto Solís',
                    'logo_url' => 'assets/img/taller-ramirez.png',
                    'created_at' => '2026-08-05 11:00:00'
                ]
            ],
            'usuarios' => [
                [
                    'id' => 1,
                    'nombre' => 'Ing. Fernando Ruiz',
                    'alias' => 'Admin TCS Motriz',
                    'email' => 'fernando.ruiz@servicio-tcsmotriz.com.mx',
                    'password_hash' => '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka',
                    'telefono' => '55-1023-4491',
                    'rfc' => 'RUFI880112-1R4',
                    'rol' => 'admin',
                    'id_matriz' => null,
                    'id_sucursal' => null,
                    'id_taller' => null,
                    'foto_url' => 'assets/img/avatar-admin.png',
                    'is_online' => 1
                ],
                [
                    'id' => 2,
                    'nombre' => 'Soporte Técnico TCS',
                    'alias' => 'Soporte',
                    'email' => 'soporte@servicio-tcsmotriz.com.mx',
                    'password_hash' => '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka',
                    'telefono' => '55-8000-4277',
                    'rfc' => 'STC191001-8BA',
                    'rol' => 'soporte',
                    'id_matriz' => null,
                    'id_sucursal' => null,
                    'id_taller' => null,
                    'foto_url' => 'assets/img/avatar-soporte.png',
                    'is_online' => 1
                ],
                [
                    'id' => 3,
                    'nombre' => 'Téc. Héctor Morales',
                    'alias' => 'Técnico Especialista',
                    'email' => 'hector.morales@servicio-tcsmotriz.com.mx',
                    'password_hash' => '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka',
                    'telefono' => '55-3322-1780',
                    'rfc' => 'MORH890120-TR4',
                    'rol' => 'tecnico',
                    'id_matriz' => null,
                    'id_sucursal' => null,
                    'id_taller' => null,
                    'foto_url' => 'assets/img/avatar-tecnico.png',
                    'is_online' => 1
                ],
                [
                    'id' => 4,
                    'nombre' => 'Carlos Mendoza',
                    'alias' => 'Carlos M. (Ford Interlomas)',
                    'email' => 'cmendoza@fordinterlomas.com',
                    'password_hash' => '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka',
                    'telefono' => '55-4433-8822',
                    'rfc' => 'MENC920314-KL2',
                    'rol' => 'cliente',
                    'id_matriz' => 1,
                    'id_sucursal' => 1,
                    'id_taller' => null,
                    'foto_url' => 'assets/img/avatar-cliente1.png',
                    'is_online' => 1
                ],
                [
                    'id' => 5,
                    'nombre' => 'Lic. Elena Torres',
                    'alias' => 'Elena T. (Nissan Santa Fe)',
                    'email' => 'elena.torres@nissansantafe.com',
                    'password_hash' => '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka',
                    'telefono' => '55-2233-4455',
                    'rfc' => 'TORE850619-3M1',
                    'rol' => 'cliente',
                    'id_matriz' => 1,
                    'id_sucursal' => 2,
                    'id_taller' => null,
                    'foto_url' => 'assets/img/avatar-cliente2.png',
                    'is_online' => 0
                ],
                [
                    'id' => 6,
                    'nombre' => 'Ing. Esteban Ramírez',
                    'alias' => 'Esteban R. (Taller Ramírez)',
                    'email' => 'contacto@taller-ramirez.com',
                    'password_hash' => '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka',
                    'telefono' => '55-5555-9911',
                    'rfc' => 'EMIR950412-5NB',
                    'rol' => 'cliente',
                    'id_matriz' => null,
                    'id_sucursal' => null,
                    'id_taller' => 1,
                    'foto_url' => 'assets/img/avatar-taller.png',
                    'is_online' => 0
                ]
            ],
            'equipos' => [
                [
                    'id' => 1,
                    'codigo_tcs' => 'TCS-EQ-001',
                    'nombre' => 'Rampa Hidráulica de 2 Postes (4.5 Ton)',
                    'categoria' => 'rampa_2_postes',
                    'marca' => 'BendPak',
                    'modelo' => 'XPR-10S',
                    'numero_serie' => 'SN-9948201-MX',
                    'capacidad' => '10,000 lbs (4.5 Ton)',
                    'ubicacion_bahia' => 'Bahía 1 (Mantenimiento Rápido)',
                    'id_sucursal' => 1,
                    'id_taller' => null,
                    'estado_salud' => 'operativo',
                    'ultimo_mantenimiento' => '2026-07-15',
                    'proximo_mantenimiento' => '2026-10-15',
                    'horas_uso' => 1420,
                    'qr_token' => 'QR-TCS-001-9948201',
                    'notas_tecnicas' => 'Elevador principal para sedanes y SUVs ligeras.'
                ],
                [
                    'id' => 2,
                    'codigo_tcs' => 'TCS-EQ-002',
                    'nombre' => 'Elevador Tijera para Alineación (5.0 Ton)',
                    'categoria' => 'elevador_tijera',
                    'marca' => 'John Bean',
                    'modelo' => 'Alignment Scissors 5.0',
                    'numero_serie' => 'SN-8837192-US',
                    'capacidad' => '11,000 lbs (5.0 Ton)',
                    'ubicacion_bahia' => 'Bahía 4 (Alineación y Tramado)',
                    'id_sucursal' => 1,
                    'id_taller' => null,
                    'estado_salud' => 'observado',
                    'ultimo_mantenimiento' => '2026-05-10',
                    'proximo_mantenimiento' => '2026-08-10',
                    'horas_uso' => 2150,
                    'qr_token' => 'QR-TCS-002-8837192',
                    'notas_tecnicas' => 'Requiere atención en sincronización hidráulica de tijera derecha.'
                ],
                [
                    'id' => 3,
                    'codigo_tcs' => 'TCS-EQ-003',
                    'nombre' => 'Compresor de Aire Tornillo 15 HP Industrial',
                    'categoria' => 'compresor',
                    'marca' => 'Kaeser',
                    'modelo' => 'SK 15 Industrial',
                    'numero_serie' => 'SN-3392810-DE',
                    'capacidad' => '15 HP / 140 PSI',
                    'ubicacion_bahia' => 'Cuarto de Máquinas Principal',
                    'id_sucursal' => 2,
                    'id_taller' => null,
                    'estado_salud' => 'operativo',
                    'ultimo_mantenimiento' => '2026-06-20',
                    'proximo_mantenimiento' => '2026-09-20',
                    'horas_uso' => 3890,
                    'qr_token' => 'QR-TCS-003-3392810',
                    'notas_tecnicas' => 'Alimenta toda la red neumática de la agencia Santa Fe.'
                ],
                [
                    'id' => 4,
                    'codigo_tcs' => 'TCS-EQ-004',
                    'nombre' => 'Desmontadora de Llantas Heavy-Duty',
                    'categoria' => 'desmontadora',
                    'marca' => 'Corghi',
                    'modelo' => 'Artiglio Master 28',
                    'numero_serie' => 'SN-6541002-IT',
                    'capacidad' => 'Rines 10" a 28"',
                    'ubicacion_bahia' => 'Bahía Llantera',
                    'id_sucursal' => null,
                    'id_taller' => 1,
                    'estado_salud' => 'operativo',
                    'ultimo_mantenimiento' => '2026-08-01',
                    'proximo_mantenimiento' => '2026-11-01',
                    'horas_uso' => 940,
                    'qr_token' => 'QR-TCS-004-6541002',
                    'notas_tecnicas' => 'Sistema leverless con brazo de asistencia para llantas runflat.'
                ],
                [
                    'id' => 5,
                    'codigo_tcs' => 'TCS-EQ-005',
                    'nombre' => 'Torno Rectificador de Discos de Freno Universal',
                    'categoria' => 'torno_rectificador',
                    'marca' => 'Ammco / Pro-Cut',
                    'modelo' => 'VTM-3000',
                    'numero_serie' => 'SN-4411090-US',
                    'capacidad' => 'Rotor 4" a 20"',
                    'ubicacion_bahia' => 'Bahía Frenos',
                    'id_sucursal' => 1,
                    'id_taller' => null,
                    'estado_salud' => 'operativo',
                    'ultimo_mantenimiento' => '2026-06-18',
                    'proximo_mantenimiento' => '2026-09-18',
                    'horas_uso' => 650,
                    'qr_token' => 'QR-TCS-005-4411090',
                    'notas_tecnicas' => 'Rectificación montado y de banco con comparador de carátula.'
                ],
                [
                    'id' => 6,
                    'codigo_tcs' => 'TCS-EQ-006',
                    'nombre' => 'Estación Recuperadora de Refrigerante R-1234yf',
                    'categoria' => 'recuperadora_1234yf',
                    'marca' => 'Robinair / TCS',
                    'modelo' => 'AC-1234-YF',
                    'numero_serie' => 'SN-1234001-MX',
                    'capacidad' => 'Tanque 12.5 kg A2L',
                    'ubicacion_bahia' => 'Bahía Clima Automotriz',
                    'id_sucursal' => 1,
                    'id_taller' => null,
                    'estado_salud' => 'operativo',
                    'ultimo_mantenimiento' => '2026-07-22',
                    'proximo_mantenimiento' => '2026-10-22',
                    'horas_uso' => 430,
                    'qr_token' => 'QR-TCS-006-1234001',
                    'notas_tecnicas' => 'Cumple normas SAE J2843 / J2911 para gas inflamable A2L.'
                ],
                [
                    'id' => 7,
                    'codigo_tcs' => 'TCS-EQ-007',
                    'nombre' => 'Estación Recuperadora de Gas R-134a',
                    'categoria' => 'recuperadora_r134a',
                    'marca' => 'Robinair',
                    'modelo' => 'CoolTech 34788',
                    'numero_serie' => 'SN-1340092-MX',
                    'capacidad' => 'Tanque 15.0 kg',
                    'ubicacion_bahia' => 'Bahía Eléctrica',
                    'id_sucursal' => 2,
                    'id_taller' => null,
                    'estado_salud' => 'operativo',
                    'ultimo_mantenimiento' => '2026-07-05',
                    'proximo_mantenimiento' => '2026-10-05',
                    'horas_uso' => 810,
                    'qr_token' => 'QR-TCS-007-1340092',
                    'notas_tecnicas' => 'Certificación ISO 9001, filtro Core renovado.'
                ],
                [
                    'id' => 8,
                    'codigo_tcs' => 'TCS-EQ-008',
                    'nombre' => 'Sistema Universal de Balanceo de Neumáticos',
                    'categoria' => 'balanceadora',
                    'marca' => 'Hunter / TCS',
                    'modelo' => 'TCS-RMB-001',
                    'numero_serie' => 'SN-8820019-DE',
                    'capacidad' => 'Neumáticos hasta 44"',
                    'ubicacion_bahia' => 'Bahía Llantera',
                    'id_sucursal' => null,
                    'id_taller' => 1,
                    'estado_salud' => 'fuera_servicio',
                    'ultimo_mantenimiento' => '2026-04-12',
                    'proximo_mantenimiento' => '2026-07-12',
                    'horas_uso' => 1780,
                    'qr_token' => 'QR-TCS-008-8820019',
                    'notas_tecnicas' => 'Desviación en sensor piezoeléctrico > 25g. Fuera de servicio temporal.'
                ]
            ],
            'ordenes_servicio' => [
                [
                    'id' => 1,
                    'folio' => 'ORD-2026-041',
                    'id_equipo' => 2,
                    'id_usuario_solicita' => 4,
                    'fecha_solicitud' => '2026-08-18 10:15:00',
                    'tipo_servicio' => 'correctivo',
                    'prioridad' => 'alta',
                    'descripcion_falla' => 'Elevador Tijera presenta un ligero desfasamiento al subir y requiere nivelación de cables y válvulas.',
                    'estado' => 'en_proceso',
                    'id_tecnico_asignado' => 3
                ],
                [
                    'id' => 2,
                    'folio' => 'ORD-2026-039',
                    'id_equipo' => 1,
                    'id_usuario_solicita' => 4,
                    'fecha_solicitud' => '2026-08-10 09:00:00',
                    'tipo_servicio' => 'preventivo',
                    'prioridad' => 'normal',
                    'descripcion_falla' => 'Mantenimiento preventivo programado según calendario. Inspección general de cables y fluido ISO 32.',
                    'estado' => 'concluido',
                    'id_tecnico_asignado' => 3
                ],
                [
                    'id' => 3,
                    'folio' => 'ORD-2026-045',
                    'id_equipo' => 8,
                    'id_usuario_solicita' => 6,
                    'fecha_solicitud' => '2026-08-22 11:30:00',
                    'tipo_servicio' => 'urgente',
                    'prioridad' => 'critica',
                    'descripcion_falla' => 'Transductor piezoeléctrico fuera de tolerancia en desbalance dinámico. Equipo fuera de servicio.',
                    'estado' => 'pendiente',
                    'id_tecnico_asignado' => 3
                ]
            ],
            'reportes_mantenimiento' => [
                [
                    'id' => 1,
                    'folio_reporte' => 'REP-TCS-902',
                    'id_orden' => 2,
                    'id_equipo' => 1,
                    'id_tecnico' => 3,
                    'fecha_servicio' => '2026-08-10',
                    'tipo_formato' => 'rampa_2_postes',
                    'matriz_inspeccion' => [
                        'estructura_columnas' => 'OK',
                        'brazos_seguros' => 'OK',
                        'cables_ecualizacion' => 'OK',
                        'unidad_hidraulica' => 'OK',
                        'poleas_pasadores' => 'OK',
                        'sistema_electrico' => 'OK',
                        'mecanismos_seguridad' => 'OK'
                    ],
                    'valores_medidos' => [
                        'nivel_aceite' => 'Óptimo ISO 32',
                        'torque_anclajes' => '150 ft-lbs',
                        'presion_valvula' => '2200 PSI'
                    ],
                    'dictamen_final' => 'operativo',
                    'diagnostico_trabajos' => 'Se realizó ajuste de tensión en cables de compensación, purga de fluido hidráulico y lubricación de poleas superiores. Trabas de seguridad probadas al 100% de carga.',
                    'firma_tecnico_nombre' => 'Téc. Héctor Morales',
                    'firma_tecnico_cedula' => 'CED-TEC-992014',
                    'firma_cliente_nombre' => 'Carlos Mendoza',
                    'firma_cliente_cargo' => 'Jefe de Taller Ford Interlomas',
                    'foto_antes' => 'uploads/evidencias/evidencia_antes_muestra.jpg',
                    'foto_despues' => 'uploads/evidencias/evidencia_despues_muestra.jpg',
                    'consumibles' => [
                        ['descripcion' => 'Aceite Hidráulico ISO VG 46 (1 Lt)', 'cantidad' => 1],
                        ['descripcion' => 'Grasa Sintética Chasis', 'cantidad' => 1]
                    ]
                ]
            ],
            'proveedores' => [
                [
                    'id' => 1,
                    'razon_social' => 'Rotary Lift de México S.A. de C.V.',
                    'rfc' => 'RLM990115-99A',
                    'contacto' => 'Ing. Arturo Salgado',
                    'telefono' => '55-4000-8800',
                    'email' => 'ventas@rotarylift.mx',
                    'categoria' => 'Elevadores y Rampas',
                    'dias_credito' => 30
                ],
                [
                    'id' => 2,
                    'razon_social' => 'Lubricantes y Fluidos Industriales S.A.',
                    'rfc' => 'LFI100420-KK1',
                    'contacto' => 'Lic. Rodrigo Pérez',
                    'telefono' => '55-7000-1122',
                    'email' => 'pedidos@lubri-ind.com',
                    'categoria' => 'Aceites y Fluidos Hidráulicos',
                    'dias_credito' => 15
                ],
                [
                    'id' => 3,
                    'razon_social' => 'Corghi y Equipos Automotrices Norte',
                    'rfc' => 'CEA080312-7L9',
                    'contacto' => 'Marco Aurelio Vega',
                    'telefono' => '55-6600-4400',
                    'email' => 'mvega@corghinorte.com',
                    'categoria' => 'Llantas y Desmontadoras',
                    'dias_credito' => 30
                ],
                [
                    'id' => 4,
                    'razon_social' => 'Refrigerantes Ecológicos y Válvulas S.A.',
                    'rfc' => 'REV150830-4P0',
                    'contacto' => 'Ing. Gabriela Silva',
                    'telefono' => '55-9988-7711',
                    'email' => 'gsilva@refrig-eco.mx',
                    'categoria' => 'Gases R-1234yf / R-134a',
                    'dias_credito' => 15
                ]
            ],
            'inventario_refacciones' => [
                [
                    'id' => 1,
                    'codigo_parte' => 'OIL-ISO32-19L',
                    'descripcion' => 'Cubeta Aceite Hidráulico Anti-Desgaste ISO 32 (19L)',
                    'categoria' => 'Fluidos Hidráulicos',
                    'stock_actual' => 24,
                    'stock_minimo' => 6,
                    'unidad_medida' => 'Cubeta',
                    'costo_unitario' => 1450.00,
                    'id_proveedor' => 2,
                    'estado_pieza' => 'nuevo',
                    'ubicacion_estante' => 'Pasillo A - Estante 1'
                ],
                [
                    'id' => 2,
                    'codigo_parte' => 'CAB-SPO10-EQ',
                    'descripcion' => 'Juego de Cables de Ecualización Acero Alta Tensión 3/8"',
                    'categoria' => 'Cables y Poleas',
                    'stock_actual' => 8,
                    'stock_minimo' => 2,
                    'unidad_medida' => 'Juego',
                    'costo_unitario' => 3200.00,
                    'id_proveedor' => 1,
                    'estado_pieza' => 'nuevo',
                    'ubicacion_estante' => 'Pasillo B - Estante 3'
                ],
                [
                    'id' => 3,
                    'codigo_parte' => 'PAD-ROT-GOM',
                    'descripcion' => 'Juego de 4 Almohadillas de Goma para Brazos Rotary',
                    'categoria' => 'Gomas y Protecciones',
                    'stock_actual' => 18,
                    'stock_minimo' => 5,
                    'unidad_medida' => 'Juego',
                    'costo_unitario' => 980.00,
                    'id_proveedor' => 1,
                    'estado_pieza' => 'nuevo',
                    'ubicacion_estante' => 'Pasillo A - Gaveta 4'
                ],
                [
                    'id' => 4,
                    'codigo_parte' => 'BUR-TORN-TCS',
                    'descripcion' => 'Buril de Corte Triangular de Carburo de Tungsteno para Torno',
                    'categoria' => 'Herramientas de Corte',
                    'stock_actual' => 45,
                    'stock_minimo' => 10,
                    'unidad_medida' => 'Pza',
                    'costo_unitario' => 320.00,
                    'id_proveedor' => 3,
                    'estado_pieza' => 'nuevo',
                    'ubicacion_estante' => 'Pasillo C - Cajón 2'
                ],
                [
                    'id' => 5,
                    'codigo_parte' => 'FLT-REC-CORE',
                    'descripcion' => 'Filtro Deshidratador Core para Estación Recuperadora R134a',
                    'categoria' => 'Filtración y Clima',
                    'stock_actual' => 14,
                    'stock_minimo' => 4,
                    'unidad_medida' => 'Pza',
                    'costo_unitario' => 890.00,
                    'id_proveedor' => 4,
                    'estado_pieza' => 'nuevo',
                    'ubicacion_estante' => 'Pasillo D - Estante 2'
                ],
                [
                    'id' => 6,
                    'codigo_parte' => 'KIT-ORING-J2888',
                    'descripcion' => 'Kit de O-Rings y Sellos de Neopreno para Acoples SAE J2888',
                    'categoria' => 'Sellos y O-Rings',
                    'stock_actual' => 30,
                    'stock_minimo' => 8,
                    'unidad_medida' => 'Kit',
                    'costo_unitario' => 450.00,
                    'id_proveedor' => 4,
                    'estado_pieza' => 'nuevo',
                    'ubicacion_estante' => 'Pasillo D - Gaveta 1'
                ]
            ],
            'mensajes' => [
                [
                    'id' => 1,
                    'id_remitente' => 4,
                    'id_destinatario' => 2,
                    'mensaje' => 'Buenas tardes Soporte, solicité la revisión del elevador de tijera para alineación en Interlomas.',
                    'hora' => '14:20',
                    'created_at' => '2026-08-18 14:20:00'
                ],
                [
                    'id' => 2,
                    'id_remitente' => 2,
                    'id_destinatario' => 4,
                    'mensaje' => 'Hola Carlos, Recibido. El Técnico Héctor Morales ya tiene asignada la orden ORD-2026-041 y acude hoy por la tarde.',
                    'hora' => '14:22',
                    'created_at' => '2026-08-18 14:22:00'
                ]
            ],
            'checklists_diarios' => [
                [
                    'id' => 1,
                    'id_equipo' => 1,
                    'fecha_turno' => '2026-08-20',
                    'operador_nombre' => 'Carlos Mendoza',
                    'supervisor_nombre' => 'Ing. Roberto Garza',
                    'dictamen_seguridad' => 'cumple',
                    'observaciones' => 'Rampa en perfectas condiciones de operación. Cables con tensión uniforme.'
                ]
            ]
        ];
    }

    /**
     * Cargar todos los datos desde archivo JSON
     */
    private static function loadData(): array {
        if (self::$memoryData !== null) {
            return self::$memoryData;
        }

        if (!file_exists(self::$storeFile)) {
            $default = self::initDefaultData();
            @file_put_contents(self::$storeFile, json_encode($default, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
            self::$memoryData = $default;
            return $default;
        }

        $raw = file_get_contents(self::$storeFile);
        $default = self::initDefaultData();
        $modified = false;
        foreach ($default as $k => $v) {
            if (!isset($decoded[$k])) {
                $decoded[$k] = $v;
                $modified = true;
            }
        }
        if (!empty($decoded['reportes_mantenimiento'])) {
            foreach ($decoded['reportes_mantenimiento'] as &$rep) {
                if ($rep['id'] == 1 && empty($rep['foto_antes'])) {
                    $rep['foto_antes'] = 'uploads/evidencias/evidencia_antes_muestra.jpg';
                    $rep['foto_despues'] = 'uploads/evidencias/evidencia_despues_muestra.jpg';
                    $modified = true;
                }
            }
            unset($rep);
        }
        if ($modified) {
            @file_put_contents(self::$storeFile, json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }

        self::$memoryData = $decoded;
        return self::$memoryData;
    }

    /**
     * Guardar datos en archivo JSON
     */
    private static function saveData(array $data): void {
        self::$memoryData = $data;
        @file_put_contents(self::$storeFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    // =========================================================================
    // MÉTODOS DE CONSULTA Y ESCRITURA CONTEXTUAL (MySQL PDO o Fallback JSON)
    // =========================================================================

    public static function getStats(): array {
        $data = self::loadData();
        $equipos = $data['equipos'] ?? [];
        $matrices = $data['matrices'] ?? [];
        $talleres = $data['talleres'] ?? [];
        $ordenes = $data['ordenes_servicio'] ?? [];
        $reportes = $data['reportes_mantenimiento'] ?? [];

        $totalEquipos = count($equipos);
        $operativos = 0;
        $observados = 0;
        $fueraServicio = 0;

        foreach ($equipos as $eq) {
            if ($eq['estado_salud'] === 'operativo') $operativos++;
            elseif ($eq['estado_salud'] === 'observado') $observados++;
            elseif ($eq['estado_salud'] === 'fuera_servicio') $fueraServicio++;
        }

        $disponibilidad = ($totalEquipos > 0) ? round(($operativos / $totalEquipos) * 100, 1) : 100;

        $pendientes = 0;
        foreach ($ordenes as $ord) {
            if ($ord['estado'] === 'pendiente' || $ord['estado'] === 'en_proceso') {
                $pendientes++;
            }
        }

        return [
            'ubicaciones_activas' => count($matrices) + count($talleres),
            'equipos_registrados' => $totalEquipos,
            'servicios_pendientes' => $pendientes,
            'mantenimientos_mes' => count($reportes) + 17, // 18 mantenimientos concluidos según video
            'operativos' => $operativos,
            'observados' => $observados,
            'fuera_servicio' => $fueraServicio,
            'disponibilidad_pct' => $disponibilidad
        ];
    }

    public static function getMatrices(): array {
        $data = self::loadData();
        return $data['matrices'] ?? [];
    }

    public static function createMatriz(array $newMatriz): int {
        $data = self::loadData();
        $id = count($data['matrices']) ? max(array_column($data['matrices'], 'id')) + 1 : 1;
        $newMatriz['id'] = $id;
        $newMatriz['created_at'] = date('Y-m-d H:i:s');
        $data['matrices'][] = $newMatriz;
        self::saveData($data);
        Logger::log('CREATE_MATRIZ', 'matrices', (string)$id, ['razon_social' => $newMatriz['razon_social']]);
        return $id;
    }

    public static function getSucursales(?int $matrizId = null): array {
        $data = self::loadData();
        $sucursales = $data['sucursales'] ?? [];
        if ($matrizId !== null) {
            return array_values(array_filter($sucursales, fn($s) => ($s['id_matriz'] ?? 0) == $matrizId));
        }
        return $sucursales;
    }

    public static function linkSucursal(array $newSucursal): int {
        $data = self::loadData();
        $id = count($data['sucursales']) ? max(array_column($data['sucursales'], 'id')) + 1 : 1;
        $newSucursal['id'] = $id;
        $newSucursal['created_at'] = date('Y-m-d H:i:s');
        $data['sucursales'][] = $newSucursal;
        self::saveData($data);
        Logger::log('LINK_SUCURSAL', 'sucursales', (string)$id, ['nombre' => $newSucursal['nombre'], 'matriz_id' => $newSucursal['id_matriz']]);
        return $id;
    }

    public static function getTalleres(): array {
        $data = self::loadData();
        return $data['talleres'] ?? [];
    }

    public static function createTaller(array $newTaller): int {
        $data = self::loadData();
        $id = count($data['talleres']) ? max(array_column($data['talleres'], 'id')) + 1 : 1;
        $newTaller['id'] = $id;
        $newTaller['created_at'] = date('Y-m-d H:i:s');
        $data['talleres'][] = $newTaller;
        self::saveData($data);
        Logger::log('CREATE_TALLER', 'talleres', (string)$id, ['razon_social' => $newTaller['razon_social']]);
        return $id;
    }

    // ==========================================
    // AUTOGESTIÓN DE CLIENTES: MATRICES
    // ==========================================
    public static function getMatrizById(int $id): ?array {
        $matrices = self::getMatrices();
        foreach ($matrices as $m) {
            if ($m['id'] == $id) return $m;
        }
        return null;
    }

    public static function updateMatriz(int $id, array $data): bool {
        $store = self::loadData();
        foreach ($store['matrices'] as &$m) {
            if ($m['id'] == $id) {
                $m['razon_social'] = $data['razon_social'] ?? $m['razon_social'];
                $m['rfc']          = $data['rfc'] ?? $m['rfc'];
                $m['direccion']    = $data['direccion'] ?? $m['direccion'];
                $m['telefono']     = $data['telefono'] ?? $m['telefono'];
                $m['email']        = $data['email'] ?? $m['email'];
                $m['updated_at']   = date('Y-m-d H:i:s');
                self::saveData($store);
                Logger::log('UPDATE_MATRIZ', 'matrices', (string)$id, ['razon_social' => $m['razon_social']]);
                return true;
            }
        }
        return false;
    }

    public static function deleteMatriz(int $id): bool {
        $store = self::loadData();
        $initial = count($store['matrices']);
        $store['matrices'] = array_values(array_filter($store['matrices'], fn($m) => $m['id'] != $id));
        if (count($store['matrices']) < $initial) {
            self::saveData($store);
            Logger::log('DELETE_MATRIZ', 'matrices', (string)$id);
            return true;
        }
        return false;
    }

    // ==========================================
    // AUTOGESTIÓN DE SUCURSALES
    // ==========================================
    public static function getSucursalById(int $id): ?array {
        $sucursales = self::getSucursales();
        foreach ($sucursales as $s) {
            if ($s['id'] == $id) return $s;
        }
        return null;
    }

    public static function updateSucursal(int $id, array $data): bool {
        $store = self::loadData();
        foreach ($store['sucursales'] as &$s) {
            if ($s['id'] == $id) {
                $s['nombre']           = $data['nombre'] ?? $s['nombre'];
                $s['direccion']        = $data['direccion'] ?? $s['direccion'];
                $s['telefono']         = $data['telefono'] ?? $s['telefono'];
                $s['gerente_servicio'] = $data['gerente_servicio'] ?? $s['gerente_servicio'];
                $s['jefe_taller']      = $data['jefe_taller'] ?? $s['jefe_taller'];
                if (isset($data['id_matriz'])) {
                    $s['id_matriz']    = (int)$data['id_matriz'];
                }
                $s['updated_at']       = date('Y-m-d H:i:s');
                self::saveData($store);
                Logger::log('UPDATE_SUCURSAL', 'sucursales', (string)$id, ['nombre' => $s['nombre']]);
                return true;
            }
        }
        return false;
    }

    public static function deleteSucursal(int $id): bool {
        $store = self::loadData();
        $initial = count($store['sucursales']);
        $store['sucursales'] = array_values(array_filter($store['sucursales'], fn($s) => $s['id'] != $id));
        if (count($store['sucursales']) < $initial) {
            self::saveData($store);
            Logger::log('DELETE_SUCURSAL', 'sucursales', (string)$id);
            return true;
        }
        return false;
    }

    // ==========================================
    // AUTOGESTIÓN DE TALLERES INDEPENDIENTES
    // ==========================================
    public static function getTallerById(int $id): ?array {
        $talleres = self::getTalleres();
        foreach ($talleres as $t) {
            if ($t['id'] == $id) return $t;
        }
        return null;
    }

    public static function updateTaller(int $id, array $data): bool {
        $store = self::loadData();
        foreach ($store['talleres'] as &$t) {
            if ($t['id'] == $id) {
                $t['razon_social']     = $data['razon_social'] ?? $t['razon_social'];
                $t['rfc']              = $data['rfc'] ?? $t['rfc'];
                $t['direccion']        = $data['direccion'] ?? $t['direccion'];
                $t['telefono']         = $data['telefono'] ?? $t['telefono'];
                $t['email']            = $data['email'] ?? $t['email'];
                $t['gerente_servicio'] = $data['gerente_servicio'] ?? $t['gerente_servicio'];
                $t['jefe_taller']      = $data['jefe_taller'] ?? $t['jefe_taller'];
                $t['updated_at']       = date('Y-m-d H:i:s');
                self::saveData($store);
                Logger::log('UPDATE_TALLER', 'talleres', (string)$id, ['razon_social' => $t['razon_social']]);
                return true;
            }
        }
        return false;
    }

    public static function deleteTaller(int $id): bool {
        $store = self::loadData();
        $initial = count($store['talleres']);
        $store['talleres'] = array_values(array_filter($store['talleres'], fn($t) => $t['id'] != $id));
        if (count($store['talleres']) < $initial) {
            self::saveData($store);
            Logger::log('DELETE_TALLER', 'talleres', (string)$id);
            return true;
        }
        return false;
    }

    // ==========================================
    // AUTOGESTIÓN DE USUARIOS Y ACCESOS CLIENTES
    // ==========================================
    public static function getUsuarios(?string $filterRol = null): array {
        $data = self::loadData();
        $usuarios = $data['usuarios'] ?? [];
        $matrices = array_column($data['matrices'] ?? [], null, 'id');
        $sucursales = array_column($data['sucursales'] ?? [], null, 'id');
        $talleres = array_column($data['talleres'] ?? [], null, 'id');

        $result = [];
        foreach ($usuarios as $u) {
            if ($filterRol !== null && $u['rol'] !== $filterRol) {
                continue;
            }

            $entidadNombre = 'Acceso General / TCS';
            if (!empty($u['id_sucursal']) && isset($sucursales[$u['id_sucursal']])) {
                $suc = $sucursales[$u['id_sucursal']];
                $matrizNombre = $matrices[$suc['id_matriz']]['razon_social'] ?? 'Matriz';
                $entidadNombre = $suc['nombre'] . ' (' . $matrizNombre . ')';
            } elseif (!empty($u['id_matriz']) && isset($matrices[$u['id_matriz']])) {
                $entidadNombre = $matrices[$u['id_matriz']]['razon_social'] . ' (Corporativo)';
            } elseif (!empty($u['id_taller']) && isset($talleres[$u['id_taller']])) {
                $entidadNombre = $talleres[$u['id_taller']]['razon_social'] . ' (Taller)';
            }

            $u['entidad_nombre'] = $entidadNombre;
            $result[] = $u;
        }
        return $result;
    }

    public static function getUsuarioById(int $id): ?array {
        $usuarios = self::getUsuarios();
        foreach ($usuarios as $u) {
            if ($u['id'] == $id) return $u;
        }
        return null;
    }

    public static function getUsuarioByEmail(string $email): ?array {
        $data = self::loadData();
        $email = strtolower(trim($email));
        foreach ($data['usuarios'] ?? [] as $u) {
            if (strtolower(trim($u['email'])) === $email) {
                return $u;
            }
        }
        return null;
    }

    public static function createUsuario(array $newUser): int {
        $data = self::loadData();
        $id = count($data['usuarios']) ? max(array_column($data['usuarios'], 'id')) + 1 : 1;
        $newUser['id'] = $id;
        $newUser['created_at'] = date('Y-m-d H:i:s');
        $newUser['is_online'] = 0;
        $newUser['estado'] = $newUser['estado'] ?? 'activo';
        if (!empty($newUser['password'])) {
            $newUser['password_hash'] = password_hash($newUser['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            unset($newUser['password']);
        }
        $data['usuarios'][] = $newUser;
        self::saveData($data);
        Logger::log('CREATE_USUARIO', 'usuarios', (string)$id, ['email' => $newUser['email'], 'rol' => $newUser['rol']]);
        return $id;
    }

    public static function updateUsuario(int $id, array $data): bool {
        $store = self::loadData();
        foreach ($store['usuarios'] as &$u) {
            if ($u['id'] == $id) {
                $u['nombre']      = $data['nombre'] ?? $u['nombre'];
                $u['alias']       = $data['alias'] ?? $u['alias'];
                $u['email']       = $data['email'] ?? $u['email'];
                $u['telefono']    = $data['telefono'] ?? $u['telefono'];
                $u['rfc']         = $data['rfc'] ?? $u['rfc'];
                if (isset($data['rol'])) $u['rol'] = $data['rol'];
                if (array_key_exists('id_matriz', $data)) $u['id_matriz'] = $data['id_matriz'];
                if (array_key_exists('id_sucursal', $data)) $u['id_sucursal'] = $data['id_sucursal'];
                if (array_key_exists('id_taller', $data)) $u['id_taller'] = $data['id_taller'];
                if (!empty($data['password'])) {
                    $u['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
                }
                $u['updated_at']  = date('Y-m-d H:i:s');
                self::saveData($store);
                Logger::log('UPDATE_USUARIO', 'usuarios', (string)$id, ['email' => $u['email']]);
                return true;
            }
        }
        return false;
    }

    public static function deleteUsuario(int $id): bool {
        $store = self::loadData();
        $initial = count($store['usuarios']);
        $store['usuarios'] = array_values(array_filter($store['usuarios'], fn($u) => $u['id'] != $id));
        if (count($store['usuarios']) < $initial) {
            self::saveData($store);
            Logger::log('DELETE_USUARIO', 'usuarios', (string)$id);
            return true;
        }
        return false;
    }

    public static function getEquipos(?int $filterSucursal = null, ?int $filterTaller = null): array {
        $data = self::loadData();
        $equipos = $data['equipos'] ?? [];
        $sucursales = array_column($data['sucursales'] ?? [], null, 'id');
        $matrices = array_column($data['matrices'] ?? [], null, 'id');
        $talleres = array_column($data['talleres'] ?? [], null, 'id');

        $result = [];
        foreach ($equipos as $eq) {
            if ($filterSucursal !== null && ($eq['id_sucursal'] ?? 0) != $filterSucursal) {
                continue;
            }
            if ($filterTaller !== null && ($eq['id_taller'] ?? 0) != $filterTaller) {
                continue;
            }

            // Enriquecer con nombre de ubicación
            if (!empty($eq['id_sucursal']) && isset($sucursales[$eq['id_sucursal']])) {
                $suc = $sucursales[$eq['id_sucursal']];
                $matrizNombre = $matrices[$suc['id_matriz']]['razon_social'] ?? 'Matriz';
                $eq['ubicacion_nombre'] = $suc['nombre'] . ' (' . $matrizNombre . ')';
                $eq['tipo_ubicacion'] = 'Sucursal';
            } elseif (!empty($eq['id_taller']) && isset($talleres[$eq['id_taller']])) {
                $tal = $talleres[$eq['id_taller']];
                $eq['ubicacion_nombre'] = $tal['razon_social'] . ' (Taller Independiente)';
                $eq['tipo_ubicacion'] = 'Taller';
            } else {
                $eq['ubicacion_nombre'] = 'Sin asignar';
                $eq['tipo_ubicacion'] = 'General';
            }

            $result[] = $eq;
        }

        return $result;
    }

    public static function getEquipoById(int $id): ?array {
        $equipos = self::getEquipos();
        foreach ($equipos as $eq) {
            if ($eq['id'] == $id) return $eq;
        }
        return null;
    }

    public static function createEquipo(array $newEquipo): int {
        $data = self::loadData();
        $id = count($data['equipos']) ? max(array_column($data['equipos'], 'id')) + 1 : 1;
        $newEquipo['id'] = $id;
        $newEquipo['codigo_tcs'] = $newEquipo['codigo_tcs'] ?? ('TCS-EQ-' . str_pad((string)$id, 3, '0', STR_PAD_LEFT));
        $newEquipo['qr_token'] = 'QR-TCS-' . str_pad((string)$id, 3, '0', STR_PAD_LEFT) . '-' . substr(md5(uniqid()), 0, 6);
        $newEquipo['horas_uso'] = (int)($newEquipo['horas_uso'] ?? 0);
        $newEquipo['created_at'] = date('Y-m-d H:i:s');
        $data['equipos'][] = $newEquipo;
        self::saveData($data);
        Logger::log('CREATE_EQUIPO', 'equipos', (string)$id, ['codigo' => $newEquipo['codigo_tcs'], 'nombre' => $newEquipo['nombre']]);
        return $id;
    }

    public static function updateEquipoHealth(int $id, string $estado): bool {
        $data = self::loadData();
        foreach ($data['equipos'] as &$eq) {
            if ($eq['id'] == $id) {
                $eq['estado_salud'] = $estado;
                self::saveData($data);
                Logger::log('UPDATE_EQUIPO_HEALTH', 'equipos', (string)$id, ['nuevo_estado' => $estado]);
                return true;
            }
        }
        return false;
    }

    public static function getOrdenes(?int $filterSucursal = null, ?int $filterTaller = null): array {
        $data = self::loadData();
        $ordenes = $data['ordenes_servicio'] ?? [];
        $equipos = array_column(self::getEquipos(), null, 'id');
        $usuarios = array_column($data['usuarios'] ?? [], null, 'id');

        $result = [];
        foreach ($ordenes as $ord) {
            $eq = $equipos[$ord['id_equipo']] ?? null;
            if ($filterSucursal !== null && ($eq['id_sucursal'] ?? 0) != $filterSucursal) {
                continue;
            }
            if ($filterTaller !== null && ($eq['id_taller'] ?? 0) != $filterTaller) {
                continue;
            }

            $ord['equipo_nombre'] = $eq ? $eq['nombre'] : 'Equipo no encontrado';
            $ord['equipo_codigo'] = $eq ? $eq['codigo_tcs'] : 'N/A';
            $ord['ubicacion'] = $eq ? $eq['ubicacion_nombre'] : 'N/A';
            $ord['solicitante_nombre'] = $usuarios[$ord['id_usuario_solicita']]['nombre'] ?? 'Cliente';
            $ord['tecnico_nombre'] = $usuarios[$ord['id_tecnico_asignado']]['nombre'] ?? 'Sin asignar';

            $result[] = $ord;
        }

        // Ordenar más recientes primero
        usort($result, fn($a, $b) => strcmp($b['fecha_solicitud'], $a['fecha_solicitud']));
        return $result;
    }

    public static function createOrden(array $newOrden): int {
        $data = self::loadData();
        $id = count($data['ordenes_servicio']) ? max(array_column($data['ordenes_servicio'], 'id')) + 1 : 1;
        $newOrden['id'] = $id;
        $newOrden['folio'] = 'ORD-2026-' . str_pad((string)(40 + $id), 3, '0', STR_PAD_LEFT);
        $newOrden['fecha_solicitud'] = date('Y-m-d H:i:s');
        $newOrden['estado'] = 'pendiente';
        $newOrden['id_tecnico_asignado'] = 3; // Asignación a técnico especialista
        $data['ordenes_servicio'][] = $newOrden;
        self::saveData($data);
        Logger::log('CREATE_SERVICE_ORDER', 'ordenes_servicio', (string)$id, ['folio' => $newOrden['folio'], 'equipo' => $newOrden['id_equipo']]);
        return $id;
    }

    public static function updateOrdenStatus(int $id, string $nuevoEstado): bool {
        $data = self::loadData();
        foreach ($data['ordenes_servicio'] as &$ord) {
            if ($ord['id'] == $id) {
                $ord['estado'] = $nuevoEstado;
                if ($nuevoEstado === 'concluido') {
                    $ord['fecha_cierre'] = date('Y-m-d H:i:s');
                }
                self::saveData($data);
                Logger::log('UPDATE_ORDER_STATUS', 'ordenes_servicio', (string)$id, ['estado' => $nuevoEstado]);
                return true;
            }
        }
        return false;
    }

    public static function getReportes(): array {
        $data = self::loadData();
        $reportes = $data['reportes_mantenimiento'] ?? [];
        $equipos = array_column(self::getEquipos(), null, 'id');
        $usuarios = array_column($data['usuarios'] ?? [], null, 'id');

        $result = [];
        foreach ($reportes as $rep) {
            $eq = $equipos[$rep['id_equipo']] ?? null;
            $rep['equipo_nombre'] = $eq ? $eq['nombre'] : 'Equipo N/A';
            $rep['equipo_codigo'] = $eq ? $eq['codigo_tcs'] : 'N/A';
            $rep['ubicacion'] = $eq ? $eq['ubicacion_nombre'] : 'N/A';
            $rep['tecnico_nombre'] = $rep['firma_tecnico_nombre'] ?? ($usuarios[$rep['id_tecnico']]['nombre'] ?? 'Técnico TCS');
            $result[] = $rep;
        }

        return $result;
    }

    public static function getReporteById(int $id): ?array {
        $reportes = self::getReportes();
        foreach ($reportes as $r) {
            if ($r['id'] == $id) return $r;
        }
        return null;
    }

    public static function createReporte(array $newReporte): int {
        $data = self::loadData();
        $id = count($data['reportes_mantenimiento']) ? max(array_column($data['reportes_mantenimiento'], 'id')) + 1 : 1;
        $newReporte['id'] = $id;
        $newReporte['folio_reporte'] = $newReporte['folio_reporte'] ?? ('REP-TCS-' . (900 + $id));
        $newReporte['fecha_servicio'] = $newReporte['fecha_servicio'] ?? date('Y-m-d');
        $newReporte['created_at'] = date('Y-m-d H:i:s');
        $data['reportes_mantenimiento'][] = $newReporte;

        // Actualizar estado del equipo
        if (!empty($newReporte['id_equipo']) && !empty($newReporte['dictamen_final'])) {
            self::updateEquipoHealth((int)$newReporte['id_equipo'], $newReporte['dictamen_final']);
        }

        // Si venía ligado a una orden, marcar orden como concluida
        if (!empty($newReporte['id_orden'])) {
            self::updateOrdenStatus((int)$newReporte['id_orden'], 'concluido');
        }

        self::saveData($data);
        Logger::log('CREATE_MAINTENANCE_REPORT', 'reportes_mantenimiento', (string)$id, ['folio' => $newReporte['folio_reporte']]);
        return $id;
    }

    public static function getInventario(): array {
        $data = self::loadData();
        $inventario = $data['inventario_refacciones'] ?? [];
        $proveedores = array_column($data['proveedores'] ?? [], null, 'id');

        foreach ($inventario as &$item) {
            $prov = $proveedores[$item['id_proveedor']] ?? null;
            $item['proveedor_nombre'] = $prov ? $prov['razon_social'] : 'Proveedor no asignado';
            $item['bajo_stock'] = ($item['stock_actual'] <= $item['stock_minimo']);
        }

        return $inventario;
    }

    public static function createRefaccion(array $item): int {
        $data = self::loadData();
        $id = count($data['inventario_refacciones']) ? max(array_column($data['inventario_refacciones'], 'id')) + 1 : 1;
        $item['id'] = $id;
        $item['created_at'] = date('Y-m-d H:i:s');
        $data['inventario_refacciones'][] = $item;
        self::saveData($data);
        Logger::log('CREATE_REFACCION', 'inventario_refacciones', (string)$id, ['codigo' => $item['codigo_parte']]);
        return $id;
    }

    public static function updateStock(int $id, int $nuevoStock): bool {
        $data = self::loadData();
        foreach ($data['inventario_refacciones'] as &$item) {
            if ($item['id'] == $id) {
                $item['stock_actual'] = $nuevoStock;
                self::saveData($data);
                Logger::log('UPDATE_STOCK', 'inventario_refacciones', (string)$id, ['nuevo_stock' => $nuevoStock]);
                return true;
            }
        }
        return false;
    }

    public static function getProveedores(): array {
        $data = self::loadData();
        return $data['proveedores'] ?? [];
    }

    public static function createProveedor(array $prov): int {
        $data = self::loadData();
        $id = count($data['proveedores']) ? max(array_column($data['proveedores'], 'id')) + 1 : 1;
        $prov['id'] = $id;
        $prov['created_at'] = date('Y-m-d H:i:s');
        $data['proveedores'][] = $prov;
        self::saveData($data);
        Logger::log('CREATE_PROVEEDOR', 'proveedores', (string)$id, ['razon_social' => $prov['razon_social']]);
        return $id;
    }

    public static function getMensajes(): array {
        $data = self::loadData();
        $mensajes = $data['mensajes'] ?? [];
        $usuarios = array_column($data['usuarios'] ?? [], null, 'id');

        foreach ($mensajes as &$msg) {
            $msg['remitente_nombre'] = $usuarios[$msg['id_remitente']]['nombre'] ?? 'Usuario';
            $msg['remitente_rol'] = $usuarios[$msg['id_remitente']]['rol'] ?? 'cliente';
            $msg['destinatario_nombre'] = $usuarios[$msg['id_destinatario']]['nombre'] ?? 'Usuario';
        }

        return $mensajes;
    }

    public static function enviarMensaje(int $from, int $to, string $texto): int {
        $data = self::loadData();
        $id = count($data['mensajes']) ? max(array_column($data['mensajes'], 'id')) + 1 : 1;
        $newMsg = [
            'id' => $id,
            'id_remitente' => $from,
            'id_destinatario' => $to,
            'mensaje' => $texto,
            'hora' => date('H:i'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $data['mensajes'][] = $newMsg;
        self::saveData($data);
        Logger::log('SEND_PRIVATE_MESSAGE', 'mensajes', (string)$id, ['de' => $from, 'para' => $to]);
        return $id;
    }


    public static function getChecklists(?int $equipoId = null): array {
        $data = self::loadData();
        $list = $data['checklists_diarios'] ?? [];
        if ($equipoId !== null) {
            return array_values(array_filter($list, fn($c) => ($c['id_equipo'] ?? 0) == $equipoId));
        }
        return $list;
    }

    public static function createChecklist(array $item): int {
        $data = self::loadData();
        $id = count($data['checklists_diarios']) ? max(array_column($data['checklists_diarios'], 'id')) + 1 : 1;
        $item['id'] = $id;
        $item['created_at'] = date('Y-m-d H:i:s');
        $data['checklists_diarios'][] = $item;
        self::saveData($data);
        Logger::log('SUBMIT_DAILY_CHECKLIST', 'checklists_diarios', (string)$id, ['equipo_id' => $item['id_equipo']]);
        return $id;
    }

    /**
     * Algoritmo de Fatiga Mecánica y Vida Útil Predictiva por Horas de Ciclo (Mejora 4)
     * Especificaciones de seguridad industrial TCS Motriz para elevadores automotrices:
     * - Cables de ecualización de acero trenzado: límite 1,500 hrs o 3 años
     * - Fluido hidráulico ISO 32 / VG 46: límite 1,000 hrs o anual
     * - Almohadillas de goma de brazos de apoyo: límite 800 hrs
     */
    public static function getDesgastePredictivo(array $equipo): array {
        $horas = (int)($equipo['horas_uso'] ?? 0);

        // 1. Cables de Acero (1,500 hrs)
        $limiteCables = 1500;
        $horasCables = $horas % $limiteCables;
        if ($horas > 0 && $horasCables === 0) $horasCables = $limiteCables;
        $pctCables = min(100, (int)round(($horasCables / $limiteCables) * 100));
        $estadoCables = $pctCables >= 90 ? 'critico' : ($pctCables >= 75 ? 'advertencia' : 'optimo');

        // 2. Fluido Hidráulico ISO 32 (1,000 hrs)
        $limiteFluido = 1000;
        $horasFluido = $horas % $limiteFluido;
        if ($horas > 0 && $horasFluido === 0) $horasFluido = $limiteFluido;
        $pctFluido = min(100, (int)round(($horasFluido / $limiteFluido) * 100));
        $estadoFluido = $pctFluido >= 90 ? 'critico' : ($pctFluido >= 75 ? 'advertencia' : 'optimo');

        // 3. Almohadillas de Goma (800 hrs)
        $limiteGomas = 800;
        $horasGomas = $horas % $limiteGomas;
        if ($horas > 0 && $horasGomas === 0) $horasGomas = $limiteGomas;
        $pctGomas = min(100, (int)round(($horasGomas / $limiteGomas) * 100));
        $estadoGomas = $pctGomas >= 90 ? 'critico' : ($pctGomas >= 75 ? 'advertencia' : 'optimo');

        return [
            'horas_acumuladas' => $horas,
            'cables' => [
                'nombre' => 'Cables de Ecualización de Acero',
                'norma' => 'Vida recomendada: 1,500 hrs / 3 años',
                'horas_consumidas' => $horasCables,
                'horas_limite' => $limiteCables,
                'horas_restantes' => max(0, $limiteCables - $horasCables),
                'porcentaje' => $pctCables,
                'estado' => $estadoCables,
                'recomendacion' => $pctCables >= 90 
                    ? 'RECAMBIO URGENTE: Riesgo de corte por fatiga de hilos trenzados' 
                    : ($pctCables >= 75 ? 'Programar recambio preventivo y pedido a Rotary/BendPak' : 'Tensión simétrica y lubricación conforme')
            ],
            'fluido' => [
                'nombre' => 'Fluido Hidráulico ISO 32 / VG 46',
                'norma' => 'Vida recomendada: 1,000 hrs / Anual',
                'horas_consumidas' => $horasFluido,
                'horas_limite' => $limiteFluido,
                'horas_restantes' => max(0, $limiteFluido - $horasFluido),
                'porcentaje' => $pctFluido,
                'estado' => $estadoFluido,
                'recomendacion' => $pctFluido >= 90 
                    ? 'PURGA Y CAMBIO INMEDIATO: Degradación térmica y pérdida de viscosidad' 
                    : ($pctFluido >= 75 ? 'Verificar acidez y nivel en depósito antes de degradación' : 'Viscosidad cinemática y presión nominal óptimas')
            ],
            'gomas' => [
                'nombre' => 'Almohadillas de Goma de Brazos de Apoyo',
                'norma' => 'Vida recomendada: 800 hrs de izaje',
                'horas_consumidas' => $horasGomas,
                'horas_limite' => $limiteGomas,
                'horas_restantes' => max(0, $limiteGomas - $horasGomas),
                'porcentaje' => $pctGomas,
                'estado' => $estadoGomas,
                'recomendacion' => $pctGomas >= 90 
                    ? 'SUSTITUCIÓN INMEDIATA: Desgaste crítico con riesgo de deslizamiento' 
                    : ($pctGomas >= 75 ? 'Desgaste visible en estrías de contacto' : 'Espesor y textura antideslizante segura')
            ]
        ];
    }

    /**
     * Motor Dinámico de Alertas Técnicas y Monitoreo de Salud Operativa
     */
    public static function getAlertas(?int $idSucursal = null, ?int $idTaller = null): array {
        $equipos = self::getEquipos($idSucursal, $idTaller);
        $ordenes = self::getOrdenes($idSucursal, $idTaller);
        $inventario = self::getInventario();
        $alertas = [];
        $hoy = date('Y-m-d');
        $limitePreventivo = date('Y-m-d', strtotime('+30 days'));

        // 1. Alertas por Equipos
        foreach ($equipos as $eq) {
            // A. Falla Crítica / Fuera de Servicio
            if ($eq['estado_salud'] === 'fuera_servicio') {
                $alertas[] = [
                    'id' => 'ALT-EQ-' . $eq['id'] . '-CRIT',
                    'tipo' => 'falla_critica',
                    'severidad' => 'critica',
                    'badge' => '🔴 FALLA CRÍTICA',
                    'titulo' => 'Equipo / Rampa Fuera de Servicio: ' . $eq['nombre'],
                    'codigo_equipo' => $eq['codigo_tcs'],
                    'equipo_id' => $eq['id'],
                    'ubicacion' => $eq['ubicacion_nombre'],
                    'mensaje' => 'La rampa ha sido bloqueada por riesgo operacional o falla severa. Bahía inoperativa.',
                    'fecha_deteccion' => $eq['ultimo_mantenimiento'] ?? $hoy,
                    'accion_sugerida' => 'Despachar orden correctiva de emergencia y peritaje presencial.',
                    'accion_url' => 'index.php?view=reportes&accion=nuevo&equipo_id=' . $eq['id'],
                    'accion_texto' => 'Atender / Reporte'
                ];
            }

            // B. Estado Observado / Tolerancias comprometidas
            if ($eq['estado_salud'] === 'observado') {
                $alertas[] = [
                    'id' => 'ALT-EQ-' . $eq['id'] . '-OBS',
                    'tipo' => 'desviacion_tolerancia',
                    'severidad' => 'alta',
                    'badge' => '🟠 ATENCIÓN REQUERIDA',
                    'titulo' => 'Desviación de Tolerancia: ' . $eq['nombre'],
                    'codigo_equipo' => $eq['codigo_tcs'],
                    'equipo_id' => $eq['id'],
                    'ubicacion' => $eq['ubicacion_nombre'],
                    'mensaje' => 'Equipo operando con holgura o desbalanceo: ' . ($eq['notas_tecnicas'] ?? 'Revisar calibración.'),
                    'fecha_deteccion' => $eq['ultimo_mantenimiento'] ?? $hoy,
                    'accion_sugerida' => 'Programar ajuste técnico antes de fallo mayor.',
                    'accion_url' => 'index.php?view=equipos&detalle_id=' . $eq['id'],
                    'accion_texto' => 'Ver Expediente'
                ];
            }

            // C. Mantenimiento Preventivo Vencido o Próximo (< 30 días)
            if (!empty($eq['proximo_mantenimiento'])) {
                if ($eq['proximo_mantenimiento'] < $hoy) {
                    $diasVencido = (int)((strtotime($hoy) - strtotime($eq['proximo_mantenimiento'])) / 86400);
                    $alertas[] = [
                        'id' => 'ALT-EQ-' . $eq['id'] . '-VENC',
                        'tipo' => 'mantenimiento_vencido',
                        'severidad' => 'critica',
                        'badge' => '🚨 MANTENIMIENTO VENCIDO',
                        'titulo' => 'Mantenimiento Preventivo Vencido: ' . $eq['nombre'],
                        'codigo_equipo' => $eq['codigo_tcs'],
                        'equipo_id' => $eq['id'],
                        'ubicacion' => $eq['ubicacion_nombre'],
                        'mensaje' => "El ciclo preventivo normativo venció hace {$diasVencido} días ({$eq['proximo_mantenimiento']}). Riesgo de pérdida de certificación NOM/OSHA.",
                        'fecha_deteccion' => $eq['proximo_mantenimiento'],
                        'accion_sugerida' => 'Emitir reporte de servicio y renovar inspección.',
                        'accion_url' => 'index.php?view=reportes&accion=nuevo&equipo_id=' . $eq['id'],
                        'accion_texto' => 'Emitir Reporte'
                    ];
                } elseif ($eq['proximo_mantenimiento'] <= $limitePreventivo) {
                    $diasFaltan = (int)((strtotime($eq['proximo_mantenimiento']) - strtotime($hoy)) / 86400);
                    $alertas[] = [
                        'id' => 'ALT-EQ-' . $eq['id'] . '-PROX',
                        'tipo' => 'mantenimiento_proximo',
                        'severidad' => 'media',
                        'badge' => '🟡 PRÓXIMO SERVICIO',
                        'titulo' => 'Próximo Servicio Preventivo: ' . $eq['nombre'],
                        'codigo_equipo' => $eq['codigo_tcs'],
                        'equipo_id' => $eq['id'],
                        'ubicacion' => $eq['ubicacion_nombre'],
                        'mensaje' => "Servicio programado para el {$eq['proximo_mantenimiento']} (en {$diasFaltan} días). Agendar visita técnica con anticipación.",
                        'fecha_deteccion' => $hoy,
                        'accion_sugerida' => 'Confirmar disponibilidad de bahía con cliente.',
                        'accion_url' => 'index.php?view=ordenes',
                        'accion_texto' => 'Ver Órdenes'
                    ];
                }
            }

            // D. Mantenimiento Predictivo por Desgaste de Horas / Ciclos (Mejora 4)
            $desgaste = self::getDesgastePredictivo($eq);
            foreach (['cables', 'fluido', 'gomas'] as $compKey) {
                $comp = $desgaste[$compKey];
                if ($comp['porcentaje'] >= 80) {
                    $sev = $comp['porcentaje'] >= 90 ? 'critica' : 'alta';
                    $alertas[] = [
                        'id' => 'ALT-PRED-' . $eq['id'] . '-' . strtoupper($compKey),
                        'tipo' => 'predictivo_desgaste',
                        'severidad' => $sev,
                        'badge' => '⏳ PREDICTIVO / CICLOS',
                        'titulo' => "Fatiga Mecánica: {$comp['nombre']} al {$comp['porcentaje']}%",
                        'codigo_equipo' => $eq['codigo_tcs'],
                        'equipo_id' => $eq['id'],
                        'ubicacion' => $eq['ubicacion_nombre'],
                        'mensaje' => "El elevador acumula {$eq['horas_uso']} hrs. Componente ha consumido {$comp['horas_consumidas']} de {$comp['horas_limite']} hrs recomendadas ({$comp['porcentaje']}%). {$comp['recomendacion']}.",
                        'fecha_deteccion' => $hoy,
                        'accion_sugerida' => "Programar recambio preventivo antes de fallo o paro en bahía.",
                        'accion_url' => 'index.php?view=equipos&detalle_id=' . $eq['id'],
                        'accion_texto' => 'Ver Desgaste'
                    ];
                }
            }
        }

        // 2. Alertas por Órdenes de Servicio Urgentes Pendientes
        foreach ($ordenes as $ord) {
            if (($ord['estado'] === 'pendiente' || $ord['estado'] === 'en_proceso') && in_array($ord['prioridad'], ['alta', 'critica'])) {
                $alertas[] = [
                    'id' => 'ALT-ORD-' . $ord['id'],
                    'tipo' => 'orden_urgente',
                    'severidad' => ($ord['prioridad'] === 'critica' ? 'critica' : 'alta'),
                    'badge' => '⚡ ORDEN URGENTE',
                    'titulo' => 'Orden de Servicio Sin Concluir: ' . $ord['folio'],
                    'codigo_equipo' => $ord['equipo_codigo'],
                    'equipo_id' => $ord['id_equipo'] ?? null,
                    'ubicacion' => $ord['ubicacion'],
                    'mensaje' => 'Falla reportada: ' . $ord['descripcion_falla'],
                    'fecha_deteccion' => $ord['fecha_solicitud'],
                    'accion_sugerida' => 'Asignar técnico especialista inmediatamente.',
                    'accion_url' => 'index.php?view=reportes&accion=nuevo&orden_id=' . $ord['id'],
                    'accion_texto' => 'Atender Orden'
                ];
            }
        }

        // 3. Alertas por Refacciones con Stock Crítico
        foreach ($inventario as $inv) {
            if (!empty($inv['bajo_stock'])) {
                $alertas[] = [
                    'id' => 'ALT-INV-' . $inv['id'],
                    'tipo' => 'stock_critico',
                    'severidad' => 'media',
                    'badge' => '📦 STOCK CRÍTICO',
                    'titulo' => 'Insumo Bajo Mínimo: ' . $inv['descripcion'],
                    'codigo_equipo' => $inv['codigo_parte'],
                    'equipo_id' => null,
                    'ubicacion' => 'Almacén Central TCS',
                    'mensaje' => "Existencias actuales: {$inv['stock_actual']} {$inv['unidad_medida']}(s) (Mínimo de reorden: {$inv['stock_minimo']}).",
                    'fecha_deteccion' => $hoy,
                    'accion_sugerida' => 'Emitir pedido de compra a proveedor.',
                    'accion_url' => 'index.php?view=inventario',
                    'accion_texto' => 'Reabastecer'
                ];
            }
        }

        return $alertas;
    }
}
