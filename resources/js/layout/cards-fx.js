// ════════════════════════════════════════════════════════════════
// Rombakan dashi v2 - EFEK KARTU BERSAMA - dimuat sekali di layout, jalan di SEMUA portal.
// Pasangan dari resources/css/layout/cards.css. Isinya:
//   1. Spotlight  : cahaya lembut mengikuti kursor di atas kartu/panel
//   2. Reveal     : kartu di bawah layar muncul halus saat di-scroll
//   3. Count-up   : angka [data-count-target] menghitung naik saat terlihat
// Semuanya mati otomatis kalau pengguna memilih "kurangi gerakan".
// ════════════════════════════════════════════════════════════════
import { countUpAll } from '../portal/utils/count-up.js';

const KARTU = '.pc-kpi, .pc-panel';
const gerakDikurangi = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const punyaKursor    = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

// ── 1. Spotlight ────────────────────────────────────────────────
// Satu listener untuk seluruh halaman (delegasi), dibatasi satu update per frame.
function pasangSpotlight() {
    if (gerakDikurangi || !punyaKursor) return;

    let menunggu = false;
    let terakhir = null;

    document.addEventListener('pointermove', (e) => {
        terakhir = e;
        if (menunggu) return;
        menunggu = true;

        requestAnimationFrame(() => {
            menunggu = false;
            const kartu = terakhir.target.closest?.(KARTU);
            if (!kartu) return;

            const kotak = kartu.getBoundingClientRect();
            kartu.style.setProperty('--mx', `${terakhir.clientX - kotak.left}px`);
            kartu.style.setProperty('--my', `${terakhir.clientY - kotak.top}px`);
        });
    }, { passive: true });
}

// ── 2. Reveal saat scroll ───────────────────────────────────────
// Hanya kartu yang awalnya di luar layar yang ditahan; kartu yang sudah terlihat
// memakai animasi masuk CSS biasa (tanpa kedip).
function pasangReveal() {
    if (gerakDikurangi || !('IntersectionObserver' in window)) return;

    const batas   = window.innerHeight * 0.95;
    const menunggu = [...document.querySelectorAll('.pc-kpi, .pc-panel, .pc-state')]
        .filter((el) => el.getBoundingClientRect().top > batas);
    if (!menunggu.length) return;

    const pengamat = new IntersectionObserver((daftar) => {
        daftar.forEach((entri) => {
            if (!entri.isIntersecting) return;
            pengamat.unobserve(entri.target);
            entri.target.style.animationDelay = '0ms';   // sudah di-scroll: tampil langsung, tanpa antre stagger
            entri.target.classList.remove('pc-wait');
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

    menunggu.forEach((el) => {
        el.classList.add('pc-wait');
        pengamat.observe(el);
    });
}

// ── Jalankan ────────────────────────────────────────────────────
function mulai() {
    pasangSpotlight();
    pasangReveal();
    countUpAll();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else {
    mulai();
}