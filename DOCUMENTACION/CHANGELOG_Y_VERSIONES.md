# Bitácora de Versiones, Mejoras y Manejo de Errores
## TCS MOTRIZ - Plataforma de Mantenimiento y Telemetría

---

### Control de Versiones

#### [v1.6.0-PROD] — 2026-09-15 (Tríada Logística: Inventario de Piezas, Proveedores Calificados y Guías de Envío)
*Implementación del ecosistema logístico y de suministros integral para TCS Motriz: control de almacén central con Kárdex y valuación de activos, directorio autogestionable de proveedores con enlaces directos a WhatsApp/cotización, y nuevo módulo de despacho de guías de envío con trazabilidad de paqueterías (DHL, FedEx, Estafeta, Paquetexpress y TCS Directo).*

##### 📦 1. Inventario Integral de Piezas, Kárdex y Valuación (`app/modules/inventario/index.php`)
- **Catálogo Central de Refacciones**: Alta, edición, ajuste de existencias y eliminación de piezas con códigos SKU, estado de la pieza, stock mínimo de seguridad y ubicación física en estanterías/racks.
- **Kárdex y Bitácora de Movimientos**: Registro cronológico de auditoría con tipo de movimiento (Entrada 🟢, Salida 🔴, Ajuste 🔄), cálculo automático de stock resultante, motivo/justificación, documento de respaldo y usuario que autoriza.
- **Valuación Financiera del Almacén**: Cuadro de mando financiero con inversión monetaria en refacciones desglosada por categoría de insumo y porcentaje de participación en el activo fijo del almacén.
- **Exportación Dual**: Exportación a Excel/CSV del catálogo de piezas (`tipo=inventario`) y del kárdex de movimientos (`tipo=movimientos_inventario`).

##### 🏭 2. Directorio Autogestionable de Proveedores Automotrices (`app/modules/proveedores/index.php`)
- **Gestión 100% Autónoma**: Alta, edición y baja de distribuidores autorizados y fabricantes (Rotary, Kaeser, Lubricantes Industriales, Corghi).
- **Botón Directo a WhatsApp**: Enlace preconfigurado para contactar al asesor comercial vía WhatsApp con mensaje de cotización redactado automáticamente.
- **Canales Directos en 1 Clic**: Botón de marcación telefónica directa y botón de correo con plantilla formal de cotización.
- **Condiciones Comerciales y Catálogo**: Visualización de días de crédito autorizados y chips de refacciones provistas en el inventario.
- **Exportación a CSV / Excel**: Directorio calificado exportable (`tipo=proveedores`).

##### 🚚 3. Guías de Envío y Despachos Logísticos (`app/modules/envios/index.php`)
- **Nuevo Módulo de Trazabilidad Logística**: Registro y monitoreo de paquetes despachados desde el Almacén Central hacia agencias, talleres mecánicos o técnicos en campo.
- **Integración con Paqueterías Nacionales**: Soporte para DHL Express, FedEx Express, Estafeta Terrestre, Paquetexpress y Logística Directa TCS Motriz con enlaces dinámicos de rastreo web.
- **Descuento Automático de Inventario**: Al emitir un despacho, el sistema aparta y descuenta las piezas enviadas del inventario central automáticamente.
- **Ciclo de Vida del Envío**: Estados operativos `preparacion` (🟡), `en_transito` (🔵), `entregado` (🟢) e `incidencia` (🔴) con registro de acuse y nombre de quien recibió.
- **Remisión de Salida Imprimible**: Formato oficial para imprimir el comprobante de despacho con lista de refacciones, datos de destino y recuadro de firma de recepción.
- **Exportación a CSV / Excel**: Bitácora completa de guías y fletes (`tipo=envios`).

#### [v1.5.0-PROD] — 2026-09-15 (Centro Autónomo de Gestión de Clientes, Sucursales, Talleres y Cuentas de Acceso)
*Implementación del Centro Integral de Autogestión Empresarial para habilitar al Administrador de TCS Motriz a dar de alta, editar, auditar y gestionar el ciclo de vida completo de Empresas Matriz, Redes de Sucursales, Talleres Independientes y Credenciales de Acceso al Portal de Clientes sin necesidad de intervenir código ni base de datos manual.*

##### 🏢 Módulo de Autogestión Empresarial 100% Autónomo (`app/modules/clientes/index.php`)
1. **Directorio y Gestión Jerárquica de Empresas Matriz**:
   - Registro y edición completa de empresas corporativas: Razón Social, RFC Fiscal validado, Dirección, Teléfono, Correo Oficial y sucursales adscritas.
   - Vista en árbol interactivo que desglosa todas las sucursales dependientes, rampas asignadas por sede y botones de acceso directo.
   - Botón directo para vincular nuevas sucursales a matrices existentes en 1 clic.
2. **Administración de Red de Sucursales y Agencias**:
   - Onboarding de sedes físicas con asignación de Gerente de Servicio y Jefe de Taller/Bahía.
   - Conexión perimetral con los elevadores automotrices instalados en cada bahía de servicio.
   - Edición y baja lógica de sedes con confirmación de seguridad.
3. **Gestión de Talleres Independientes (Local Único)**:
   - Registro de talleres mecánicos multimarca con datos fiscales, dirección de taller, contacto y parque de maquinaria.
   - Edición completa de datos de contacto y administración de maquinaria adscrita.
4. **Directorio Unificado de Sedes y Bahías**:
   - Tabla global interactiva con buscador en vivo de sucursales y talleres.
   - Desglose de número de elevadores en operación, teléfono directo, correo y administradores locales.
   - Exportación a Excel/CSV con codificación UTF-8 BOM (`index.php?action=exportar_csv&tipo=clientes`).
5. **Autogestión de Cuentas y Accesos de Cliente al Portal**:
   - Alta autónoma de cuentas de usuario cliente (Rol `cliente`): Nombre, Correo Electrónico institucional, Contraseña cifrada con `bcrypt` (cost 12), Teléfono y RFC.
   - Asignación estricta de perímetro de visibilidad: Sucursal corporativa o Taller independiente (prevención IDOR OWASP A01).
   - Edición de credenciales, restablecimiento inmediato de contraseñas de acceso y opción para simular la vista del cliente con un solo clic.
   - Eliminación segura de accesos con auditoría en bitácora ISO 27001.

#### [v1.4.0-PROD] — 2026-09-15 (6 Mejoras de Alto Impacto: Firmas, Evidencias, Failsafe, Predictivo, WhatsApp y Facturación)
*Implementación integral de las 6 mejoras operativas e industriales solicitadas para elevar la plataforma a nivel de madurez operativa y comercial superior.*

##### 🚀 6 Mejoras de Alto Impacto Implementadas
1. ✍️ **Firma Digital Táctil en Campo (Smartphones y Tablets)**:
   - Lienzo interactivo HTML5 Canvas táctil de alta precisión para técnicos y clientes en campo.
   - Captura de doble rúbrica manuscrita: Técnico Especialista Certificado TCS y Cliente / Jefe de Bahía.
   - Almacenamiento perimetral en Base64 e inserción directa en el dictamen técnico oficial imprimible en PDF con validez legal.
2. 📸 **Registro de Evidencia Fotográfica (Antes y Después)**:
   - Módulo de carga de fotografías periciales en el reporte técnico oficial con vista previa en tiempo real.
   - **Evidencia Inicial**: Registro fotográfico del daño o condición previa (cables deshilachados, sellos hidráulicos dañados).
   - **Evidencia Final**: Registro fotográfico del componente nuevo instalado, torqueado y calibrado.
   - Renderizado comparativo lado a lado en el certificado oficial para transparencia total ante aseguradoras y directores de agencia.
3. 🚨 **Checklist Diario con "Bloqueo Preventivo Automático" (Failsafe)**:
   - Checklist pre-operativo de 5 minutos disponible en la ficha de cada rampa para operadores de bahía.
   - Si se detecta un fallo crítico (trinquetes de seguridad que no enclavan, fugas a presión, cables desbalanceados):
     - El elevador se bloquea automáticamente pasando a estado `FUERA DE SERVICIO (BLOQUEADO)`.
     - Se genera de inmediato una Orden de Servicio Correctiva de Emergencia (Prioridad Crítica) asignada al soporte de TCS Motriz.
     - Se dispara una alerta de severidad crítica en el centro de monitoreo.
4. ⏳ **Mantenimiento Predictivo y Desgaste por Horas / Ciclos**:
   - Algoritmo de fatiga mecánica y vida útil predictiva basado en horas efectivas de ciclo:
     - Cables de ecualización de acero trenzado: vida límite nominal de 1,500 hrs o 3 años.
     - Fluido hidráulico ISO 32 / VG 46: vida límite nominal de 1,000 hrs o anual.
     - Almohadillas de goma de brazos de apoyo: vida límite nominal de 800 hrs.
   - Matriz visual de barras de progreso con porcentaje consumido, horas restantes y semáforo tricolor en el expediente técnico de la rampa.
   - Emisión anticipada de alertas automáticas al superar el 80% y 90% de ciclo para prevenir paros en bahía.
5. 📲 **Botón Directo a WhatsApp para Técnicos y Clientes**:
   - Botón directo de notificación rápida vía WhatsApp (`api.whatsapp.com`) en cada orden de servicio, en la tabla de bitácora y en la ficha modal.
   - Mensaje dinámico pre-redactado: *"Hola [Cliente], el Técnico Héctor Morales de TCS Motriz va en camino para atender el [Equipo] ([Código]) (Orden [Folio]). Soporte: 55-8000-4277."*
   - Botones de compartir dictamen pericial por WhatsApp en reportes técnicos y expedientes de elevadores.
6. 📊 **Exportación a Excel / CSV para Facturación e Inventario**:
   - **Módulo de Facturación Comercial**: Exportación a CSV con UTF-8 BOM para el departamento contable de TCS Motriz con desglose de cliente, horas de mano de obra técnica, tarifa horaria, refacciones utilizadas, subtotal, IVA 16% y total neto facturable.
   - **Conciliación de Almacén**: Exportación rápida de inventario y consumibles para compras con proveedores y auditorías mensuales.

#### [v1.2.0-PROD] — 2026-09-15 (Landing Page Oficial, Firmas Digitales y Failsafe)
*Incorporación de la Landing Page pública de alto impacto, firmas táctiles en pantalla y bloqueo de seguridad failsafe.*

##### 🚀 Nuevas Funcionalidades y Mejoras
1. **Landing Page Oficial de Ingeniería y Mantenimiento (`app/landing.php`)**:
   - Integración del logotipo oficial de **TCS Motriz**.
   - Estética industrial moderna con tema oscuro perimetral (`#040814`, `#0f1c35`, `#0284c7`, `#10b981`, `#e11d48`).
   - Mockup visual interactivo de telemetría de bahía con gráfico vectorial de elevador de 2 postes.
   - **Calculadora Interactiva de ROI**: Estimación dinámica del costo financiero por bahía inactiva vs. ahorro garantizado con TCS Motriz.
   - Catálogo de equipos atendidos, normativas ISO/SAE y contacto directo vía WhatsApp con mensaje preconfigurado.
   - Optimización SEO con datos estructurados Schema.org (`AutoRepair`) y metadatos Open Graph.
2. **Firma Digital Táctil HTML5 Canvas para Órdenes y Reportes**:
   - Captura de firma manuscrita en pantalla táctil (tablets de campo / smartphones) o con cursor de mouse.
   - Inserción automática de la firma renderizada en el certificado técnico oficial imprimible en PDF.
3. **Checklist Pre-operativo de 5 Minutos con Bloqueo Failsafe**:
   - Módulo de verificación rápida para operadores antes de usar el elevador (trinquetes, cables, fugas, paro de emergencia, anclaje).
   - Bloqueo preventivo automático: Si se detecta un fallo crítico, el equipo pasa inmediatamente a estado `fuera_servicio` y genera una orden de servicio correctivo urgente automática.
4. **Exportación Industrial a CSV / Excel**:
   - Exportación de inventario de refacciones, catálogo de elevadores y bitácora de órdenes con codificación UTF-8 BOM para apertura nativa en Microsoft Excel sin problemas de acentos o caracteres especiales.

#### [v1.0.0-PROD] — 2026-09-15 (Versión Inicial Estable para Hostinger)
*Lanzamiento oficial de la arquitectura integral basada en los requerimientos, formatos técnicos y esquemas organizacionales de TCS Motriz.*

##### 🚀 Nuevas Funcionalidades Implementadas
1. **Doble Portal de Acceso y RBAC**:
   - Portal para Clientes (agencias y talleres mecánicos con aislamiento de datos).
   - Portal de Administración y Técnicos para control de operaciones, altas y peritajes.
   - Selector en tiempo real de roles para pruebas operativas en vivo (`Admin TCS Motriz`, `Técnico`, `Usuario Agencia`).
2. **Estructura Jerárquica Completa**:
   - Soporte para **Empresas Matriz** vinculadas a múltiples sucursales locales.
   - Soporte para **Talleres Independientes** de local único.
   - Asociación jerárquica de elevadores y equipos por sucursal o taller.
3. **Catálogo de Elevadores y Telemetría de Salud de Equipos**:
   - Semáforo operativo tri-estado: **Operativo 100%**, **Requiere Atención / Observado**, **Fuera de Servicio (Crítico)**.
   - Métricas en tiempo real de porcentaje de disponibilidad global y por taller.
   - Generación e impresión de etiquetas con **Código QR Dinámico** para adherir a las columnas de los elevadores.
4. **Digitalización de Reportes Técnicos Fieles a Documentos Originales**:
   - Elevadores de 2 Postes Rotary Lift / BendPak (Matriz de 7 subsistemas y checklist diario de 8 puntos).
   - Torno Rectificador de Discos de Freno Universal (tolerancias Runout <0.001").
   - Desmontadora de Llantas Heavy-Duty (norma de presión 110-140 PSI, holgura de pato 2mm).
   - Estación Recuperadora R-1234yf (normas SAE J2843/J2911 para gas inflamable A2L, prueba de vacío inHg).
   - Estación Recuperadora Gas R134a (certificación ISO 9001, presostato 450-500 PSI).
   - Sistema Universal de Balanceo (calibración dinámica con peso patrón 100g).
   - Vista de impresión oficial formateada con firmas de conformidad y cédula técnica.
5. **Módulo de Inventario de Refacciones y Consumibles**:
   - Control de stock actual vs. punto de reorden mínimo.
   - Alerta visual automática de reabastecimiento en rojo para refacciones críticas.
   - Ajuste rápido de inventario y asociación de piezas utilizadas en órdenes de servicio.
6. **Directorio y Registro de Proveedores**:
   - Catálogo calificado de proveedores con RFC, teléfono, correo de compras, categoría y días de crédito.
7. **Mensajería Privada Perimetral**:
   - Canal de chat interno seguro para soporte directo entre clientes, administración y técnicos.
8. **Credenciales Digitales de Rol**:
   - Directorio de colaboradores y clientes con estatus de presencia Online/Offline.

---

### 🛡️ Errores Prevenidos y Mitigaciones Implementadas

| Incidencia / Riesgo Potencial | Causa Raíz Identificada | Mitigación Implementada en v1.0.0 |
| :--- | :--- | :--- |
| **Fuga de datos entre agencias rivales (IDOR)** | Consultas generales que no validaban la pertenencia de la sucursal del usuario solicitante. | `Auth::canAccessEquipment()` y filtros automáticos en `DataStore::getEquipos()` que impiden a un usuario de Ford Interlomas ver o solicitar servicios sobre equipos de Nissan Santa Fe o Taller Ramírez. |
| **Inyección de SQL (SQLi)** | Concatenación directa de cadenas de entrada en cláusulas `WHERE`. | Refactorización al 100% de consultas PDO con sentencias preparadas y parámetros fuertemente tipados. |
| **Ataques de Fuerza Bruta en Autenticación** | Peticiones recurrentes no limitadas hacia el formulario de login. | Implementación de `Security::checkRateLimit('login', 5, 300)` que congela temporalmente la IP tras 5 intentos fallidos. |
| **Ataques de Falsificación CSRF** | Peticiones POST disparadas desde sitios de terceros no autorizados. | Generación de token criptográfico único por sesión (`$_SESSION['csrf_token']`) y validación estricta con `hash_equals()` en cada formulario. |
| **Exposición de Credenciales o Códigos en Hostinger** | Servidores Apache compartidos donde `.env` o `.sql` son accesibles vía navegador. | Creación de reglas perimetrales en `app/.htaccess` y `app/logs/.htaccess` que retornan código HTTP 403 Forbidden ante cualquier archivo de configuración o bitácora. |
| **Caída de la aplicación por falta inicial de BD remota** | Retraso o fallo temporal en la conexión a la base de datos MySQL de Hostinger. | Mecanismo de **Fallback Transparente Autónomo** (`Database::isFallback()`), que mantiene la aplicación completamente funcional con persistencia en `database/store.json` mientras se suministran las credenciales finales de MySQL. |

---

### 🗺️ Hoja de Ruta de Mejoras Futuras (Roadmap)

#### [v1.1.0] — Previsto para Q4 2026
- [ ] Notificaciones automáticas por correo electrónico (vía SMTP de Hostinger) al crearse una orden de servicio o al concluir un mantenimiento.
- [ ] Módulo de firma digital táctil directamente en pantalla para smartphones y tablets de los técnicos en campo.
- [ ] Exportación masiva de inventario y equipos a formatos Microsoft Excel / CSV.

#### [v1.2.0] — Previsto para Q1 2027
- [ ] Módulo de predicción de desgaste de cables y empaques basado en horas acumuladas de uso del elevador.
- [ ] Conexión API REST con autenticación Bearer JWT para sincronización con software contable del cliente.
