/**
 * TCS MOTRIZ - Scripts Interactivos de la Landing Page
 * Calculadora de ROI de Elevadores y Telemetría en Vivo
 */

document.addEventListener('DOMContentLoaded', () => {
    initRoiCalculator();
    initSimulatedTelemetry();
});

/**
 * Calculadora de Costo por Elevador Detenido
 */
function initRoiCalculator() {
    const rangeLifts = document.getElementById('calc-lifts');
    const rangeCars = document.getElementById('calc-cars');
    const rangeTicket = document.getElementById('calc-ticket');

    const outLifts = document.getElementById('val-lifts');
    const outCars = document.getElementById('val-cars');
    const outTicket = document.getElementById('val-ticket');

    const outDailyLoss = document.getElementById('res-daily-loss');
    const outMonthlyLoss = document.getElementById('res-monthly-loss');
    const outPreventiveSavings = document.getElementById('res-savings');

    if (!rangeLifts || !rangeCars || !rangeTicket) return;

    function updateCalculations() {
        const lifts = parseInt(rangeLifts.value, 10);
        const carsPerLift = parseInt(rangeCars.value, 10);
        const ticket = parseInt(rangeTicket.value, 10);

        outLifts.textContent = `${lifts} Rampa${lifts > 1 ? 's' : ''}`;
        outCars.textContent = `${carsPerLift} autos / día`;
        outTicket.textContent = `$${ticket.toLocaleString('es-MX')} MXN`;

        // Si 1 elevador se detiene (típico en taller)
        const dailyLoss = carsPerLift * ticket;
        const monthlyLoss = dailyLoss * 24; // 24 días laborales
        const savings = monthlyLoss * 0.92; // 92% mitigación con póliza preventiva

        outDailyLoss.textContent = `$${dailyLoss.toLocaleString('es-MX')} MXN`;
        outMonthlyLoss.textContent = `$${monthlyLoss.toLocaleString('es-MX')} MXN`;
        outPreventiveSavings.textContent = `$${Math.round(savings).toLocaleString('es-MX')} MXN`;
    }

    rangeLifts.addEventListener('input', updateCalculations);
    rangeCars.addEventListener('input', updateCalculations);
    rangeTicket.addEventListener('input', updateCalculations);

    updateCalculations();
}

/**
 * Micro-animación de telemetría en tarjeta Hero
 */
function initSimulatedTelemetry() {
    const pressureEl = document.getElementById('telemetry-pressure');
    if (!pressureEl) return;

    setInterval(() => {
        // Fluctuación realista de presión hidráulica (2,180 a 2,220 PSI)
        const base = 2200;
        const jitter = Math.floor(Math.random() * 31) - 15;
        pressureEl.textContent = `${base + jitter} PSI`;
    }, 2500);
}

/**
 * Redirección de cotización directa a WhatsApp Oficial de TCS
 */
function enviarCotizacionWhatsApp(e) {
    e.preventDefault();
    const nombre = document.getElementById('cot-nombre').value.trim();
    const empresa = document.getElementById('cot-empresa').value.trim();
    const tel = document.getElementById('cot-tel').value.trim();
    const equipos = document.getElementById('cot-equipos').value;
    const cant = document.getElementById('cot-cant').value;
    const msg = document.getElementById('cot-msg').value.trim();

    const texto = `Hola TCS Motriz, solicito cotización de servicio técnico:%0A` +
                  `• *Nombre:* ${encodeURIComponent(nombre)}%0A` +
                  `• *Empresa/Taller:* ${encodeURIComponent(empresa)}%0A` +
                  `• *Teléfono:* ${encodeURIComponent(tel)}%0A` +
                  `• *Tipo de Equipos:* ${encodeURIComponent(equipos)}%0A` +
                  `• *Cantidad de Rampas:* ${encodeURIComponent(cant)}%0A` +
                  `• *Mensaje:* ${encodeURIComponent(msg || 'Requiero diagnóstico de seguridad y póliza preventiva.')}`;

    window.open(`https://wa.me/525580004277?text=${texto}`, '_blank');
}
