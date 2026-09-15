# Bitácora de Versiones, Mejoras y Manejo de Errores
## TCS MOTRIZ - Plataforma de Mantenimiento y Telemetría

---

### Control de Versiones

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
