/* ══════════════════════════════════════════════
   Detail BOR 
   Level 1 : Semua Ruangan  (fetch API)
   Level 2 : Manajemen Bed  (data sementara)
   Level 3 : Detail Pasien  (data sementara)
══════════════════════════════════════════════ */

(function () {
  'use strict';

  /* ══════════════════════════════════════════════
     Konfigurasi Per Ruangan
  ══════════════════════════════════════════════ */
  const RUANGAN = [
    { kode: 1,  nama: 'ABIMANYU',           kapasitas: 68  },
    { kode: 2,  nama: 'ABIMANYU INFEKSIUS', kapasitas: 15,  todo: true }, // PR cari kode
    { kode: 3,  nama: 'ARIMBI',             kapasitas: 30  },
    { kode: 4,  nama: 'BIMA',               kapasitas: 28,  todo: true }, // PR cari kode
    { kode: 5,  nama: 'DRUPADI',            kapasitas: 29  },
    { kode: 6,  nama: 'GATOTKACA',          kapasitas: 50  },
    { kode: 7,  nama: 'ICU SENTRAL',        kapasitas: 36  },
    { kode: 9,  nama: 'SADEWA',             kapasitas: 44  },
    { kode: 10, nama: 'SRIKANDI',           kapasitas: 48  },
    { kode: 11, nama: 'VK',                 kapasitas: 12,  todo: true }, // PR cari kode
    { kode: 99, nama: 'YUDHISTIRA',         kapasitas: 143, todo: true }, // kode sementara
    { kode: 13, nama: 'ISTANA PANDAWA',     kapasitas: 40  },
  ];

  // Threshold BOR (%) 
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
      if (bor >= BOR_THRESHOLD.rendah && bor <= BOR_THRESHOLD.ideal_max) return { cls: 'ideal',  label: '✓ Ideal'  };
        if (bor < BOR_THRESHOLD.rendah) return { cls: 'rendah', label: '↓ Rendah' };
    return { cls: 'tinggi', label: '↑ Tinggi' };
  }

  function getBOR(d) {
    if (!d) return 0;
      return parseFloat(d.bor ?? d.BOR ?? d.nilai_bor ?? 0) || 0;
  }

  function renderLoading(msg = 'Memuat data...') {
    return `<div class="bor-loading"><div class="bor-spinner"></div><span>${escHtml(msg)}</span></div>`;
  }

  function renderError(msg, onRetry) {
    // onRetry: string nama fungsi yang dipanggil tombol retry (opsional)
    const retryBtn = onRetry
      ? `<button class="bor-error-retry" data-retry="${onRetry}">Coba lagi</button>`
      : '';
    return `<div class="bor-error"><div class="bor-error-icon">⚠</div><div>${escHtml(msg)}</div>${retryBtn}</div>`;
  }

  function formatDate(offsetDays = 0) {
    const d = new Date();
    d.setDate(d.getDate() + offsetDays);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  /* ══════════════════════════════════════════════
     STATE
  ══════════════════════════════════════════════ */
  const state = {
    level:           1,
    fetchDate:       null,   // { from, to }
    apiCache:        null,   // cache per sesi buka modal
    cacheDateKey:    null,  
    selectedRuangan: null,
    selectedBed:     null,
  };

  /* ══════════════════════════════════════════════
     DOM REFS  
  ══════════════════════════════════════════════ */
  let overlay, btnBack, btnClose,
      titleEl, subtitleEl, breadcrumb,
      panels; // { 1: el, 2: el, 3: el }

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
                <div class="bor-modal-title" id="borTitle">BOR per Ruangan</div>
                <div class="bor-modal-subtitle" id="borSubtitle"></div>
              </div>
            </div>
            <button class="bor-modal-close" id="borBtnClose">✕</button>
          </div>
          <div class="bor-breadcrumb" id="borBreadcrumb"></div>
          <div class="bor-modal-body">
            <div class="bor-panel active" id="borPanelL1"></div>
            <div class="bor-panel"        id="borPanelL2"></div>
            <div class="bor-panel"        id="borPanelL3"></div>
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
      3: document.getElementById('borPanelL3'),
    };

    btnClose.addEventListener('click', closeModal);
    btnBack.addEventListener('click', goBack);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
  }

  /* ══════════════════════════════════════════════
     OPEN / CLOSE
  ══════════════════════════════════════════════ */
  function openModal(dateFrom, dateTo) {
    const newKey = `${dateFrom}|${dateTo}`;

    // Invalidate cache kalau tanggal berubah
    if (state.cacheDateKey !== newKey) {
      state.apiCache    = null;
      state.cacheDateKey = newKey;
    }

    state.fetchDate = { from: dateFrom, to: dateTo };
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
    [1, 2, 3].forEach(l => panels[l].classList.toggle('active', l === level));
    btnBack.classList.toggle('hidden', level === 1);
    updateHeader();
  }

  function updateHeader() {
    const { from, to } = state.fetchDate ?? {};
    const dateStr = (from && to) ? `${from} → ${to}` : '';
    const r = state.selectedRuangan;
    const b = state.selectedBed;

    const crumbs = [{ label: 'Semua Ruangan', level: 1 }];
    if (state.level >= 2 && r) crumbs.push({ label: r.nama, level: 2 });
    if (state.level >= 3 && b) crumbs.push({ label: `Bed ${b.nomor}`, level: 3 });

    switch (state.level) {
      case 1:
        titleEl.textContent    = 'BOR per Ruangan';
        subtitleEl.textContent = dateStr;
        break;
      case 2:
        titleEl.textContent    = `Manajemen Bed — ${r.nama}`;
        subtitleEl.textContent = `Kapasitas ${r.kapasitas} bed • ${dateStr}`;
        break;
      case 3:
        titleEl.textContent    = `Detail Pasien — Bed ${b.nomor}`;
        subtitleEl.textContent = `${r.nama} • ${b.pasien ?? 'Kosong'}`;
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
     LEVEL 1 — Semua Ruangan
  ══════════════════════════════════════════════ */
  async function loadLevel1() {
    panels[1].innerHTML = renderLoading('Memuat data BOR...');

    // cache kalau sudah ada untuk tanggal ini
    if (state.apiCache) {
      renderLevel1();
      return;
    }

    const { from, to } = state.fetchDate;

    // Fetch paralel, skip ruangan bertanda todo (kode belum pasti)
    const results = await Promise.all(
      RUANGAN.map(async r => {
        if (r.todo) return { kode: r.kode, data: null };
        try {
          const res = await fetch(`/api-proxy/borlostoi/${r.kode}/${from}/${to}`, {
            signal: AbortSignal.timeout(10000),
          });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const json = await res.json();
          return { kode: r.kode, data: json?.rows?.[0] ?? null };
        } catch {
          return { kode: r.kode, data: null };
        }
      })
    );

    // Simpan cache — key = kode ruangan
    state.apiCache = {};
    results.forEach(({ kode, data }) => { state.apiCache[kode] = data; });

    renderLevel1();
  }

  function renderLevel1() {
    const cache = state.apiCache ?? {};

    // Hitung summary strip
    let totalKapasitas = 0, totalTerisi = 0, borSum = 0, borCount = 0;
    RUANGAN.forEach(r => {
      totalKapasitas += r.kapasitas;
      const bor = getBOR(cache[r.kode]);
      if (bor > 0) {
        totalTerisi += Math.round((bor / 100) * r.kapasitas);
        borSum += bor;
        borCount++;
      }
    });

    const summaryHTML = `
      <div class="bor-summary-strip">
        <div class="bor-strip-card">
          <div class="bor-strip-val">${RUANGAN.length}</div>
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
          <div class="bor-strip-val" style="color:#22c55e">${totalKapasitas - totalTerisi}</div>
          <div class="bor-strip-label">Kosong</div>
        </div>
      </div>`;

    const cardsHTML = RUANGAN.map(r => {
      const d      = cache[r.kode];
      const bor    = getBOR(d);
      const los    = parseFloat(d?.los ?? d?.avlos ?? 0) || 0;
      const toi    = parseFloat(d?.toi ?? 0) || 0;
      const color  = borColor(bor);
      const status = borStatus(bor);
      const terisi = bor > 0 ? Math.round((bor / 100) * r.kapasitas) : 0;
      const pct    = Math.min(bor, 100);
      const todobadge = r.todo
        ? `<span class="bor-status-badge nodata" title="Kode ruangan belum dikonfirmasi">⚠ Kode sementara</span>`
        : '';

      return `
        <div class="bor-ruangan-card" style="--bor-color:${color}" data-kode="${r.kode}" title="Klik untuk lihat detail bed">
          <div class="bor-ruangan-name">${escHtml(r.nama)}</div>
          <div class="bor-ruangan-kode">Ruangan #${r.kode}</div>
          <div class="bor-ruangan-bor-val">${bor > 0 ? bor.toFixed(1) + '%' : '–'}</div>
          <div class="bor-ruangan-bor-label">BOR${los > 0 ? ' • LOS ' + los.toFixed(1) + 'hr' : ''}${toi > 0 ? ' • TOI ' + toi.toFixed(1) + 'hr' : ''}</div>
          <div class="bor-ruangan-stats">
            <div class="bor-stat-item"><div class="bor-stat-num">${r.kapasitas}</div><div class="bor-stat-label">Kapasitas</div></div>
            <div class="bor-stat-item"><div class="bor-stat-num" style="color:#ef4444">${terisi}</div><div class="bor-stat-label">Terisi</div></div>
            <div class="bor-stat-item"><div class="bor-stat-num" style="color:#22c55e">${r.kapasitas - terisi}</div><div class="bor-stat-label">Kosong</div></div>
          </div>
          <div class="bor-progress-wrap"><div class="bor-progress-fill" style="width:${pct}%"></div></div>
          <span class="bor-status-badge ${status.cls}">${status.label}</span>
          ${todobadge}
          <div class="bor-ruangan-arrow">→</div>
        </div>`;
    }).join('');

    panels[1].innerHTML = summaryHTML + `<div class="bor-ruangan-grid">${cardsHTML}</div>`;

    panels[1].querySelectorAll('.bor-ruangan-card').forEach(card => {
      card.addEventListener('click', () => {
        const ruangan = RUANGAN.find(r => r.kode === parseInt(card.dataset.kode)
                                       && r.nama === card.querySelector('.bor-ruangan-name').textContent);
        if (ruangan) openLevel2(ruangan);
      });
    });
  }

  /* ══════════════════════════════════════════════
    Manajemen Bed data sementara
    { nomor, status, pasien, diagnosa, no_rm, umur, jk, masuk, dokter, jaminan, kelas }
  ══════════════════════════════════════════════ */
  function openLevel2(ruangan) {
    state.selectedRuangan = ruangan;
    showLevel(2);
    panels[2].innerHTML = renderLoading('Memuat data bed...');
    fetchBeds(ruangan).then(beds => renderLevel2(ruangan, beds));
  }

  // janlup ganti fungsi ini sama fetch real kalau DB bed sudah siap
  function fetchBeds(ruangan) {
    return Promise.resolve(generateDummyBeds(ruangan));
  }

  function renderLevel2(ruangan, beds) {
    const BED_STATUS = {
      kosong:       { label: 'Kosong',      color: '#22c55e' },
      terisi:       { label: 'Terisi',      color: '#ef4444' },
      'sudah-bayar':{ label: 'Sudah Bayar', color: '#2563eb' },
      'dok-selesai':{ label: 'Dok Selesai', color: '#a78bfa' },
    };

    const counts = Object.fromEntries(Object.keys(BED_STATUS).map(k => [k, 0]));
    beds.forEach(b => { if (counts[b.status] !== undefined) counts[b.status]++; });
    const occupied = beds.length - counts.kosong;
    const borVal   = beds.length > 0 ? ((occupied / beds.length) * 100).toFixed(1) : 0;

    const summaryHTML = `
      <div class="bed-summary-strip">
        <div class="bed-strip-card"><div class="bed-strip-val">${beds.length}</div><div class="bed-strip-label">Total Bed</div></div>
        ${Object.entries(BED_STATUS).map(([k, v]) => `
        <div class="bed-strip-card">
          <div class="bed-strip-val" style="color:${v.color}">${counts[k]}</div>
          <div class="bed-strip-label">${v.label}</div>
        </div>`).join('')}
      </div>`;

    const legendHTML = `
      <div class="bed-legend">
        ${Object.entries(BED_STATUS).map(([, v]) => `
        <div class="bed-legend-item">
          <div class="bed-legend-dot" style="background:${v.color}33;border:1px solid ${v.color}"></div>
          ${escHtml(v.label)}
        </div>`).join('')}
        <div style="margin-left:auto;font-size:11px;color:var(--pp-muted)">
          BOR aktual: <strong style="color:${borColor(parseFloat(borVal))}">${borVal}%</strong>
          &nbsp;•&nbsp;<span style="font-size:10px">⚠ Data bed masih sementara</span>
        </div>
      </div>`;

    const bedsHTML = beds.map(b => {
      const cfg = BED_STATUS[b.status] ?? { label: b.status, color: '#7d8590' };
      return `
        <div class="bed-card ${b.status}"
             data-bed='${JSON.stringify(b).replace(/'/g, "&#39;")}'
             title="Bed ${b.nomor}${b.pasien ? ' — ' + b.pasien : ''}">
          <div class="bed-number">${b.nomor}</div>
          <div class="bed-label">${cfg.label}</div>
          ${b.pasien ? `<div class="bed-pasien">${escHtml(b.pasien.split(' ').slice(0, 2).join(' '))}</div>` : ''}
        </div>`;
    }).join('');

    panels[2].innerHTML = summaryHTML + legendHTML + `<div class="bed-grid">${bedsHTML}</div>`;

    panels[2].querySelectorAll('.bed-card').forEach(card => {
      card.addEventListener('click', () => {
        try { openLevel3(JSON.parse(card.dataset.bed)); } catch {}
      });
    });
  }

  /* ══════════════════════════════════════════════
    Detail Pasien
    ganti fetchPatient() dengan query real ke endpoint pasien ketika DB sudah siap.
  ══════════════════════════════════════════════ */
  function openLevel3(bed) {
    state.selectedBed = bed;
    showLevel(3);
    renderLevel3(bed);
  }

  function renderLevel3(bed) {
    if (bed.status === 'kosong') {
      panels[3].innerHTML = `
        <div class="bor-error">
          <div class="bor-error-icon">🛏</div>
          <div>Bed ${bed.nomor} sedang <strong>kosong</strong></div>
          <div style="font-size:10px;margin-top:4px">Tidak ada pasien yang menempati bed ini</div>
        </div>`;
      return;
    }

    const STATUS_CHIP = {
      'terisi':       { cls: 'aktif',   label: '● Dirawat'      },
      'sudah-bayar':  { cls: 'bayar',   label: '✓ Sudah Bayar'  },
      'dok-selesai':  { cls: 'selesai', label: '✓ Dok. Selesai' },
    };
    const st = STATUS_CHIP[bed.status] ?? { cls: 'dummy', label: bed.status };
    const r  = state.selectedRuangan;

    panels[3].innerHTML = `
      <div class="pasien-detail-header">
        <div class="pasien-avatar">👤</div>
        <div style="flex:1;min-width:0">
          <div class="pasien-nama">${escHtml(bed.pasien)}</div>
          <div class="pasien-meta">
            <span class="pasien-meta-item">🆔 ${escHtml(bed.no_rm ?? '–')}</span>
            <span class="pasien-meta-item">🎂 ${bed.umur ?? '–'} tahun</span>
            <span class="pasien-meta-item">⚧ ${escHtml(bed.jk ?? '–')}</span>
          </div>
          <div class="pasien-status-row">
            <span class="pasien-status-chip ${st.cls}">${st.label}</span>
            <span class="pasien-status-chip dummy">⚠ Data Sementara</span>
          </div>
        </div>
      </div>

      <div class="pasien-diagnosa-box">
        <div class="pasien-detail-section-title">Diagnosa</div>
        <div class="pasien-diagnosa-text">${escHtml(bed.diagnosa ?? '–')}</div>
      </div>

      <div class="pasien-detail-grid">
        <div class="pasien-detail-card">
          <div class="pasien-detail-section-title">Data Rawat Inap</div>
          ${detailRow('Bed',       `${bed.nomor} — ${escHtml(r?.nama ?? '–')}`)}
          ${detailRow('Tgl Masuk', escHtml(bed.masuk   ?? '–'))}
          ${detailRow('Kelas',     escHtml(bed.kelas   ?? '–'))}
          ${detailRow('Jaminan',   escHtml(bed.jaminan ?? '–'))}
        </div>
        <div class="pasien-detail-card">
          <div class="pasien-detail-section-title">Tenaga Medis</div>
          ${detailRow('DPJP',    escHtml(bed.dokter  ?? '–'))}
          ${detailRow('Ruangan', escHtml(r?.nama     ?? '–'))}
          ${detailRow('Status',  st.label)}
        </div>
      </div>

      <div style="padding:10px 14px;background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:9px;font-size:11px;color:#f59e0b">
        ⚠ Contoh data pasien <strong>sementara</strong>.
      </div>`;
  }

  function detailRow(key, val) {
    return `<div class="pasien-detail-row">
      <span class="pasien-detail-key">${escHtml(key)}</span>
      <span class="pasien-detail-val">${val}</span>
    </div>`;
  }

  /* ══════════════════════════════════════════════
     DUMMY DATA GENERATOR
     – Dihapus / diganti fetchBeds() kalau DB bed sudah siap
  ══════════════════════════════════════════════ */
  function generateDummyBeds(ruangan) {
    const STATUS_POOL  = ['terisi', 'terisi', 'terisi', 'kosong', 'kosong', 'sudah-bayar', 'dok-selesai'];
    const NAMA_DUMMY   = ['Andi Santoso','Siti Rahayu','Budi Kurniawan','Dewi Lestari',
                          'Ahmad Fauzi','Rina Wulandari','Hendra Wijaya','Sri Mulyani',
                          'Agus Prasetyo','Nurul Hidayah','Dian Permata','Rudi Hermawan',
                          'Yuni Astuti','Bambang Susilo','Fitria Nuraini','Eko Wahyudi'];
    const DIAGNOSA_DUMMY = ['Demam Berdarah Dengue (DBD)','Hipertensi Grade II','Diabetes Mellitus Tipe 2',
                            'Appendisitis Akut','Fraktur Femur Kanan','Pneumonia','Gagal Jantung Kongestif',
                            'Stroke Iskemik','Acute Kidney Injury','Gastroenteritis Akut'];
    const DOKTER_DUMMY = ['dr. Andi Sp.PD','dr. Budi Sp.PD','dr. Citra Sp.PD','dr. Dian Sp.PD','dr. Eko Sp.PD'];

    let nameIdx = 0;
    return Array.from({ length: ruangan.kapasitas }, (_, i) => {
      const status  = STATUS_POOL[i % STATUS_POOL.length];
      const hasPasien = status !== 'kosong';
      const pasien  = hasPasien ? NAMA_DUMMY[nameIdx++ % NAMA_DUMMY.length] : null;
      return {
        nomor:    i + 1,
        status,
        pasien,
        diagnosa: pasien ? DIAGNOSA_DUMMY[i % DIAGNOSA_DUMMY.length]         : null,
        no_rm:    pasien ? `RM-${String(100000 + i * 13 + ruangan.kode * 7).slice(0, 6)}` : null,
        umur:     pasien ? 20 + (i * 3 % 60)                                 : null,
        jk:       pasien ? (i % 2 === 0 ? 'Laki-laki' : 'Perempuan')        : null,
        masuk:    pasien ? formatDate(-3 - (i % 7))                          : null,
        dokter:   pasien ? DOKTER_DUMMY[i % DOKTER_DUMMY.length]             : null,
        jaminan:  pasien ? ['BPJS','BPJS','BPJS','Umum','Asuransi'][i % 5]  : null,
        kelas:    pasien ? ['Kelas 1','Kelas 2','Kelas 3','VIP'][i % 4]      : null,
      };
    });
  }

  /* ══════════════════════════════════════════════
     ATTACH KE BOR CARD
  ══════════════════════════════════════════════ */
  function attachToBORChart() {
    function getCurrentDates() {
      const bulan = document.querySelector('select[name="bulan"]')?.value;
      const tahun = document.querySelector('select[name="tahun"]')?.value;
      if (bulan && tahun) {
        const y = parseInt(tahun), m = parseInt(bulan);
        return {
          dari:   `${y}-${String(m).padStart(2, '0')}-01`,
          sampai: `${y}-${String(m).padStart(2, '0')}-${new Date(y, m, 0).getDate()}`,
        };
      }
      const now = new Date();
      return {
        dari:   new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0],
        sampai: now.toISOString().split('T')[0],
      };
    }

    const borCard = document.getElementById('borKpiCard');
    if (borCard) {
      borCard.style.cursor = 'pointer';
      borCard.title = 'Klik untuk melihat detail BOR per ruangan';
      borCard.addEventListener('click', () => {
        const { dari, sampai } = getCurrentDates();
        openModal(dari, sampai);
      });
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