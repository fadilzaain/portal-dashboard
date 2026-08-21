import Chart from 'chart.js/auto';

// ── Ambil data inject dari blade ────────────────
const {
    prioritasUnit = [],
    statusLabels  = [],
    statusValues  = [],
    bezetting     = { kurang: [], cukup: [], lebih: [] },
} = window.SDM_DATA ?? {};

const sdmReduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ════════════════════════════════════════════════════════════════
// RK CARD 
// ════════════════════════════════════════════════════════════════
function rkToggle(i) {
    const card = document.querySelector(`.rk-card[data-rk="${i}"]`);
    const btn  = card.querySelector('.rk-card-head');
    const open = card.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
}

// Buka/tutup semua card unit sekaligus (cuma yang lagi kefilter/kelihatan).
// Sengaja gak set class .open manual — reuse rkToggle() yg udah ada biar
// logic buka/tutup tetap 1 sumber, tinggal skip card yg state-nya udah sesuai target.
function rkToggleAll(open) {
    document.querySelectorAll('#sdm-unit-grid .rk-card:not(.rk-row-hidden)').forEach(card => {
        if (card.classList.contains('open') !== open) {
            rkToggle(card.dataset.rk);
        }
    });
}

// Dipanggil dari card "Unit & Jabatan Perlu Perhatian"
function sdmScrollToUnit(slug) {
    const card = document.getElementById(`unit-${slug}`);
    if (!card) return;

    if (!card.classList.contains('open')) {
        rkToggle(card.dataset.rk);
    }

    card.scrollIntoView({ behavior: 'smooth', block: 'center' });

    card.classList.add('rk-highlight');
    setTimeout(() => card.classList.remove('rk-highlight'), 1600);
}

function rkFilter(i, q) {
    q = q.trim().toLowerCase();
    const wrap = document.getElementById(`rk-detail-${i}`);
    const rows = wrap.querySelectorAll('.rk-row');
    let visible = 0;

    rows.forEach(row => {
        const match = !q || row.dataset.search.includes(q);
        row.classList.toggle('rk-row-hidden', !match);
        if (match) visible++;
    });

    document.getElementById(`rk-empty-${i}`).style.display = visible ? 'none' : 'block';
}

// ── cari nama unit dan filter status  ──
let rkStatusFilter = '';
const rkTabsEl      = document.querySelector('[data-role="rk-status-tabs"]');
const rkIndicatorEl = document.querySelector('[data-role="rk-status-indicator"]');

function rkSetStatusTab(status, el) {
    rkStatusFilter = status;
    rkTabsEl.querySelectorAll('.bez-tab').forEach(t => t.classList.remove('is-active'));
    el.classList.add('is-active');
    moveTabIndicator(rkIndicatorEl, el);
    rkUnitRender();
}

function rkUnitRender() {
    const q = document.getElementById('rk-unit-search').value.trim().toLowerCase();
    const cards = document.querySelectorAll('#sdm-unit-grid .rk-card');
    let visible = 0;

    cards.forEach(card => {
        const match = (!rkStatusFilter || card.dataset.status === rkStatusFilter)
                   && (!q || card.dataset.unit.includes(q));
        card.classList.toggle('rk-row-hidden', !match);
        if (match) visible++;
    });

    const emptyEl = document.getElementById('rk-unit-empty');
    if (emptyEl) emptyEl.style.display = visible ? 'none' : 'block';
}

requestAnimationFrame(() => {
    moveTabIndicator(rkIndicatorEl, rkTabsEl?.querySelector('.bez-tab.is-active'));
    rkTabsEl?.classList.add('is-ready');
});

// Track ranking "Jabatan Perlu Perhatian" dari 0% ke nilai 
document.querySelectorAll('.jprio-track-fill').forEach((el, idx) => {
    const target = el.dataset.targetWidth;
    if (sdmReduceMotion) {
        el.style.width = target;
        return;
    }
    requestAnimationFrame(() => {
        setTimeout(() => { el.style.width = target; }, 60 * idx);
    });
});

// ════════════════════════════════════════════════════════════════
// CHART "UNIT PERLU PERHATIAN" 
// ════════════════════════════════════════════════════════════════
const prioUnitCanvas = document.getElementById('prioUnitChart');
if (prioUnitCanvas) {
    const prioUnitSlugs = prioritasUnit.map(u => u.slug);

    new Chart(prioUnitCanvas, {
        type: 'bar',
        data: {
            labels: prioritasUnit.map(u => u.unit),
            datasets: [{
                data: prioritasUnit.map(u => u.kekurangan),
                backgroundColor(ctx) {
                    const { chartArea, ctx: c } = ctx.chart;
                    if (!chartArea) return 'rgba(248,113,113,0.7)';
                    const g = c.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
                    g.addColorStop(0, 'rgba(248,113,113,0.55)');
                    g.addColorStop(1, 'rgba(239,68,68,0.95)');
                    return g;
                },
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 16,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 900, easing: 'easeOutQuart' },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,0.08)' },
                    ticks: { color: '#94a3b8', precision: 0, font: { size: 10.5, family: 'Plus Jakarta Sans' } }
                },
                y: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11, family: 'Plus Jakarta Sans', weight: '600' } }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0a1628',
                    titleColor: '#e2e8f0',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(248,113,113,.3)',
                    borderWidth: 1,
                    callbacks: { label: ctx => ` ${ctx.parsed.x.toLocaleString('id-ID')} orang kurang` }
                }
            },
            onClick(evt, elements) {
                if (!elements.length) return;
                const slug = prioUnitSlugs[elements[0].index];
                if (slug) sdmScrollToUnit(slug);
            },
            onHover(evt, elements) {
                evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
            }
        }
    });
}

// ════════════════════════════════════════════════════════════════
// BAR CHART status kepegawaian
// ════════════════════════════════════════════════════════════════
const statusBarCanvas = document.getElementById('statusBarChart');
if (statusBarCanvas) {
    new Chart(statusBarCanvas, {
        type: 'bar',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: 'rgba(56,189,248,0.7)',
                borderColor: '#38bdf8',
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11, family: 'Plus Jakarta Sans' } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: {
                        color: '#94a3b8',
                        callback: v => v.toLocaleString('id-ID'),
                        font: { size: 11, family: 'Plus Jakarta Sans' }
                    }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0a1628',
                    titleColor: '#e2e8f0',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(56,189,248,.25)',
                    borderWidth: 1,
                    callbacks: { label: ctx => ` ${ctx.parsed.y.toLocaleString('id-ID')} orang` }
                }
            }
        },
        plugins: [{
            id: 'topLabel',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                chart.data.datasets.forEach((ds, i) => {
                    chart.getDatasetMeta(i).data.forEach((bar, idx) => {
                        const v = ds.data[idx];
                        if (!v) return;
                        ctx.save();
                        ctx.font = 'bold 11px Plus Jakarta Sans, sans-serif';
                        ctx.fillStyle = '#94a3b8';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        ctx.fillText(v.toLocaleString('id-ID'), bar.x, bar.y - 4);
                        ctx.restore();
                    });
                });
            }
        }]
    });
}

// ════════════════════════════════════════════════════════════════
// BEZETTING TABLE
// ════════════════════════════════════════════════════════════════
const BEZ = {
    kurang: bezetting.kurang,
    cukup:  bezetting.cukup,
    lebih:  bezetting.lebih,
};

let bezTab = 'kurang';
const bezTabsEl      = document.querySelector('[data-role="bez-tabs"]');
const bezIndicatorEl = document.querySelector('[data-role="bez-tab-indicator"]');

// Geser pill indicator ke tombol tab yang aktif dan ganti warna sesuai tone 
function moveTabIndicator(indicatorEl, btn) {
    if (!indicatorEl || !btn) return;
    indicatorEl.style.left   = btn.offsetLeft + 'px';
    indicatorEl.style.width  = btn.offsetWidth + 'px';
    indicatorEl.dataset.tone = btn.dataset.tone;
}

function bezMoveIndicator(btn) {
    moveTabIndicator(bezIndicatorEl, btn);
}

function bezSetTab(tab, el) {
    bezTab = tab;
    document.querySelectorAll('.bez-tab').forEach(t => t.classList.remove('is-active'));
    el.classList.add('is-active');
    bezMoveIndicator(el);
    bezRender();
}

function katClass(kat) {
    const m = { 'Dokter': 'kat-dokter', 'Perawat': 'kat-perawat', 'Farmasi': 'kat-farmasi', 'Medis Lainnya': 'kat-medis' };
    return m[kat] || 'kat-lainnya';
}

function bezRender() {
    const q   = document.getElementById('bez-search').value.toLowerCase();
    const kat = document.getElementById('bez-kat').value;
    const rows = BEZ[bezTab].filter(r =>
        (!q   || r.jabatan.toLowerCase().includes(q)) &&
        (!kat || r.kategori === kat)
    );

    const tbody      = document.getElementById('bez-tbody');
    const emptyState = document.getElementById('bez-empty-state');

    if (!rows.length) {
        tbody.innerHTML = '';
        emptyState.style.display = 'flex';
        return;
    }
    emptyState.style.display = 'none';

    const tone     = bezTab === 'kurang' ? 'red' : bezTab === 'cukup' ? 'green' : 'blue';
    const badgeCls = bezTab === 'kurang' ? 'rk-badge-red' : bezTab === 'cukup' ? 'rk-badge-green' : 'rk-badge-blue';

    tbody.innerHTML = rows.map((r, i) => {
        const sign = r.delta > 0 ? '+' : r.delta === 0 ? '=' : '';
        return `<tr>
            <td style="color:var(--text-muted);font-size:11px">${i + 1}</td>
            <td>
                <div style="font-size:12px;line-height:1.3">${r.jabatan}</div>
                <span class="kat-badge ${katClass(r.kategori)}">${r.kategori}</span>
            </td>
            <td class="r" style="font-weight:600">${r.kebutuhan}</td>
            <td>
                <div style="display:flex;align-items:center;gap:6px">
                    <span style="font-weight:600;min-width:24px">${r.tersedia}</span>
                    <div style="flex:1">
                        <div class="mini-bar"><div class="mini-fill tone-${tone}" style="width:${r.pct}%"></div></div>
                        <div style="font-size:9px;color:var(--text-muted);margin-top:1px">${r.pct}%</div>
                    </div>
                </div>
            </td>
            <td class="c"><span class="rk-badge ${badgeCls}">${sign}${r.delta}</span></td>
        </tr>`;
    }).join('');
}

// Posisi awal indicator dan hint pulse
requestAnimationFrame(() => {
    bezMoveIndicator(bezTabsEl?.querySelector('.bez-tab.is-active'));
    bezTabsEl?.classList.add('is-ready');
    if (!sdmReduceMotion) bezTabsEl?.classList.add('bez-tab-hint');
});

window.addEventListener('resize', () => {
    bezMoveIndicator(bezTabsEl?.querySelector('.bez-tab.is-active'));
    moveTabIndicator(rkIndicatorEl, rkTabsEl?.querySelector('.bez-tab.is-active'));
});

bezRender();

// ── Expose ke global scope ──────────────────
window.sdmScrollToUnit = sdmScrollToUnit;
window.rkToggle        = rkToggle;
window.rkToggleAll     = rkToggleAll;
window.rkFilter        = rkFilter;
window.rkSetStatusTab  = rkSetStatusTab;
window.rkUnitRender    = rkUnitRender;
window.bezSetTab       = bezSetTab;
window.bezRender       = bezRender;