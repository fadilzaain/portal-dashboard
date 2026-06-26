/* ══════════════════════════════════════════════
   Info Detail BOR 
   Level 1 : Informasi Tempat Tidur (API http://192.168.10.29/wslokal/kominfo/realtime/infott)
   Level 2 : Detail Ruangan atau Per Kelas (http://192.168.10.29/wslokal/kominfo/realtime/infott)
══════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ══════════════════════════════════════════════
     Threshold BOR (%)
  ══════════════════════════════════════════════ */
  const BOR_THRESHOLD = { rendah: 60, ideal_max: 85 };

  /* ══════════════════════════════════════════════
     HELPERS UMUM
  ══════════════════════════════════════════════ */
  function escHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function borColor(bor) {
    if (!bor) return '#7d8590';
    if (bor >= BOR_THRESHOLD.rendah && bor <= BOR_THRESHOLD.ideal_max) return '#22c55e';
    if (bor < BOR_THRESHOLD.rendah) return '#f59e0b';
    return '#ef4444';
  }

  function borStatus(bor) {
    if (!bor) return { cls: 'nodata', label: 'Belum ada data' };
    if (bor >= BOR_THRESHOLD.rendah && bor <= BOR_THRESHOLD.ideal_max) return { cls: 'ideal', label: '✓ Ideal' };
    if (bor < BOR_THRESHOLD.rendah) return { cls: 'rendah', label: '↓ Rendah' };
    return { cls: 'tinggi', label: '↑ Tinggi' };
  }

  function renderLoading(msg = 'Memuat data...') {
    return `<div class="bor-loading"><div class="bor-spinner"></div><span>${escHtml(msg)}</span></div>`;
  }

  function renderError(msg) {
    return `<div class="bor-error"><div class="bor-error-icon">⚠</div><div>${escHtml(msg)}</div></div>`;
  }

  /* ══════════════════════════════════════════════
     KELAS WARNA PER KELAS PASIEN
  ══════════════════════════════════════════════ */
  const KELAS_LABEL = {
    '1': 'Kelas I',
    '2': 'Kelas II',
    '3': 'Kelas III',
    '4': 'VIP',
    '5': 'VVIP',
  };

  function kelasColor(kelas) {
    const map = {
      '1': { bg: 'rgba(167,139,250,0.12)', text: '#a78bfa', border: 'rgba(167,139,250,0.3)' },
      '2': { bg: 'rgba(37,99,235,0.10)',   text: '#2563eb', border: 'rgba(37,99,235,0.25)' },
      '3': { bg: 'rgba(34,197,94,0.10)',   text: '#22c55e', border: 'rgba(34,197,94,0.25)' },
      '4': { bg: 'rgba(245,158,11,0.10)',  text: '#f59e0b', border: 'rgba(245,158,11,0.25)' },
      '5': { bg: 'rgba(239,68,68,0.10)',   text: '#ef4444', border: 'rgba(239,68,68,0.25)' },
    };
    return map[String(kelas)] ?? { bg: 'rgba(125,133,144,0.10)', text: '#7d8590', border: 'rgba(125,133,144,0.2)' };
  }

  /* ══════════════════════════════════════════════
     STATE
  ══════════════════════════════════════════════ */
  const state = {
    level:           1,
    apiCache:        null,   // fetch dari proxy
    selectedRuangan: null,   // id, nama, tempatrawat
  };

  /* ══════════════════════════════════════════════
     DOM REFS
  ══════════════════════════════════════════════ */
  let overlay, btnBack, btnClose,
      titleEl, subtitleEl, breadcrumb,
      panels;

  /* ══════════════════════════════════════════════
     INIT
  ══════════════════════════════════════════════ */
  function init() {
    const wrap = document.createElement('div');
    wrap.innerHTML = `
      <div class="bor-overlay" id="borOverlay">
        <div class="bor-modal">
          <div class="bor-modal-header">
            <div class="bor-modal-header-left">
              <button class="bor-modal-back hidden" id="borBtnBack" title="Kembali">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
              </button>
              <div>
                <div class="bor-modal-title" id="borTitle">Detail Tempat Tidur</div>
                <div class="bor-modal-subtitle" id="borSubtitle"></div>
              </div>
            </div>
            <button class="bor-modal-close" id="borBtnClose">✕</button>
          </div>
          <div class="bor-breadcrumb" id="borBreadcrumb"></div>
          <div class="bor-modal-body">
            <div class="bor-panel active" id="borPanelL1"></div>
            <div class="bor-panel"        id="borPanelL2"></div>
          </div>
        </div>
      </div>`;
    document.body.appendChild(wrap.firstElementChild);

    overlay    = document.getElementById('borOverlay');
    btnBack    = document.getElementById('borBtnBack');
    btnClose   = document.getElementById('borBtnClose');
    titleEl    = document.getElementById('borTitle');
    subtitleEl = document.getElementById('borSubtitle');
    breadcrumb = document.getElementById('borBreadcrumb');
    panels     = {
      1: document.getElementById('borPanelL1'),
      2: document.getElementById('borPanelL2'),
    };

    btnClose.addEventListener('click', closeModal);
    btnBack.addEventListener('click', goBack);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
  }

  /* ══════════════════════════════════════════════
     OPEN / CLOSE
  ══════════════════════════════════════════════ */
  function openModal() {
    overlay.classList.add('show');
    showLevel(1);
    loadLevel1();
  }

  function closeModal() {
    overlay.classList.remove('show');
  }

  /* ══════════════════════════════════════════════
     NAVIGASI
  ══════════════════════════════════════════════ */
  function goBack() {
    if (state.level > 1) showLevel(state.level - 1);
  }

  function showLevel(level) {
    state.level = level;
    [1, 2].forEach(l => panels[l].classList.toggle('active', l === level));
    btnBack.classList.toggle('hidden', level === 1);
    updateHeader();
  }

  function updateHeader() {
    const r = state.selectedRuangan;

    const crumbs = [{ label: 'Semua Ruangan', level: 1 }];
    if (state.level >= 2 && r) crumbs.push({ label: r.nama, level: 2 });

    switch (state.level) {
      case 1:
        titleEl.textContent    = 'Informasi Tempat Tidur';
        subtitleEl.textContent = 'Data Terbaru Informasi Tempat Tidur';
        break;
      case 2:
        titleEl.textContent    = r?.nama ?? '—';
        subtitleEl.textContent = r
          ? `${r._totalKapasitas} bed total · ${r._totalTerisi} terisi · ${r._totalKosong} kosong`
          : '';
        break;
    }

    breadcrumb.innerHTML = crumbs.map((c, i) => {
      const isLast = i === crumbs.length - 1;
      const sep    = i > 0 ? '<span class="bor-breadcrumb-sep">›</span>' : '';
      return isLast
        ? `${sep}<span class="bor-breadcrumb-item active">${escHtml(c.label)}</span>`
        : `${sep}<span class="bor-breadcrumb-item" data-level="${c.level}">${escHtml(c.label)}</span>`;
    }).join('');

    breadcrumb.querySelectorAll('[data-level]').forEach(el => {
      el.addEventListener('click', () => {
        const lv = parseInt(el.dataset.level);
        if (lv < state.level) showLevel(lv);
      });
    });
  }

  /* ══════════════════════════════════════════════
     FETCH DATA VIA PROXY LARAVEL
  ══════════════════════════════════════════════ */
  async function fetchRealtime() {
    // cache sesi biar tidak fetch berulang selama modal terbuka
    if (state.apiCache) return state.apiCache;

    const res = await fetch('/api-proxy/infott', {
      signal: AbortSignal.timeout(10000),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    const ruangan = json?.response?.ruangan ?? [];

    // Hitung total per ruangan dan simpan ke cache
    state.apiCache = ruangan.map(r => {
      const totalKapasitas = r.tempatrawat.reduce((s, t) => s + (t.kapasitas ?? 0), 0);
      const totalTerisi    = r.tempatrawat.reduce((s, t) => s + (t.terisi    ?? 0), 0);
      const totalKosong    = r.tempatrawat.reduce((s, t) => s + (t.kosong    ?? 0), 0);
      return {
        ...r,
        _totalKapasitas: totalKapasitas,
        _totalTerisi:    totalTerisi,
        _totalKosong:    totalKosong,
      };
    });

    return state.apiCache;
  }

  /* ══════════════════════════════════════════════
     level 1 - akumulasi semua ruangan
  ══════════════════════════════════════════════ */
  async function loadLevel1() {
    panels[1].innerHTML = renderLoading('Memuat data...');

    let ruangan;
    try {
      ruangan = await fetchRealtime();
    } catch (err) {
      panels[1].innerHTML = renderError('Gagal memuat data. Periksa koneksi ke server SIMRS.');
      return;
    }

    renderLevel1(ruangan);
  }

  function renderLevel1(ruangan) {
    const totalKapasitas = ruangan.reduce((s, r) => s + r._totalKapasitas, 0);
    const totalTerisi    = ruangan.reduce((s, r) => s + r._totalTerisi,    0);
    const totalKosong    = ruangan.reduce((s, r) => s + r._totalKosong,    0);

    const summaryHTML = `
      <div class="bor-summary-strip">
        <div class="bor-strip-card">
          <div class="bor-strip-val">${ruangan.length}</div>
          <div class="bor-strip-label">Ruangan</div>
        </div>
        <div class="bor-strip-card">
          <div class="bor-strip-val">${totalKapasitas}</div>
          <div class="bor-strip-label">Total Bed</div>
        </div>
        <div class="bor-strip-card">
          <div class="bor-strip-val" style="color:#ef4444">${totalTerisi}</div>
          <div class="bor-strip-label">Terisi</div>
        </div>
        <div class="bor-strip-card">
          <div class="bor-strip-val" style="color:#22c55e">${totalKosong}</div>
          <div class="bor-strip-label">Kosong</div>
        </div>
      </div>`;

    const cardsHTML = ruangan.map(r => {
      const pctOccupy = r._totalKapasitas > 0
        ? Math.round((r._totalTerisi / r._totalKapasitas) * 100)
        : 0;

      return `
        <div class="bor-ruangan-card" data-ruangan-id="${r.id}" title="Klik untuk lihat detail per kelas">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
            <div>
              <div class="bor-ruangan-name">${escHtml(r.nama)}</div>
              <div class="bor-ruangan-kode">Ruangan #${r.id}</div>
            </div>
            <span style="font-size:10px;padding:3px 8px;border-radius:6px;background:rgba(37,99,235,0.1);color:#2563eb;font-weight:600">
              Detail →
            </span>
          </div>

          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px">
            <div style="text-align:center;padding:8px;background:var(--pp-surface);border-radius:8px">
              <div style="font-size:20px;font-weight:700;color:var(--pp-text);font-family:var(--pp-mono)">${r._totalKapasitas}</div>
              <div style="font-size:10px;color:var(--pp-muted);margin-top:2px">Kapasitas</div>
            </div>
            <div style="text-align:center;padding:8px;background:rgba(239,68,68,0.07);border-radius:8px">
              <div style="font-size:20px;font-weight:700;color:#ef4444;font-family:var(--pp-mono)">${r._totalTerisi}</div>
              <div style="font-size:10px;color:var(--pp-muted);margin-top:2px">Terisi</div>
            </div>
            <div style="text-align:center;padding:8px;background:rgba(34,197,94,0.07);border-radius:8px">
              <div style="font-size:20px;font-weight:700;color:#22c55e;font-family:var(--pp-mono)">${r._totalKosong}</div>
              <div style="font-size:10px;color:var(--pp-muted);margin-top:2px">Kosong</div>
            </div>
          </div>

          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:10px;color:var(--pp-muted)">Presentase Kapasitas</span>
            <span style="font-size:10px;color:var(--pp-muted)">${pctOccupy}%</span>
          </div>
          <div class="bor-progress-wrap" style="height:4px">
            <div class="bor-progress-fill" style="width:${pctOccupy}%;background:#2563eb"></div>
          </div>

          <div style="margin-top:10px;padding:6px 10px;background:rgba(125,133,144,0.08);border-radius:6px;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:10px;color:var(--pp-muted)">BOR</span>
            <span style="font-size:11px;color:var(--pp-muted);font-style:italic"></span>
          </div>
        </div>`;
    }).join('');

    panels[1].innerHTML = summaryHTML + `<div class="bor-ruangan-grid">${cardsHTML}</div>`;

    panels[1].querySelectorAll('.bor-ruangan-card').forEach(card => {
      card.addEventListener('click', () => {
        const id          = card.dataset.ruanganId;
        const ruanganItem = (state.apiCache ?? []).find(r => String(r.id) === String(id));
        if (ruanganItem) openLevel2(ruanganItem);
      });
    });
  }

  /* ══════════════════════════════════════════════
     level 2 — detail sub ruangan
  ══════════════════════════════════════════════ */
  function openLevel2(ruangan) {
    state.selectedRuangan = ruangan;
    showLevel(2);
    renderLevel2(ruangan);
  }

  function renderLevel2(ruangan) {
    const tempatrawat = ruangan.tempatrawat ?? [];

    if (tempatrawat.length === 0) {
      panels[2].innerHTML = renderError('Tidak ada data sub-ruangan untuk ruangan ini.');
      return;
    }

    const totalKap    = ruangan._totalKapasitas;
    const totalTerisi = ruangan._totalTerisi;
    const totalKosong = ruangan._totalKosong;

    const summaryHTML = `
      <div class="bor-summary-strip">
        <div class="bor-strip-card">
          <div class="bor-strip-val">${tempatrawat.length}</div>
          <div class="bor-strip-label">Sub-Ruangan</div>
        </div>
        <div class="bor-strip-card">
          <div class="bor-strip-val">${totalKap}</div>
          <div class="bor-strip-label">Total Bed</div>
        </div>
        <div class="bor-strip-card">
          <div class="bor-strip-val" style="color:#ef4444">${totalTerisi}</div>
          <div class="bor-strip-label">Terisi</div>
        </div>
        <div class="bor-strip-card">
          <div class="bor-strip-val" style="color:#22c55e">${totalKosong}</div>
          <div class="bor-strip-label">Kosong</div>
        </div>
      </div>`;

    const cardsHTML = tempatrawat.map(t => {
      const kap      = t.kapasitas ?? 0;
      const terisi   = t.terisi    ?? 0;
      const kosong   = t.kosong    ?? 0;
      const klsCfg   = kelasColor(t.kelas);
      const klsLabel = KELAS_LABEL[String(t.kelas)] ?? `Kelas ${t.kelas}`;

      return `
        <div class="bor-subruangan-card">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:12px">
            <div style="flex:1;min-width:0">
              <div style="font-size:13px;font-weight:600;color:var(--pp-text);line-height:1.3">${escHtml(t.nama)}</div>
              <div style="font-size:11px;color:var(--pp-muted);margin-top:2px">#${t.id}</div>
            </div>
            <span style="flex-shrink:0;font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px;
                         background:${klsCfg.bg};color:${klsCfg.text};border:1px solid ${klsCfg.border}">
              ${escHtml(klsLabel)}
            </span>
          </div>

          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:12px">
            <div style="text-align:center;padding:8px;background:var(--pp-surface);border-radius:8px">
              <div style="font-size:20px;font-weight:700;color:var(--pp-text);font-family:var(--pp-mono)">${kap}</div>
              <div style="font-size:10px;color:var(--pp-muted);margin-top:2px">Kapasitas</div>
            </div>
            <div style="text-align:center;padding:8px;background:rgba(239,68,68,0.07);border-radius:8px">
              <div style="font-size:20px;font-weight:700;color:#ef4444;font-family:var(--pp-mono)">${terisi}</div>
              <div style="font-size:10px;color:var(--pp-muted);margin-top:2px">Terisi</div>
            </div>
            <div style="text-align:center;padding:8px;background:rgba(34,197,94,0.07);border-radius:8px">
              <div style="font-size:20px;font-weight:700;color:#22c55e;font-family:var(--pp-mono)">${kosong}</div>
              <div style="font-size:10px;color:var(--pp-muted);margin-top:2px">Kosong</div>
            </div>
          </div>

          <div style="padding:6px 10px;background:rgba(125,133,144,0.08);border-radius:6px;display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:10px;color:var(--pp-muted)">BOR</span>
            <span style="font-size:11px;color:var(--pp-muted);font-style:italic"></span>
          </div>
        </div>`;
    }).join('');

    panels[2].innerHTML = summaryHTML + `<div class="bor-subruangan-grid">${cardsHTML}</div>`;
  }

  /* ══════════════════════════════════════════════
     ATTACH KE BOR CARD
  ══════════════════════════════════════════════ */
  function attachToBORChart() {
    const borCard = document.getElementById('borKpiCard');
    if (borCard) {
      borCard.style.cursor = 'pointer';
      borCard.title = 'Klik untuk melihat Detail Tempat Tidur';
      borCard.addEventListener('click', () => openModal());
    }
  }

  /* ══════════════════════════════════════════════
     EXPOSE & BOOT
  ══════════════════════════════════════════════ */
  window.BORModal = { open: openModal };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => { init(); attachToBORChart(); });
  } else {
    init();
    setTimeout(attachToBORChart, 800);
  }

})();