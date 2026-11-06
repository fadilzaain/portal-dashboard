// ════════════════════════════════════════════════════════════════
// COUNT-UP UTILITY
// ════════════════════════════════════════════════════════════════

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Animate count-up on a single element.
 * @param {HTMLElement} el
 * @param {number}      target
 * @param {object}      opts
 *   @param {number}  opts.duration
 *   @param {number}  opts.decimal
 *   @param {number}  opts.from
 *   @param {string}  opts.locale
 */
export function countUp(el, target, { duration = 1100, decimal = 0, from = 0, locale = 'id-ID' } = {}) {
    if (!el || isNaN(target)) return;
    const numEl = el.querySelector('.count-num') ?? el;

    // preferensi reduce-motion: langsung tampilin nilai akhir, gak usah animasi
    if (prefersReducedMotion()) {
        numEl.textContent = target.toLocaleString(locale, {
            minimumFractionDigits: decimal,
            maximumFractionDigits: decimal,
        });
        return;
    }

    const start = performance.now();
    function frame(now) {
        const p    = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - p, 4);   // easeOutQuart: ngebut di awal, melambat halus di ujung
        const val  = from + (target - from) * ease;
        numEl.textContent = val.toLocaleString(locale, {
            minimumFractionDigits: decimal,
            maximumFractionDigits: decimal,
        });
        if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
}

// Satu pengamat bersama: angka baru mulai menghitung saat benar-benar terlihat di layar
let pengamat = null;

function jalankan(el, urutan = 0) {
    const target  = parseFloat(el.dataset.countTarget);
    const decimal = parseInt(el.dataset.countDecimal ?? '0', 10);
    // stagger ringan antar elemen yang muncul bersamaan
    setTimeout(() => countUp(el, target, { decimal }), urutan * 60);
}

/**
 * Scan semua [data-count-target] dan jalankan count-up saat elemen terlihat.
 * Aman dipanggil berkali-kali: elemen yang sudah ditandai tidak dihitung ulang
 * (penanda data-count-done). Update data live tetap lewat countUp() langsung.
 * @param {HTMLElement} root
 */
export function countUpAll(root = document) {
    const daftar = [...root.querySelectorAll('[data-count-target]')]
        .filter((el) => el.dataset.countDone !== '1');
    if (!daftar.length) return;

    // Browser lama tanpa IntersectionObserver: langsung jalan
    if (!('IntersectionObserver' in window)) {
        daftar.forEach((el, i) => { el.dataset.countDone = '1'; jalankan(el, i); });
        return;
    }

    pengamat ??= new IntersectionObserver((entri) => {
        entri.filter((e) => e.isIntersecting).forEach((e, i) => {
            pengamat.unobserve(e.target);
            jalankan(e.target, i);
        });
    }, { threshold: 0.35 });

    daftar.forEach((el) => {
        el.dataset.countDone = '1';
        pengamat.observe(el);
    });
}