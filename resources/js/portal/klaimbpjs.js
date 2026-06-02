/* ══════════════════════════════════════════════
   KLAIM BPJS DASHBOARD — klaim-bpjs.js
══════════════════════════════════════════════ */

/* ══════════════════════════════════════════════
   CONFIG
══════════════════════════════════════════════ */
const API_BASE  = '/bpjs';
const RELOAD_MS = 5 * 60 * 1000; // auto-reload setiap 5 menit

let currentPeriod = 'weekly';
let dateFrom      = null;
let dateTo        = null;
let chartRinap    = null;
let chartRjalan   = null;
let chartDonut    = null;
let reloadTimer   = null;

/* ══════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════ */
const fmtRp  = n => (n == null || isNaN(n)) ? 'Rp –' : 'Rp ' + Number(n).toLocaleString('id-ID');
const fmtNum = n => (n == null || isNaN(n)) ? '–'    : Number(n).toLocaleString('id-ID');
const $      = id => document.getElementById(id);

function setLoading(key, show) {
    $('loading-' + key)?.classList.toggle('show', show);
}
function setError(key, show) {
    $('error-' + key)?.classList.toggle('show', show);
}

async function apiFetch(endpoint, params) {
    const url = `${API_BASE}/${endpoint}${params ? '?' + params : ''}`;
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

/* ══════════════════════════════════════════════
   PERIOD / FILTER
══════════════════════════════════════════════ */
function setPeriod(btn, period) {
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentPeriod = period;
    dateFrom = dateTo = null;

    const label = btn.dataset.label;
    ['label-rinap', 'label-rjalan', 'label-komposisi'].forEach(id => $(id).textContent = label);

    loadAll();
}

function applyCustomRange() {
    const f = $('date-from').value;
    const t = $('date-to').value;
    if (!f || !t) { alert('Pilih tanggal awal dan akhir terlebih dahulu.'); return; }
    if (f > t)    { alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir.'); return; }

    dateFrom = f;
    dateTo   = t;
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    currentPeriod = 'custom';

    const label = `${f} → ${t}`;
    ['label-rinap', 'label-rjalan', 'label-komposisi'].forEach(id => $(id).textContent = label);

    loadAll();
}

function buildParams() {
    const p = new URLSearchParams();
    if (currentPeriod === 'custom') {
        p.set('from', dateFrom);
        p.set('to',   dateTo);
    } else {
        p.set('period', currentPeriod);
    }
    return p.toString();
}

/* ══════════════════════════════════════════════
   MAIN LOAD
══════════════════════════════════════════════ */
async function loadAll() {
    // Reset auto-reload timer
    clearTimeout(reloadTimer);
    reloadTimer = setTimeout(loadAll, RELOAD_MS);

    const params = buildParams();
    await Promise.all([
        loadCharts(params),
        loadSummary(params),
    ]);
}

/* ══════════════════════════════════════════════
   CHART FACTORY
══════════════════════════════════════════════ */
function makeComboChart(canvasId, data, colors) {
    const ctx = $(canvasId).getContext('2d');
    return new Chart(ctx, {
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    type: 'bar',
                    label: 'Pengajuan',
                    data: data.pengajuan || [],
                    backgroundColor: colors.pengajuan,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y',
                    order: 2
                },
                {
                    type: 'bar',
                    label: 'Terbayar',
                    data: data.terbayar_count || [],
                    backgroundColor: colors.terbayar,
                    borderRadius: 6,
                    borderSkipped: false,
                    yAxisID: 'y',
                    order: 3
                },
                {
                    type: 'line',
                    label: 'Nominal',
                    data: data.nominal || [],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#f59e0b',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'yRight',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 600, easing: 'easeInOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f1629',
                    borderColor: 'rgba(255,255,255,.1)',
                    borderWidth: 1,
                    titleFont: { family: 'Sora',    size: 12, weight: '700' },
                    bodyFont:  { family: 'DM Mono', size: 11 },
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => {
                            if (ctx.dataset.type === 'line') return ` Nominal: ${fmtRp(ctx.raw)}`;
                            return ` ${ctx.dataset.label}: ${fmtNum(ctx.raw)} kasus`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'DM Mono', size: 10 }, color: '#475569' },
                    border: { color: 'rgba(255,255,255,.06)' }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,.04)', drawBorder: false },
                    ticks: { font: { family: 'DM Mono', size: 10 }, color: '#475569' },
                    border: { dash: [4,4], color: 'transparent' },
                    beginAtZero: true
                },
                yRight: {
                    type: 'linear',
                    position: 'right',
                    grid: { display: false },
                    ticks: {
                        font: { family: 'DM Mono', size: 10 },
                        color: '#f59e0b',
                        callback: v => {
                            if (v >= 1e9) return (v / 1e9).toFixed(1) + 'M';
                            if (v >= 1e6) return (v / 1e6).toFixed(1) + 'jt';
                            if (v >= 1e3) return (v / 1e3).toFixed(0) + 'rb';
                            return v;
                        }
                    },
                    border: { color: 'transparent' }
                }
            }
        }
    });
}

/* ══════════════════════════════════════════════
   LOAD CHARTS (/bpjs/chart-jenis)
══════════════════════════════════════════════ */
async function loadCharts(params) {
    setLoading('rinap',  true);
    setLoading('rjalan', true);
    setError('rinap',    false);
    setError('rjalan',   false);

    try {
        const d = await apiFetch('chart-jenis', params);

        // Rawat Inap
        if (chartRinap) chartRinap.destroy();
        chartRinap = makeComboChart('chart-rinap', d.rinap ?? {}, {
            pengajuan: 'rgba(20,184,166,.65)',
            terbayar:  'rgba(34,197,94,.65)'
        });

        // Rawat Jalan
        if (chartRjalan) chartRjalan.destroy();
        chartRjalan = makeComboChart('chart-rjalan', d.rjalan ?? {}, {
            pengajuan: 'rgba(99,102,241,.65)',
            terbayar:  'rgba(59,130,246,.65)'
        });

        // Donut komposisi
        renderDonut(d.summary ?? null);

    } catch (e) {
        console.error('[chartJenis] error:', e);
        setError('rinap',  true);
        setError('rjalan', true);

        // Fallback dummy — agar halaman tidak kosong
        const dummy = {
            labels:          ['Sen','Sel','Rab','Kam','Jum','Sab','Min'],
            pengajuan:       [40, 32, 55, 48, 60, 43, 38],
            terbayar_count:  [30, 25, 42, 38, 50, 35, 28],
            nominal:         [320e6, 260e6, 430e6, 380e6, 500e6, 350e6, 280e6]
        };
        if (chartRinap)  chartRinap.destroy();
        if (chartRjalan) chartRjalan.destroy();
        chartRinap  = makeComboChart('chart-rinap',  dummy, { pengajuan:'rgba(20,184,166,.65)', terbayar:'rgba(34,197,94,.65)' });
        chartRjalan = makeComboChart('chart-rjalan',
            { ...dummy, pengajuan:[25,20,35,28,40,30,22], terbayar_count:[18,14,28,22,32,24,16] },
            { pengajuan:'rgba(99,102,241,.65)', terbayar:'rgba(59,130,246,.65)' }
        );
    } finally {
        setLoading('rinap',  false);
        setLoading('rjalan', false);
    }
}

/* ══════════════════════════════════════════════
   DONUT CHART
══════════════════════════════════════════════ */
function renderDonut(s) {
    if (!s) return;

    const vals  = [s.terbayar ?? 0, s.pending ?? 0, s.tidak_layak ?? 0, s.diproses ?? 0];
    const total = vals.reduce((a, b) => a + b, 0);

    $('donut-total').textContent     = fmtNum(total);
    $('leg-terbayar').textContent    = fmtNum(s.terbayar);
    $('leg-pending').textContent     = fmtNum(s.pending);
    $('leg-tidaklayak').textContent  = fmtNum(s.tidak_layak);
    $('leg-diproses').textContent    = fmtNum(s.diproses);

    const pct = v => total > 0 ? `(${((v / total) * 100).toFixed(1)}%)` : '';
    $('pct-terbayar').textContent    = pct(s.terbayar);
    $('pct-pending').textContent     = pct(s.pending);
    $('pct-tidaklayak').textContent  = pct(s.tidak_layak);
    $('pct-diproses').textContent    = pct(s.diproses);

    if (chartDonut) chartDonut.destroy();
    chartDonut = new Chart($('donutChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Terbayar', 'Pending', 'Tidak Layak', 'Diproses'],
            datasets: [{
                data: vals,
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#6366f1'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            cutout: '68%',
            responsive: false,
            animation: { duration: 600 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f1629',
                    borderColor: 'rgba(255,255,255,.1)',
                    borderWidth: 1,
                    titleFont: { family: 'Sora',    size: 12, weight: '700' },
                    bodyFont:  { family: 'DM Mono', size: 11 },
                    cornerRadius: 10,
                    padding: 10
                }
            }
        }
    });
}

/* ══════════════════════════════════════════════
   SUMMARY NOMINAL (/bpjs/summary)
══════════════════════════════════════════════ */
function renderDelta(elId, delta) {
    const el = $(elId);
    if (!el) return;
    if (delta === null || delta === undefined) { el.innerHTML = ''; return; }

    let cls  = 'delta-flat';
    let icon = '→';
    if (delta > 0)  { cls = 'delta-up';   icon = '▲'; }
    if (delta < 0)  { cls = 'delta-down'; icon = '▼'; }

    el.innerHTML = `<span class="delta-badge ${cls}">${icon} ${Math.abs(delta)}%</span>`;
}

async function loadSummary(params) {
    try {
        // summary endpoint untuk terbayar/pending/tidak_layak
        const d = await apiFetch('summary', params);

        const set = (key, apiKey) => {
            const obj = d[apiKey];
            if (!obj) return;
            $('sum-' + key + '-rp').textContent    = fmtRp(obj.nominal);
            $('sum-' + key + '-kasus').textContent = fmtNum(obj.count) + ' kasus';
            renderDelta('sum-' + key + '-delta', obj.delta ?? null);
        };

        set('terbayar',   'terbayar');
        set('pending',    'pending');
        set('tidaklayak', 'tidak_layak');

        // rinap & rjalan dari chart-jenis, ambil lagi untuk summary box
        const c = await apiFetch('chart-jenis', params);
        const rinapTotal  = (c.rinap?.pengajuan  ?? []).reduce((a, b) => a + b, 0);
        const rjalanTotal = (c.rjalan?.pengajuan ?? []).reduce((a, b) => a + b, 0);
        const rinapNom    = (c.rinap?.nominal    ?? []).reduce((a, b) => a + b, 0);
        const rjalanNom   = (c.rjalan?.nominal   ?? []).reduce((a, b) => a + b, 0);

        $('sum-rinap-rp').textContent    = fmtRp(rinapNom);
        $('sum-rinap-kasus').textContent = fmtNum(rinapTotal) + ' kasus';
        $('sum-rjalan-rp').textContent   = fmtRp(rjalanNom);
        $('sum-rjalan-kasus').textContent = fmtNum(rjalanTotal) + ' kasus';

    } catch (e) {
        console.error('[summary] error:', e);
    }
}

/* ══════════════════════════════════════════════
   INIT
══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const meta = await apiFetch('meta', '');
        dateFrom = meta.default_from;
        dateTo   = meta.default_to;
        $('date-from').value = dateFrom;
        $('date-to').value   = dateTo;

        // ← PENTING: set custom SEBELUM loadAll()
        currentPeriod = 'custom';
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        const label = `${dateFrom} → ${dateTo}`;
        ['label-rinap', 'label-rjalan', 'label-komposisi'].forEach(id => $(id).textContent = label);

    } catch {
        const now  = new Date();
        const y    = now.getFullYear();
        const m    = String(now.getMonth() + 1).padStart(2, '0');
        const last = new Date(y, now.getMonth() + 1, 0).getDate();
        dateFrom   = `${y}-${m}-01`;
        dateTo     = `${y}-${m}-${last}`;
        $('date-from').value = dateFrom;
        $('date-to').value   = dateTo;
        currentPeriod = 'custom'; // ← tambah ini
    }

    loadAll(); // ← sekarang currentPeriod sudah 'custom'
});