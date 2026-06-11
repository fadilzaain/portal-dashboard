import Chart from 'chart.js/auto';

(function loadJsPDF() {
  if (window.jspdf) return;
  const s = document.createElement('script');
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
  document.head.appendChild(s);
})();

// ── Ambil data inject dari blade ──────────────────────
const {
  trendData,
  borData,
  avlosData,
  rajalData,
  triageData,
} = window.PP_DATA ?? {};

// ── Global Chart defaults ─────────────────────────────────────
Chart.defaults.font.family = "'DM Sans', system-ui, sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#7d8590';

// ── Shared config ─────────────────────────────────────────────
const TOOLTIP = {
  backgroundColor: '#1c2330',
  borderColor:     'rgba(48,54,61,0.8)',
  borderWidth:     1,
  padding:         10,
  titleColor:      '#e6edf3',
  bodyColor:       '#7d8590',
};

const GRID = { color: 'rgba(48,54,61,0.5)' };

const DATASET_DEFAULTS = {
  borderWidth:      2,
  fill:             true,
  tension:          0.4,
  pointRadius:      0,
  pointHoverRadius: 4,
};

// ── Helper: chart kosong jika tidak ada data ──────────────────
function emptyChart(canvasId) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#7d8590';
  ctx.font      = '12px DM Sans, sans-serif';
  ctx.textAlign = 'center';
  ctx.fillText('Belum ada data', canvas.width / 2, canvas.height / 2);
}

// ── Tren Kunjungan Harian ──────────────────────────────────
(function initTrendHarian() {
  const canvas = document.getElementById('chartTrendHarian');
  if (!canvas) return;

  if (!trendData?.length) { emptyChart('chartTrendHarian'); return; }

  new Chart(canvas, {
    type: 'line',
    data: {
      labels: trendData.map(d => d.tanggal),
      datasets: [
        { label: 'Ranap', data: trendData.map(d => d.ranap), borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.08)' },
        { label: 'Rajal', data: trendData.map(d => d.rajal), borderColor: '#06b6d4', backgroundColor: 'rgba(6,182,212,0.08)' },
        { label: 'IGD',   data: trendData.map(d => d.igd),   borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.06)' },
      ].map(ds => ({ ...DATASET_DEFAULTS, ...ds })),
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: true, position: 'top', labels: { boxWidth: 8, padding: 16, usePointStyle: true } },
        tooltip: TOOLTIP,
      },
      scales: {
        x: { grid: GRID, ticks: { maxTicksLimit: 8 } },
        y: { grid: GRID, beginAtZero: true },
      },
    },
  });
})();

// ── BOR Bulanan ────────────────────────────────────────────
(function initBOR() {
  const canvas = document.getElementById('chartBOR');
  if (!canvas) return;

  if (!borData?.length) { emptyChart('chartBOR'); return; }

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels: borData.map(d => d.bulan),
      datasets: [
        {
          label: 'BOR (%)',
          data: borData.map(d => d.bor),
          borderRadius: 5,
          borderSkipped: false,
          backgroundColor: borData.map(d =>
            d.bor === 0                ? 'rgba(48,54,61,0.35)'       :
            d.bor >= 60 && d.bor <= 85 ? 'rgba(167,139,250,0.85)'   :
            d.bor < 60                 ? 'rgba(245,158,11,0.75)'     :
                                         'rgba(239,68,68,0.75)'
          ),
        },
        {
          label: 'Min 60%', type: 'line', data: borData.map(() => 60),
          borderColor: 'rgba(34,197,94,0.45)', borderDash: [5, 4],
          borderWidth: 1.5, pointRadius: 0, fill: false,
        },
        {
          label: 'Max 85%', type: 'line', data: borData.map(() => 85),
          borderColor: 'rgba(239,68,68,0.45)', borderDash: [5, 4],
          borderWidth: 1.5, pointRadius: 0, fill: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          ...TOOLTIP,
          callbacks: { label: ctx => ctx.raw === 0 ? 'Belum ada data' : `BOR: ${ctx.raw}%` },
        },
      },
      scales: {
        x: { grid: { display: false } },
        y: { grid: GRID, min: 0, max: 100, ticks: { callback: v => v + '%' } },
      },
    },
  });
})();

// ── Kunjungan per Poli ─────────────────────────────────────
(function initRajal() {
  const canvas = document.getElementById('chartRajal');
  if (!canvas) return;

  if (!rajalData?.length) { emptyChart('chartRajal'); return; }

  const sorted = [...rajalData]
    .sort((a, b) => b.total_kunjungan - a.total_kunjungan)
    .slice(0, 8);

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels: sorted.map(d => d.nama_poli),
      datasets: [{
        label: 'Kunjungan',
        data: sorted.map(d => d.total_kunjungan),
        backgroundColor: 'rgba(6,182,212,0.75)',
        borderRadius: 4,
      }],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: TOOLTIP },
      scales: {
        x: { grid: GRID, ticks: { maxTicksLimit: 5 } },
        y: { grid: { display: false } },
      },
    },
  });
})();

// ── IGD per Triage ─────────────────────────────────────────
(function initTriage() {
  const canvas = document.getElementById('chartTriage');
  if (!canvas) return;

  if (!triageData?.length) { emptyChart('chartTriage'); return; }

  const total = triageData.reduce((s, d) => s + d.jumlah, 0);
  const el    = document.getElementById('triage-total');
  if (el) el.textContent = total.toLocaleString('id-ID');

  new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: triageData.map(d => d.kategori_triage || 'Tidak Diketahui'),
      datasets: [{
        data: triageData.map(d => d.jumlah),
        backgroundColor: ['#ef4444', '#f59e0b', '#22c55e', '#3b82f6', '#a78bfa'],
        borderColor:     '#161b22',
        borderWidth:     3,
        hoverOffset:     6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '62%',
      plugins: {
        legend: {
          position: 'right',
          labels: {
            boxWidth: 10,
            padding:  10,
            font:     { size: 11 },
            generateLabels(chart) {
              return chart.data.labels.map((label, i) => {
                const val = chart.data.datasets[0].data[i];
                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : '0';
                return {
                  text:        `${label}  ${val} (${pct}%)`,
                  fillStyle:   chart.data.datasets[0].backgroundColor[i],
                  strokeStyle: '#161b22',
                  lineWidth:   2,
                  index:       i,
                };
              });
            },
          },
        },
        tooltip: TOOLTIP,
      },
    },
  });
})();

// ── Barber-Johnson ─────────────────────────────────────────
(function initBarberJohnson() {
  let bjChart = null;

  function isEfisien(d) {
    return d.avlos >= 3 && d.avlos <= 12 &&
           d.toi   >= 1 && d.toi   <= 3  &&
           d.bor   >= 60 && d.bor  <= 85;
  }

  function renderBJ(idx) {
    if (bjChart) { bjChart.destroy(); bjChart = null; }

    const d = avlosData?.[idx];
    if (!d) return;

    const ef = isEfisien(d);
    const fx = +d.toi.toFixed(2);    // koordinat X = TOI
    const fy = +d.avlos.toFixed(2);  // koordinat Y = AVLOS

    // Garis BTO: AVLOS + TOI = C_BTO
    const C_BTO = +(d.periode / (d.bto || 1)).toFixed(2);

    // ─────────────────────────────────────────────────────────
    // KPI cards
    // ─────────────────────────────────────────────────────────
    [
      {
        id:    'BOR',
        val:   d.bor,
        unit:  '%',
        coord: `Titik potong: (${fx}, ${fy})`,
      },
      {
        id:    'BTO',
        val:   d.bto,
        unit:  '',
        coord: `AVLOS + TOI = ${C_BTO}`,
      },
      {
        id:    'AVLOS',
        val:   d.avlos,
        unit:  ' hr',
        coord: `Y = ${fy}`,
      },
      {
        id:    'TOI',
        val:   d.toi,
        unit:  ' hr',
        coord: `X = ${fx}`,
      },
    ].forEach(({ id, val, unit, coord }) => {
      const vEl = document.getElementById('bjKpi'   + id);
      const cEl = document.getElementById('bjCoord' + id);
      if (vEl) vEl.innerHTML = (val || '—') + `<span class="bj-kpi-unit">${unit}</span>`;
      if (cEl) cEl.textContent = coord;
    });

    // Status badge
    const sb = document.getElementById('bjStatusBadge');
    if (sb) {
      sb.innerHTML = d.bor > 0
        ? `<span class="bj-status-${ef ? 'ok' : 'warn'}">${ef ? '✓ Dalam zona efisien' : '⚠ Di luar zona efisien'}</span>`
        : '<span style="font-size:11px;color:#7d8590">Belum ada data bulan ini</span>';
    }

    // Legend
    const legendItems = [
      ['#06b6d4', 'dash', `BOR ${d.bor}% (aktual)`],
      ['#e86868', 'dash', 'BOR 75%'],
      ['#2563eb', 'dash', `Garis BTO (AVLOS+TOI=${C_BTO})`],
      ['#22c55e', 'dash', 'AVLOS & TOI'],
      [ef ? '#22c55e' : '#2563eb', 'dot', `Titik focal (TOI=${fx}, AVLOS=${fy})`],
      ['rgba(34,197,94,0.15)', 'box', 'Daerah efisien'],
    ];

    const lb = document.getElementById('bjLegendBar');
    if (lb) {
      lb.innerHTML = legendItems.map(([c, t, l]) => `<span>
        ${t === 'dot'
          ? `<span style="width:9px;height:9px;border-radius:50%;background:${c};display:inline-block"></span>`
          : t === 'box'
          ? `<span style="width:14px;height:9px;border-radius:2px;background:${c};border:1px solid rgba(34,197,94,0.4);display:inline-block"></span>`
          : `<span style="width:20px;height:0;display:inline-block;border-top:2px dashed ${c}"></span>`
        }${l}</span>`).join('');
    }

    // ── daerah efisien ──
    const zonePlugin = {
      id: 'bjZone',
      beforeDatasetsDraw({ ctx, scales: { x, y } }) {
        const x1 = x.getPixelForValue(1);
        const x2 = x.getPixelForValue(3);
        const yA = y.getPixelForValue(12);
        const yB = y.getPixelForValue(9);
        const yC = y.getPixelForValue(3);

        ctx.save();
        ctx.fillStyle   = 'rgba(34,197,94,0.07)';
        ctx.strokeStyle = 'rgba(34,197,94,0.4)';
        ctx.lineWidth   = 1.2;
        ctx.setLineDash([5, 4]);

        ctx.beginPath();
        ctx.moveTo(x1, yC);
        ctx.lineTo(x1, yA);
        ctx.lineTo(x2, yA);
        ctx.lineTo(x2, yB);
        ctx.closePath();

        ctx.fill();
        ctx.stroke();
        ctx.setLineDash([]);

        ctx.font      = 'bold 10px DM Sans,sans-serif';
        ctx.fillStyle = 'rgba(34,197,94,0.85)';
        ctx.fillText('Daerah Efisien', x1 + 6, yA + 14);
        ctx.restore();
      },
    };

    // ── Plugin: garis BOR, BTO, AVLOS, TOI ──
    const linesPlugin = {
      id: 'bjLines',
      afterDatasetsDraw({ ctx, scales: { x, y } }) {
        if (!d.bor) return;

        // ── Garis BOR ──
        // y = slope × x, slope = BOR / (100 - BOR)
        // Garis dari origin (0,0) — titik potong dengan y=AVLOS ada di x=TOI
        const borLines = [
          { bor: 75,    color: '#e86868', label: 'BOR 75%',        dash: [4, 3] }, //garis bantu
          { bor: d.bor, color: '#06b6d4', label: `BOR ${d.bor}%`, dash: [7, 3] }, //garis aktual
        ];

        borLines.forEach(({ bor, color, label, dash }) => {
          const slope = bor / (100 - bor);
          ctx.save();
          ctx.strokeStyle = color; ctx.lineWidth = 1.6; ctx.setLineDash(dash);
          ctx.beginPath();
          ctx.moveTo(x.getPixelForValue(0), y.getPixelForValue(0));
          for (let xi = 0.01; xi <= 8.5; xi += 0.04) {
            const yi = slope * xi;
            if (yi > 14) break;
            ctx.lineTo(x.getPixelForValue(xi), y.getPixelForValue(yi));
          }
          ctx.stroke();
          const lx = 4, ly = slope * 4;
          if (ly > 0.3 && ly < 13.5) {
            ctx.setLineDash([]);
            ctx.font = 'bold 10px DM Sans,sans-serif';
            ctx.fillStyle = color; ctx.textAlign = 'left';
            ctx.fillText(label, x.getPixelForValue(lx) + 4, y.getPixelForValue(ly) - 5);
          }
          ctx.restore();
        });

        // ── Garis BTO: AVLOS + TOI = C_BTO ──
        const bto_y0 = Math.min(C_BTO, 14);
        const bto_x0 = C_BTO - bto_y0;
        const bto_x1 = Math.min(C_BTO, 8);
        const bto_y1 = Math.max(C_BTO - bto_x1, 0);

        ctx.save();
        ctx.strokeStyle = '#2563eb'; ctx.lineWidth = 1.8; ctx.setLineDash([6, 3]);
        ctx.beginPath();
        ctx.moveTo(x.getPixelForValue(bto_x0), y.getPixelForValue(bto_y0));
        ctx.lineTo(x.getPixelForValue(bto_x1), y.getPixelForValue(bto_y1));
        ctx.stroke();
        const midX = (bto_x0 + bto_x1) / 2;
        const midY = (bto_y0 + bto_y1) / 2;
        ctx.setLineDash([]);
        ctx.font = 'bold 10px DM Sans,sans-serif';
        ctx.fillStyle = '#2563eb'; ctx.textAlign = 'left';
        ctx.fillText(`BTO = ${d.bto}`, x.getPixelForValue(midX) + 5, y.getPixelForValue(midY) - 5);
        ctx.restore();

        // ── Garis AVLOS (horizontal) ──
        // Dari sumbu Y (x=0) ke titik focal
        // Ini adalah garis pembaca: temukan AVLOS di sumbu Y, tarik ke kanan
        // sampai memotong garis BOR → itulah titik kinerja RS
        ctx.save();
        ctx.strokeStyle = '#22c55e'; ctx.lineWidth = 1.4; ctx.setLineDash([3, 3]);
        ctx.beginPath();
        ctx.moveTo(x.getPixelForValue(0),  y.getPixelForValue(fy));
        ctx.lineTo(x.getPixelForValue(fx), y.getPixelForValue(fy));
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.font = 'bold 10px DM Sans,sans-serif';
        ctx.fillStyle = '#22c55e'; ctx.textAlign = 'right';
        ctx.fillText(`AVLOS = ${fy}`, x.getPixelForValue(0) - 4, y.getPixelForValue(fy) + 4);
        ctx.restore();

        // ── Garis TOI (vertikal) ──
        // Dari titik focal turun ke sumbu X 
        ctx.save();
        ctx.strokeStyle = '#22c55e'; ctx.lineWidth = 1.4; ctx.setLineDash([3, 3]);
        ctx.beginPath();
        ctx.moveTo(x.getPixelForValue(fx), y.getPixelForValue(0));
        ctx.lineTo(x.getPixelForValue(fx), y.getPixelForValue(fy));
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.font = 'bold 10px DM Sans,sans-serif';
        ctx.fillStyle = '#22c55e'; ctx.textAlign = 'center';
        ctx.fillText(`TOI = ${fx}`, x.getPixelForValue(fx), y.getPixelForValue(0) + 14);
        ctx.restore();
      },
    };

    // ── Plugin: titik focal ──
    const focalPlugin = {
      id: 'bjFocal',
      afterDraw({ ctx, scales: { x, y } }) {
        if (!d.bor) return;
        const px = x.getPixelForValue(fx);
        const py = y.getPixelForValue(fy);

        ctx.save();
        ctx.fillStyle   = ef ? '#16a34a' : '#1d4ed8';
        ctx.strokeStyle = '#fff';
        ctx.lineWidth   = 2;
        ctx.beginPath(); ctx.arc(px, py, 8, 0, Math.PI * 2);
        ctx.fill(); ctx.stroke();

        const label = `(${fx}, ${fy})`;
        ctx.font = 'bold 11px Arial,sans-serif';
        const tw = ctx.measureText(label).width;
        const bx = px - tw / 2 - 6, by = py - 36, bw = tw + 12, bh = 18, r = 4;

        ctx.beginPath();
        ctx.moveTo(bx + r, by); ctx.lineTo(bx + bw - r, by);
        ctx.quadraticCurveTo(bx + bw, by, bx + bw, by + r);
        ctx.lineTo(bx + bw, by + bh - r); ctx.quadraticCurveTo(bx + bw, by + bh, bx + bw - r, by + bh);
        ctx.lineTo(bx + r, by + bh); ctx.quadraticCurveTo(bx, by + bh, bx, by + bh - r);
        ctx.lineTo(bx, by + r); ctx.quadraticCurveTo(bx, by, bx + r, by);
        ctx.closePath();
        ctx.fillStyle   = ef ? '#16a34a' : '#1d4ed8';
        ctx.strokeStyle = ef ? '#bbf7d0' : '#bfdbfe';
        ctx.lineWidth   = 1;
        ctx.fill(); ctx.stroke();

        ctx.fillStyle    = '#fff';
        ctx.textAlign    = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(label, px, by + bh / 2);
        ctx.restore();
      },
    };

    bjChart = new Chart(document.getElementById('chartBJ'), {
      type: 'scatter',
      data: { datasets: [{ data: [], label: '' }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { top: 20, right: 20, left: 45, bottom: 10 } },
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: {
          x: {
            title: { display: true, text: 'TOI — Turn Over Interval (hari)', font: { size: 11 }, color: '#7d8590' },
            min: 0, max: 8, grid: GRID, ticks: { stepSize: 1 },
          },
          y: {
            title: { display: true, text: 'AVLOS — Average Length of Stay (hari)', font: { size: 11 }, color: '#7d8590' },
            min: 0, max: 14, grid: GRID, ticks: { stepSize: 1 },
          },
        },
      },
      plugins: [zonePlugin, linesPlugin, focalPlugin],
    });
  }

  const activeBulan = window.PP_DATA?.bulan ?? new Date().getMonth() + 1;
  const defaultIdx  = activeBulan - 1; //bulan 1=januari
  
  const sel = document.getElementById('bjBulanSelect');
  if (sel) {
    if (defaultIdx >= 0) sel.value = defaultIdx;
    sel.addEventListener('change', () => renderBJ(parseInt(sel.value)));
  }
  renderBJ(defaultIdx >= 0 ? defaultIdx : 0);

  // ── Download PDF ──────────────────────────────────────────
  document.getElementById('bjDownloadBtn')?.addEventListener('click', function () {
    const btn = this;
    btn.textContent = 'Menyiapkan...';
    btn.disabled    = true;

    // load PDF
    const tryExport = () => {
      if (!window.jspdf?.jsPDF) {
        setTimeout(tryExport, 200);
        return;
      }

      const { jsPDF } = window.jspdf;

      // Ambil data bulan yang dipilih
      const selEl     = document.getElementById('bjBulanSelect');
      const bulanIdx  = parseInt(selEl?.value ?? 0);
      const namaBulan = ['Januari','Februari','Maret','April','Mei','Juni',
                         'Juli','Agustus','September','Oktober','November','Desember'];
      const bulanLabel = namaBulan[bulanIdx] ?? '';
      const tahun      = window.PP_DATA?.tahun ?? new Date().getFullYear();

      const d    = avlosData?.[bulanIdx];
      const canvas = document.getElementById('chartBJ');

      // A4 landscape
      const pdf  = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
      const pw   = pdf.internal.pageSize.getWidth();   // 297
      const ph   = pdf.internal.pageSize.getHeight();  // 210

      // ── Header ──
      pdf.setFillColor(22, 27, 34); //pp-surface
      pdf.rect(0, 0, pw, 22, 'F');

      pdf.setTextColor(230, 237, 243);
      pdf.setFont('helvetica', 'bold');
      pdf.setFontSize(13);
      pdf.text('Grafik Barber-Johnson', 14, 10);

      pdf.setFont('helvetica', 'normal');
      pdf.setFontSize(9);
      pdf.setTextColor(125, 133, 144);
      pdf.text(`${bulanLabel} ${tahun}  ·  RS Portal`, 14, 17);

      // Tanggal cetak
      const tgl = new Date().toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
      pdf.text(`Dicetak: ${tgl}`, pw - 14, 17, { align: 'right' });

      // ── Grafik dari canvas ──
      const imgData  = canvas.toDataURL('image/png', 1.0);
      const chartW   = pw - 28;
      const chartH   = chartW * (canvas.height / canvas.width);
      const chartY   = 26;
      pdf.addImage(imgData, 'PNG', 14, chartY, chartW, Math.min(chartH, ph - chartY - 40));

      // ── Tabel KPI di bawah grafik ──
      if (d) {
        const tableY = Math.min(chartY + chartH + 6, ph - 36);

        const kpiCols = ['Indikator', 'Nilai', 'Satuan', 'Keterangan'];
        const kpiRows = [
          ['BOR (Bed Occupancy Rate)', d.bor ?? '—', '%',   d.bor >= 60 && d.bor <= 85 ? 'Ideal (60-85%)' : d.bor < 60 ? 'Di bawah standar' : 'Di atas standar'],
          ['AVLOS (Avg Length of Stay)', d.avlos ?? '—', 'hari', d.avlos >= 3 && d.avlos <= 12 ? 'Ideal (3-12 hr)' : 'Di luar standar'],
          ['TOI (Turn Over Interval)',   d.toi   ?? '—', 'hari', d.toi >= 1 && d.toi <= 3 ? 'Ideal (1-3 hr)' : 'Di luar standar'],
          ['BTO (Bed Turn Over)',        d.bto   ?? '—', 'kali', '—'],
        ];

        // Header tabel
        pdf.setFillColor(37, 99, 235);
        pdf.rect(14, tableY, pw - 28, 7, 'F');
        pdf.setTextColor(255, 255, 255);
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(8);
        const colW = [(pw - 28) * 0.38, (pw - 28) * 0.15, (pw - 28) * 0.12, (pw - 28) * 0.35];
        let cx = 14;
        kpiCols.forEach((col, i) => {
          pdf.text(col, cx + 3, tableY + 5);
          cx += colW[i];
        });

        // Baris tabel
        kpiRows.forEach((row, ri) => {
          const rowY = tableY + 7 + ri * 7;
          pdf.setFillColor(ri % 2 === 0 ? 28 : 22, ri % 2 === 0 ? 35 : 27, ri % 2 === 0 ? 48 : 34);
          pdf.rect(14, rowY, pw - 28, 7, 'F');
          pdf.setTextColor(230, 237, 243);
          pdf.setFont('helvetica', ri === 0 ? 'bold' : 'normal');
          pdf.setFontSize(8);
          let cx2 = 14;
          row.forEach((cell, ci) => {
            pdf.text(String(cell), cx2 + 3, rowY + 5);
            cx2 += colW[ci];
          });
        });
      }

      // ── Footer ──
      pdf.setFillColor(22, 27, 34);
      pdf.rect(0, ph - 8, pw, 8, 'F');
      pdf.setTextColor(125, 133, 144);
      pdf.setFontSize(7);
      pdf.setFont('helvetica', 'normal');
      pdf.text('Portal Pelayanan Pasien  ·  Data bersumber dari sistem informasi RS', pw / 2, ph - 3, { align: 'center' });

      // ── Save ──
      pdf.save(`Barber-Johnson_${bulanLabel}_${tahun}.pdf`);

      btn.innerHTML = `<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Download PDF`;
      btn.disabled = false;
    };

    tryExport();
  });
})();