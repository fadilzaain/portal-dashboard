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
export function countUp(el, target, { duration = 900, decimal = 0, from = 0, locale = 'id-ID' } = {}) {
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
        const ease = 1 - Math.pow(1 - p, 3);
        const val  = from + (target - from) * ease;
        numEl.textContent = val.toLocaleString(locale, {
            minimumFractionDigits: decimal,
            maximumFractionDigits: decimal,
        });
        if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
}

/**
 * Scan semua [data-count-target] dan jalankan count-up.
 * Bisa dipanggil ulang (misal setelah data di-refresh via AJAX).
 * @param {HTMLElement} root
 */
export function countUpAll(root = document) {
    root.querySelectorAll('[data-count-target]').forEach((el, i) => {
        const target  = parseFloat(el.dataset.countTarget);
        const decimal = parseInt(el.dataset.countDecimal ?? '0', 10);
        // stagger ringan antar elemen
        setTimeout(() => countUp(el, target, { decimal, duration: 900 }), i * 60);
    });
}