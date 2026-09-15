/**
 * TCS MOTRIZ - Lógica de Interfaz, Modales y Filtros Dinámicos
 * Telemetría de Equipos y Elevadores
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar manejadores de eventos
    initModals();
    initRoleSwitchers();
    initFilters();
    initPrintButtons();
    initSignatures();
});

/**
 * Manejo accesible de modales
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}
window.openModal = openModal;

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = 'auto';
    }
}
window.closeModal = closeModal;

function showTab(tabId, btn) {
    document.querySelectorAll('.tab-content-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    const panel = document.getElementById(tabId);
    if (panel) panel.style.display = 'block';
    if (btn) btn.classList.add('active');
}
window.showTab = showTab;

function initModals() {
    // Cerrar con botones data-close-modal
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal-backdrop');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });

    // Cerrar al dar click fuera del diálogo
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                closeModal(backdrop.id);
            }
        });
    });

    // Tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.open').forEach(m => closeModal(m.id));
        }
    });
}

/**
 * Conmutador de Roles Simulado (Admin / Técnico / Cliente)
 */
function initRoleSwitchers() {
    document.querySelectorAll('.btn-sim-role').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const targetRole = btn.getAttribute('data-role');
            if (targetRole) {
                window.location.href = `index.php?action=switch_role&role=${targetRole}&csrf=${window.TCS_CSRF || ''}`;
            }
        });
    });
}

/**
 * Filtros de Búsqueda Dinámicos en tiempo real
 */
function initFilters() {
    // Buscador de equipos
    const searchEquipo = document.getElementById('search-equipo-input');
    if (searchEquipo) {
        searchEquipo.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.equipo-card').forEach(card => {
                const text = card.innerText.toLowerCase();
                card.style.display = text.includes(term) ? 'flex' : 'none';
            });
        });
    }

    // Filtro por estado de salud de equipos
    const filterSalud = document.getElementById('filter-salud-select');
    if (filterSalud) {
        filterSalud.addEventListener('change', (e) => {
            const val = e.target.value;
            document.querySelectorAll('.equipo-card').forEach(card => {
                if (!val || card.getAttribute('data-salud') === val) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Buscador en tablas generales
    const tableSearch = document.getElementById('table-search-input');
    if (tableSearch) {
        tableSearch.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            const table = document.querySelector('.data-table tbody');
            if (table) {
                table.querySelectorAll('tr').forEach(tr => {
                    tr.style.display = tr.innerText.toLowerCase().includes(term) ? '' : 'none';
                });
            }
        });
    }
}

/**
 * Botones de impresión oficial
 */
function initPrintButtons() {
    document.querySelectorAll('.btn-print-report').forEach(btn => {
        btn.addEventListener('click', () => {
            window.print();
        });
    });
}

/**
 * Renderizador de QR en Canvas Dinámico
 */
function renderEquipmentQR(containerId, qrText, equipoCodigo) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Generar representación visual de código QR de alta resolución
    const size = 180;
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;
    const ctx = canvas.getContext('2d');

    // Fondo blanco
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, size, size);

    // Patrón matricial procedural reproducible basado en hash del texto
    ctx.fillStyle = '#0f172a';
    
    // Cuadros guía en las 3 esquinas
    function drawCorner(x, y) {
        ctx.fillRect(x, y, 42, 42);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x + 6, y + 6, 30, 30);
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(x + 12, y + 12, 18, 18);
    }
    drawCorner(10, 10);
    drawCorner(size - 52, 10);
    drawCorner(10, size - 52);

    // Módulos internos basados en código
    let seed = 0;
    for (let i = 0; i < qrText.length; i++) {
        seed = (seed * 31 + qrText.charCodeAt(i)) % 2147483647;
    }

    const blockSize = 6;
    for (let r = 10; r < size - 10; r += blockSize) {
        for (let c = 10; c < size - 10; c += blockSize) {
            // No dibujar sobre las esquinas guía
            if ((r < 58 && c < 58) || (r < 58 && c > size - 58) || (r > size - 58 && c < 58)) {
                continue;
            }
            seed = (seed * 16807) % 2147483647;
            if (seed % 2 === 0) {
                ctx.fillRect(c, r, blockSize - 1, blockSize - 1);
            }
        }
    }

    container.innerHTML = '';
    container.appendChild(canvas);
}

/**
 * Mostrar modal de expediente QR
 */
function showQRModal(codigo, nombre, marca, serie, bahia) {
    document.getElementById('qr-modal-codigo').innerText = codigo;
    document.getElementById('qr-modal-nombre').innerText = nombre;
    document.getElementById('qr-modal-meta').innerText = `${marca} | Serie: ${serie} | ${bahia}`;
    renderEquipmentQR('qr-modal-canvas-box', `https://servicio-tcsmotriz.com.mx/equipo?id=${codigo}`, codigo);
    openModal('modal-qr-expediente');
}

/**
 * Ver Expediente Completo de Equipo
 */
function verExpedienteEquipo(id) {
    window.location.href = `index.php?view=equipos&detalle_id=${id}`;
}

/**
 * Ver Reporte Técnico Oficial
 */
function verReporteOficial(id) {
    window.location.href = `index.php?view=reportes&reporte_id=${id}`;
}

/**
 * Inicializar todos los pads de firma interactivos
 */
function initSignatures() {
    initSignaturePad('signature-canvas-cliente', 'firma_cliente_canvas');
    initSignaturePad('signature-canvas-tecnico', 'firma_tecnico_canvas');
}

/**
 * Inicializador de Canvas de Firma Táctil (Móvil / Tablet / Mouse)
 */
function initSignaturePad(canvasId, inputHiddenId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const input = document.getElementById(inputHiddenId);
    
    let isDrawing = false;
    let hasDrawn = false;
    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = (e.touches && e.touches.length > 0) ? e.touches[0].clientX : e.clientX;
        const clientY = (e.touches && e.touches.length > 0) ? e.touches[0].clientY : e.clientY;
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    function startDraw(e) {
        isDrawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        if (e.cancelable) e.preventDefault();
    }

    function draw(e) {
        if (!isDrawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        hasDrawn = true;
        if (e.cancelable) e.preventDefault();
    }

    function endDraw() {
        if (!isDrawing) return;
        isDrawing = false;
        if (hasDrawn && input) {
            input.value = canvas.toDataURL('image/png');
        }
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', endDraw);

    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    window.addEventListener('touchend', endDraw);
}

function clearCanvas(canvasId, inputHiddenId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    const input = document.getElementById(inputHiddenId);
    if (input) input.value = '';
}

function previewImage(inputElement, imgElementId) {
    if (inputElement.files && inputElement.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(imgElementId);
            if (img) {
                img.src = e.target.result;
                const parent = img.parentElement;
                if (parent) parent.style.display = 'block';
            }
        };
        reader.readAsDataURL(inputElement.files[0]);
    }
}

