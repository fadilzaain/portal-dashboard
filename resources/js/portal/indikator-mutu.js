import Chart from 'chart.js/auto';

const ROUTES          = window.IM_CONFIG.routes;
const DEFAULT_FILTERS = window.IM_CONFIG.filters;

let grafikInstance = null;
let ndrInstance    = null;

const $          = id => document.getElementById(id);
const getTw      = () => parseInt($('filter-triwulan').value) || 1;
const getTahun   = () => $('filter-tahun').value;
const getJenis   = () => $('filter-jenis').value;
const toRoman    = n => ['I', 'II', 'III', 'IV'][n - 1] ?? n;
const setLoading = show => $('loading-tabel').classList.toggle('active', show);

// panggil loadGdrNdr
async function loadAll() {
    await Promise.all([loadData(), loadGdrNdr()]);
}

async function fetchJson(url) {
    const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const json = await res.json();
    if (!json.success) throw new Error(json.message || 'Gagal');
    return json;
}

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
    } catch (e) {
        $('tbody-indikator').innerHTML =
            `<tr><td colspan="7" style="text-align:center;color:var(--ar);padding:2rem;">Gagal memuat data: ${e.message}</td></tr>`;
    } finally {
        setLoading(false);
    }
}

async function loadGdrNdr() {
    const params = new URLSearchParams({ triwulan: getTw(), tahun: getTahun() });
    try {
        const json = await fetchJson(`${ROUTES.gdrndr}?${params}`);
        renderGDR(json.grafik.gdr);
        renderNDR(json.grafik.ndr);
    } catch (e) {
        $('insight-capaian-text').textContent = 'Gagal memuat GDR/NDR: ' + e.message;
    }
}

function renderMeta(meta, filters) {
    $('meta-total').textContent    = meta.total_indikator ?? 0;
    $('meta-tercapai').textContent = meta.tercapai        ?? 0;
    $('meta-belum').textContent    = meta.belum_tercapai  ?? 0;

    const label = `TW ${toRoman(filters.triwulan)} / ${filters.tahun}`;
    $('meta-periode').textContent  = label;
    $('periode-label').textContent = label;
}

function makeChartOptions(color, unitSuffix) {
    return {
        responsive:          true,
        maintainAspectRatio: false,
        interaction:         { mode: 'index', intersect: false },
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

// render GDR
function renderGDR(g) {
    if (grafikInstance) grafikInstance.destroy();

    const opts = makeChartOptions('#f59e0b', '‰');
    opts.scales.y.ticks.callback = v => v.toFixed(1) + '‰';

    grafikInstance = new Chart($('grafikIndikator').getContext('2d'), {
        type: 'line',
        data: { labels: g.labels, datasets: g.datasets },
        options: opts,
    });

    const vals = (g.datasets[0]?.data ?? []).filter(v => v !== null);
    if (vals.length >= 2) {
        const diff = vals.at(-1) - vals[0];
        const sign = diff >= 0 ? '+' : '';
        const color = diff >= 0 ? '#f87171' : '#34d399';
        $('insight-capaian-text').innerHTML =
            `GDR ${diff >= 0 ? 'naik' : 'turun'} <span style="color:${color};font-weight:700;">${sign}${diff.toFixed(2)}‰</span> dari ${g.labels[0]} ke ${g.labels.at(-1)}.`;
    } else {
        $('insight-capaian-text').textContent = 'Belum cukup data untuk analisis tren.';
    }
}

// render NDR
function renderNDR(g) {
    if (ndrInstance) ndrInstance.destroy();

    const opts = makeChartOptions('#f87171', '‰');
    opts.scales.y.ticks.callback = v => v.toFixed(1) + '‰';

    ndrInstance = new Chart($('grafikNDR').getContext('2d'), {
        type: 'line',
        data: { labels: g.labels, datasets: g.datasets },
        options: opts,
    });

    const vals = (g.datasets[0]?.data ?? []).filter(v => v !== null);
    if (vals.length >= 2) {
        const diff = vals.at(-1) - vals[0];
        const sign = diff >= 0 ? '+' : '';
        const color = diff >= 0 ? '#f87171' : '#34d399';
        $('insight-ndr-text').innerHTML =
            `NDR ${diff >= 0 ? 'naik' : 'turun'} <span style="color:${color};font-weight:700;">${sign}${diff.toFixed(2)}‰</span> dari ${g.labels[0]} ke ${g.labels.at(-1)}.`;
    } else {
        $('insight-ndr-text').textContent = 'Belum cukup data untuk analisis tren.';
    }
}

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

        const twOk   = ind.triwulan !== null && ind.target_num !== null
            ? (ind.is_lower ? ind.triwulan <= ind.target_num : ind.triwulan >= ind.target_num)
            : null;
        const twHtml = ind.triwulan !== null
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

function switchTw(type, tw) {
    document.querySelectorAll(`#tw-tabs-${type} .tw-tab`)
        .forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === tw));
    $('filter-triwulan').value = tw;
    type === 'grafik' ? loadGdrNdr() : loadGdrNdr(); // keduanya panggil loadGdrNdr
}

function syncTwTabs(tw) {
    const n = parseInt(tw);
    document.querySelectorAll('#tw-tabs-grafik .tw-tab, #tw-tabs-ndr .tw-tab')
        .forEach(b => b.classList.toggle('active', parseInt(b.dataset.tw) === n));
}

function resetFilter() {
    $('filter-jenis').value    = '';
    $('filter-triwulan').value = '1';
    $('filter-tahun').value    = DEFAULT_FILTERS.tahun;
    syncTwTabs(1);
    loadAll();
}

document.addEventListener('DOMContentLoaded', loadAll);

window.loadAll     = loadAll;
window.switchTw    = switchTw;
window.syncTwTabs  = syncTwTabs;
window.resetFilter = resetFilter;