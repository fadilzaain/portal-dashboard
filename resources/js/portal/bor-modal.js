/* ══════════════════════════════════════════════
   BOR MODAL SYSTEM ( masih sementara)
   Level 1: Semua Ruangan (fetch API)
   Level 2: Manajemen Bed (dummy)
   Level 3: Detail Pasien (dummy)
══════════════════════════════════════════════ */

(function () {
  'use strict';

// konfigurasi ruangan 
  const RUANGAN = [
    { kode: 1,  nama: 'ABIMANYU',           kapasitas: 30 },
    { kode: 2,  nama: 'ABIMANYU INFEKSIUS', kapasitas: 15 },
    { kode: 3,  nama: 'ARIMBI',             kapasitas: 25 },
    { kode: 4,  nama: 'BIMA',               kapasitas: 28 },
    { kode: 5,  nama: 'DRUPADI',            kapasitas: 20 },
    { kode: 6,  nama: 'GATOTKACA',          kapasitas: 22 },
    { kode: 7,  nama: 'ICU SENTRAL',        kapasitas: 10 },
    { kode: 8,  nama: 'ISTANA PANDAWA',     kapasitas: 35 },
    { kode: 9,  nama: 'SADEWA',             kapasitas: 18 },
    { kode: 10, nama: 'SRIKANDI',           kapasitas: 24 },
    { kode: 11, nama: 'VK',                 kapasitas: 12 },
    { kode: 12, nama: 'YUDISTHIRA',         kapasitas: 26 },
  ];

//warna per statur BOR
  function borColor(bor) {
    if (bor === 0 || bor === null) return '#7d8590';
    if (bor >= 60 && bor <= 85) return '#22c55e';
    if (bor < 60) return '#f59e0b';
    return '#ef4444';
  }
  function borStatus(bor) {
    if (bor === 0 || bor === null) return { cls: 'nodata', label: 'Belum ada data' };
    if (bor >= 60 && bor <= 85) return { cls: 'ideal',  label: '✓ Ideal' };
    if (bor < 60)               return { cls: 'rendah', label: '↓ Rendah' };
    return                             { cls: 'tinggi', label: '↑ Tinggi' };
  }

 //state
  let state = {
    level: 1,
    allData: null,         // data dari API
    selectedRuangan: null, // objek RUANGAN yang dipilih
    selectedBed: null,     // objek bed yang dipilih
    fetchDate: null,       // tanggal yang sedang dipakai BOR chart
  };

  //DOM refs
  let overlay, modal, btnBack, btnClose,
      titleEl, subtitleEl, breadcrumb,
      panelL1, panelL2, panelL3;

  /* ══════════════════════════════════════════════
     INISIALISASI DOM
  ══════════════════════════════════════════════ */
  function init() {
    // Buat overlay + modal sekali ke body
    const tpl = document.createElement('div');
    tpl.innerHTML = `
      <div class="bor-overlay" id="borOverlay">
        <div class="bor-modal">

          <!-- Header -->
          <div class="bor-modal-header">
            <div class="bor-modal-header-left">
              <button class="bor-modal-back hidden" id="borBtnBack" title="Kembali">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
              </button>
              <div>
                <div class="bor-modal-title"  id="borTitle">BOR per Ruangan</div>
                <div class="bor-modal-subtitle" id="borSubtitle"></div>
              </div>
            </div>
            <button class="bor-modal-close" id="borBtnClose">✕</button>
          </div>

          <!-- Breadcrumb -->
          <div class="bor-breadcrumb" id="borBreadcrumb">
            <span class="bor-breadcrumb-item active" data-level="1">Semua Ruangan</span>
          </div>

          <!-- Body -->
          <div class="bor-modal-body" id="borModalBody">
            <!-- Panel L1 -->
            <div class="bor-panel active" id="borPanelL1"></div>
            <!-- Panel L2 -->
            <div class="bor-panel" id="borPanelL2"></div>
            <!-- Panel L3 -->
            <div class="bor-panel" id="borPanelL3"></div>
          </div>

        </div>
      </div>`;
    document.body.appendChild(tpl.firstElementChild);

    overlay   = document.getElementById('borOverlay');
    modal     = overlay.querySelector('.bor-modal');
    btnBack   = document.getElementById('borBtnBack');
    btnClose  = document.getElementById('borBtnClose');
    titleEl   = document.getElementById('borTitle');
    subtitleEl= document.getElementById('borSubtitle');
    breadcrumb= document.getElementById('borBreadcrumb');
    panelL1   = document.getElementById('borPanelL1');
    panelL2   = document.getElementById('borPanelL2');
    panelL3   = document.getElementById('borPanelL3');

    // Events
    btnClose.addEventListener('click', closeModal);
    btnBack.addEventListener('click', goBack);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
  }

  /* ══════════════════════════════════════════════
     OPEN / CLOSE
  ══════════════════════════════════════════════ */
  function openModal(dateFrom, dateTo) {
    state.fetchDate = { from: dateFrom, to: dateTo };
    state.level = 1;
    showOverlay();
    loadLevel1();
  }

  function closeModal() {
    overlay.classList.remove('show');
  }

  function showOverlay() {
    overlay.classList.add('show');
  }

  /* ══════════════════════════════════════════════
     NAVIGASI LEVEL
  ══════════════════════════════════════════════ */
  function goBack() {
    if (state.level === 2) showLevel(1);
    else if (state.level === 3) showLevel(2);
  }

  function showLevel(level) {
    state.level = level;

    // Panel visibility
    panelL1.classList.toggle('active', level === 1);
    panelL2.classList.toggle('active', level === 2);
    panelL3.classList.toggle('active', level === 3);

    // Back button
    btnBack.classList.toggle('hidden', level === 1);

    // Header & breadcrumb
    updateHeader();
  }

  function updateHeader() {
    const { from, to } = state.fetchDate ?? {};
    const dateStr = (from && to) ? `${from} → ${to}` : '';

    if (state.level === 1) {
      titleEl.textContent    = 'BOR per Ruangan';
      subtitleEl.textContent = dateStr;
      breadcrumb.innerHTML   = `
        <span class="bor-breadcrumb-item active">Semua Ruangan</span>`;
    } else if (state.level === 2) {
      const r = state.selectedRuangan;
      titleEl.textContent    = `Manajemen Bed — ${r.nama}`;
      subtitleEl.textContent = `Kapasitas ${r.kapasitas} bed • ${dateStr}`;
      breadcrumb.innerHTML   = `
        <span class="bor-breadcrumb-item" data-level="1">Semua Ruangan</span>
        <span class="bor-breadcrumb-sep">›</span>
        <span class="bor-breadcrumb-item active">${r.nama}</span>`;
    } else if (state.level === 3) {
      const r = state.selectedRuangan;
      const b = state.selectedBed;
      titleEl.textContent    = `Detail Pasien — Bed ${b.nomor}`;
      subtitleEl.textContent = `${r.nama} • ${b.pasien ?? 'Kosong'}`;
      breadcrumb.innerHTML   = `
        <span class="bor-breadcrumb-item" data-level="1">Semua Ruangan</span>
        <span class="bor-breadcrumb-sep">›</span>
        <span class="bor-breadcrumb-item" data-level="2">${r.nama}</span>
        <span class="bor-breadcrumb-sep">›</span>
        <span class="bor-breadcrumb-item active">Bed ${b.nomor}</span>`;
    }

    // Breadcrumb click navigasi
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
    showLevel(1);
    panelL1.innerHTML = renderLoading();

    const { from, to } = state.fetchDate;

    try {
      const results = await Promise.all(
      RUANGAN.map(async r => {
        try {
          const url = `/api-proxy/borlostoi/${r.kode}/${from}/${to}`;
          const res = await fetch(url, { signal: AbortSignal.timeout(10000) });
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          const json = await res.json();
          // Ambil baris pertama dari rows[]
          const d = json?.rows?.[0] ?? null;
          return { kode: r.kode, data: d };
        } catch {
          return { kode: r.kode, data: null };
        }
      })
    );

    const map = {};
    results.forEach(({ kode, data }) => { map[kode] = data; });
    state.allData = map;
    renderLevel1();

    } catch (err) {
        console.error('[BOR Modal] API error:', err);
        panelL1.innerHTML = renderError('Gagal memuat data dari API');
        panelL1.querySelector('.bor-error-retry')?.addEventListener('click', loadLevel1);
    }
    }
  
  //  Normalisasi response API = Map { kode: { bor, los, toi, ... } }

  function normalizeAllData(raw) {
    const map = {};
    // if (Array.isArray(raw)) {
    //   raw.forEach(item => {
    //     const kode = item.kode ?? item.id ?? item.ruangan_kode;
    //     if (kode != null) map[kode] = item;
    //   });
    // } else if (typeof raw === 'object') {
    //   // { "1": { bor: 72, ... }, "2": {...} }
    //   Object.entries(raw).forEach(([k, v]) => { map[parseInt(k)] = v; });
    // }
    return map;
  }

  function renderLevel1() {
    const data = state.allData ?? {};

    // Hitung summary
    let totalKapasitas = 0, totalTerisi = 0, borSum = 0, borCount = 0;
    RUANGAN.forEach(r => {
      totalKapasitas += r.kapasitas;
      const d = data[r.kode];
      const bor = getBOR(d);
      if (bor > 0) {
        const terisi = Math.round((bor / 100) * r.kapasitas);
        totalTerisi += terisi;
        borSum += bor;
        borCount++;
      }
    });
    const avgBOR   = borCount > 0 ? (borSum / borCount).toFixed(1) : '–';
    const totalKosong = totalKapasitas - totalTerisi;

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
          <div class="bor-strip-val" style="color:#22c55e">${totalKosong}</div>
          <div class="bor-strip-label">Kosong</div>
        </div>
      </div>`;

    const cardsHTML = RUANGAN.map(r => {
      const d    = data[r.kode];
      const bor  = getBOR(d);
      const los  = d?.los   ?? d?.avlos ?? 0;
      const toi  = d?.toi   ?? 0;
      const color  = borColor(bor);
      const status = borStatus(bor);
      const terisi = bor > 0 ? Math.round((bor / 100) * r.kapasitas) : 0;
      const kosong = r.kapasitas - terisi;
      const pct    = bor > 0 ? Math.min(bor, 100) : 0;

      return `
        <div class="bor-ruangan-card"
             style="--bor-color:${color}"
             data-kode="${r.kode}"
             title="Klik untuk lihat detail bed">
          <div class="bor-ruangan-name">${r.nama}</div>
          <div class="bor-ruangan-kode">Ruangan #${r.kode}</div>
          <div class="bor-ruangan-bor-val">${bor > 0 ? bor.toFixed(1) + '%' : '–'}</div>
          <div class="bor-ruangan-bor-label">BOR • ${los > 0 ? 'LOS ' + los.toFixed(1) + 'hr' : ''} ${toi > 0 ? '• TOI ' + toi.toFixed(1) + 'hr' : ''}</div>
          <div class="bor-ruangan-stats">
            <div class="bor-stat-item">
              <div class="bor-stat-num">${r.kapasitas}</div>
              <div class="bor-stat-label">Kapasitas</div>
            </div>
            <div class="bor-stat-item">
              <div class="bor-stat-num" style="color:#ef4444">${terisi}</div>
              <div class="bor-stat-label">Terisi</div>
            </div>
            <div class="bor-stat-item">
              <div class="bor-stat-num" style="color:#22c55e">${kosong}</div>
              <div class="bor-stat-label">Kosong</div>
            </div>
          </div>
          <div class="bor-progress-wrap">
            <div class="bor-progress-fill" style="width:${pct}%"></div>
          </div>
          <span class="bor-status-badge ${status.cls}">${status.label}</span>
          <div class="bor-ruangan-arrow">→</div>
        </div>`;
    }).join('');

    panelL1.innerHTML = summaryHTML + `<div class="bor-ruangan-grid">${cardsHTML}</div>`;

    // Event: klik ruangan
    panelL1.querySelectorAll('.bor-ruangan-card').forEach(card => {
      card.addEventListener('click', () => {
        const kode = parseInt(card.dataset.kode);
        const ruangan = RUANGAN.find(r => r.kode === kode);
        if (ruangan) openLevel2(ruangan);
      });
    });
  }

  function getBOR(d) {
    if (!d) return 0;
    const v = d.bor ?? d.BOR ?? d.nilai_bor ?? 0;
    return parseFloat(v) || 0;
}

  /* ══════════════════════════════════════════════
     LEVEL 2 — Manajemen Bed (dummy)
  ══════════════════════════════════════════════ */
  function openLevel2(ruangan) {
    state.selectedRuangan = ruangan;
    showLevel(2);
    renderLevel2(ruangan);
  }

  function renderLevel2(ruangan) {
    // Generate dummy bed data
    const beds = generateDummyBeds(ruangan);

    const kosong    = beds.filter(b => b.status === 'kosong').length;
    const terisi    = beds.filter(b => b.status === 'terisi').length;
    const bayar     = beds.filter(b => b.status === 'sudah-bayar').length;
    const dokSelesai= beds.filter(b => b.status === 'dok-selesai').length;
    const total     = beds.length;
    const borVal    = total > 0 ? (((terisi + bayar + dokSelesai) / total) * 100).toFixed(1) : 0;

    const summaryHTML = `
      <div class="bed-summary-strip">
        <div class="bed-strip-card">
          <div class="bed-strip-val">${total}</div>
          <div class="bed-strip-label">Total Bed</div>
        </div>
        <div class="bed-strip-card">
          <div class="bed-strip-val" style="color:#22c55e">${kosong}</div>
          <div class="bed-strip-label">Kosong</div>
        </div>
        <div class="bed-strip-card">
          <div class="bed-strip-val" style="color:#ef4444">${terisi}</div>
          <div class="bed-strip-label">Terisi</div>
        </div>
        <div class="bed-strip-card">
          <div class="bed-strip-val" style="color:#2563eb">${bayar}</div>
          <div class="bed-strip-label">Sudah Bayar</div>
        </div>
        <div class="bed-strip-card">
          <div class="bed-strip-val" style="color:#a78bfa">${dokSelesai}</div>
          <div class="bed-strip-label">Dok Selesai</div>
        </div>
      </div>`;

    const legendHTML = `
      <div class="bed-legend">
        <div class="bed-legend-item"><div class="bed-legend-dot" style="background:rgba(34,197,94,0.4);border:1px solid #22c55e"></div>Kosong</div>
        <div class="bed-legend-item"><div class="bed-legend-dot" style="background:rgba(239,68,68,0.4);border:1px solid #ef4444"></div>Terisi</div>
        <div class="bed-legend-item"><div class="bed-legend-dot" style="background:rgba(37,99,235,0.4);border:1px solid #2563eb"></div>Sudah Bayar</div>
        <div class="bed-legend-item"><div class="bed-legend-dot" style="background:rgba(167,139,250,0.4);border:1px solid #a78bfa"></div>Dok. Selesai</div>
        <div style="margin-left:auto;font-size:11px;color:var(--pp-muted)">
          BOR aktual: <strong style="color:${borColor(parseFloat(borVal))}">${borVal}%</strong>
          &nbsp;•&nbsp; <span style="font-size:10px;color:var(--pp-muted)">⚠ Data bed masih sementara</span>
        </div>
      </div>`;

    const bedsHTML = beds.map(b => {
      const labelMap = { kosong: 'Kosong', terisi: 'Terisi', 'sudah-bayar': 'Sudah Bayar', 'dok-selesai': 'Dok Selesai' };
      const pasienAttr = b.pasien ? `data-pasien="${escHtml(b.pasien)}"` : '';
      return `
        <div class="bed-card ${b.status}"
             data-bed='${JSON.stringify(b).replace(/'/g, "&#39;")}'
             ${pasienAttr}
             title="Bed ${b.nomor}${b.pasien ? ' — ' + b.pasien : ''}">
          <div class="bed-number">${b.nomor}</div>
          <div class="bed-label">${labelMap[b.status]}</div>
          ${b.pasien ? `<div class="bed-pasien">${escHtml(b.pasien.split(' ').slice(0,2).join(' '))}</div>` : ''}
        </div>`;
    }).join('');

    panelL2.innerHTML = summaryHTML + legendHTML + `<div class="bed-grid">${bedsHTML}</div>`;

    // Event: klik bed
    panelL2.querySelectorAll('.bed-card').forEach(card => {
      card.addEventListener('click', () => {
        try {
          const bed = JSON.parse(card.dataset.bed);
          openLevel3(bed);
        } catch {}
      });
    });
  }

  // Dummy bed generator
  function generateDummyBeds(ruangan) {
    const statusPool = ['terisi', 'terisi', 'terisi', 'kosong', 'kosong', 'sudah-bayar', 'dok-selesai'];
    const namaDummy = [
      'Andi Santoso', 'Siti Rahayu', 'Budi Kurniawan', 'Dewi Lestari',
      'Ahmad Fauzi', 'Rina Wulandari', 'Hendra Wijaya', 'Sri Mulyani',
      'Agus Prasetyo', 'Nurul Hidayah', 'Dian Permata', 'Rudi Hermawan',
      'Yuni Astuti', 'Bambang Susilo', 'Fitria Nuraini', 'Eko Wahyudi',
    ];
    const diagnosaDummy = [
      'Demam Berdarah Dengue (DBD)', 'Hipertensi Grade II', 'Diabetes Mellitus Tipe 2',
      'Appendisitis Akut', 'Fraktur Femur Kanan', 'Pneumonia', 'Gagal Jantung Kongestif',
      'Stroke Iskemik', 'Acute Kidney Injury', 'Gastroenteritis Akut',
    ];

    let nameIdx = 0;
    return Array.from({ length: ruangan.kapasitas }, (_, i) => {
      const nomor  = i + 1;
      const status = statusPool[i % statusPool.length];
      const pasien = status !== 'kosong' ? namaDummy[nameIdx++ % namaDummy.length] : null;
      const diagnosa = pasien ? diagnosaDummy[i % diagnosaDummy.length] : null;

      return {
        nomor,
        status,
        pasien,
        diagnosa,
        no_rm:     pasien ? `RM-${String(100000 + i * 13 + ruangan.kode * 7).slice(0,6)}` : null,
        umur:      pasien ? 20 + (i * 3 % 60) : null,
        jk:        pasien ? (i % 2 === 0 ? 'Laki-laki' : 'Perempuan') : null,
        masuk:     pasien ? formatDateDummy(-3 - (i % 7)) : null,
        dokter:    pasien ? `dr. ${['Andi', 'Budi', 'Citra', 'Dian', 'Eko'][i % 5]} Sp.PD` : null,
        jaminan:   pasien ? ['BPJS', 'BPJS', 'BPJS', 'Umum', 'Asuransi'][i % 5] : null,
        kelas:     pasien ? ['Kelas 1', 'Kelas 2', 'Kelas 3', 'VIP'][i % 4] : null,
      };
    });
  }

  function formatDateDummy(offsetDays) {
    const d = new Date();
    d.setDate(d.getDate() + offsetDays);
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  /* ══════════════════════════════════════════════
     LEVEL 3 — Detail Pasien (dummy)
  ══════════════════════════════════════════════ */
  function openLevel3(bed) {
    state.selectedBed = bed;
    showLevel(3);
    renderLevel3(bed);
  }

  function renderLevel3(bed) {
    if (bed.status === 'kosong') {
      panelL3.innerHTML = `
        <div class="bor-error">
          <div class="bor-error-icon">🛏</div>
          <div>Bed ${bed.nomor} sedang <strong>kosong</strong></div>
          <div style="font-size:10px;margin-top:4px">Tidak ada pasien yang menempati bed ini</div>
        </div>`;
      return;
    }

    const statusMap = {
      'terisi':      { chip: 'aktif',   label: '● Dirawat' },
      'sudah-bayar': { chip: 'bayar',   label: '✓ Sudah Bayar' },
      'dok-selesai': { chip: 'selesai', label: '✓ Dok. Selesai' },
    };
    const st = statusMap[bed.status] ?? { chip: 'dummy', label: bed.status };

    panelL3.innerHTML = `
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
            <span class="pasien-status-chip ${st.chip}">${st.label}</span>
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
          <div class="pasien-detail-row">
            <span class="pasien-detail-key">Bed</span>
            <span class="pasien-detail-val">${bed.nomor} — ${escHtml(state.selectedRuangan?.nama ?? '–')}</span>
          </div>
          <div class="pasien-detail-row">
            <span class="pasien-detail-key">Tgl Masuk</span>
            <span class="pasien-detail-val">${escHtml(bed.masuk ?? '–')}</span>
          </div>
          <div class="pasien-detail-row">
            <span class="pasien-detail-key">Kelas</span>
            <span class="pasien-detail-val">${escHtml(bed.kelas ?? '–')}</span>
          </div>
          <div class="pasien-detail-row">
            <span class="pasien-detail-key">Jaminan</span>
            <span class="pasien-detail-val">${escHtml(bed.jaminan ?? '–')}</span>
          </div>
        </div>
        <div class="pasien-detail-card">
          <div class="pasien-detail-section-title">Tenaga Medis</div>
          <div class="pasien-detail-row">
            <span class="pasien-detail-key">DPJP</span>
            <span class="pasien-detail-val">${escHtml(bed.dokter ?? '–')}</span>
          </div>
          <div class="pasien-detail-row">
            <span class="pasien-detail-key">Ruangan</span>
            <span class="pasien-detail-val">${escHtml(state.selectedRuangan?.nama ?? '–')}</span>
          </div>
          <div class="pasien-detail-row">
            <span class="pasien-detail-key">Status</span>
            <span class="pasien-detail-val">${st.label}</span>
          </div>
        </div>
      </div>

      <div style="padding:10px 14px;background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:9px;font-size:11px;color:#f59e0b">
        ⚠ Contoh Data Pasien <strong>sementara</strong>. 
      </div>`;
  }

  /* ══════════════════════════════════════════════
     HELPERS
  ══════════════════════════════════════════════ */
  function renderLoading() {
    return `<div class="bor-loading"><div class="bor-spinner"></div><span>Memuat data BOR...</span></div>`;
  }

  function renderError(msg) {
    return `
      <div class="bor-error">
        <div class="bor-error-icon">⚠</div>
        <div>${escHtml(msg)}</div>
        <button class="bor-error-retry">Coba lagi</button>
      </div>`;
  }

  function escHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /* ══════════════════════════════════════════════
     INTEGRASI CHART BOR
  ══════════════════════════════════════════════ */
  // function attachToBORChart() {
  //   function getCurrentDates() {
  //     const dari   = document.querySelector('input[name="dari"]')?.value
  //                 || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
  //     const sampai = document.querySelector('input[name="sampai"]')?.value
  //                 || new Date().toISOString().split('T')[0];
  //     return { dari, sampai };
  //   }

  function attachToBORChart() {
    function getCurrentDates() {
      const bulan = document.querySelector('select[name="bulan"]')?.value;
            const tahun = document.querySelector('select[name="tahun"]')?.value;

            if (bulan && tahun) {
              const y      = parseInt(tahun);
              const m      = parseInt(bulan);
              const dari   = `${y}-${String(m).padStart(2, '0')}-01`;
              const lastDay = new Date(y, m, 0).getDate();
              const sampai  = `${y}-${String(m).padStart(2, '0')}-${lastDay}`;
              return { dari, sampai };
            }

            // fallback jika select tidak ditemukan
            const now    = new Date();
            const dari   = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
            const sampai = now.toISOString().split('T')[0];
            return { dari, sampai };
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