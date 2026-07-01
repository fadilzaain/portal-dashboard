/* ════════════════════════════════════════════════════════════════
   IGD CAPACITY TABS
   ════════════════════════════════════════════════════════════════ */

const IGDCapacity = (() => {

  // ── CONFIG  ──────────────
  const CONFIG = {
    proxyUrl: null, // diisi dari data attribute saat init()
    refreshIntervalMs: 5 * 60 * 1000, // auto refresh tab Kapasitas TT tiap 5 menit
    colorByOccupancy(pct) {
      if (pct >= 90) return 'var(--pp-red)';
      if (pct >= 70) return 'var(--pp-yellow)';
      if (pct === 0) return 'var(--pp-muted)';
      return 'var(--pp-green)';
    },
    kelasLabel: { '1': 'Kelas I', '2': 'Kelas II', '3': 'Kelas III', '4': 'VIP', '5': 'VVIP' },
    kelasColor: {
      '1': 'rgba(37,99,235,0.15)',
      '2': 'rgba(167,139,250,0.15)',
      '3': 'rgba(6,182,212,0.15)',
      '4': 'rgba(245,158,11,0.15)',
      '5': 'rgba(220,38,38,0.15)',
    },
  };

  let state = {
    activeTab: 'monitoring',
    rooms: [],          // hasil dari API
    expandedRoomId: null,
    loading: false,
    lastFetched: null,
  };

  let els = {};
  let tooltipEl = null;

  // ── Helpers ──────────────────────────────────────────────────
  function pct(terisi, kapasitas) {
    if (!kapasitas) return 0;
    return Math.round((terisi / kapasitas) * 100);
  }

  function sum(arr, key) {
    return arr.reduce((a, b) => a + (b[key] || 0), 0);
  }

  // Normalisasi response API jadi struktur ringkas + agregat per ruangan
  function normalizeRooms(apiRooms) {
    return apiRooms.map(r => {
      const tt = r.tempatrawat || [];
      const kapasitas = sum(tt, 'kapasitas');
      const terisi = sum(tt, 'terisi');
      const kosong = sum(tt, 'kosong');
      return {
        id: r.id,
        nama: r.nama,
        kapasitas,
        terisi,
        kosong,
        pct: pct(terisi, kapasitas),
        tempatrawat: tt.map(t => ({
          id: t.id,
          nama: t.nama,
          kelas: t.kelas,
          kapasitas: t.kapasitas,
          kosong: t.kosong,
          terisi: t.terisi,
          pct: pct(t.terisi, t.kapasitas),
        })),
      };
    }).filter(r => r.kapasitas > 0); // skip ruangan tanpa bed sama sekali
  }

  // ── Tooltip ──────────────────────────────────────────────────
  function ensureTooltip() {
    if (tooltipEl) return tooltipEl;
    tooltipEl = document.createElement('div');
    tooltipEl.className = 'igd-tooltip';
    document.body.appendChild(tooltipEl);
    return tooltipEl;
  }

  function showTooltip(x, y, html) {
    const el = ensureTooltip();
    el.innerHTML = html;
    el.classList.add('is-visible');
    positionTooltip(x, y);
  }

  function positionTooltip(x, y) {
    if (!tooltipEl) return;
    const padding = 14;
    let left = x + padding;
    let top = y + padding;
    const rect = tooltipEl.getBoundingClientRect();
    if (left + rect.width > window.innerWidth - 10) left = x - rect.width - padding;
    if (top + rect.height > window.innerHeight - 10) top = y - rect.height - padding;
    tooltipEl.style.left = left + 'px';
    tooltipEl.style.top = top + 'px';
  }

  function hideTooltip() {
    if (tooltipEl) tooltipEl.classList.remove('is-visible');
  }

  // ── Tab switching ─────────
  function switchTab(tab) {
    state.activeTab = tab;
    els.tabs.querySelectorAll('.igd-tab-btn').forEach(btn => {
      btn.classList.toggle('is-active', btn.dataset.tab === tab);
    });
    els.panels.forEach(p => {
      p.classList.toggle('is-active', p.dataset.panel === tab);
    });

    if (tab === 'kapasitas' && state.rooms.length === 0 && !state.loading) {
      fetchCapacity();
    }
  }

  // ── Render: grid card per ruangan (level 1) ─────────────────
  function renderRoomGrid() {
    const totalKapasitas = sum(state.rooms, 'kapasitas');
    const totalTerisi = sum(state.rooms, 'terisi');
    const totalKosong = sum(state.rooms, 'kosong');

    if (els.ttStatRuangan) els.ttStatRuangan.textContent = state.rooms.length;
    if (els.ttStatTotal) els.ttStatTotal.textContent = totalKapasitas;
    if (els.ttStatTerisi) els.ttStatTerisi.textContent = totalTerisi;
    if (els.ttStatKosong) els.ttStatKosong.textContent = totalKosong;

    if (!state.rooms.length) {
      els.grid.innerHTML = `
        <div class="igd-tt-state" style="grid-column:1/-1">
          <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
          </svg>
          <span>Data kapasitas tempat tidur belum tersedia</span>
        </div>`;
      return;
    }

    // ── card ruangan 
    els.grid.innerHTML = state.rooms.map(room => {
      const color = CONFIG.colorByOccupancy(room.pct);
      const isExpanded = state.expandedRoomId === room.id;
      const statusLabel = room.pct >= 90 ? 'Penuh' : room.pct >= 70 ? 'Siaga' : room.pct === 0 ? 'Kosong' : 'Aman';

      const tone = room.pct >= 90
        ? { bg: 'rgba(239,68,68,0.07)', border: 'rgba(239,68,68,0.28)' }
        : room.pct >= 70
        ? { bg: 'rgba(245,158,11,0.07)', border: 'rgba(245,158,11,0.28)' }
        : room.pct === 0
        ? { bg: 'var(--pp-surface2)', border: 'var(--pp-border)' }
        : { bg: 'rgba(34,197,94,0.07)', border: 'rgba(34,197,94,0.28)' };

      const cardStyle = `--igd-room-bg:${tone.bg};--igd-room-border:${tone.border};--igd-room-accent:${color}`;

      return `
        <div class="igd-room-card ${isExpanded ? 'is-expanded' : ''}"
             data-room-id="${room.id}" data-role="room-card"
             style="${cardStyle}">

          <div class="igd-room-head">
            <div>
              <div class="igd-room-name">${room.nama}</div>
              <div class="igd-room-sub">Ruangan #${room.id}</div>
            </div>
            <button class="igd-room-detail-btn" type="button" data-role="detail-toggle" data-room-id="${room.id}">
              Detail
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l6-6-6-6"/>
              </svg>
            </button>
          </div>

          <div class="igd-room-stats">
            <div class="igd-room-stat">
              <div class="igd-room-stat-val">${room.kapasitas}</div>
              <div class="igd-room-stat-lbl">Kapasitas</div>
            </div>
            <div class="igd-room-stat">
              <div class="igd-room-stat-val" style="color:var(--pp-red)">${room.terisi}</div>
              <div class="igd-room-stat-lbl">Terisi</div>
            </div>
            <div class="igd-room-stat">
              <div class="igd-room-stat-val" style="color:var(--pp-green)">${room.kosong}</div>
              <div class="igd-room-stat-lbl">Kosong</div>
            </div>
          </div>

          <div class="igd-room-progress-head">
            <span>Presentase Kapasitas</span>
            <b>${room.pct}%</b>
          </div>
          <div class="igd-room-progress-track">
            <div class="igd-room-progress-fill" style="width:${room.pct}%;background:${color}"></div>
          </div>

          <span class="igd-room-badge" style="background:${tone.border};color:${color}">${statusLabel}</span>

          <div class="igd-room-detail-wrap" data-role="detail-wrap">
            <div class="igd-room-detail-inner">
              ${room.tempatrawat.map(tt => renderTTCard(tt)).join('')}
            </div>
          </div>
        </div>`;
    }).join('');

    bindGridEvents();
  }

  // ── Render: 1 kartu tempatrawat (level 2 detail) ────────────
  function renderTTCard(tt) {
    const color = CONFIG.colorByOccupancy(tt.pct);
    const kelasLabel = CONFIG.kelasLabel[tt.kelas] || `Kelas ${tt.kelas}`;
    const kelasBg = CONFIG.kelasColor[tt.kelas] || 'rgba(125,133,144,0.15)';
    return `
      <div class="igd-tt-card" data-role="tt-card"
           data-tooltip-title="${tt.nama}"
           data-tooltip-terisi="${tt.terisi}"
           data-tooltip-kosong="${tt.kosong}"
           data-tooltip-kapasitas="${tt.kapasitas}"
           data-tooltip-pct="${tt.pct}">
        <div class="igd-tt-card-name">${tt.nama}</div>
        <div class="igd-tt-card-row">
          <span>Terisi</span><b style="color:var(--pp-text)">${tt.terisi}/${tt.kapasitas}</b>
        </div>
        <div class="igd-tt-card-bar">
          <div class="igd-tt-card-bar-fill" style="width:${tt.pct}%;background:${color}"></div>
        </div>
        <span class="igd-tt-card-kelas" style="background:${kelasBg};color:${color === 'var(--pp-muted)' ? 'var(--pp-muted)' : color}">${kelasLabel}</span>
      </div>`;
  }

  // ── Events: klik "Detail"  ───
  function bindGridEvents() {
    els.grid.querySelectorAll('[data-role="detail-toggle"]').forEach(btn => {
      btn.addEventListener('click', () => {
        const roomId = btn.dataset.roomId;
        state.expandedRoomId = state.expandedRoomId === roomId ? null : roomId;
        renderRoomGrid();
        toggleBreadcrumb();

        // scroll halus ke card yang baru expand
        requestAnimationFrame(() => {
          const card = els.grid.querySelector(`[data-room-id="${roomId}"]`);
          card?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
      });
    });

    els.grid.querySelectorAll('[data-role="room-card"]').forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const room = state.rooms.find(r => r.id === card.dataset.roomId);
        if (!room) return;
        showTooltip(e.clientX, e.clientY, `
          <div class="igd-tooltip-title">${room.nama}</div>
          <div class="igd-tooltip-row"><span>Terisi</span><b>${room.terisi}/${room.kapasitas}</b></div>
          <div class="igd-tooltip-row"><span>Kosong</span><b>${room.kosong}</b></div>
          <div class="igd-tooltip-row"><span>Okupansi</span><b>${room.pct}%</b></div>
        `);
      });
      card.addEventListener('mouseleave', hideTooltip);
    });

    els.grid.querySelectorAll('[data-role="tt-card"]').forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const d = card.dataset;
        showTooltip(e.clientX, e.clientY, `
          <div class="igd-tooltip-title">${d.tooltipTitle}</div>
          <div class="igd-tooltip-row"><span>Terisi</span><b>${d.tooltipTerisi}/${d.tooltipKapasitas}</b></div>
          <div class="igd-tooltip-row"><span>Kosong</span><b>${d.tooltipKosong}</b></div>
          <div class="igd-tooltip-row"><span>Okupansi</span><b>${d.tooltipPct}%</b></div>
        `);
      });
      card.addEventListener('mouseleave', hideTooltip);
    });
  }

  function toggleBreadcrumb() {
    if (!els.breadcrumb) return;
    const room = state.rooms.find(r => r.id === state.expandedRoomId);
    if (room) {
      els.breadcrumb.classList.add('is-visible');
      els.breadcrumb.querySelector('[data-role="breadcrumb-label"]').textContent = `Semua Ruangan / ${room.nama}`;
    } else {
      els.breadcrumb.classList.remove('is-visible');
    }
  }

  // ── Fetch dari proxy BOR/infott ──────────────────────────────
  async function fetchCapacity() {
    if (!CONFIG.proxyUrl) return;
    state.loading = true;
    setLoadingUI(true);

    try {
      const res = await fetch(CONFIG.proxyUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: AbortSignal.timeout(12000),
      });
      const json = await res.json();
      const rooms = json?.response?.ruangan ?? [];
      state.rooms = normalizeRooms(rooms);
      state.lastFetched = new Date();
      updateLastFetchedLabel();
      renderRoomGrid();
    } catch (err) {
      console.error('[IGD Kapasitas TT] Gagal fetch:', err);
      els.grid.innerHTML = `
        <div class="igd-tt-state" style="grid-column:1/-1">
          <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
          </svg>
          <span>Gagal memuat data kapasitas. Coba refresh.</span>
        </div>`;
    } finally {
      state.loading = false;
      setLoadingUI(false);
    }
  }

  function setLoadingUI(isLoading) {
    if (els.refreshBtn) els.refreshBtn.classList.toggle('is-loading', isLoading);
    if (isLoading && !state.rooms.length) {
      els.grid.innerHTML = Array.from({ length: 6 })
        .map(() => '<div class="igd-skeleton" style="height:190px;border-radius:14px"></div>')
        .join('');
    }
  }

  function updateLastFetchedLabel() {
    if (!els.ttUpdated || !state.lastFetched) return;
    const fmt = state.lastFetched.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    els.ttUpdated.textContent = `Diperbarui ${fmt}`;
  }

  // ── Init ─────────────────────────────────────────────────────
  function init() {
    const card = document.getElementById('igdTabCard');
    if (!card) return;

    CONFIG.proxyUrl = card.dataset.ttProxyUrl;

    els = {
      card,
      tabs: card.querySelector('[data-role="tabs"]'),
      panels: card.querySelectorAll('[data-panel]'),
      grid: card.querySelector('[data-role="grid"]'),
      breadcrumb: card.querySelector('[data-role="breadcrumb"]'),
      refreshBtn: card.querySelector('[data-role="refresh-btn"]'),
      ttStatRuangan: card.querySelector('[data-role="tt-stat-ruangan"]'),
      ttStatTotal: card.querySelector('[data-role="tt-stat-total"]'),
      ttStatTerisi: card.querySelector('[data-role="tt-stat-terisi"]'),
      ttStatKosong: card.querySelector('[data-role="tt-stat-kosong"]'),
      ttUpdated: card.querySelector('[data-role="tt-updated"]'),
    };

    if (!els.tabs) return;

    els.tabs.querySelectorAll('.igd-tab-btn').forEach(btn => {
      btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    els.breadcrumb?.addEventListener('click', () => {
      state.expandedRoomId = null;
      renderRoomGrid();
      toggleBreadcrumb();
    });

    els.refreshBtn?.addEventListener('click', () => fetchCapacity());

    // Auto refresh kapasitas TT secara berkala saat tab aktif
    setInterval(() => {
      if (state.activeTab === 'kapasitas') fetchCapacity();
    }, CONFIG.refreshIntervalMs);
  }

  document.addEventListener('DOMContentLoaded', init);

  return { switchTab, fetchCapacity }; 
})();