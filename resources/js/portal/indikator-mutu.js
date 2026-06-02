/* ============================================================
   resources/js/indikator-mutu.js
   ============================================================ */

import Chart from 'chart.js/auto';

// ── Config dari blade ──────────────────────────────────────
const ROUTES          = window.IM_CONFIG.routes;
const DEFAULT_FILTERS = window.IM_CONFIG.filters;

// ── State ──────────────────────────────────────────────────
let grafikInstance = null;
let ndrInstance    = null;

// ── Helpers ────────────────────────────────────────────────
const $          = id => document.getElementById(id);
const getTw      = () => parseInt($('filter-triwulan').value) || 1;
const getTahun   = () => $('filter-tahun').value;
const getJenis   = () => $('filter-jenis').value;
const toRoman    = n => ['I', 'II', 'III', 'IV'][n - 1] ?? n;
const setLoading = show => $('loading-tabel').classList.toggle('active', show);

// ── Entry Point ────────────────────────────────────────────
async function loadAll() {
    await Promise.all([loadData(), loadNDR()]);
}

async function fetchJson(url) {
    const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal');
    return json;
}

// ── Data Tabel + Grafik Capaian ────────────────────────────
async function loadData() {
    const params = new URLSearchParams({
        jenis_mutu: getJenis(),
        triwulan:   getTw(),
        tahun:      getTahun(),
    });

    setLoading(true);
    try {
        const json = await fetchJson(`${ROUTES.data}?${params}`);
        renderMeta(json.meta, json.filters);
        renderTabel(json.tabel, json.filters);
        renderGrafikCapaian(json.grafik);
    } catch (e) {
        $('tbody-indikator').innerHTML =
            `<tr><td colspan="7" style="text-align:center;color:var(--ar);padding:2rem;">Gagal memuat data: ${e.message}</td></tr>`;
    } finally {
        setLoading(false);
    }
}

// ── NDR ────────────────────────────────────────────────────
async function loadNDR() {
    const params = new URLSearchParams({ triwulan: getTw(), tahun: getTahun() });
    try {
        const json = await fetchJson(`${ROUTES.ndr}?${params}`);
        renderNDR(json.grafik);
        buildRuanganToggles(json.grafik.ruangan_list, json.grafik.datasets);
    } catch (e) {
        $('insight-ndr-text').textContent = 'Gagal memuat NDR: ' + e.message;
    }
}

// ── Render Meta / Summary Cards ────────────────────────────
function renderMeta(meta, filters) {
    $('meta-total').textContent    = meta.total_indikator ?? 0;
    $('meta-tercapai').textContent = meta.tercapai        ?? 0;
    $('meta-belum').textContent    = meta.belum_tercapai  ?? 0;

    const label = `TW ${toRoman(filters.triwulan)} / ${filters.tahun}`;
    $('meta-periode').textContent   = label;
    $('periode-label').textContent  = label;
}

// ── Chart Options Factory ──────────────────────────────────
function makeChartOptions(color, unitSuffix) {
    return {
        responsive:        true,
        maintainAspectRatio: false,
        interaction:       { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0a1628',
                titleColor:      '#e2e8f0',
                bodyColor:       '#94a3b8',
                borderColor:     `${color}40`,
                borderWidth:     1,
                padding:         10,
                callbacks: {
                    label: ctx => `${ctx.dataset.label}: ${ctx.raw ?? '–'}${unitSuffix}`,
                },
            },
        },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#94a3b8', font: { size: 11 } } },
            y: { min: 0, grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#94a3b8', font: { size: 11 } } },
        },
    };
}

// ── Grafik Capaian ─────────────────────────────────────────
function renderGrafikCapaian(g) {
    if (grafikInstance) grafikInstance.destroy();

    const opts          = makeChartOptions('#38bdf8', '%');
    opts.scales.y.max   = 105;
    opts.scales.y.ticks.callback = v => v + '%';

    grafikInstance = new Chart($('grafikIndikator').getContext('2d'), {
        type: 'line',
        data: { labels: g.labels, datasets: g.datasets },
        options: opts,
    });

    const vals = (g.datasets[0]?.data ?? []).filter(v => v !== null);
    if (vals.length >= 2) {
        const diff  = vals.at(-1) - vals[0];
        const sign  = diff >= 0 ? '+' : '';
        const color = diff >= 0 ? '#34d399' : '#f87171';
        $('insight-capaian-text').innerHTML =
            `Rata-rata capaian ${diff >= 0 ? 'naik' : 'turun'} <span style="color:${color};font-weight:700;">${sign}${diff.toFixed(1)}%</span> dari ${g.labels[0]} ke ${g.labels[vals.length - 1]}.`;
    } else {
        $('insight-capaian-text').textContent = 'Belum cukup data untuk analisis tren.';
    }
}

// ── Grafik NDR ─────────────────────────────────────────────
function renderNDR(g) {
    if (ndrInstance) ndrInstance.destroy();

    const opts = makeChartOptions('#f87171', '‰');
    opts.scales.y.ticks.callback = v => v.toFixed(1) + '‰';

    ndrInstance = new Chart($('grafikNDR').getContext('2d'), {
        type: 'line',
        data: { labels: g.labels, datasets: g.datasets },
        options: opts,
    });

    const above = (g.datasets[0]?.data ?? []).filter(v => v !== null && v > 1.5).length;
    $('insight-ndr-text').innerHTML = above > 0
        ? `NDR total RS masih di atas target (&lt;1.5‰) pada <span style="color:#f87171;font-weight:700;">${above} bulan</span>.`
        : `NDR total RS sudah di bawah target selama semua bulan. <span style="color:#34d399;font-weight:700;">Pertahankan!</span>`;
}

// ── NDR Ruangan Toggles ────────────────────────────────────
function buildRuanganToggles(ruanganList, datasets) {
    const container = $('ndr-ruangan-toggles');
    container.innerHTML = '';

    const makeBtn = (idx, label, active) => {
        const btn       = document.createElement('button');
        btn.className   = 'ndr-toggle-btn' + (active ? ' active' : '');
        btn.dataset.idx = idx;
        btn.textContent = label;
        btn.onclick     = () => {
            if (!ndrInstance) return;
            const ds  = ndrInstance.data.datasets[idx];
            if (!ds) return;
            ds.hidden = !ds.hidden;
            btn.classList.toggle('active', !ds.hidden);
            ndrInstance.update();
        };
        container.appendChild(btn);
    };

    makeBtn(0, 'Total RS', true);
    ruanganList.forEach((nama, i) => makeBtn(i + 2, nama, false));
}

// ── Render Tabel ───────────────────────────────────────────
function renderTabel(tabel, filters) {
    if (!tabel.length) {
        $('tbody-indikator').innerHTML =
            `<tr><td colspan="7" style="text-align:center;color:var(--tm);padding:2rem;">Tidak ada data untuk filter yang dipilih.</td></tr>`;
        return;
    }

    const b = tabel[0].bulan_data ?? [];
    $('thead-row').innerHTML =
        `<th style="min-width:280px;text-align:left;">Indikator</th><th>Target</th>${b.map(bln => `<th>${bln.nama_bulan}</th>`).join('')}<th>Triwulan</th><th>Status</th>`;

    $('tbody-indikator').innerHTML = tabel.map(ind => {
        const bulanCells = (ind.bulan_data ?? []).map(b => {
            if (b.capaian === null) return `<td><span class="capaian-null">–</span></td>`;
            const ok  = ind.is_lower ? b.capaian <= ind.target_num : b.capaian >= ind.target_num;
            const pct = Math.min(b.capaian / (ind.target_num > 0 ? ind.target_num : 100) * 100, 100);
            return `<td>
                <div class="${ok ? 'capaian-ok' : 'capaian-fail'}">${b.capaian}%</div>
                <div class="pb-wrap"><div class="pb-fill" style="width:${pct}%;background:${ok ? '#34d399' : '#f87171'}"></div></div>
            </td>`;
        }).join('');

        const twOk     = ind.triwulan !== null && ind.target_num !== null
            ? (ind.is_lower ? ind.triwulan <= ind.target_num : ind.triwulan >= ind.target_num)
            : null;
        const twHtml   = ind.triwulan !== null
            ? `<div class="${twOk === true ? 'capaian-ok' : twOk === false ? 'capaian-fail' : ''}">${ind.triwulan}%</div>`
            : '<span class="capaian-null">–</span>';
        const statusHtml = ind.status
            ? `<span class="badge-${ind.status}">${ind.status === 'tercapai' ? '✓ Tercapai' : '✗ Belum'}</span>`
            : '<span style="color:var(--tm);font-size:.75rem;">–</span>';

        return `<tr>
            <td>
                <div class="indikator-nama">${ind.nama_html || ind.nama}</div>
                <div style="margin-top:.3rem;"><span class="badge-${ind.jenis_mutu}">${ind.label_jenis}</span></div>
            </td>
            <td class="target-cell">${ind.target_raw}</td>
            ${bulanCells}
            <td>${twHtml}</td>
            <td>${statusHtml}</td>
        </tr>`;
    }).join('');
}

// ── Tab Switcher ───────────────────────────────────────────
function switchTw(type, tw) {
    document.querySelectorAll(`#tw-tabs-${type} .tw-tab`)
        .forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === tw));
    $('filter-triwulan').value = tw;
    type === 'grafik' ? loadData() : loadNDR();
}

function syncTwTabs(tw) {
    const n = parseInt(tw);
    document.querySelectorAll('#tw-tabs-grafik .tw-tab, #tw-tabs-ndr .tw-tab')
        .forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === n));
}

// ── Reset Filter ───────────────────────────────────────────
function resetFilter() {
    $('filter-jenis').value    = '';
    $('filter-triwulan').value = '1';
    $('filter-tahun').value    = DEFAULT_FILTERS.tahun;
    syncTwTabs(1);
    loadAll();
}

// ── Init ───────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadAll);

// Expose ke HTML (onclick attributes di blade)
window.loadAll      = loadAll;
window.switchTw     = switchTw;
window.syncTwTabs   = syncTwTabs;
window.resetFilter  = resetFilter;