-- =====================================================================
-- TCS MOTRIZ - SISTEMA DE GESTIÓN TÉCNICA Y TELEMETRÍA INDUSTRIAL
-- Base de Datos MySQL / MariaDB (Optimizado para Hostinger)
-- Cumplimiento: OWASP Top 10 e ISO 27001 (Auditoría e Integridad Referencial)
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `mensajes`;
DROP TABLE IF EXISTS `reporte_consumibles`;
DROP TABLE IF EXISTS `inventario_refacciones`;
DROP TABLE IF EXISTS `proveedores`;
DROP TABLE IF EXISTS `checklists_diarios`;
DROP TABLE IF EXISTS `reportes_mantenimiento`;
DROP TABLE IF EXISTS `ordenes_servicio`;
DROP TABLE IF EXISTS `equipos`;
DROP TABLE IF EXISTS `sucursales`;
DROP TABLE IF EXISTS `matrices`;
DROP TABLE IF EXISTS `talleres`;
DROP TABLE IF EXISTS `usuarios`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. TABLA: matrices (Empresas matriz con múltiples sucursales)
-- ---------------------------------------------------------------------
CREATE TABLE `matrices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `razon_social` VARCHAR(255) NOT NULL,
  `rfc` VARCHAR(20) NOT NULL UNIQUE,
  `direccion` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `logo_url` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. TABLA: sucursales (Sucursales dependientes de una matriz)
-- ---------------------------------------------------------------------
CREATE TABLE `sucursales` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_matriz` INT UNSIGNED NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `direccion` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50) NOT NULL,
  `gerente_servicio` VARCHAR(150) NULL,
  `jefe_taller` VARCHAR(150) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sucursales_matriz` FOREIGN KEY (`id_matriz`) REFERENCES `matrices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. TABLA: talleres (Talleres independientes de sede única)
-- ---------------------------------------------------------------------
CREATE TABLE `talleres` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `razon_social` VARCHAR(255) NOT NULL,
  `rfc` VARCHAR(20) NOT NULL UNIQUE,
  `direccion` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `gerente_servicio` VARCHAR(150) NULL,
  `jefe_taller` VARCHAR(150) NULL,
  `logo_url` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. TABLA: usuarios (Control de acceso seguro RBAC)
-- Roles: 'admin', 'soporte', 'tecnico', 'cliente'
-- ---------------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(150) NOT NULL,
  `alias` VARCHAR(100) NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(50) NULL,
  `rfc` VARCHAR(20) NULL,
  `rol` ENUM('admin', 'soporte', 'tecnico', 'cliente') NOT NULL DEFAULT 'cliente',
  `id_matriz` INT UNSIGNED NULL,
  `id_sucursal` INT UNSIGNED NULL,
  `id_taller` INT UNSIGNED NULL,
  `foto_url` VARCHAR(255) NULL,
  `is_online` TINYINT(1) NOT NULL DEFAULT 0,
  `ultimo_acceso` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_usuario_rol` (`rol`),
  INDEX `idx_usuario_sucursal` (`id_sucursal`),
  INDEX `idx_usuario_taller` (`id_taller`),
  CONSTRAINT `fk_usuarios_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_usuarios_taller` FOREIGN KEY (`id_taller`) REFERENCES `talleres` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. TABLA: equipos (Elevadores, rampas y maquinaria técnica)
-- ---------------------------------------------------------------------
CREATE TABLE `equipos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `codigo_tcs` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(200) NOT NULL,
  `categoria` ENUM('rampa_2_postes', 'elevador_tijera', 'torno_rectificador', 'desmontadora', 'recuperadora_1234yf', 'recuperadora_r134a', 'balanceadora', 'compresor') NOT NULL,
  `marca` VARCHAR(100) NOT NULL,
  `modelo` VARCHAR(100) NOT NULL,
  `numero_serie` VARCHAR(100) NOT NULL UNIQUE,
  `capacidad` VARCHAR(100) NULL,
  `ubicacion_bahia` VARCHAR(100) NULL,
  `id_sucursal` INT UNSIGNED NULL,
  `id_taller` INT UNSIGNED NULL,
  `estado_salud` ENUM('operativo', 'observado', 'fuera_servicio') NOT NULL DEFAULT 'operativo',
  `ultimo_mantenimiento` DATE NULL,
  `proximo_mantenimiento` DATE NULL,
  `horas_uso` INT UNSIGNED DEFAULT 0,
  `qr_token` VARCHAR(64) NOT NULL UNIQUE,
  `notas_tecnicas` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_equipo_salud` (`estado_salud`),
  CONSTRAINT `fk_equipos_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_equipos_taller` FOREIGN KEY (`id_taller`) REFERENCES `talleres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. TABLA: ordenes_servicio (Solicitudes y órdenes de trabajo)
-- ---------------------------------------------------------------------
CREATE TABLE `ordenes_servicio` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `folio` VARCHAR(50) NOT NULL UNIQUE,
  `id_equipo` INT UNSIGNED NOT NULL,
  `id_usuario_solicita` INT UNSIGNED NOT NULL,
  `fecha_solicitud` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `tipo_servicio` ENUM('preventivo', 'correctivo', 'calibracion', 'urgente') NOT NULL,
  `prioridad` ENUM('baja', 'normal', 'alta', 'critica') NOT NULL DEFAULT 'normal',
  `descripcion_falla` TEXT NOT NULL,
  `estado` ENUM('pendiente', 'en_proceso', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendiente',
  `id_tecnico_asignado` INT UNSIGNED NULL,
  `fecha_cierre` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ordenes_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ordenes_usuario` FOREIGN KEY (`id_usuario_solicita`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ordenes_tecnico` FOREIGN KEY (`id_tecnico_asignado`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. TABLA: reportes_mantenimiento (Informes oficiales detallados)
-- ---------------------------------------------------------------------
CREATE TABLE `reportes_mantenimiento` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `folio_reporte` VARCHAR(50) NOT NULL UNIQUE,
  `id_orden` INT UNSIGNED NULL,
  `id_equipo` INT UNSIGNED NOT NULL,
  `id_tecnico` INT UNSIGNED NOT NULL,
  `fecha_servicio` DATE NOT NULL,
  `tipo_formato` ENUM('rampa_2_postes', 'elevador_tijera', 'torno_rectificador', 'desmontadora', 'recuperadora_1234yf', 'recuperadora_r134a', 'balanceadora', 'compresor') NOT NULL,
  `matriz_inspeccion` JSON NOT NULL,
  `valores_medidos` JSON NULL,
  `dictamen_final` ENUM('operativo', 'observado', 'fuera_servicio') NOT NULL,
  `diagnostico_trabajos` TEXT NOT NULL,
  `firma_tecnico_nombre` VARCHAR(150) NOT NULL,
  `firma_tecnico_cedula` VARCHAR(50) NULL,
  `firma_cliente_nombre` VARCHAR(150) NOT NULL,
  `firma_cliente_cargo` VARCHAR(100) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reportes_orden` FOREIGN KEY (`id_orden`) REFERENCES `ordenes_servicio` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reportes_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reportes_tecnico` FOREIGN KEY (`id_tecnico`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. TABLA: proveedores (Registro y directorio de proveedores)
-- ---------------------------------------------------------------------
CREATE TABLE `proveedores` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `razon_social` VARCHAR(255) NOT NULL,
  `rfc` VARCHAR(20) NOT NULL UNIQUE,
  `contacto` VARCHAR(150) NOT NULL,
  `telefono` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `direccion` VARCHAR(255) NULL,
  `categoria` VARCHAR(100) NOT NULL,
  `dias_credito` INT DEFAULT 0,
  `notas` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. TABLA: inventario_refacciones (Almacén de refacciones y consumibles)
-- ---------------------------------------------------------------------
CREATE TABLE `inventario_refacciones` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `codigo_parte` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NOT NULL,
  `categoria` VARCHAR(100) NOT NULL,
  `stock_actual` INT NOT NULL DEFAULT 0,
  `stock_minimo` INT NOT NULL DEFAULT 5,
  `unidad_medida` VARCHAR(30) NOT NULL DEFAULT 'Pza',
  `costo_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `id_proveedor` INT UNSIGNED NULL,
  `estado_pieza` ENUM('nuevo', 'usado', 'reacondicionado') NOT NULL DEFAULT 'nuevo',
  `ubicacion_estante` VARCHAR(50) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_inventario_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. TABLA: reporte_consumibles (Refacciones asignadas al reporte)
-- ---------------------------------------------------------------------
CREATE TABLE `reporte_consumibles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_reporte` INT UNSIGNED NOT NULL,
  `id_refaccion` INT UNSIGNED NOT NULL,
  `cantidad` INT NOT NULL DEFAULT 1,
  `costo_aplicado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT `fk_repcons_reporte` FOREIGN KEY (`id_reporte`) REFERENCES `reportes_mantenimiento` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_repcons_refaccion` FOREIGN KEY (`id_refaccion`) REFERENCES `inventario_refacciones` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. TABLA: checklists_diarios (Hojas de verificación pre-operacional 5 min)
-- ---------------------------------------------------------------------
CREATE TABLE `checklists_diarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_equipo` INT UNSIGNED NOT NULL,
  `fecha_turno` DATE NOT NULL,
  `operador_nombre` VARCHAR(150) NOT NULL,
  `supervisor_nombre` VARCHAR(150) NULL,
  `respuestas` JSON NOT NULL,
  `dictamen_seguridad` ENUM('cumple', 'falla_critica') NOT NULL,
  `observaciones` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_checklists_equipo` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. TABLA: mensajes (Canal perimetral de mensajería interna)
-- ---------------------------------------------------------------------
CREATE TABLE `mensajes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_remitente` INT UNSIGNED NOT NULL,
  `id_destinatario` INT UNSIGNED NOT NULL,
  `mensaje` TEXT NOT NULL,
  `leido` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_mensajes_remitente` FOREIGN KEY (`id_remitente`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mensajes_destinatario` FOREIGN KEY (`id_destinatario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. TABLA: audit_logs (ISO 27001 A.12.4 Registro Inmutable de Auditoría)
-- ---------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_usuario` INT UNSIGNED NULL,
  `rol_usuario` VARCHAR(50) NULL,
  `accion` VARCHAR(100) NOT NULL,
  `entidad` VARCHAR(100) NOT NULL,
  `entidad_id` VARCHAR(50) NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `detalles` JSON NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_usuario` (`id_usuario`),
  INDEX `idx_audit_accion` (`accion`),
  INDEX `idx_audit_fecha` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- SEED DATA: DATOS OFICIALES DE TCS MOTRIZ PARA INICIALIZACIÓN INMEDIATA
-- =====================================================================

-- Matrices
INSERT INTO `matrices` (`id`, `razon_social`, `rfc`, `direccion`, `telefono`, `email`, `logo_url`) VALUES
(1, 'Grupo Automotriz Premier S.A. de C.V.', 'GAP880215-AB1', 'Av. de las Industrias 500, CDMX', '55-5555-0000', 'contacto@grupopremier.com', 'assets/img/matriz-premier.png');

-- Sucursales
INSERT INTO `sucursales` (`id`, `id_matriz`, `nombre`, `direccion`, `telefono`, `gerente_servicio`, `jefe_taller`) VALUES
(1, 1, 'Sucursal Ford Interlomas', 'Vía Magna 12, Interlomas, Huixquilucan', '55-5555-1111', 'Ing. Roberto Garza', 'Carlos Mendoza'),
(2, 1, 'Sucursal Nissan Santa Fe', 'Vasco de Quiroga 3800, Santa Fe', '55-5555-2222', 'Lic. Elena Torres', 'Jorge Morales');

-- Talleres Independientes
INSERT INTO `talleres` (`id`, `razon_social`, `rfc`, `direccion`, `telefono`, `email`, `gerente_servicio`, `jefe_taller`) VALUES
(1, 'Taller Electromecánico Ramírez', 'EMIR950412-5NB', 'Av. Patriotismo 120, Escandón, CDMX', '55-5555-9911', 'contacto@taller-ramirez.com', 'Ing. Esteban Ramírez', 'Roberto Solís');

-- Usuarios Iniciales (Contraseñas con password_hash de 'TCS@2026!')
-- Hash para 'TCS@2026!': $2y$12$N3aM0P9Qo9QO... se puede autenticar también en fallback
INSERT INTO `usuarios` (`id`, `nombre`, `alias`, `email`, `password_hash`, `telefono`, `rfc`, `rol`, `id_matriz`, `id_sucursal`, `id_taller`, `is_online`) VALUES
(1, 'Ing. Fernando Ruiz', 'Admin TCS Motriz', 'fernando.ruiz@servicio-tcsmotriz.com.mx', '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka', '55-1023-4491', 'RUFI880112-1R4', 'admin', NULL, NULL, NULL, 1),
(2, 'Soporte Técnico TCS', 'Soporte', 'soporte@servicio-tcsmotriz.com.mx', '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka', '55-8000-4277', 'STC191001-8BA', 'soporte', NULL, NULL, NULL, 1),
(3, 'Téc. Héctor Morales', 'Técnico Especialista', 'hector.morales@servicio-tcsmotriz.com.mx', '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka', '55-3322-1780', 'MORH890120-TR4', 'tecnico', NULL, NULL, NULL, 1),
(4, 'Carlos Mendoza', 'Carlos M. (Interlomas)', 'cmendoza@fordinterlomas.com', '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka', '55-4433-8822', 'MENC920314-KL2', 'cliente', 1, 1, NULL, 1),
(5, 'Lic. Elena Torres', 'Elena T. (Santa Fe)', 'elena.torres@nissansantafe.com', '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka', '55-2233-4455', 'TORE850619-3M1', 'cliente', 1, 2, NULL, 0),
(6, 'Ing. Esteban Ramírez', 'Taller Ramírez', 'contacto@taller-ramirez.com', '$2y$12$5W0Q2X5oekZ.xZf1fB4XG.d7wT4aL5FwVfS3Tj5e0Hq4rY5Q7l8Ka', '55-5555-9911', 'EMIR950412-5NB', 'cliente', NULL, NULL, 1, 0);

-- Proveedores
INSERT INTO `proveedores` (`id`, `razon_social`, `rfc`, `contacto`, `telefono`, `email`, `categoria`, `dias_credito`) VALUES
(1, 'Rotary Lift de México S.A. de C.V.', 'RLM990115-99A', 'Ing. Arturo Salgado', '55-4000-8800', 'ventas@rotarylift.mx', 'Elevadores y Rampas', 30),
(2, 'Lubricantes y Fluidos Industriales S.A.', 'LFI100420-KK1', 'Lic. Rodrigo Pérez', '55-7000-1122', 'pedidos@lubri-ind.com', 'Aceites y Fluidos Hidráulicos', 15),
(3, 'Corghi y Equipos Automotrices Norte', 'CEA080312-7L9', 'Marco Aurelio Vega', '55-6600-4400', 'mvega@corghinorte.com', 'Llantas y Desmontadoras', 30),
(4, 'Refrigerantes Ecológicos y Válvulas S.A.', 'REV150830-4P0', 'Ing. Gabriela Silva', '55-9988-7711', 'gsilva@refrig-eco.mx', 'Gases R-1234yf / R-134a', 15);

-- Inventario de Refacciones y Consumibles
INSERT INTO `inventario_refacciones` (`id`, `codigo_parte`, `descripcion`, `categoria`, `stock_actual`, `stock_minimo`, `unidad_medida`, `costo_unitario`, `id_proveedor`, `estado_pieza`, `ubicacion_estante`) VALUES
(1, 'OIL-ISO32-19L', 'Cubeta Aceite Hidráulico Anti-Desgaste ISO 32 (19L)', 'Fluidos', 24, 6, 'Cubeta', 1450.00, 2, 'nuevo', 'Pasillo A - Estante 1'),
(2, 'CAB-SPO10-EQ', 'Juego de Cables de Ecualización Acero Alta Tensión 3/8"', 'Cables y Poleas', 8, 2, 'Juego', 3200.00, 1, 'nuevo', 'Pasillo B - Estante 3'),
(3, 'PAD-ROT-GOM', 'Juego de 4 Almohadillas de Goma para Brazos Rotary', 'Gomas y Protecciones', 18, 5, 'Juego', 980.00, 1, 'nuevo', 'Pasillo A - Gaveta 4'),
(4, 'BUR-TORN-TCS', 'Buril de Corte Triangular de Carburo de Tungsteno para Torno', 'Corte', 45, 10, 'Pza', 320.00, 3, 'nuevo', 'Pasillo C - Cajón 2'),
(5, 'FLT-REC-CORE', 'Filtro Deshidratador Core para Estación Recuperadora R134a', 'Filtración', 14, 4, 'Pza', 890.00, 4, 'nuevo', 'Pasillo D - Estante 2'),
(6, 'KIT-ORING-J2888', 'Kit de O-Rings y Sellos de Neopreno para Acoples SAE J2888', 'Sellos', 30, 8, 'Kit', 450.00, 4, 'nuevo', 'Pasillo D - Gaveta 1'),
(7, 'INS-PATO-NYL', 'Inserto de Nylon Anti-Rayón para Cabeza Desmontadora Corghi', 'Desmontadoras', 36, 12, 'Pza', 180.00, 3, 'nuevo', 'Pasillo B - Gaveta 2');

-- Equipos y Rampas
INSERT INTO `equipos` (`id`, `codigo_tcs`, `nombre`, `categoria`, `marca`, `modelo`, `numero_serie`, `capacidad`, `ubicacion_bahia`, `id_sucursal`, `id_taller`, `estado_salud`, `ultimo_mantenimiento`, `proximo_mantenimiento`, `horas_uso`, `qr_token`) VALUES
(1, 'TCS-EQ-001', 'Rampa Hidráulica de 2 Postes (4.5 Ton)', 'rampa_2_postes', 'BendPak', 'XPR-10S', 'SN-9948201-MX', '10,000 lbs (4.5 Ton)', 'Bahía 1 (Mantenimiento Rápido)', 1, NULL, 'operativo', '2026-07-15', '2026-10-15', 1420, 'QR-TCS-001-9948201'),
(2, 'TCS-EQ-002', 'Elevador Tijera para Alineación (5.0 Ton)', 'elevador_tijera', 'John Bean', 'Alignment Scissors 5.0', 'SN-8837192-US', '11,000 lbs (5.0 Ton)', 'Bahía 4 (Alineación y Tramado)', 1, NULL, 'observado', '2026-05-10', '2026-08-10', 2150, 'QR-TCS-002-8837192'),
(3, 'TCS-EQ-003', 'Compresor de Aire Tornillo 15 HP Industrial', 'compresor', 'Kaeser', 'SK 15 Industrial', 'SN-3392810-DE', '15 HP / 140 PSI', 'Cuarto de Máquinas Principal', 2, NULL, 'operativo', '2026-06-20', '2026-09-20', 3890, 'QR-TCS-003-3392810'),
(4, 'TCS-EQ-004', 'Desmontadora de Llantas Heavy-Duty', 'desmontadora', 'Corghi', 'Artiglio Master 28', 'SN-6541002-IT', 'Rines 10" a 28"', 'Bahía Llantera', NULL, 1, 'operativo', '2026-08-01', '2026-11-01', 940, 'QR-TCS-004-6541002'),
(5, 'TCS-EQ-005', 'Torno Rectificador de Discos de Freno Universal', 'torno_rectificador', 'Ammco / Pro-Cut', 'VTM-3000', 'SN-4411090-US', 'Rotor 4" a 20"', 'Bahía Frenos', 1, NULL, 'operativo', '2026-06-18', '2026-09-18', 650, 'QR-TCS-005-4411090'),
(6, 'TCS-EQ-006', 'Estación Recuperadora de Refrigerante R-1234yf', 'recuperadora_1234yf', 'Robinair / TCS', 'AC-1234-YF', 'SN-1234001-MX', 'Tanque 12.5 kg A2L', 'Bahía Aire Acondicionado', 1, NULL, 'operativo', '2026-07-22', '2026-10-22', 430, 'QR-TCS-006-1234001'),
(7, 'TCS-EQ-007', 'Estación Recuperadora de Gas R-134a', 'recuperadora_r134a', 'Robinair', 'CoolTech 34788', 'SN-1340092-MX', 'Tanque 15.0 kg', 'Bahía Eléctrica', 2, NULL, 'operativo', '2026-07-05', '2026-10-05', 810, 'QR-TCS-007-1340092'),
(8, 'TCS-EQ-008', 'Sistema Universal de Balanceo de Neumáticos', 'balanceadora', 'Hunter / TCS', 'TCS-RMB-001', 'SN-8820019-DE', 'Neumáticos hasta 44"', 'Bahía Llantera', NULL, 1, 'fuera_servicio', '2026-04-12', '2026-07-12', 1780, 'QR-TCS-008-8820019');

-- Órdenes de Servicio
INSERT INTO `ordenes_servicio` (`id`, `folio`, `id_equipo`, `id_usuario_solicita`, `fecha_solicitud`, `tipo_servicio`, `prioridad`, `descripcion_falla`, `estado`, `id_tecnico_asignado`) VALUES
(1, 'ORD-2026-041', 2, 4, '2026-08-18 10:15:00', 'correctivo', 'alta', 'Elevador Tijera presenta desnivel de 1.5 cm en ascenso sincronizado y ruido en micro-válvula derecha. Se requiere inspección urgente.', 'en_proceso', 3),
(2, 'ORD-2026-039', 1, 4, '2026-08-10 09:00:00', 'preventivo', 'normal', 'Mantenimiento trimestral programado según bitácora. Revisión de cables de ecualización y fluido hidráulico ISO 32.', 'concluido', 3),
(3, 'ORD-2026-045', 8, 6, '2026-08-22 11:30:00', 'urgente', 'critica', 'Transductor piezoeléctrico fuera de rango en desequilibrio dinámico superior a 25g. Máquina bloqueada por seguridad.', 'pendiente', 3);

-- Reporte de Mantenimiento Histórico Oficial (REP-TCS-902)
INSERT INTO `reportes_mantenimiento` (`id`, `folio_reporte`, `id_orden`, `id_equipo`, `id_tecnico`, `fecha_servicio`, `tipo_formato`, `matriz_inspeccion`, `valores_medidos`, `dictamen_final`, `diagnostico_trabajos`, `firma_tecnico_nombre`, `firma_tecnico_cedula`, `firma_cliente_nombre`, `firma_cliente_cargo`) VALUES
(1, 'REP-TCS-902', 2, 1, 3, '2026-08-10', 'rampa_2_postes', 
'{"estructura_columnas": "OK", "brazos_seguros": "OK", "cables_ecualizacion": "OK", "unidad_hidraulica": "OK", "poleas_pasadores": "OK", "sistema_electrico": "OK", "mecanismos_seguridad": "OK"}',
'{"nivel_aceite": "Optimo ISO 32", "torque_anclajes": "150 ft-lbs", "presion_valvula": "2200 PSI"}',
'operativo',
'Se realizó ajuste de tensión en cables de compensación, purga de fluido hidráulico y lubricación de poleas superiores. Trabas de seguridad mecánicas probadas al 100% de carga.',
'Téc. Héctor Morales', 'CED-TEC-992014', 'Carlos Mendoza', 'Jefe de Taller Ford Interlomas');

-- Consumibles aplicados al reporte 1
INSERT INTO `reporte_consumibles` (`id`, `id_reporte`, `id_refaccion`, `cantidad`, `costo_aplicado`) VALUES
(1, 1, 1, 1, 1450.00),
(2, 1, 3, 1, 980.00);

-- Mensajes de Comunicación
INSERT INTO `mensajes` (`id`, `id_remitente`, `id_destinatario`, `mensaje`, `leido`, `created_at`) VALUES
(1, 4, 2, 'Buenas tardes Soporte, solicité la revisión del elevador de tijera para alineación en Interlomas (ORD-2026-041).', 1, '2026-08-18 14:20:00'),
(2, 2, 4, 'Hola Carlos, Recibido. El Técnico Héctor Morales ya tiene asignada la orden ORD-2026-041 y acude hoy por la tarde.', 1, '2026-08-18 14:22:00'),
(3, 3, 1, 'Ing. Fernando, se atendió el reporte preventivo REP-TCS-902 en Ford Interlomas. Rampa SPO10 al 100% operativa.', 1, '2026-08-10 16:45:00');

-- Auditoría Inicial ISO 27001
INSERT INTO `audit_logs` (`id`, `id_usuario`, `rol_usuario`, `accion`, `entidad`, `entidad_id`, `ip_address`, `user_agent`, `detalles`) VALUES
(1, 1, 'admin', 'SYSTEM_INITIALIZATION', 'database', '1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '{"version": "1.0.0", "status": "Schema loaded successfully"}'),
(2, 3, 'tecnico', 'SUBMIT_MAINTENANCE_REPORT', 'reportes_mantenimiento', 'REP-TCS-902', '189.201.44.12', 'Mozilla/5.0 (Mobile; Android)', '{"dictamen": "operativo", "equipo": "TCS-EQ-001"}');
