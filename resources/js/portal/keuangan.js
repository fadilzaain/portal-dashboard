/* ============================================================
   resources/js/portal/keuangan.js
   ============================================================ */

import Chart from 'chart.js/auto';

/* ── State ────────────────────────────────────────────────── */
let chartHarian = null;
let amRoot      = null;

/* ── Helpers ──────────────────────────────────────────────── */
const $       = id => document.getElementById(id);
const setText = (id, v) => { const e = $(id); if (e) e.textContent = v; };
const show    = id     => { const e = $(id); if (e) e.style.display = 'flex'; };
const hide    = id     => { const e = $(id); if (e) e.style.display = 'none'; };

const BULAN_NAMA  = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const BULAN_SHORT = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

const fmt = (v, prefix = true) => {
    v = Number(v || 0);
    const neg = v < 0, abs = Math.abs(v);
    const s = abs >= 1e9 ? `${(abs / 1e9).toFixed(1).replace('.', ',')} M`
            : abs >= 1e6 ? `${(abs / 1e6).toFixed(1).replace('.', ',')} Jt`
            : abs >= 1e3 ? `${(abs / 1e3).toFixed(0)} Rb`
            :              `${abs}`;
    if (!prefix) return (neg ? '- ' : '+') + s;
    return (neg ? '- Rp ' : 'Rp ') + s;
};

const fmtAxis = v => {
    v = Number(v || 0);
    if (!v) return '';
    return v >= 1e9 ? (v / 1e9).toFixed(1).replace('.', ',') + 'M'
         : v >= 1e6 ? (v / 1e6).toFixed(1).replace('.', ',') + 'Jt'
         : v >= 1e3 ? (v / 1e3).toFixed(0) + 'Rb'
         : String(v);
};

const pct = (r, t) => t > 0 ? (r / t) * 100 : 0;
const n2  = v => v.toFixed(1).replace('.', ',');

/* ── Chart.js Defaults ────────────────────────────────────── */
Chart.defaults.color       = '#7D8590';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = 'DM Sans';

const TT = {
    backgroundColor: '#1E2430', borderColor: 'rgba(255,255,255,0.1)',
    borderWidth: 1, titleColor: '#E6EDF3', bodyColor: '#7D8590', padding: 10,
};

/* ── MoM Delta ────────────────────────────────────────────── */
function momDelta(now, last, elId, tahun) {
    const el = $(elId);
    if (!el) return;
    if (!last) {
        el.innerHTML = `<div class="kpi-delta-row"><span style="font-size:9px;opacity:.5">— data bulan lalu belum tersedia</span></div>`;
        return;
    }
    const delta = now - last, pctV = (delta / last) * 100, naik = delta >= 0;
    const barPct = Math.min(100, (now / Math.max(now, last)) * 100).toFixed(1);
    const arrow  = dir => `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.95)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="${dir ? '18 15 12 9 6 15' : '6 9 12 15 18 9'}"/></svg>`;
    el.innerHTML = `
        <div class="kpi-delta-row">${arrow(naik)}<span style="font-size:10.5px;font-weight:600">${n2(Math.abs(pctV))}%</span><span style="font-size:9px;opacity:.65">vs tahun lalu ${tahun - 1} (${fmt(last)})</span></div>
        <div class="kpi-delta-bar"><div class="kpi-delta-bar-inner" style="width:${barPct}%"></div></div>`;
}

/* ── KPI Margin ───────────────────────────────────────────── */
function renderKpiMargin(pRows, bRows, momData) {
    const totalBReal   = bRows.reduce((a, r) => a + Number(r.realisasi || 0), 0);
    const totalBTarget = bRows.reduce((a, r) => a + Number(r.target    || 0), 0);
    const serapan      = totalBTarget > 0 ? (totalBReal / totalBTarget) * 100 : 0;

    const validBulan = pRows.reduce((acc, r, i) => {
        const p = Number(r.realisasi || 0), b = Number(bRows[i]?.realisasi || 0);
        if (p > 0 && b > 0) acc.push({ p, b });
        return acc;
    }, []);
    const crr = validBulan.length
        ? (validBulan.reduce((a, r) => a + r.b, 0) / validBulan.reduce((a, r) => a + r.p, 0)) * 100
        : 0;

    const pBulanIni  = Number(momData?.pendapatan_bulan_ini  || 0);
    const pBulanLalu = Number(momData?.pendapatan_bulan_lalu || 0);
    const yoyGrowth  = pBulanLalu > 0 ? ((pBulanIni - pBulanLalu) / pBulanLalu) * 100 : null;

    const sColor = serapan === 0 ? '#94A3B8'
                 : serapan < 50  ? '#F87171'
                 : serapan < 100 ? (serapan < 85 ? '#FBBF24' : '#34D399')
                 : '#F87171';
    const cColor = crr === 0 ? '#94A3B8' : crr < 100 ? '#34D399' : '#F87171';
    const cLabel = crr === 0 ? '—' : crr < 85 ? 'Sehat' : crr < 100 ? 'Waspada' : 'Merugi';
    const yColor = yoyGrowth === null ? '#94A3B8' : yoyGrowth >= 0 ? '#34D399' : '#F87171';
    const yTxt   = yoyGrowth === null
        ? '— data thn lalu kosong'
        : `${yoyGrowth >= 0 ? '▲' : '▼'} ${n2(Math.abs(yoyGrowth))}% YoY pendapatan`;

    $('kpiMargin').innerHTML = serapan > 0
        ? `<span style="color:${sColor}">${n2(serapan)}%</span>`
        : `<span style="opacity:.4">— %</span>`;

    $('kpiMarginSub').innerHTML = `
        <div class="kpi-delta-row" style="justify-content:space-between"><span style="opacity:.65;font-size:9px">Serapan Anggaran</span><span style="font-weight:700;color:${sColor};font-size:10px">${serapan > 0 ? n2(serapan) + '%' : '—'}</span></div>
        <div class="kpi-delta-row" style="justify-content:space-between;margin-top:2px"><span style="opacity:.65;font-size:9px">Cost Recovery Rate</span><span style="font-weight:700;color:${cColor};font-size:10px">${crr > 0 ? n2(crr) + '% · ' + cLabel : '—'}</span></div>
        <div class="kpi-delta-bar" style="margin-top:3px"><div class="kpi-delta-bar-inner" style="width:${Math.min(100, serapan).toFixed(1)}%;background:${sColor}"></div></div>
        <div class="kpi-delta-row" style="margin-top:3px"><span style="font-size:9px;color:${yColor}">${yTxt}</span></div>`;
}

/* ── Rekap ────────────────────────────────────────────────── */
function renderRekap(pRows, bRows, tahun) {
    setText('rekapTahunLabel', tahun);

    const totalP  = pRows.reduce((a, r) => a + Number(r.realisasi || 0), 0);
    const totalB  = bRows.reduce((a, r) => a + Number(r.realisasi || 0), 0);
    const active  = pRows.filter(r => Number(r.realisasi || 0) > 0);
    const avgP    = active.length ? totalP / active.length : 0;
    const bestRow = pRows.reduce((a, r) => Number(r.realisasi || 0) > Number(a.realisasi || 0) ? r : a, pRows[0] || {});
    const defBulan = pRows.filter((r, i) => {
        const p = Number(r.realisasi || 0), b = Number(bRows[i]?.realisasi || 0);
        return p > 0 && b > p;
    });
    const ratio = totalP > 0 ? Math.round((totalB / totalP) * 100) : 0;

    const parts = [];
    if (bestRow?.label && Number(bestRow.realisasi || 0) > 0) parts.push(`Tertinggi <b>${bestRow.label}</b> (${fmt(bestRow.realisasi)})`);
    if (avgP > 0) parts.push(`rata-rata <b>${fmt(Math.round(avgP))}/bln</b>`);
    parts.push(defBulan.length > 0 ? `<b>${defBulan.length} bln</b> defisit` : active.length > 0 ? `semua bln aktif <b>surplus</b>` : '');
    if (ratio > 0) parts.push(`rasio belanja <b>${ratio}%</b>`);

    const insightEl = $('rekapInsight');
    if (insightEl) insightEl.innerHTML = parts.filter(Boolean).join(' · ');

    const maxP      = Math.max(...pRows.map(r => Number(r.realisasi || 0)), 1);
    const maxB      = Math.max(...bRows.map(r => Number(r.realisasi || 0)), 1);
    const bulanSkrg = parseInt($('bulanSelect').value) || 0;
    const container = $('rekapRows');
    if (!container) return;

    const trendIcon = (cur, prev) => {
        if (!prev) return '';
        const [d, color] = cur > prev
            ? ['M2 9L6 3l4 6', '#34D399']
            : cur < prev
            ? ['M2 3l4 6 4-6', '#F87171']
            : ['M2 6h8', 'rgba(255,255,255,.25)'];
        return `<svg viewBox="0 0 12 12" fill="none" style="width:12px;height:12px"><path d="${d}" stroke="${color}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
    };

    const buildRows = () => Array.from({ length: 12 }, (_, i) => {
        const pr = pRows[i] || {}, br = bRows[i] || {};
        const p  = Number(pr.realisasi || 0), b = Number(br.realisasi || 0), net = p - b;
        const isNow    = (i + 1) === bulanSkrg;
        const isFuture = p === 0 && b === 0 && (i + 1) > bulanSkrg;
        const hasData  = p > 0 || b > 0;
        const label    = pr.label || BULAN_SHORT[i];
        const netCls   = !hasData ? 'net-empty' : net >= 0 ? 'net-pos' : 'net-neg';
        const netTxt   = !hasData ? '—' : (net >= 0 ? '+' : '-') + fmt(Math.abs(net), false).replace(/^[+-]/, '');
        return `<div class="rekap-row${isNow ? ' is-now' : ''}${isFuture ? ' is-future' : ''}">
            <div class="rekap-bulan${isNow ? ' bold' : ''}">${label}${isNow ? '◀' : ''}</div>
            <div class="rekap-bar-cell">
                <div class="rekap-bar-track"><div class="rekap-bar-fill" style="width:${p > 0 ? (p / maxP * 100).toFixed(1) : 0}%;background:#2DD4BF"></div></div>
                <div class="rekap-bar-track"><div class="rekap-bar-fill" style="width:${b > 0 ? (b / maxB * 100).toFixed(1) : 0}%;background:#FBBF24"></div></div>
            </div>
            <div class="rekap-amt" style="color:#2DD4BF">${p > 0 ? fmt(p) : '—'}</div>
            <div class="rekap-amt" style="color:#FBBF24">${b > 0 ? fmt(b) : '—'}</div>
            <div style="text-align:right"><span class="net-pill ${netCls}">${netTxt}</span></div>
            <div class="rekap-trend">${hasData ? trendIcon(p, i > 0 ? Number(pRows[i - 1]?.realisasi || 0) : 0) : ''}</div>
        </div>`;
    }).join('');

    const ROW_HEIGHT  = 30;
    const containerH  = container.clientHeight || 200;
    const needsScroll = 12 > Math.floor(containerH / ROW_HEIGHT);

    if (needsScroll) {
        const dur = Math.max(10, 12 * 2.5);
        container.innerHTML = `<div class="rekap-scroll-track looping" style="--rekap-duration:${dur}s;--rekap-offset:-50%">${buildRows()}${buildRows()}</div>`;
    } else {
        container.innerHTML = `<div class="rekap-scroll-track" style="height:100%;display:flex;flex-direction:column;justify-content:space-between">${buildRows()}</div>`;
    }
}

/* ── amCharts Trend ───────────────────────────────────────── */
function renderTrendAmCharts(labels, pendapatan, belanja) {
    if (amRoot) { amRoot.dispose(); amRoot = null; }

    const am5    = window.am5;
    const am5xy  = window.am5xy;
    const data   = labels.map((label, i) => ({
        label,
        pendapatan: Number(pendapatan[i] || 0),
        belanja:    Number(belanja[i]    || 0),
        rawP:       Number(pendapatan[i] || 0),
        rawB:       Number(belanja[i]    || 0),
    }));

    const root = am5.Root.new('chartTrendAm');
    amRoot = root;
    root.setThemes([window.am5themes_Animated.new(root)]);
    root._logo?.dispose();

    const chart = root.container.children.push(am5xy.XYChart.new(root, {
        panX: false, panY: false, wheelX: 'none', wheelY: 'none',
        layout: root.verticalLayout,
        paddingTop: 4, paddingRight: 10, paddingBottom: 0, paddingLeft: 0,
    }));
    chart.plotContainer.set('background', am5.Rectangle.new(root, { fill: am5.color(0x000000), fillOpacity: 0 }));

    const xRen = am5xy.AxisRendererX.new(root, { minGridDistance: 28, cellStartLocation: 0.1, cellEndLocation: 0.9 });
    xRen.grid.template.set('visible', false);
    xRen.labels.template.setAll({ fill: am5.color(0x7D8590), fontSize: 10, fontFamily: 'DM Sans' });
    const xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, { categoryField: 'label', renderer: xRen }));
    xAxis.data.setAll(data);

    const yRen = am5xy.AxisRendererY.new(root, { inside: false });
    yRen.grid.template.setAll({ stroke: am5.color(0xffffff), strokeOpacity: 0.05, strokeDasharray: [2, 3] });
    yRen.labels.template.setAll({ fill: am5.color(0x7D8590), fontSize: 9, fontFamily: 'DM Mono, monospace' });
    const yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
        renderer: yRen, min: 0, strictMinMax: true, maxDeviation: 0.05, extraMax: 0.18,
        numberFormatter: am5.NumberFormatter.new(root, {
            numberFormat: '#a',
            bigNumberPrefixes:  [{ number: 1e3, suffix: ' Rb' }, { number: 1e6, suffix: ' Jt' }, { number: 1e9, suffix: ' M' }, { number: 1e12, suffix: ' T' }],
            smallNumberPrefixes: [],
        }),
    }));

    const mkTooltip = () => {
        const tp = am5.Tooltip.new(root, { getFillFromSprite: false, autoTextColor: false, pointerOrientation: 'vertical' });
        tp.get('background').setAll({ fill: am5.color(0x1E2430), stroke: am5.color(0xffffff), strokeOpacity: 0.10, cornerRadiusBL: 6, cornerRadiusBR: 6, cornerRadiusTL: 6, cornerRadiusTR: 6 });
        tp.label.setAll({ fill: am5.color(0xE6EDF3), fontSize: 11, fontFamily: 'DM Sans' });
        return tp;
    };

    // Series Pendapatan
    const sP = chart.series.push(am5xy.ColumnSeries.new(root, {
        xAxis, yAxis, valueYField: 'pendapatan', categoryXField: 'label',
        clustered: false, tooltip: mkTooltip(),
    }));
    sP.columns.template.setAll({
        width: am5.percent(80),
        cornerRadiusTL: 5, cornerRadiusTR: 5, cornerRadiusBL: 0, cornerRadiusBR: 0,
        fillGradient: am5.LinearGradient.new(root, { stops: [{ color: am5.color(0x2DD4BF), opacity: 0.92 }, { color: am5.color(0x0D9488), opacity: 0.58 }], rotation: 90 }),
        strokeOpacity: 0,
    });
    sP.columns.template.adapters.add('tooltipText', (_, t) => {
        const d = t.dataItem?.dataContext;
        return d ? `[bold #E6EDF3]${d.label}[/]\n[#7D8590]Pendapatan:[/] [#2DD4BF]${fmt(d.pendapatan)}[/]` : _;
    });
    sP.data.setAll(data);
    sP.bullets.push(() => {
        const l = am5.Label.new(root, { fill: am5.color(0xffffff), fontSize: 9, fontFamily: 'DM Mono, monospace', centerX: am5.percent(50), centerY: am5.percent(50), populateText: true, text: '' });
        l.adapters.add('text', (_, t) => { const d = t.dataItem?.dataContext; return d?.rawP ? fmtAxis(d.rawP) : ''; });
        return am5.Bullet.new(root, { locationY: 0.5, sprite: l });
    });

    // Series Belanja
    const sB = chart.series.push(am5xy.ColumnSeries.new(root, {
        xAxis, yAxis, valueYField: 'belanja', categoryXField: 'label',
        clustered: false, tooltip: mkTooltip(),
    }));
    sB.columns.template.setAll({
        width: am5.percent(42),
        cornerRadiusTL: 4, cornerRadiusTR: 4, cornerRadiusBL: 0, cornerRadiusBR: 0,
        fillGradient: am5.LinearGradient.new(root, { stops: [{ color: am5.color(0xFBBF24), opacity: 0.92 }, { color: am5.color(0xB45309), opacity: 0.58 }], rotation: 90 }),
        strokeOpacity: 0,
    });
    sB.columns.template.adapters.add('tooltipText', (_, t) => {
        const d = t.dataItem?.dataContext;
        return d ? `[bold #E6EDF3]${d.label}[/]\n[#7D8590]Belanja :[/] [#FBBF24]${fmt(d.belanja)}[/]\n[#7D8590]Net     :[/] [#34D399]${fmt(d.pendapatan - d.belanja)}[/]` : _;
    });
    sB.data.setAll(data);
    sB.bullets.push(() => {
        const l = am5.Label.new(root, { fill: am5.color(0xFBBF24), fontSize: 9, fontFamily: 'DM Mono, monospace', centerX: am5.percent(50), centerY: am5.percent(100), dy: -4, populateText: true, text: '' });
        l.adapters.add('text', (_, t) => { const d = t.dataItem?.dataContext; return d?.rawB ? fmtAxis(d.rawB) : ''; });
        return am5.Bullet.new(root, { locationY: 1, sprite: l });
    });

    chart.set('cursor', am5xy.XYCursor.new(root, { behavior: 'none', xAxis }));
    chart.get('cursor').lineY.set('visible', false);
    chart.get('cursor').lineX.setAll({ stroke: am5.color(0xffffff), strokeOpacity: 0.15, strokeDasharray: [3, 4] });

    sB.appear(1000, 100);
    sP.appear(1000, 200);
    chart.appear(1000, 100);
}

/* ── Chart Harian ─────────────────────────────────────────── */
function renderHarian(json) {
    setText('harianBulanLabel', BULAN_NAMA[(json.bulan || 1) - 1]);
    setText('harianTahunLabel', json.tahun);
    hide('emptyHarian');

    const ctx   = $('chartHarian').getContext('2d');
    if (chartHarian) chartHarian.destroy();

    const dataP  = json.hari.map(h => h.pendapatan || 0);
    const dataB  = json.hari.map(h => h.belanja    || 0);
    const validB = dataB.filter(v => v > 0), sortedB = [...validB].sort((a, b) => a - b);
    const p90B   = sortedB[Math.floor(sortedB.length * 0.9)] || Math.max(...validB, 1);

    const datalabelPlugin = {
        id: 'customLabels',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            const occupied = [];
            const isTooClose = (x, y) => occupied.some(p => Math.abs(p.x - x) < 38 && Math.abs(p.y - y) < 14);
            chart.data.datasets.forEach((ds, di) => {
                const meta  = chart.getDatasetMeta(di);
                const isBar = ds.type === 'bar';
                ds.data
                    .map((val, i) => ({ val, i, el: meta.data[i] }))
                    .filter(d => d.val > 0)
                    .sort((a, b) => b.val - a.val)
                    .forEach(({ val, i, el }) => {
                        if (!el) return;
                        const abs   = Math.abs(val);
                        const label = abs >= 1e9 ? (val / 1e9).toFixed(1).replace('.', ',') + ' M'
                                    : abs >= 1e6 ? (val / 1e6).toFixed(1).replace('.', ',') + ' Jt'
                                    : abs >= 1e3 ? (val / 1e3).toFixed(0) + ' Rb'
                                    : String(val);
                        const cp = el.getCenterPoint ? el.getCenterPoint() : el;
                        const x  = cp.x;
                        let y    = isBar ? el.y - 5 : cp.y - 8;
                        if (isTooClose(x, y)) y = isBar ? el.y - 5 : cp.y + 14;
                        if (isTooClose(x, y)) return;
                        occupied.push({ x, y });
                        ctx.save();
                        ctx.font          = '600 8px DM Mono, monospace';
                        ctx.textAlign     = 'center';
                        ctx.textBaseline  = 'bottom';
                        const tw = ctx.measureText(label).width;
                        ctx.fillStyle = 'rgba(15,20,30,0.55)';
                        ctx.fillRect(x - tw / 2 - 2, y - 10, tw + 4, 11);
                        ctx.fillStyle = isBar ? '#FBBF24' : '#34D399';
                        ctx.fillText(label, x, y);
                        ctx.restore();
                    });
            });
        },
    };

    chartHarian = new Chart(ctx, {
        plugins: [datalabelPlugin],
        data: {
            labels: json.hari.map(h => h.label),
            datasets: [
                {
                    type: 'bar', label: 'Belanja', data: dataB,
                    backgroundColor: dataB.map(v => v > 0 ? 'rgba(251,191,36,0.75)' : 'transparent'),
                    borderColor:     dataB.map(v => v > 0 ? '#FBBF24' : 'transparent'),
                    borderWidth: 1, borderRadius: 3, borderSkipped: false,
                    yAxisID: 'y1', order: 2,
                },
                {
                    type: 'line', label: 'Pendapatan', data: dataP,
                    borderColor: '#34D399', backgroundColor: 'rgba(52,211,153,0.08)',
                    borderWidth: 2.5,
                    pointRadius:          dataP.map(v => v > 0 ? 4 : 2),
                    pointHoverRadius:     6,
                    pointBackgroundColor: dataP.map(v => v > 0 ? '#34D399' : 'rgba(52,211,153,0.3)'),
                    tension: 0.35, fill: true, yAxisID: 'y', order: 1,
                },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false, animation: { duration: 500 },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...TT,
                    callbacks: {
                        title:     items => `Hari ke-${items[0].label}`,
                        label:     c => { const v = c.parsed.y; if (v === 0 && c.dataset.type === 'bar') return null; return ` ${c.dataset.label}: ${fmt(v)}`; },
                        afterBody: items => { const i = items[0].dataIndex, p = dataP[i] || 0, b = dataB[i] || 0, net = p - b; return p === 0 && b === 0 ? [] : [``, ` Net: ${net >= 0 ? '+' : ''}${fmt(net)}`]; },
                    },
                },
            },
            scales: {
                x:  { ticks: { font: { size: 9 }, color: '#7D8590' }, grid: { display: false }, border: { display: false } },
                y:  { type: 'logarithmic', position: 'left', ticks: { font: { size: 9, family: 'DM Mono' }, color: '#34D399', callback: v => fmtAxis(v), maxTicksLimit: 5 }, grid: { color: 'rgba(255,255,255,0.05)' }, border: { display: false } },
                y1: { type: 'linear', position: 'right', min: validB.length ? Math.min(...validB) * 0.80 : 0, max: p90B * 1.35, ticks: { font: { size: 9, family: 'DM Mono' }, color: '#FBBF24', callback: v => fmtAxis(v), maxTicksLimit: 5 }, grid: { drawOnChartArea: false }, border: { display: false } },
            },
        },
    });
}

/* ── Unit Table ───────────────────────────────────────────── */
function renderUnit(units, bulan, totalBulan) {
    setText('unitBulanLabel', BULAN_NAMA[(bulan || 1) - 1]);

    const wrap = $('unitTableWrap'), body = $('unitTableBody');
    if (!units?.length) { if (wrap) wrap.style.display = 'none'; show('emptyUnit'); return; }
    hide('emptyUnit');
    if (wrap) wrap.style.display = 'flex';

    const total   = totalBulan || units.reduce((a, u) => a + Number(u.realisasi || 0), 0);
    setText('unitTotalBulan', fmt(total));

    const PALETTE = ['#FBBF24','#60A5FA','#F87171','#A78BFA','#22D3EE','#FB923C','#34D399','#E879F9','#F472B6','#94A3B8'];

    const buildRows = () => units.map((u, i) => {
        const rank  = i + 1;
        const real  = Number(u.realisasi      || 0);
        const pctV  = Number(u.pct_dari_total || 0);
        const color = PALETTE[i % PALETTE.length];
        return `<div class="unit-row${rank === 1 ? ' rank-1' : ''}">
            <div class="unit-rank${rank === 1 ? ' top' : ''}">${rank}</div>
            <div class="unit-name" title="${u.unit}">${u.unit || '—'}</div>
            <div class="unit-realisasi">${fmt(real)}</div>
            <div class="unit-pct-cell">
                <div class="unit-pct-bar-wrap"><div class="unit-pct-bar-fill" style="width:${pctV}%;background:${color}"></div></div>
                <div class="unit-pct-label" style="color:${color}">${n2(pctV)}%</div>
            </div>
        </div>`;
    }).join('');

    const ROW_HEIGHT  = 34;
    const containerH  = body.clientHeight || 200;
    const needsScroll = units.length > Math.floor(containerH / ROW_HEIGHT);

    if (needsScroll) {
        const dur      = Math.max(8, units.length * 2);
        const totalPx  = units.length * ROW_HEIGHT;
        body.innerHTML = `<div class="unit-scroll-track looping" style="--unit-duration:${dur}s;--unit-offset:-${totalPx}px">${buildRows()}${buildRows()}</div>`;
    } else {
        body.innerHTML = `<div class="unit-scroll-track" style="height:100%;justify-content:space-between;display:flex;flex-direction:column">${buildRows()}</div>`;
    }
}

/* ── Fetch & Render ───────────────────────────────────────── */
const FETCH_OPTS = {
    cache: 'no-store',
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
};

async function loadData(silent = false) {
    const year  = $('tahunSelect').value;
    const month = $('bulanSelect').value;

    if (!silent) setText('trendYearLabel', year);

    try {
        const [rT, rH, rU] = await Promise.all([
            fetch(`/api/dashboard-trend?tahun=${year}&bulan=${month}`,  FETCH_OPTS),
            fetch(`/api/dashboard-harian?tahun=${year}&bulan=${month}`, FETCH_OPTS),
            fetch(`/api/dashboard-unit?tahun=${year}&bulan=${month}`,   FETCH_OPTS),
        ]);
        const [jT, jH, jU] = await Promise.all([
            rT.ok ? rT.json() : null,
            rH.ok ? rH.json() : null,
            rU.ok ? rU.json() : null,
        ]);

        if (jT) {
            const pRows = Array.isArray(jT.pendapatan) ? jT.pendapatan : [];
            const bRows = Array.isArray(jT.belanja)    ? jT.belanja    : [];

            const totalPReal = pRows.reduce((a, r) => a + Number(r.realisasi || 0), 0);
            const totalBReal = bRows.reduce((a, r) => a + Number(r.realisasi || 0), 0);
            const totalPTgt  = pRows.reduce((a, r) => a + Number(r.target    || 0), 0);
            const totalBTgt  = bRows.reduce((a, r) => a + Number(r.target    || 0), 0);
            const avg        = (pct(totalPReal, totalPTgt) + pct(totalBReal, totalBTgt)) / 2;

            setText('kpiPendapatan', fmt(totalPReal));
            setText('kpiBelanja',    fmt(totalBReal));
            setText('kpiSurplus',    fmt(totalPReal - totalBReal));
            setText('kpiAvg',        `Rata-rata kinerja ${n2(avg)}%`);

            renderKpiMargin(pRows, bRows, jT.mom);

            if (jT.mom) {
                momDelta(jT.mom.pendapatan_bulan_ini, jT.mom.pendapatan_bulan_lalu, 'kpiPendapatanMom', parseInt(year));
                momDelta(jT.mom.belanja_bulan_ini,    jT.mom.belanja_bulan_lalu,    'kpiBelanjaMom',    parseInt(year));
            }

            const labels = pRows.map((r, i) => r.label || BULAN_SHORT[i] || `Bln ${i + 1}`);
            const rawP   = pRows.map(r => Number(r.realisasi || 0));
            const rawB   = bRows.map(r => Number(r.realisasi || 0));

            // Update amCharts secara silent tanpa rebuild
            if (silent && amRoot) {
                const newData = labels.map((label, i) => ({ label, pendapatan: rawP[i], belanja: rawB[i], rawP: rawP[i], rawB: rawB[i] }));
                const ch      = amRoot.container.children.getIndex(0);
                if (ch) {
                    ch.series.each(s => s.data.setAll(newData));
                    ch.xAxes.getIndex(0)?.data.setAll(newData);
                }
            } else {
                renderTrendAmCharts(labels, rawP, rawB);
            }

            renderRekap(pRows, bRows, year);
        }

        if (jH && Array.isArray(jH.hari) && jH.hari.length > 0) {
            renderHarian(jH);
        } else if (!silent) {
            if (chartHarian) { chartHarian.destroy(); chartHarian = null; }
            setText('harianBulanLabel', BULAN_NAMA[(parseInt(month) || 1) - 1]);
            show('emptyHarian');
        }

        if (jU) renderUnit(jU.units || [], parseInt(month), jU.total_bulan || 0);

    } catch (err) {
        if (err.name !== 'AbortError') console.error('[Dashboard Keuangan]', err.message);
    }
}

/* ── Init ─────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    $('tahunSelect').addEventListener('change', () => loadData());
    $('bulanSelect').addEventListener('change', () => loadData());
    loadData();
    setInterval(() => loadData(true), 300_000);
});