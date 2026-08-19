import { countUpAll } from './utils/count-up.js';

// ── Data terinject dari blade ───────────────────────────────
const { dashboardRoute = '' } = window.DASHBOARD_DATA ?? {};

// ════════════════════════════════════════════════════════════════
// GREETING (Selamat Pagi/Siang/Sore/Malam) - ganti tiap 30 detik
// ════════════════════════════════════════════════════════════════
function updateGreeting() {
    const h     = new Date().getHours();
    const salam = h < 11 ? 'Selamat Pagi'
                : h < 15 ? 'Selamat Siang'
                : h < 18 ? 'Selamat Sore'
                :          'Selamat Malam';

    document.getElementById('greeting-time').textContent = salam;
}
updateGreeting();
setInterval(updateGreeting, 30_000);

// ════════════════════════════════════════════════════════════════
// JAM LIVE 
// ════════════════════════════════════════════════════════════════
function updateHeroClock() {
    const now     = new Date();
    const tanggal = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
    const waktu   = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    const el = document.getElementById('hero-clock-text');
    if (el) el.textContent = `${tanggal} · ${waktu}`;
}
updateHeroClock();
setInterval(updateHeroClock, 1000);

// ════════════════════════════════════════════════════════════════
// FILTER BULAN/TAHUN (hero)
// ════════════════════════════════════════════════════════════════
function applyFilter() {
    const bulan = document.getElementById('filter-bulan').value;
    const tahun = document.getElementById('filter-tahun').value;
    window.location.href = `${dashboardRoute}?bulan=${bulan}&tahun=${tahun}`;
}
window.applyFilter = applyFilter; // dipanggil dari onclick di Blade

// ════════════════════════════════════════════════════════════════
// PROGRESS BAR 
// ════════════════════════════════════════════════════════════════
function animateProgressBars() {
    document.querySelectorAll('.progress-bar-fill').forEach((el, i) => {
        const target = el.style.width;
        if (!target) return;
        el.style.width = '0%';
        setTimeout(() => { el.style.width = target; }, 550 + i * 90);
    });
}
window.addEventListener('load', animateProgressBars);

// ════════════════════════════════════════════════════════════════
// COUNT-UP ANGKA DI 5 CARD DASHBOARD (BOR, LOS, SDM, Mutu, dst)
// ════════════════════════════════════════════════════════════════
window.addEventListener('load', () => countUpAll());

