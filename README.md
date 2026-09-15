# TCS Motriz — Plataforma de Gestión Técnica y Telemetría Industrial

Plataforma web integral para la gestión de ciclo de vida de **elevadores automotrices de taller**, maquinaria de balanceo, tornos y estaciones de aire acondicionado, con arquitectura industrial para **Hostinger (PHP 8.x + MySQL)** y cumplimiento **OWASP Top 10 e ISO 27001**.

---

## 🚀 Características Principales

1. **Doble Portal de Acceso (RBAC)**:
   - **Portal Clientes**: Aislamiento por sucursal/taller (prevención IDOR), consulta de expediente de equipos, bitácora de reportes foliados y levantamiento de solicitudes de servicio con selección de prioridad.
   - **Portal Administración & Técnicos**: Gestión de entidades, peritajes técnicos, emisión de reportes certificados, catálogo de refacciones y proveedores, y auditoría inmutable.
   - **Simulador de Roles en Tiempo Real**: Conmutador rápido entre Admin, Técnico y Cliente para validación operativa.

2. **Estructura Jerárquica Organizacional**:
   - **Empresa Matriz $\rightarrow$ Sucursales $\rightarrow$ Equipos**: Modelado para redes de agencias automotrices (ej. Ford Interlomas, Nissan Santa Fe).
   - **Taller Independiente $\rightarrow$ Equipos**: Modelado para talleres mecánicos de sede única.

3. **Monitoreo de Estado de Salud de Elevadores & Equipos**:
   - Semáforo tri-estado: **Operativo 100%**, **Requiere Atención / Observado**, **Fuera de Servicio**.
   - Porcentaje de disponibilidad global y por taller.
   - Generación e impresión de etiquetas con **Código QR Dinámico** para adherir a las columnas de los elevadores.

4. **Digitalización Fiel de Reportes Técnicos**:
   Formatos normalizados según la documentación técnica y normativas internacionales:
   - Elevadores de 2 Postes (Rotary Lift SPO10/SPOA10 / BendPak) — Matriz de 7 subsistemas y checklist preoperacional diario.
   - Torno Rectificador de Discos de Freno Universal (Tolerancias Runout <0.001").
   - Desmontadora de Llantas Heavy-Duty (FRL 110-140 PSI, holgura de pato 2mm).
   - Estación Recuperadora R-1234yf (Normas SAE J2843 / J2911 / J3030 para gas inflamable A2L).
   - Estación Recuperadora Gas R134a (Certificación ISO 9001, presostato 450-500 PSI).
   - Sistema Universal de Balanceo (Calibración dinámica con peso patrón 100g).
   - Generación de vista imprimible y descarga oficial en formato PDF.

5. **Inventario de Refacciones & Proveedores**:
   - Control de existencias de cubetas de aceite ISO 32, cables de acero, almohadillas, filtros Core y buriles de torno.
   - Alerta visual automática de stock crítico y reabastecimiento.
   - Directorio calificado de proveedores con RFC, teléfono, correo de compras y días de crédito.

6. **Mensajería Privada y Credenciales**:
   - Canal perimetral seguro de mensajería directa entre personal de soporte, técnicos y agencias.
   - Credenciales de rol digitales con indicador de presencia Online/Offline.

---

## 🛡️ Enfoque de Seguridad Perimetral, OWASP e ISO 27001

- **Defensa Perimetral (.htaccess)**: Bloqueo directo de archivos de configuración, base de datos y bitácoras; cabeceras HTTP `HSTS`, `X-Frame-Options`, `X-Content-Type-Options` y `Content-Security-Policy`.
- **Cero Inyecciones SQL**: 100% de consultas preparadas mediante PDO (`PDO::prepare`).
- **Protección CSRF & XSS**: Tokens criptográficos por sesión y sanitización exhaustiva de salidas.
- **Prevención de Fuerza Bruta**: Rate limiting temporal por IP en formularios de autenticación.
- **Auditoría Inmutable ISO 27001 A.12.4**: Registro en tiempo real de cada acción con fecha, usuario, rol, IP remota y User-Agent.

---

## 📂 Estructura del Repositorio

```
├── app/                             # Núcleo de la Aplicación Web (Listo para subir a Hostinger)
│   ├── .htaccess                    # Seguridad perimetral Hostinger
│   ├── index.php                    # Enrutador principal y layout
│   ├── login.php                    # Acceso seguro RBAC
│   ├── logout.php                   # Cierre de sesión e invalidación
│   ├── config/                      # Configuración de base de datos y seguridad
│   ├── core/                        # Auth, Database, DataStore, Logger, Security
│   ├── modules/                     # Dashboard, Equipos, Sucursales, Órdenes, Reportes, Inventario, Proveedores
│   ├── assets/                      # CSS Dark Industrial, JavaScript, Logo oficial TCS
│   ├── database/                    # schema.sql DDL oficial
│   └── logs/                        # Bitácora protegida de auditoría
│
└── DOCUMENTACION/                   # Especificaciones Técnicas y Manuales
    ├── AUDITORIA_Y_SEGURIDAD_ISO27001.md
    ├── CHANGELOG_Y_VERSIONES.md
    └── MANUAL_DESPLIEGUE_HOSTINGER.md
```

---

## 🗄️ Base de Datos en Hostinger

La base de datos se encuentra alojada en el clúster MySQL de Hostinger:
- **Base de Datos**: `u666149057_TCS`
- **Host**: `srv1264.hstgr.io` (o `localhost`)
- **Tablas Verificadas (13)**: `matrices`, `sucursales`, `talleres`, `usuarios`, `equipos`, `ordenes_servicio`, `reportes_mantenimiento`, `reporte_consumibles`, `proveedores`, `inventario_refacciones`, `checklists_diarios`, `mensajes`, `audit_logs`.

---

## 👥 Credenciales Iniciales de Prueba

| Rol | Correo Electrónico | Contraseña Inicial |
| :--- | :--- | :--- |
| **Administrador General** | `fernando.ruiz@servicio-tcsmotriz.com.mx` | `TCS@2026!` |
| **Soporte Técnico** | `soporte@servicio-tcsmotriz.com.mx` | `TCS@2026!` |
| **Técnico Especialista** | `hector.morales@servicio-tcsmotriz.com.mx` | `TCS@2026!` |
| **Cliente (Ford Interlomas)** | `cmendoza@fordinterlomas.com` | `TCS@2026!` |
| **Cliente (Nissan Santa Fe)** | `elena.torres@nissansantafe.com` | `TCS@2026!` |
| **Cliente (Taller Ramírez)** | `contacto@taller-ramirez.com` | `TCS@2026!` |

---
**TCS Motriz © 2026** • Plataforma de Ingeniería y Mantenimiento • `servicio-tcsmotriz.com.mx`
