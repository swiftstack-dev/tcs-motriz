<?php
/**
 * TCS MOTRIZ - Landing Page Oficial de Ingeniería y Mantenimiento Industrial
 * Líder en Certificación, Telemetría y Servicio Técnico para Elevadores Automotrices
 */

define('TCS_ACCESS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Security.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TCS Motriz | Mantenimiento, Certificación y Telemetría de Elevadores Automotrices</title>
    <meta name="description" content="Especialistas en certificación técnica, mantenimiento preventivo y reparación de elevadores de 2 postes, tijeras de alineación, tornos y desmontadoras para talleres y agencias.">
    <meta name="keywords" content="mantenimiento elevadores automotrices, reparacion de rampas hidraulicas, Rotary Lift, BendPak, tornos rectificadores discos, desmontadoras corghi, ISO 9001 taller">
    
    <!-- Open Graph / Redes Sociales -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://servicio-tcsmotriz.com.mx/">
    <meta property="og:title" content="TCS Motriz — Ingeniería y Mantenimiento de Elevadores Automotrices">
    <meta property="og:description" content="Garantiza la seguridad y operatividad continua en las bahías de tu taller con telemetría en tiempo real y peritajes normativos.">
    <meta property="og:image" content="assets/img/logo-tcs.png">

    <!-- CSS Dedicado -->
    <link rel="stylesheet" href="assets/css/landing.css">

    <!-- JSON-LD Datos Estructurados (SEO) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "AutoRepair",
      "name": "TCS Motriz",
      "image": "https://servicio-tcsmotriz.com.mx/assets/img/logo-tcs.png",
      "description": "Servicio de ingeniería, certificación y mantenimiento preventivo para elevadores y equipamiento de taller automotriz.",
      "telephone": "+52-55-8000-4277",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Ciudad de México",
        "addressCountry": "MX"
      },
      "url": "https://servicio-tcsmotriz.com.mx"
    }
    </script>
</head>
<body>

<!-- =====================================================================
     HEADER & NAVEGACIÓN
     ===================================================================== -->
<header class="landing-header">
    <div class="container nav-flex">
        <a href="landing.php" class="brand-combo" id="nav-brand-logo">
            <img src="assets/img/logo-tcs.png" alt="TCS Motriz Logo" class="brand-logo-img">
            <div class="brand-text-block">
                <span class="brand-name">TCS Motriz</span>
                <span class="brand-sub">INGENIERÍA & TELEMETRÍA</span>
            </div>
        </a>

        <ul class="nav-links">
            <li><a href="#servicios" class="nav-link">Servicios</a></li>
            <li><a href="#maquinaria" class="nav-link">Equipos y Rampas</a></li>
            <li><a href="#plataforma" class="nav-link">Software & Telemetría</a></li>
            <li><a href="#calculadora" class="nav-link">Calculadora ROI</a></li>
            <li><a href="#seguridad" class="nav-link">Normativas ISO</a></li>
            <li><a href="#contacto" class="nav-link">Contacto</a></li>
        </ul>

        <div class="nav-actions">
            <a href="tel:5580004277" class="hotline-badge">
                <span class="pulse-dot"></span>
                <span>55-8000-4277</span>
            </a>
            <a href="login.php" class="btn-lnd btn-lnd-primary" id="btn-header-portal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <span>Acceso Clientes</span>
            </a>
        </div>
    </div>
</header>

<!-- =====================================================================
     HERO SECTION
     ===================================================================== -->
<section class="hero-section">
    <div class="container hero-grid">
        <div>
            <div class="hero-badge-tag">
                <span>🛡️ SERVICIO TÉCNICO ESPECIALIZADO Y CERTIFICADO</span>
            </div>

            <h1 class="hero-title">
                Garantiza la Operatividad y Seguridad de los <span class="highlight">Elevadores de tu Taller</span>
            </h1>

            <p class="hero-subtitle">
                Mantenimiento preventivo, peritajes técnicos con normativas <strong>SAE e ISO 9001</strong>, telemetría de salud en tiempo real y suministro garantizado de refacciones originales para agencias multisede y talleres mecánicos.
            </p>

            <div class="hero-btn-row">
                <a href="#contacto" class="btn-lnd btn-lnd-primary" id="btn-hero-quote">
                    <span>Solicitar Cotización de Servicio</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="login.php" class="btn-lnd btn-lnd-outline" id="btn-hero-portal">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/></svg>
                    <span>Entrar al Portal Digital</span>
                </a>
            </div>

            <div class="hero-stats-row">
                <div class="stat-item">
                    <span class="stat-number">+1,200</span>
                    <span class="stat-label">Elevadores Inspeccionados</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">99.4%</span>
                    <span class="stat-label">Disponibilidad en Bahías</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Respuesta a Urgencias</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Cumplimiento NOM / OSHA</span>
                </div>
            </div>
        </div>

        <!-- TELEMETRÍA EN VIVO (HERO GRAPHIC INTERACTIVO) -->
        <div>
            <div class="telemetry-card-mockup">
                <div class="mockup-header">
                    <div class="mockup-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        <span>Telemetría de Bahía • TCS-EQ-001</span>
                    </div>
                    <span class="mockup-status-tag">● 100% OPERATIVO</span>
                </div>

                <div class="lift-vector-visual">
                    <!-- SVG Vectorial Elevador 2 Postes -->
                    <svg width="240" height="140" viewBox="0 0 300 180" fill="none" style="margin: 0 auto; display: block;">
                        <!-- Columna Izquierda -->
                        <rect x="35" y="20" width="18" height="140" rx="3" fill="#1e293b" stroke="#38bdf8" stroke-width="2"/>
                        <!-- Columna Derecha -->
                        <rect x="247" y="20" width="18" height="140" rx="3" fill="#1e293b" stroke="#38bdf8" stroke-width="2"/>
                        <!-- Viga Superior / Sensor de Tope -->
                        <line x1="35" y1="26" x2="265" y2="26" stroke="#e11d48" stroke-width="4" stroke-dasharray="6 3"/>
                        <!-- Carro Izquierdo y Brazo -->
                        <rect x="30" y="80" width="28" height="24" rx="2" fill="#0284c7"/>
                        <path d="M58 92 L130 92" stroke="#38bdf8" stroke-width="6" stroke-linecap="round"/>
                        <!-- Carro Derecho y Brazo -->
                        <rect x="242" y="80" width="28" height="24" rx="2" fill="#0284c7"/>
                        <path d="M242 92 L170 92" stroke="#38bdf8" stroke-width="6" stroke-linecap="round"/>
                        <!-- Silueta Vehículo Elevado -->
                        <path d="M100 85 Q150 65 200 85 L208 90 L92 90 Z" fill="rgba(255, 255, 255, 0.15)" stroke="#f1f5f9" stroke-width="1.5"/>
                        <!-- Indicador de Almohadillas -->
                        <circle cx="130" cy="92" r="5" fill="#10b981"/>
                        <circle cx="170" cy="92" r="5" fill="#10b981"/>
                    </svg>
                    <div style="font-size: 11px; color: var(--text-dim); margin-top: 6px; font-family: var(--font-code);">
                        Rotary Lift SPOA10 • 10,000 lbs (4.5 Ton) • Bahía 1 Ford
                    </div>
                </div>

                <div class="telemetry-gauges-grid">
                    <div class="gauge-box">
                        <div class="gauge-val" id="telemetry-pressure" style="color: #38bdf8;">2,200 PSI</div>
                        <div class="gauge-lbl">Presión en Circuito Hidráulico</div>
                    </div>
                    <div class="gauge-box">
                        <div class="gauge-val" style="color: #10b981;">ISO VG 32</div>
                        <div class="gauge-lbl">Calidad y Nivel de Fluido</div>
                    </div>
                    <div class="gauge-box">
                        <div class="gauge-val" style="color: #fbbf24;">150 ft-lbs</div>
                        <div class="gauge-lbl">Torque Pernos Anclaje a Concreto</div>
                    </div>
                    <div class="gauge-box">
                        <div class="gauge-val" style="color: #10b981;">Simétrico</div>
                        <div class="gauge-lbl">Engranaje de Trinquetes de Seguridad</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     SEGMENTACIÓN (MATRICES MULTI-SEDE VS TALLERES INDEPENDIENTES)
     ===================================================================== -->
<section id="servicios" class="section-padding" style="background: rgba(7, 14, 31, 0.6);">
    <div class="container">
        <div class="section-head">
            <span class="section-tag">ARQUITECTURA ORGANIZACIONAL</span>
            <h2 class="section-title">Soluciones a la Medida de tu Estructura de Taller</h2>
            <p class="section-desc">
                Ya sea que dirijas una red de concesionarios automotrices multisede o un taller mecánico independiente, diseñamos un esquema operativo adaptado a tus bahías.
            </p>
        </div>

        <div class="segment-grid">
            <!-- TARJETA EMPRESAS MATRIZ -->
            <div class="segment-card matriz-theme">
                <span class="segment-badge" style="background: rgba(16, 185, 129, 0.15); color: var(--emerald);">
                    CORPORATIVOS & AGENCIAS
                </span>
                <h3>Redes de Agencias Automotrices (Empresas Matriz)</h3>
                <p>
                    Control total y unificado para grupos automotrices con múltiples sucursales locales. Supervisa la salud técnica de tus rampas desde una sola consola directiva.
                </p>
                <ul class="feature-check-list">
                    <li><span class="check-icon">✔</span> Vinculación ilimitada de sucursales a tu Razón Social central.</li>
                    <li><span class="check-icon">✔</span> Acceso restringido por taller para jefes de bahía (Cero cruce de datos).</li>
                    <li><span class="check-icon">✔</span> Indicador de disponibilidad operativa global en tiempo real.</li>
                    <li><span class="check-icon">✔</span> Consolidación de facturación, contratos y consumibles de mantenimiento.</li>
                </ul>
                <div style="margin-top: 26px;">
                    <a href="#contacto" class="btn-lnd btn-lnd-cyan" style="font-size: 13px;">Plan Corporativo Multi-Sucursal</a>
                </div>
            </div>

            <!-- TARJETA TALLERES INDEPENDIENTES -->
            <div class="segment-card taller-theme">
                <span class="segment-badge" style="background: rgba(2, 132, 199, 0.15); color: var(--blue-tech-light);">
                    TALLERES MECÁNICOS INDEPENDIENTES
                </span>
                <h3>Talleres Especializados (Local Único)</h3>
                <p>
                    Protege a tus mecánicos y maximiza el rendimiento de tus rampas. Atención inmediata ante averías imprevistas y abasto prioritario de refacciones.
                </p>
                <ul class="feature-check-list">
                    <li><span class="check-icon">✔</span> Registro simplificado de taller con alta inmediata de maquinaria.</li>
                    <li><span class="check-icon">✔</span> Levantamiento de solicitudes de servicio con selección de prioridad.</li>
                    <li><span class="check-icon">✔</span> Historial técnico digital descargable para auditorías y pólizas de seguro.</li>
                    <li><span class="check-icon">✔</span> Stock garantizado de cables, poleas, sellos y fluidos hidráulicos.</li>
                </ul>
                <div style="margin-top: 26px;">
                    <a href="#contacto" class="btn-lnd btn-lnd-primary" style="font-size: 13px;">Plan Taller Mecánico</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     CATÁLOGO DE EQUIPAMIENTO ESPECIALIZADO
     ===================================================================== -->
<section id="maquinaria" class="section-padding">
    <div class="container">
        <div class="section-head">
            <span class="section-tag">CATÁLOGO DE ESPECIALIDAD</span>
            <h2 class="section-title">Ingeniería Especializada en Cada Equipo del Taller</h2>
            <p class="section-desc">
                Peritajes, calibraciones dinámicas y mantenimiento técnico bajo manuales de fabricante original.
            </p>
        </div>

        <div class="services-grid">
            <!-- 1. Elevadores 2 Postes -->
            <div class="service-card">
                <div>
                    <div class="service-icon-box">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3v18M18 3v18M6 12h12M6 8h12"/></svg>
                    </div>
                    <h4>Elevadores de 2 Postes Hidráulicos</h4>
                    <p>
                        Revisión profunda de verticalidad, ecualización de cables de acero, sellos de pistón, nivel de fluido ISO 32 y torque de pernos de anclaje a 150 ft-lbs.
                    </p>
                </div>
                <div class="service-tech-spec">
                    Rotary Lift SPO10/SPOA10 • BendPak XPR • Challenger (Hasta 10,000 lbs)
                </div>
            </div>

            <!-- 2. Elevadores Tijera -->
            <div class="service-card">
                <div>
                    <div class="service-icon-box" style="color: var(--blue-tech-light); background: rgba(2, 132, 199, 0.1);">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="20" x2="20" y2="4"/><line x1="4" y1="4" x2="20" y2="20"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                    </div>
                    <h4>Elevadores Tijera para Alineación</h4>
                    <p>
                        Calibración de paralelismo y sincronización milimétrica hidráulica entre rampas para garantizar lecturas exactas en equipos de tramado láser.
                    </p>
                </div>
                <div class="service-tech-spec">
                    John Bean Alignment Scissors • Hunter • Ravaglioli (5.0 Ton)
                </div>
            </div>

            <!-- 3. Tornos de Freno -->
            <div class="service-card">
                <div>
                    <div class="service-icon-box">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <h4>Tornos Rectificadores de Discos</h4>
                    <p>
                        Inspección de buriles de carburo de tungsteno, verificación de holguras axiales en mandril y prueba de descentramiento Runout con reloj comparador (&lt;0.001").
                    </p>
                </div>
                <div class="service-tech-spec">
                    Ammco • Pro-Cut • VTM-3000 (Rectificado en auto y de banco)
                </div>
            </div>

            <!-- 4. Desmontadoras -->
            <div class="service-card">
                <div>
                    <div class="service-icon-box" style="color: var(--blue-tech-light); background: rgba(2, 132, 199, 0.1);">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="3" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="21"/></svg>
                    </div>
                    <h4>Desmontadoras de Llantas Heavy-Duty</h4>
                    <p>
                        Mantenimiento a unidades neumáticas FRL (110-140 PSI), validación de holgura de 2mm en cabeza pato, fuerza de mordazas y destalonador lateral.
                    </p>
                </div>
                <div class="service-tech-spec">
                    Corghi Artiglio • Coats • Hunter Leverless (Rines 10" a 28")
                </div>
            </div>

            <!-- 5. Estaciones R-1234yf y R134a -->
            <div class="service-card">
                <div>
                    <div class="service-icon-box">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
                    </div>
                    <h4>Estaciones Recuperadoras A/C</h4>
                    <p>
                        Mantenimiento bajo normativas SAE J2843 para gas inflamable A2L (R-1234yf), sustitución de filtros Core y prueba de hermeticidad de vacío interno.
                    </p>
                </div>
                <div class="service-tech-spec">
                    Robinair AC-1234 • CoolTech 34788 • Certificación ISO 9001
                </div>
            </div>

            <!-- 6. Balanceadoras y Compresores -->
            <div class="service-card">
                <div>
                    <div class="service-icon-box" style="color: var(--blue-tech-light); background: rgba(2, 132, 199, 0.1);">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 12h10M12 7v10"/></svg>
                    </div>
                    <h4>Compresores y Balanceadoras</h4>
                    <p>
                        Calibración dinámica de celdas piezoeléctricas con peso patrón de 100g. Mantenimiento a compresores de tornillo y redes de secado neumático.
                    </p>
                </div>
                <div class="service-tech-spec">
                    Kaeser SK 15 • Hunter Road Force • Código TCS-RMB-001
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     PLATAFORMA DIGITAL & SOFTWARE SHOWCASE
     ===================================================================== -->
<section id="plataforma" class="section-padding" style="background: rgba(8, 16, 36, 0.7); border-top: 1px solid var(--border-line); border-bottom: 1px solid var(--border-line);">
    <div class="container">
        <div class="section-head">
            <span class="section-tag">TECNOLOGÍA INDUSTRIAL EXCLUSIVA</span>
            <h2 class="section-title">Tu Taller Respaldado por Nuestra Plataforma Web</h2>
            <p class="section-desc">
                Como cliente de TCS Motriz, recibes acceso sin costo adicional a nuestro software de gestión técnica en la nube.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
            <div style="background: var(--bg-card); border: 1px solid var(--border-line); border-radius: var(--radius-md); padding: 26px;">
                <div style="font-size: 28px; margin-bottom: 12px;">🏷️</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 8px;">Etiquetas Técnicas QR en Bahía</h4>
                <p style="font-size: 13px; color: var(--text-dim); line-height: 1.6;">
                    Cada rampa cuenta con su código QR plastificado adherido a la columna. Escanéalo desde tu teléfono para ver al instante la última fecha de servicio y estado de salud.
                </p>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-line); border-radius: var(--radius-md); padding: 26px;">
                <div style="font-size: 28px; margin-bottom: 12px;">📑</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 8px;">Reportes Oficiales Certificados en PDF</h4>
                <p style="font-size: 13px; color: var(--text-dim); line-height: 1.6;">
                    Descarga e imprime certificados oficiales con firmas técnicas, matrices de ingeniería y desglose de consumibles utilizados para tus auditorías.
                </p>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-line); border-radius: var(--radius-md); padding: 26px;">
                <div style="font-size: 28px; margin-bottom: 12px;">🚦</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 8px;">Semáforo de Telemetría Tri-Estado</h4>
                <p style="font-size: 13px; color: var(--text-dim); line-height: 1.6;">
                    Monitoreo en tiempo real: verde (Operativo 100%), amarillo (Observado / Atención menor) y rojo (Fuera de Servicio por seguridad de vidas humanas).
                </p>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-line); border-radius: var(--radius-md); padding: 26px;">
                <div style="font-size: 28px; margin-bottom: 12px;">💬</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 8px;">Canal Directo con Ingenieros de Soporte</h4>
                <p style="font-size: 13px; color: var(--text-dim); line-height: 1.6;">
                    Comunícate en tiempo real con los técnicos certificados y el área directiva de TCS Motriz para agendar visitas urgentes sin intermediarios.
                </p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 44px;">
            <a href="login.php" class="btn-lnd btn-lnd-primary" style="padding: 14px 32px; font-size: 15px;">
                <span>Probar la Plataforma de Clientes Ahora</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    </div>
</section>

<!-- =====================================================================
     CALCULADORA INTERACTIVA DE ROI
     ===================================================================== -->
<section id="calculadora" class="section-padding">
    <div class="container">
        <div class="section-head">
            <span class="section-tag">CALCULADORA DE RENTABILIDAD</span>
            <h2 class="section-title">¿Cuánto Cuesta un Elevador Descompuesto en tu Taller?</h2>
            <p class="section-desc">
                Una rampa detenida no solo representa un riesgo físico: genera cancelaciones de citas y pérdidas económicas directas cada día.
            </p>
        </div>

        <div class="roi-container">
            <div class="roi-grid">
                <!-- CONTROLES DESLIZANTES -->
                <div>
                    <div class="roi-slider-group">
                        <div class="roi-label-flex">
                            <span>Cantidad de Elevadores en el Taller:</span>
                            <span id="val-lifts" class="roi-val-badge">4 Rampas</span>
                        </div>
                        <input type="range" id="calc-lifts" class="roi-range" min="1" max="20" value="4">
                    </div>

                    <div class="roi-slider-group">
                        <div class="roi-label-flex">
                            <span>Autos Atendidos por Bahía al Día:</span>
                            <span id="val-cars" class="roi-val-badge">4 autos / día</span>
                        </div>
                        <input type="range" id="calc-cars" class="roi-range" min="1" max="8" value="4">
                    </div>

                    <div class="roi-slider-group">
                        <div class="roi-label-flex">
                            <span>Ticket Promedio de Mano de Obra por Auto:</span>
                            <span id="val-ticket" class="roi-val-badge">$1,800 MXN</span>
                        </div>
                        <input type="range" id="calc-ticket" class="roi-range" min="800" max="6000" step="100" value="1800">
                    </div>

                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 14px;">
                        *Cálculo proyectado sobre 24 días hábiles de operación al mes en una sola bahía detenida.
                    </div>
                </div>

                <!-- RESULTADOS -->
                <div>
                    <div class="roi-result-card">
                        <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px;">
                            Pérdida por 1 Elevador Inhabilitado:
                        </div>
                        <div id="res-daily-loss" class="roi-loss-num">$7,200 MXN</div>
                        <div style="font-size: 12px; color: var(--text-dim); margin-bottom: 16px;">Pérdida directa por cada día sin operar</div>

                        <div style="border-top: 1px solid var(--border-line); padding-top: 14px; margin-top: 14px;">
                            <div style="font-size: 11px; color: var(--text-muted);">Impacto Mensual Proyectado:</div>
                            <div id="res-monthly-loss" style="font-size: 22px; font-weight: 800; color: #fff; font-family: var(--font-code);">$172,800 MXN</div>
                        </div>

                        <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-sm); padding: 10px; margin-top: 16px;">
                            <div style="font-size: 11px; color: var(--emerald); font-weight: 700;">Ahorro con Póliza Preventiva TCS:</div>
                            <div id="res-savings" style="font-size: 16px; font-weight: 800; color: #fff;">$158,976 MXN</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     NORMATIVAS Y SEGURIDAD INDUSTRIAL
     ===================================================================== -->
<section id="seguridad" class="section-padding" style="background: rgba(6, 11, 25, 0.8); border-top: 1px solid var(--border-line);">
    <div class="container">
        <div class="section-head">
            <span class="section-tag">SEGURIDAD Y COMPLIANCE</span>
            <h2 class="section-title">Certificación y Normativas de Grado Industrial</h2>
            <p class="section-desc">
                Cumplimiento estricto con los más altos estándares internacionales para proteger la integridad física de tus mecánicos y avalar tu taller ante auditorías.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
            <div style="background: #091226; border: 1px solid var(--border-line); border-radius: var(--radius-md); padding: 24px; text-align: center;">
                <div style="font-size: 20px; font-weight: 800; color: var(--blue-tech-light); font-family: var(--font-code); margin-bottom: 8px;">ISO 9001:2015</div>
                <div style="font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 6px;">Gestión de Calidad en Servicio</div>
                <div style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">Procesos estandarizados de revisión, calibración y bitácoras foliadas sin margen de error.</div>
            </div>

            <div style="background: #091226; border: 1px solid var(--border-line); border-radius: var(--radius-md); padding: 24px; text-align: center;">
                <div style="font-size: 20px; font-weight: 800; color: var(--red-tcs-light); font-family: var(--font-code); margin-bottom: 8px;">SAE J2843 / J2911</div>
                <div style="font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 6px;">Manejo Seguro de Gas A2L</div>
                <div style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">Certificación técnica en estaciones R-1234yf previniendo riesgos de inflamabilidad.</div>
            </div>

            <div style="background: #091226; border: 1px solid var(--border-line); border-radius: var(--radius-md); padding: 24px; text-align: center;">
                <div style="font-size: 20px; font-weight: 800; color: var(--emerald); font-family: var(--font-code); margin-bottom: 8px;">NOM-004-STPS</div>
                <div style="font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 6px;">Sistemas de Protección en Máquinas</div>
                <div style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">Inspección rigurosa de topes superiores, guardas, botoneras y paradas de emergencia.</div>
            </div>

            <div style="background: #091226; border: 1px solid var(--border-line); border-radius: var(--radius-md); padding: 24px; text-align: center;">
                <div style="font-size: 20px; font-weight: 800; color: #fbbf24; font-family: var(--font-code); margin-bottom: 8px;">ALI / ANSI / OSHA</div>
                <div style="font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 6px;">Estándares de Elevadores de Autos</div>
                <div style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">Verificación de resistencia en brazos telescópicos, seguros automáticos y cables de carga.</div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     CONTACTO & AGENDAR SERVICIO
     ===================================================================== -->
<section id="contacto" class="section-padding">
    <div class="container">
        <div class="contact-box">
            <div class="contact-grid">
                <div>
                    <span class="section-tag">ATENCIÓN NACIONAL INMEDIATA</span>
                    <h2 style="font-family: var(--font-display); font-size: 32px; font-weight: 800; color: #fff; margin-bottom: 14px;">
                        Agenda el Diagnóstico Técnico de tus Elevadores
                    </h2>
                    <p style="font-size: 14px; color: var(--text-dim); line-height: 1.6; margin-bottom: 24px;">
                        Déjanos tus datos o envíanos un mensaje directo por WhatsApp. Un ingeniero especialista de TCS Motriz se pondrá en contacto contigo para programar la visita a tus instalaciones.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13px; margin-bottom: 30px;">
                        <div>📍 <strong>Cobertura:</strong> Área Metropolitana, Ciudad de México y Servicio Nacional</div>
                        <div>📞 <strong>Teléfono Oficina:</strong> <a href="tel:5580004277" style="color: var(--blue-tech-light); text-decoration: none;">55-8000-4277</a></div>
                        <div>✉️ <strong>Correo Electrónico:</strong> <a href="mailto:soporte@servicio-tcsmotriz.com.mx" style="color: var(--blue-tech-light); text-decoration: none;">soporte@servicio-tcsmotriz.com.mx</a></div>
                        <div>⏰ <strong>Horario de Operaciones:</strong> Lunes a Sábado: 8:00 hrs a 19:00 hrs</div>
                    </div>

                    <a href="https://wa.me/525580004277?text=Hola%20TCS%20Motriz,%20deseo%20cotizar%20mantenimiento%20para%20los%20elevadores%20de%20mi%20taller" target="_blank" class="btn-lnd btn-lnd-primary" style="background: #15803d; border-color: #16a34a; box-shadow: 0 4px 18px rgba(22, 163, 74, 0.4);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Escribir por WhatsApp</span>
                    </a>
                </div>

                <!-- FORMULARIO DIRECTO -->
                <div>
                    <form onsubmit="enviarCotizacionWhatsApp(event)">
                        <div class="form-group-lnd">
                            <label class="form-label-lnd">Nombre Completo *</label>
                            <input type="text" id="cot-nombre" class="form-control-lnd" required placeholder="Ing. Roberto Garza">
                        </div>

                        <div class="form-group-lnd">
                            <label class="form-label-lnd">Empresa, Agencia o Taller Mecánico *</label>
                            <input type="text" id="cot-empresa" class="form-control-lnd" required placeholder="Ej: Taller Automotriz San Ángel">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div class="form-group-lnd">
                                <label class="form-label-lnd">Teléfono de Contacto *</label>
                                <input type="tel" id="cot-tel" class="form-control-lnd" required placeholder="55-1234-5678">
                            </div>
                            <div class="form-group-lnd">
                                <label class="form-label-lnd">Cantidad de Elevadores *</label>
                                <input type="number" id="cot-cant" class="form-control-lnd" required value="3" min="1">
                            </div>
                        </div>

                        <div class="form-group-lnd">
                            <label class="form-label-lnd">Tipo de Equipamiento Principal</label>
                            <select id="cot-equipos" class="form-control-lnd">
                                <option value="Elevadores de 2 Postes">Elevadores de 2 Postes (Rotary / BendPak)</option>
                                <option value="Elevadores Tijera para Alineación">Elevadores Tijera para Alineación</option>
                                <option value="Tornos Rectificadores de Freno">Tornos Rectificadores de Freno</option>
                                <option value="Desmontadoras y Balanceadoras">Desmontadoras y Balanceadoras</option>
                                <option value="Estaciones de Gas A/C R-1234yf">Estaciones de Gas A/C R-1234yf</option>
                                <option value="Paquete Completo de Taller">Paquete Integral Multiequipo</option>
                            </select>
                        </div>

                        <div class="form-group-lnd">
                            <label class="form-label-lnd">Mensaje o Falla Detectada (Opcional)</label>
                            <textarea id="cot-msg" class="form-control-lnd" rows="3" placeholder="Describe si requieres póliza de mantenimiento preventivo, peritaje o reparación urgente..."></textarea>
                        </div>

                        <button type="submit" class="btn-lnd btn-lnd-primary" style="width: 100%; justify-content: center; padding: 14px;">
                            <span>Enviar Solicitud de Cotización</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     FOOTER
     ===================================================================== -->
<footer class="landing-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="assets/img/logo-tcs.png" alt="TCS Logo" style="height: 38px;">
                    <span style="font-family: var(--font-display); font-size: 16px; font-weight: 800; color: #fff;">TCS Motriz</span>
                </div>
                <p>
                    Líderes en ingeniería automotriz, telemetría y certificación técnica de elevadores y maquinaria para talleres de alta productividad en México.
                </p>
                <div style="margin-top: 14px; font-size: 12px; color: var(--blue-tech-light); font-family: var(--font-code);">
                    servicio-tcsmotriz.com.mx
                </div>
            </div>

            <div class="footer-col">
                <h5>Servicios</h5>
                <ul class="footer-links">
                    <li><a href="#maquinaria">Elevadores de 2 Postes</a></li>
                    <li><a href="#maquinaria">Elevadores de Tijera</a></li>
                    <li><a href="#maquinaria">Tornos de Discos de Freno</a></li>
                    <li><a href="#maquinaria">Desmontadoras de Llantas</a></li>
                    <li><a href="#maquinaria">Recuperadoras R-1234yf</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Plataforma</h5>
                <ul class="footer-links">
                    <li><a href="login.php">Portal de Clientes</a></li>
                    <li><a href="login.php">Acceso a Técnicos</a></li>
                    <li><a href="#plataforma">Códigos QR en Bahía</a></li>
                    <li><a href="#plataforma">Reportes Foliados en PDF</a></li>
                    <li><a href="#seguridad">Cumplimiento ISO 9001</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Contacto</h5>
                <ul class="footer-links">
                    <li><span style="color: #fff;">Línea Directa:</span> 55-8000-4277</li>
                    <li><span style="color: #fff;">Soporte:</span> soporte@servicio-tcsmotriz.com.mx</li>
                    <li><span style="color: #fff;">Cobertura:</span> Todo México</li>
                    <li><a href="login.php" style="color: var(--red-tcs-light); font-weight: 700;">Ingreso al Sistema →</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© 2026 TCS Motriz. Todos los derechos reservados.</span>
            <span>Seguridad Perimetral OWASP • ISO 27001 • ISO 9001 SGC</span>
        </div>
    </div>
</footer>

<script src="assets/js/landing.js"></script>
</body>
</html>
