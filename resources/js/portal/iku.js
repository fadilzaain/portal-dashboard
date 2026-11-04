// Grafik Dashboard IKU
// Data datang dari Blade lewat window.IKU_DATA (halaman utama) atau window.IKU_DETAIL (halaman detail).
// Semua warna dibaca dari CSS variable tema, jadi grafik otomatis ikut tema terang/gelap.

import Chart from 'chart.js/auto';

const root   = document.documentElement;
const charts = [];                                            // semua grafik yang aktif (dibuang & dibuat ulang saat tema berganti)
const css    = (nama) => getComputedStyle(root).getPropertyValue(nama).trim();
const angka  = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });

// Warna sesuai status capaian
const warnaStatus = (status) => ({
    tercapai:  css('--success'),
    perhatian: css('--warning'),
    belum:     css('--danger'),
}[status] ?? css('--text-low'));

// Memecah nama indikator yang panjang jadi beberapa baris supaya label sumbu tidak terpotong
function bungkus(teks, maks = 26) {
    const baris = [];
    let sekarang = '';
    for (const kata of teks.split(' ')) {
        if (sekarang && (sekarang + ' ' + kata).length > maks) {
            baris.push(sekarang);
            sekarang = kata;
        } else {
            sekarang = (sekarang + ' ' + kata).trim();
        }
    }
    if (sekarang) baris.push(sekarang);
    return baris;
}

// Plugin kecil: garis putus-putus penanda target (mis. 100%) di sumbu nilai
const garisAcuan = {
    id: 'garisAcuan',
    afterDatasetsDraw(chart, _args, opsi) {
        if (!opsi || opsi.nilai == null) return;

        const { ctx, chartArea: area, scales } = chart;
        const px = scales[opsi.sumbu].getPixelForValue(opsi.nilai);
        const vertikal = opsi.sumbu === 'x';

        // Di luar area gambar (nilai di luar rentang sumbu): tidak digambar
        if (vertikal ? (px < area.left || px > area.right) : (px < area.top || px > area.bottom)) return;

        ctx.save();
        ctx.setLineDash([5, 4]);
        ctx.lineWidth = 1.25;
        ctx.strokeStyle = opsi.warna;
        ctx.beginPath();
        if (vertikal) { ctx.moveTo(px, area.top); ctx.lineTo(px, area.bottom); }
        else          { ctx.moveTo(area.left, px); ctx.lineTo(area.right, px); }
        ctx.stroke();

        if (opsi.label) {
            ctx.setLineDash([]);
            ctx.fillStyle = opsi.warna;
            ctx.font = `600 10px ${css('--font') || 'sans-serif'}`;
            if (vertikal) { ctx.textAlign = 'left';  ctx.fillText(opsi.label, px + 5, area.top + 11); }
            else          { ctx.textAlign = 'right'; ctx.fillText(opsi.label, area.right - 4, px - 5); }
        }
        ctx.restore();
    },
};

// Pengaturan dasar yang sama untuk semua grafik
function dasar() {
    const teks = css('--text-mid');
    const grid = css('--border');
    Chart.defaults.font.family = css('--font') || 'sans-serif';
    Chart.defaults.color = teks;
    return { teks, grid };
}

// ── Halaman utama ─────────────────────────────────────────────────────────────

function grafikCapaian(d) {
    const el = document.getElementById('ikuChartCapaian');
    if (!el || !d.capaian.nilai.length) return;
    const { teks, grid } = dasar();

    charts.push(new Chart(el, {
        type: 'bar',
        plugins: [garisAcuan],
        data: {
            labels: d.capaian.labels.map((l) => bungkus(l)),
            datasets: [{
                data: d.capaian.nilai,
                backgroundColor: d.capaian.status.map(warnaStatus),
                borderRadius: 6,
                maxBarThickness: 26,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700 },
            scales: {
                x: { min: 0, suggestedMax: 130, grid: { color: grid }, ticks: { color: teks, callback: (v) => v + '%' } },
                y: { grid: { display: false }, ticks: { color: teks, font: { size: 11 } } },
            },
            plugins: {
                legend: { display: false },
                garisAcuan: { nilai: 100, sumbu: 'x', warna: teks, label: 'Target' },
                tooltip: {
                    callbacks: {
                        title: (items) => d.capaian.labels[items[0].dataIndex],
                        label: (c) => 'Capaian ' + angka.format(c.parsed.x) + '%',
                        afterLabel: (c) => d.capaian.tip[c.dataIndex],
                    },
                },
            },
        },
    }));
}

function grafikTren(d) {
    const el = document.getElementById('ikuChartTren');
    if (!el || !d.tren.seri.length) return;
    const { teks, grid } = dasar();

    const palet = ['--teal', '--sky', '--violet', '--champagne', '--rose', '--gold'].map(css);

    // Rentang sumbu Y menyesuaikan data supaya perbedaan antar triwulan terlihat, tapi garis 100% selalu masuk
    const semua = d.tren.seri.flatMap((s) => s.data).filter((v) => v !== null);
    const min = Math.max(0, Math.floor((Math.min(...semua) - 10) / 10) * 10);
    const max = Math.max(110, Math.ceil((Math.max(...semua) + 5) / 10) * 10);

    charts.push(new Chart(el, {
        type: 'line',
        plugins: [garisAcuan],
        data: {
            labels: d.tren.labels,
            datasets: d.tren.seri.map((s, i) => ({
                label: s.nama,
                data: s.data,
                borderColor: palet[i % palet.length],
                backgroundColor: palet[i % palet.length],
                borderWidth: 2,
                tension: 0.3,
                spanGaps: false,            // triwulan tanpa data = celah, bukan 0
                pointRadius: 4,
                pointHoverRadius: 6,
            })),
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700 },
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { grid: { display: false }, ticks: { color: teks } },
                y: { min, max, grid: { color: grid }, ticks: { color: teks, callback: (v) => v + '%' } },
            },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, color: teks } },
                garisAcuan: { nilai: 100, sumbu: 'y', warna: teks, label: 'Target' },
                tooltip: {
                    callbacks: {
                        label: (c) => `${c.dataset.label}: ${angka.format(c.parsed.y)}%`,
                        afterLabel: (c) => d.tren.seri[c.datasetIndex].tip[c.dataIndex] ?? '',
                    },
                },
            },
        },
    }));
}

// ── Halaman detail: nilai mentah realisasi vs target (satu indikator = satu satuan, jadi tidak perlu dinormalkan) ──

function grafikDetail(d) {
    const el = document.getElementById('ikuChartDetail');
    if (!el || !d.ada_data) return;
    const { teks, grid } = dasar();

    const datasets = [{
        label: 'Realisasi',
        data: d.realisasi,
        borderColor: css('--teal'),
        backgroundColor: css('--teal'),
        borderWidth: 2.5,
        tension: 0.3,
        spanGaps: false,
        pointRadius: 5,
        pointHoverRadius: 7,
    }];

    if (d.target !== null) {
        datasets.push({
            label: 'Target',
            data: d.labels.map(() => d.target),
            borderColor: teks,
            borderDash: [6, 5],
            borderWidth: 1.5,
            pointRadius: 0,
            pointHoverRadius: 0,
        });
    }

    charts.push(new Chart(el, {
        type: 'line',
        data: { labels: d.labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 700 },
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { grid: { display: false }, ticks: { color: teks } },
                y: { grace: '15%', grid: { color: grid }, ticks: { color: teks } },
            },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, color: teks } },
                tooltip: {
                    callbacks: {
                        label: (c) => c.datasetIndex === 0
                            ? `Realisasi: ${d.realisasi_fmt[c.dataIndex]}`
                            : `Target: ${d.target_fmt}`,
                    },
                },
            },
        },
    }));
}

// ── Jalankan; bangun ulang saat tema terang/gelap diganti ─────────────────────

function render() {
    charts.splice(0).forEach((c) => c.destroy());

    if (window.IKU_DATA) {
        grafikCapaian(window.IKU_DATA);
        grafikTren(window.IKU_DATA);
    }
    if (window.IKU_DETAIL) {
        grafikDetail(window.IKU_DETAIL);
    }
}

render();
new MutationObserver(render).observe(root, { attributes: true, attributeFilter: ['data-theme'] });