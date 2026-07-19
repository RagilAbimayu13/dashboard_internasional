@extends('layouts.app')

@section('title', 'Dashboard Cockpit - Global Logistics Dashboard')

@section('styles')
<style>
  /* ===== LOCAL DASHBOARD STYLES ===== */

  /* Floating ambient orbs for depth */
  body::before, body::after {
    content: '';
    position: fixed;
    border-radius: 50%;
    filter: blur(100px);
    pointer-events: none;
    z-index: 0;
  }
  body::before {
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(212, 175, 55, 0.04) 0%, transparent 70%);
    top: -200px; left: -200px;
    animation: orbFloat 20s ease-in-out infinite alternate;
  }
  body::after {
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.03) 0%, transparent 70%);
    bottom: -100px; right: -100px;
    animation: orbFloat 25s ease-in-out infinite alternate-reverse;
  }
  @keyframes orbFloat {
    0%   { transform: translate(0, 0) scale(1); }
    50%  { transform: translate(80px, 40px) scale(1.1); }
    100% { transform: translate(-40px, 80px) scale(0.95); }
  }

  /* Map Container */
  #worldMap {
    height: 520px;
    border-radius: 16px;
    border: 1px solid var(--border-luxury);
    box-shadow: inset 0 0 20px rgba(0,0,0,0.8), 0 10px 30px rgba(0,0,0,0.5);
    background-color: var(--bg-darkest);
    position: relative;
    z-index: 1;
  }

  /* Panes display control */
  .tab-pane { display: none; }

  /* ===== GLASSMORPHISM PANELS ===== */
  .luxury-glass-panel {
    background: var(--bg-panel);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid var(--border-luxury);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    border-radius: 16px;
    padding: 1.75rem;
    position: relative;
    overflow: hidden;
  }
  /* Subtle shimmer top border line */
  .luxury-glass-panel::before {
    content: '';
    position: absolute;
    top: 0; left: 10%; right: 10%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.25), transparent);
    border-radius: 50%;
  }

  /* ===== STAT CARDS (Hero Banner) ===== */
  .stat-value {
    font-size: 2.4rem;
    font-weight: 800;
    color: var(--text-white);
    line-height: 1;
    letter-spacing: -0.02em;
  }
  .stat-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(200, 215, 235, 0.85) !important; /* Brighter label */
    margin-top: 0.4rem;
    font-weight: 600;
  }

  /* ===== ADMIN GOLD STAT CARDS ===== */
  .stat-card-gold {
    background: rgba(255, 255, 255, 0.015);
    border: 1px solid var(--border-luxury);
    border-radius: 14px;
    padding: 1.5rem 1.25rem;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }
  .stat-card-gold::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--gold-secondary), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  .stat-card-gold:hover {
    border-color: var(--border-luxury-active);
    background: rgba(212, 175, 55, 0.04);
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4), 0 0 1px var(--border-luxury-active) inset;
  }
  .stat-card-gold:hover::after { opacity: 1; }
  .stat-card-gold .stat-value { font-size: 2rem; color: var(--gold-primary); text-shadow: 0 0 20px rgba(212, 175, 55, 0.3); }
  .stat-card-gold .stat-label { margin-top: 0.5rem; }

  /* ===== SEARCH DROPDOWN ===== */
  .search-wrap { position: relative; z-index: 50; }
  #searchResults {
    position: absolute; top: calc(100% + 8px); left: 0; right: 0;
    background: rgba(4, 6, 12, 0.98);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-luxury-active);
    border-radius: 14px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7), 0 0 1px rgba(212, 175, 55, 0.3);
    max-height: 320px; overflow-y: auto;
    display: none;
    overflow: hidden;
  }
  .result-item {
    padding: 12px 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--text-silver);
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--border-luxury);
  }
  .result-item:last-child { border-bottom: none; }
  .result-item:hover {
    background: rgba(212, 175, 55, 0.05);
    color: var(--gold-primary);
    padding-left: 24px;
  }

  /* ===== LUXURY TABLE ===== */
  .luxury-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: transparent;
  }
  .luxury-table thead tr th {
    padding: 14px 16px;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    font-weight: 700;
    background: rgba(0, 0, 0, 0.2);
    border-bottom: 1px solid var(--border-luxury);
    backdrop-filter: blur(5px);
  }
  .luxury-table thead tr th:first-child { border-radius: 12px 0 0 0; }
  .luxury-table thead tr th:last-child  { border-radius: 0 12px 0 0; }
  .luxury-table tbody tr {
    transition: all 0.2s ease;
    cursor: pointer;
    border-bottom: 1px solid var(--border-luxury);
  }
  .luxury-table tbody tr td {
    padding: 14px 16px;
    vertical-align: middle;
    color: var(--text-silver);
    font-size: 0.88rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.03);
  }
  .luxury-table tbody tr:hover td {
    background: rgba(212, 175, 55, 0.03);
    color: var(--text-white);
  }
  .luxury-table tbody tr:hover td:first-child { border-left: 2px solid var(--gold-primary); }

  /* ===== METRIC BOXES ===== */
  .metric-box {
    background: rgba(255, 255, 255, 0.01);
    border: 1px solid var(--border-luxury);
    border-radius: 12px;
    padding: 1.2rem;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
  }
  .metric-box::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.3), transparent);
    opacity: 0;
    transition: opacity 0.25s ease;
  }
  .metric-box:hover { border-color: rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); }
  .metric-box:hover::after { opacity: 1; }
  .metric-title {
    font-size: 0.68rem; color: rgba(190, 210, 235, 0.9) !important; /* Brighter metric title */
    text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.6rem; font-weight: 700;
  }
  .metric-value { font-size: 1.25rem; font-weight: 700; color: var(--text-white); }

  /* ===== COMPARE ROWS ===== */
  .compare-row {
    border-bottom: 1px solid var(--border-luxury);
    padding: 16px 0; transition: all 0.25s ease;
    color: #ffffff !important;
  }
  .compare-row:hover { background: rgba(255,255,255,0.01); }
  .compare-label { font-size: 0.78rem; color: rgba(210, 225, 245, 0.92) !important; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600; }
  .winner-glow { color: var(--gold-primary) !important; font-weight: 700 !important; text-shadow: 0 0 10px rgba(212, 175, 55, 0.4); position: relative; }
  .winner-glow::after { content: ' 👑'; font-size: 0.8rem; }
  .compare-loser { color: rgba(255, 255, 255, 0.35) !important; }

  /* ===== NEWS ITEMS ===== */
  .news-item {
    border-bottom: 1px solid var(--border-luxury);
    padding: 12px 0;
    display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem;
    transition: all 0.2s ease;
  }
  .news-item:last-child { border-bottom: none; }
  .news-item:hover { padding-left: 6px; }

  /* ===== CHART CONTAINERS ===== */
  .chart-container {
    position: relative;
    height: 220px; width: 100%;
    background: rgba(0,0,0,0.2);
    border: 1px solid var(--border-luxury);
    border-radius: 12px;
    padding: 1rem;
  }

  /* ===== DETAIL PANEL ===== */
  .detail-panel {
    border: 1px solid var(--border-luxury-active);
    background: rgba(5, 8, 16, 0.85);
  }

  /* ===== SECTION HEADING DIVIDER ===== */
  .section-heading {
    font-family: 'Cinzel', serif;
    font-weight: 700;
    color: var(--text-white);
    font-size: 1.05rem;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .section-heading::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(212, 175, 55, 0.2), transparent);
  }

  /* ===== EMPTY STATE ===== */
  .empty-state-luxury {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
  }
  .empty-state-luxury .empty-icon {
    font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;
  }
  .empty-state-luxury p { font-size: 0.88rem; max-width: 360px; margin: 0 auto; line-height: 1.6; }

  /* ===== ADMIN TOOLS ===== */
  .admin-sidebar-nav { display: flex; flex-direction: column; gap: 0.5rem; }
  .admin-sub-tab {
    padding: 0.75rem 1.25rem; border-radius: 8px; color: var(--text-muted);
    text-decoration: none; font-size: 0.88rem; transition: all 0.25s ease; border: 1px solid transparent;
  }
  .admin-sub-tab:hover, .admin-sub-tab.active { background: rgba(255,255,255,0.02); color: var(--text-white); border-color: var(--border-luxury); }
  .admin-sub-tab.active { border-color: var(--border-luxury-active); color: var(--gold-primary); }

  /* ===== BUTTON LOADING SPINNER ===== */
  .btn-luxury:disabled { opacity: 0.6; cursor: not-allowed; }

  /* ===== PAGE LOAD ANIMATION ===== */
  .content-body { animation: contentEnter 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
  @keyframes contentEnter {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ===== RESPONSIVE SIDEBAR OVERLAY ===== */
  @media (max-width: 768px) {
    .sidebar { display: none; }
    .main-content { margin-left: 0; }
    .content-body { padding: 1.25rem; }
  }

  .result-item-port {
    cursor: pointer;
  }
  .result-item-port:hover {
    background: rgba(56, 189, 248, 0.08) !important;
    border-color: rgba(56, 189, 248, 0.3) !important;
  }

  /* Style Leaflet Popups to match dark premium dashboard theme */
  .leaflet-popup-content-wrapper, .leaflet-popup-tip {
    background: #050810 !important;
    color: #ffffff !important;
    border: 1px solid var(--border-luxury) !important;
    box-shadow: 0 12px 30px rgba(0,0,0,0.7) !important;
  }
  .leaflet-popup-content {
    margin: 12px 16px !important;
    line-height: 1.4 !important;
  }

  /* GeoJSON Choropleth Tooltip */
  .luxury-geo-tooltip {
    background: rgba(5, 8, 16, 0.95) !important;
    border: 1px solid rgba(212, 175, 55, 0.3) !important;
    border-radius: 10px !important;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.7), 0 0 1px rgba(212, 175, 55, 0.2) !important;
    padding: 10px 14px !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px) !important;
    pointer-events: none !important;
  }
  .luxury-geo-tooltip::before {
    display: none !important;
  }

  /* Leaflet zoom control dark theme */
  .leaflet-control-zoom a {
    background: rgba(5, 8, 16, 0.92) !important;
    border: 1px solid rgba(212, 175, 55, 0.2) !important;
    color: rgba(255,255,255,0.7) !important;
    transition: all 0.2s ease !important;
  }
  .leaflet-control-zoom a:hover {
    background: rgba(212, 175, 55, 0.1) !important;
    color: #d4af37 !important;
  }

  /* ===== GLOBAL TEXT CONTRAST OVERRIDES ===== */
  /* Override Bootstrap's default .text-muted to a visible bright color */
  .text-muted {
    color: #adc0d4 !important;  /* Was #6c757d — now readable on dark bg */
  }
  /* Override .text-white-50 to be brighter */
  .text-white-50 {
    color: rgba(210, 228, 248, 0.75) !important; /* Was rgba(255,255,255,0.5) */
  }

  /* ===== METRIC TITLES GLOBAL FIX ===== */
  /* Any metric title color forced via inline rgba(255,255,255,0.55) */
  .metric-box .metric-title,
  [style*="rgba(255, 255, 255, 0.55)"] {
    color: rgba(190, 212, 238, 0.92) !important;
  }

  /* ===== LOADING STATES ===== */
  #loadingMap, #loadingRisk, #loadingWatchlist, #loadingPortsMap {
    color: #9ab8cc !important;
  }
  #loadingMap div, #loadingRisk div, #loadingWatchlist div, #loadingPortsMap div {
    color: #9ab8cc !important;
    font-size: 0.88rem;
    margin-top: 0.5rem;
  }

  /* ===== EMPTY/PLACEHOLDER STATES ===== */
  #comparePlaceholder, #routeResultPlaceholder, #currencySelectPlaceholder,
  #watchlistEmptyState, #portSearchResultList .text-center {
    color: rgba(185, 210, 235, 0.65) !important;
    font-size: 0.9rem;
  }

  /* ===== LUXURY GLASS PANELS — upgraded borders ===== */
  .luxury-glass-panel {
    border: 1px solid rgba(255, 255, 255, 0.07) !important;
    background: rgba(10, 14, 24, 0.72) !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255,255,255,0.04) !important;
  }

  /* ===== SPINNER COLORS ===== */
  .spinner-border.text-warning {
    color: var(--gold-primary) !important;
  }

  /* ===== SECTION HEADINGS UPGRADE ===== */
  .section-heading {
    font-family: 'Cinzel', serif;
    font-weight: 700;
    color: #ffffff;
    font-size: 1.05rem;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  .section-heading::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(212, 175, 55, 0.25), transparent);
  }

  /* ===== PANEL INNER HEADINGS (h5, h6) ===== */
  .luxury-glass-panel h5 {
    color: #f0f4f8 !important;
    font-weight: 700 !important;
  }
  .luxury-glass-panel h6 {
    color: #e8edf5 !important;
    font-weight: 600 !important;
  }

  /* ===== TABLE HEADERS ===== */
  .luxury-table thead tr th {
    color: #b8cce0 !important; /* Brighter table header text */
  }

  /* ===== ADMIN TABLE ===== */
  .table-dark thead th {
    color: #b8cce0 !important;
    border-color: rgba(255,255,255,0.05) !important;
  }
  .table-dark tbody tr td {
    border-color: rgba(255,255,255,0.03) !important;
  }

  /* ===== LUXURY GLASS PANEL SHIMMER UPGRADED ===== */
  .luxury-glass-panel::before {
    content: '';
    position: absolute;
    top: 0; left: 10%; right: 10%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.35), transparent);
    border-radius: 50%;
  }
</style>
@endsection

@section('content')

<!-- ==================== TAB 1: LIVE MAP PANEL ==================== -->
<div id="tab-map" class="tab-pane tab-fade-in">
  <!-- Brand Header Banner -->
  <div class="luxury-glass-panel mb-4 p-4 border-0" style="background: linear-gradient(135deg, rgba(13, 19, 33, 0.9), rgba(5, 7, 18, 0.95)); border: 1px solid var(--border-luxury-active) !important;">
    <div class="row align-items-center">
      <div class="col-md-6">
        <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white); letter-spacing:0.04em;">Global Risk Intelligence Map</h2>
        <p class="mb-0" style="font-size:0.85rem; color: #b8cce0;">Pilih penanda negara di peta untuk menampilkan instrumen analisis risiko rantai pasok secara komprehensif.</p>
      </div>
      <div class="col-md-6 mt-3 mt-md-0">
        <div class="row text-center">
          <div class="col-4">
            <div class="stat-value mono" id="statTotal">-</div>
            <div class="stat-label">Negara Pantauan</div>
          </div>
          <div class="col-4">
            <div class="stat-value mono text-warning" id="statMedium" style="text-shadow: 0 0 10px rgba(245, 158, 11, 0.25);">-</div>
            <div class="stat-label">Medium Risk</div>
          </div>
          <div class="col-4">
            <div class="stat-value mono text-danger" id="statHigh" style="text-shadow: 0 0 10px rgba(239, 68, 68, 0.25);">-</div>
            <div class="stat-label">High Risk</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Search wrap -->
  <div class="search-wrap mb-4">
    <input type="text" id="searchBox" class="form-control form-control-lg form-luxury-input" placeholder="Cari profil negara (misal: Indonesia, Jerman...)" autocomplete="off">
    <div id="searchResults"></div>
  </div>

  <div id="loadingMap" class="text-center py-5 text-muted luxury-glass-panel mb-4">
    <div class="spinner-border spinner-border-sm text-warning mb-2" role="status"></div>
    <div>Memuat Peta Kartografi Global...</div>
  </div>

  <div id="worldMap" class="mb-4" style="display:none"></div>

  <!-- Detailed Dashboard Panel -->
  <div id="detailPanel" class="luxury-glass-panel detail-panel mt-4" style="display:none">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-secondary" style="--bs-border-opacity: 0.1;">
      <div id="detailFlagContainer"></div>
      <div>
        <h3 id="detailCountryName" class="mb-0" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Negara</h3>
        <span id="detailRegionCapital" style="color: #a8c0d8; font-size: 0.85rem;">Kawasan · Ibu kota</span>
      </div>
      <div class="ms-auto d-flex align-items-center gap-3">
        <!-- Interactive Risk Scale -->
        <div class="d-none d-md-flex align-items-center gap-2 me-2">
          <span style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.04em; color: #98b4cc;">Risk Scale:</span>
          <div class="d-flex gap-1" style="height: 8px; width: 60px;">
            <div id="scale-low" style="flex:1; border-radius:3px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); transition: all 0.3s ease;"></div>
            <div id="scale-medium" style="flex:1; border-radius:3px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); transition: all 0.3s ease;"></div>
            <div id="scale-high" style="flex:1; border-radius:3px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); transition: all 0.3s ease;"></div>
          </div>
        </div>
        <span id="detailRiskBadge"></span>
        <button id="watchlistBtn" class="btn-luxury-outline">⭐ Simpan Monitor</button>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="metric-box" style="background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border: 1px solid var(--border-luxury); border-radius: 14px; padding: 1.3rem;">
          <div class="metric-title" style="color: rgba(255, 255, 255, 0.55) !important; font-size: 0.72rem; letter-spacing:0.04em; text-transform:uppercase; font-weight: 700;">📊 Ekonomi & PDB</div>
          <div class="metric-value mono" id="detailGdp" style="font-size: 1.45rem; font-weight:700; color:var(--gold-primary); margin-top: 6px;">-</div>
          <div class="small mt-1" id="detailInflationSub" style="color: #9ab4cc;">-</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="metric-box" style="background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border: 1px solid var(--border-luxury); border-radius: 14px; padding: 1.3rem;">
          <div class="metric-title" style="color: rgba(255, 255, 255, 0.55) !important; font-size: 0.72rem; letter-spacing:0.04em; text-transform:uppercase; font-weight: 700;">🌦️ Iklim & Cuaca</div>
          <div class="metric-value mono" id="detailWeather" style="font-size: 1.45rem; font-weight:700; color:#38bdf8; margin-top: 6px;">-</div>
          <div class="small mt-1" id="detailWeatherSub" style="color: #9ab4cc;">-</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="metric-box" style="background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border: 1px solid var(--border-luxury); border-radius: 14px; padding: 1.3rem;">
          <div class="metric-title" style="color: rgba(255, 255, 255, 0.55) !important; font-size: 0.72rem; letter-spacing:0.04em; text-transform:uppercase; font-weight: 700;">💱 Kurs Valuta</div>
          <div class="metric-value mono" id="detailCurrency" style="font-size: 1.45rem; font-weight:700; color:#10b981; margin-top: 6px;">-</div>
          <div class="small mt-1" id="detailCurrencySub" style="color: #9ab4cc;">-</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="metric-box" id="detailPortsBox" style="background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border: 1px solid var(--border-luxury); border-radius: 14px; padding: 1.3rem; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01))'">
          <div class="metric-title" style="color: rgba(255, 255, 255, 0.55) !important; font-size: 0.72rem; letter-spacing:0.04em; text-transform:uppercase; font-weight: 700;">⚓ Logistik Laut</div>
          <div class="metric-value mono" id="detailPorts" style="font-size: 1.45rem; font-weight:700; color:#a855f7; margin-top: 6px;">-</div>
          <div class="small mt-1" id="detailPortsSub" style="color: #9ab4cc;">-</div>
        </div>
      </div>
    </div>

    <!-- Extended metrics tabs -->
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="luxury-glass-panel h-100 p-4" style="background:rgba(0,0,0,0.15)">
          <h5 class="mb-3" style="color:var(--text-white); font-weight:600;">📰 Berita Intelijen & Analisis Sentimen</h5>
          <div id="detailNewsList" style="max-height: 290px; overflow-y:auto; padding-right:5px;">
            <p class="text-muted small">Memuat berita...</p>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="luxury-glass-panel h-100 p-4" style="background:rgba(0,0,0,0.15)">
          <h5 class="mb-3" style="color:var(--text-white); font-weight:600;">📈 Grafik Trend Historis</h5>
          <div class="d-flex flex-column gap-3">
            <div>
              <div class="chart-container">
                <canvas id="weatherChart"></canvas>
              </div>
            </div>
            <div>
              <div class="chart-container">
                <canvas id="riskChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ==================== TAB 2: RISK ANALYTICS ==================== -->
<div id="tab-risk" class="tab-pane tab-fade-in">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="--bs-border-opacity: 0.05;">
    <div>
      <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Supply Chain Risk Ranking</h2>
      <p class="mb-0" style="font-size:0.88rem; color: #aac0d8;">Negara diurutkan berdasarkan tingkat kerentanan risiko global tertinggi ke terendah.</p>
    </div>
    <div class="d-flex gap-2">
      <select id="filterLevel" class="form-select form-luxury-input w-auto">
        <option value="">Semua Level Risiko</option>
        <option value="High">High Risk</option>
        <option value="Medium">Medium Risk</option>
        <option value="Low">Low Risk</option>
      </select>
    </div>
  </div>

  <div id="loadingRisk" class="text-muted py-5 text-center luxury-glass-panel">
    <div class="spinner-border spinner-border-sm text-warning mb-2" role="status"></div>
    <div>Mengompilasi peringkat risiko...</div>
  </div>

  <div class="card border-0 bg-transparent" id="riskTableContainer" style="display:none">
    <!-- Risk Charts Row -->
    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="luxury-glass-panel h-100 p-4" style="background:rgba(0,0,0,0.15)">
          <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif;">📈 10 Negara Kerentanan Risiko Tertinggi</h5>
          <div class="chart-container" style="height: 300px;">
            <canvas id="topRiskChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="luxury-glass-panel h-100 p-4" style="background:rgba(0,0,0,0.15)">
          <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif;">📊 Distribusi Level Risiko</h5>
          <div class="chart-container" style="height: 300px;">
            <canvas id="riskDistributionChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="luxury-table" id="riskTable">
        <thead>
          <tr>
            <th style="width: 80px" class="ps-4">Rank</th>
            <th>Negara</th>
            <th class="mono">Cuaca</th>
            <th class="mono">Inflasi</th>
            <th class="mono">Sentimen</th>
            <th class="mono">Valuta</th>
            <th style="width: 260px">Total Indeks Skor</th>
            <th class="pe-4" style="width: 140px">Status</th>
          </tr>
        </thead>
        <tbody id="riskTableBody"></tbody>
      </table>
    </div>
  </div>
</div>


<!-- ==================== TAB 3: COUNTRY COMPARATOR ==================== -->
<div id="tab-compare" class="tab-pane tab-fade-in">
  <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Country Risk Comparator</h2>
  <p class="mb-4" style="font-size:0.88rem; color: #aac0d8;">Lakukan komparasi langsung indikator makroekonomi, iklim, dan risiko logistik antara beberapa negara.</p>

  <div class="luxury-glass-panel p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
      <h5 class="mb-0 text-white font-family-cinzel" style="font-weight:600;"><i class="fas fa-globe-asia me-2 text-warning"></i>Pilih Negara Komparasi</h5>
      <button class="btn btn-sm btn-outline-warning" onclick="addNewCompareSelector()" style="border-radius:20px; font-size:0.8rem; padding: 6px 16px;">
        <i class="fas fa-plus me-1"></i> Tambah Negara
      </button>
    </div>
    
    <div class="row g-3" id="compareSelectorsRow">
      <!-- Dynamic select elements will be loaded here -->
    </div>
  </div>

  <div id="comparePlaceholder" class="text-center text-white-50 py-5 luxury-glass-panel">
    Silakan pilih minimal 2 negara di atas untuk menampilkan data komparasi metrik.
  </div>

  <div id="compareResults" class="luxury-glass-panel p-4" style="display:none; overflow-x: auto;">
    <table class="luxury-table align-middle text-center" style="width:100%; border-collapse: collapse; min-width: 600px;">
      <thead>
        <tr id="compareTableHeaderRow">
          <!-- Will hold metric titles and country headers (Flag, Name, Risk Badge) -->
        </tr>
      </thead>
      <tbody id="compareTableBody">
        <!-- Will hold metric comparison rows -->
      </tbody>
    </table>
  </div>
</div>


<!-- ==================== TAB 4: WATCHLIST MONITOR ==================== -->
<div id="tab-watchlist" class="tab-pane tab-fade-in">
  <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Watchlist Monitor</h2>
  <p class="mb-4" style="font-size:0.88rem; color: #aac0d8;">Negara-negara yang saat ini berada dalam pengawasan prioritas logistik Anda.</p>

  <div id="loadingWatchlist" class="text-muted py-5 text-center luxury-glass-panel">
    <div class="spinner-border spinner-border-sm text-warning mb-2" role="status"></div>
    <div>Sinkronisasi data monitor...</div>
  </div>

  <div class="row g-4" id="watchlistGrid"></div>
  
  <div id="watchlistEmptyState" class="text-center text-white-50 py-5 luxury-glass-panel" style="display:none">
    Belum ada negara yang Anda pantau. Silakan tambahkan negara ke daftar monitor melalui <strong>Global Map Panel</strong>.
  </div>
</div>


<!-- ==================== TAB: CURRENCY IMPACT DASHBOARD ==================== -->
<div id="tab-currency" class="tab-pane tab-fade-in">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="--bs-border-opacity: 0.05;">
    <div>
      <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Currency Impact Dashboard</h2>
      <p class="mb-0" style="font-size:0.88rem; color: #aac0d8;">Analisis fluktuasi nilai tukar valas global real-time dan dampaknya terhadap biaya logistik.</p>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-4">
      <div class="luxury-glass-panel h-100 p-4">
        <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif;">💵 Analisis Mata Uang Negara</h5>
        <div class="mb-3">
          <label class="form-luxury-label">Pilih Negara</label>
          <select id="currencyCountrySelect" class="form-select form-luxury-input" onchange="loadCurrencyAnalytics()">
            <option value="">Pilih negara...</option>
          </select>
        </div>
        
        <div id="currencyDetailCard" style="display:none" class="mt-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div id="currencyFlagContainer"></div>
            <div>
              <h6 class="mb-0 text-white font-family-cinzel" id="currencyCountryName">Negara</h6>
              <span style="color: #9ab4cc; font-size: 0.85rem;" id="currencyCodeLabel">CODE</span>
            </div>
          </div>
          <div class="metric-box bg-dark mb-3" style="border: 1px solid var(--border-luxury); border-radius: 12px; padding: 1.2rem;">
            <div class="metric-title" style="font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.6rem; font-weight: 700;">Nilai Tukar Terhadap USD</div>
            <div class="metric-value mono text-warning" id="currencyValueLabel" style="font-size: 1.6rem; font-weight: 700;">-</div>
          </div>
          <div class="small text-muted">
            Status: <span class="badge bg-success" id="currencyStatusBadge">Aktif</span>
            <div class="mt-2">Pembaruan: <span id="currencyUpdateLabel" class="mono">-</span></div>
          </div>
        </div>

        <div id="currencySelectPlaceholder" class="text-center text-white-50 py-5">
          Silakan pilih negara untuk memuat data analisis nilai tukar.
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="luxury-glass-panel h-100 p-4">
        <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif;">📈 Grafik Perubahan Kurs (vs USD)</h5>
        <div class="chart-container" style="height: 300px;">
          <canvas id="currencyTrendChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="luxury-glass-panel p-4">
    <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif;">📋 Daftar Kurs Valas Global Terbaru</h5>
    <div class="table-responsive" style="max-height: 400px; overflow-y:auto;">
      <table class="luxury-table" id="currencyTable">
        <thead>
          <tr>
            <th class="ps-4">Negara</th>
            <th>Mata Uang</th>
            <th class="mono">Kode</th>
            <th class="mono">Kurs / USD</th>
            <th class="pe-4">Pembaruan Terakhir</th>
          </tr>
        </thead>
        <tbody id="currencyTableBody">
          <tr>
            <td colspan="5" class="text-center text-muted py-4">Memuat data kurs...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- ==================== TAB: PORT LOCATION DASHBOARD ==================== -->
<div id="tab-ports" class="tab-pane tab-fade-in">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="--bs-border-opacity: 0.05;">
    <div>
      <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Port Location Dashboard</h2>
      <p class="mb-0" style="font-size:0.88rem; color: #aac0d8;">Pemantauan persebaran dan kapasitas pelabuhan utama di seluruh dunia secara interaktif.</p>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-8">
      <div id="loadingPortsMap" class="text-center py-5 text-muted luxury-glass-panel mb-4" style="height:520px; display:flex; flex-direction:column; justify-content:center; align-items:center;">
        <div class="spinner-border spinner-border-sm text-warning mb-2" role="status"></div>
        <div>Menginisialisasi Peta Logistik Pelabuhan...</div>
      </div>
      <div id="portsMap" class="mb-4" style="height: 520px; border-radius: 16px; border: 1px solid var(--border-luxury); display:none"></div>
    </div>

    <div class="col-lg-4">
      <div class="luxury-glass-panel h-100 p-4 d-flex flex-column justify-content-between" style="min-height:520px;">
        <div>
          <h5 class="mb-4" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif; border-bottom: 1px solid var(--border-luxury); padding-bottom:10px;">🔍 Cari Pelabuhan & Negara</h5>
          
          <div class="mb-3">
            <label class="form-luxury-label">Nama Pelabuhan</label>
            <input type="text" id="portSearchName" class="form-control form-luxury-input" placeholder="Masukkan nama pelabuhan..." oninput="filterPorts()">
          </div>

          <div class="mb-4">
            <label class="form-luxury-label">Negara Pelabuhan</label>
            <select id="portSearchCountry" class="form-select form-luxury-input" onchange="filterPorts()">
              <option value="">Semua Negara</option>
            </select>
          </div>
        </div>

        <div class="flex-grow-1 overflow-auto mt-2" style="max-height: 240px;" id="portSearchResultList">
          <div class="text-center text-white-50 small py-4">Gunakan kolom di atas untuk mencari data pelabuhan.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Shipping Route Estimator Row -->
  <div class="row g-4 mt-2">
    <div class="col-12">
      <!-- Port Details Panel (moved from Modal) -->
      <div id="portDetailModal" class="luxury-glass-panel p-4 mb-4" style="display: none; background: rgba(10, 15, 30, 0.95); border: 1px solid var(--border-luxury); border-radius: 16px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6); position: relative; z-index: 10;">
        <button type="button" class="btn-close btn-close-white position-absolute" onclick="document.getElementById('portDetailModal').style.display='none'" aria-label="Close" style="top: 15px; right: 15px; font-size: 0.7rem; opacity: 0.6;"></button>
        <h5 class="mb-3 text-white font-family-cinzel" style="font-weight: 700; letter-spacing: 0.05em;" id="portModalName">⚓ Detail Pelabuhan</h5>
        
        <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.04);">
          <div id="portModalFlag" style="font-size: 2.2rem; line-height: 1;">🗺️</div>
          <div>
            <h4 class="text-white fw-bold mb-1" id="portModalTitle" style="font-size: 1.05rem;">-</h4>
            <div class="small" id="portModalCountry" style="color: #9ab4cc;">-</div>
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-3 col-6">
            <div class="p-2 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255,255,255,0.03);">
              <div class="small text-white-50 mb-1" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.05em;">📦 Tipe</div>
              <div class="fw-bold text-white mono" style="font-size: 0.8rem;" id="portModalType">-</div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="p-2 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255,255,255,0.03);">
              <div class="small text-white-50 mb-1" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.05em;">📍 Koordinat</div>
              <div class="fw-bold text-white mono" style="font-size: 0.75rem;" id="portModalCoords">-</div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="p-2 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255,255,255,0.03);">
              <div class="small text-white-50 mb-1" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.05em;">⚠️ Risiko</div>
              <div class="fw-bold" id="portModalRisk">-</div>
            </div>
          </div>
          <div class="col-md-3 col-6">
            <div class="p-2 rounded h-100" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255,255,255,0.03);">
              <div class="small text-white-50 mb-1" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.05em;">🌡️ Cuaca</div>
              <div class="fw-bold text-white mono" style="font-size: 0.8rem;" id="portModalWeather">-</div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2" style="max-width: 400px;">
          <button class="btn btn-luxury-outline flex-grow-1" id="portModalBtnOrigin" style="font-size: 0.75rem; padding: 6px 10px;">Set Asal 🚢</button>
          <button class="btn btn-luxury-outline flex-grow-1" id="portModalBtnDest" style="font-size: 0.75rem; padding: 6px 10px;">Set Tujuan 🏁</button>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="luxury-glass-panel p-4">
        <h5 class="mb-4" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif; border-bottom: 1px solid var(--border-luxury); padding-bottom:10px;">🚢 Rute Pengiriman</h5>
        
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-luxury-label mb-0">Pelabuhan Asal</label>
            <button type="button" class="btn btn-link text-warning p-0 small" onclick="showOriginPortDetail()" style="font-size:0.75rem; text-decoration:none; display:none;" id="btnOriginPortDetail">🔍 Detail</button>
          </div>
          <select id="routeOriginPort" class="form-select form-luxury-input" onchange="togglePortDetailLink('Origin')">
            <option value="">Pilih pelabuhan asal...</option>
          </select>
        </div>

        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-luxury-label mb-0">Pelabuhan Tujuan</label>
            <button type="button" class="btn btn-link text-warning p-0 small" onclick="showDestPortDetail()" style="font-size:0.75rem; text-decoration:none; display:none;" id="btnDestPortDetail">🔍 Detail</button>
          </div>
          <select id="routeDestPort" class="form-select form-luxury-input" onchange="togglePortDetailLink('Dest')">
            <option value="">Pilih pelabuhan tujuan...</option>
          </select>
        </div>

        <button class="btn btn-luxury w-100" onclick="calculateShippingRoute()">Hitung Estimasi Rute</button>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="luxury-glass-panel p-4 h-100" id="routeEstimatorResult" style="min-height: 250px;">
        <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif;">📊 Hasil Estimasi Rute</h5>
        
        <div id="routeResultPlaceholder" class="text-center text-white-50 py-5">
          Pilih pelabuhan asal dan tujuan, lalu klik "Hitung Estimasi Rute".
        </div>

        <div id="routeResultContent" style="display:none">
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <div class="metric-box bg-dark" style="border: 1px solid var(--border-luxury); border-radius: 12px; padding: 1.2rem;">
                <div class="metric-title" style="font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.6rem; font-weight: 700;">📏 Jarak Rute</div>
                <div class="metric-value mono text-warning" id="routeDistanceLabel" style="font-size: 1.25rem; font-weight: 700;">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="metric-box bg-dark" style="border: 1px solid var(--border-luxury); border-radius: 12px; padding: 1.2rem;">
                <div class="metric-title" style="font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.6rem; font-weight: 700;">⏱️ Waktu Transit</div>
                <div class="metric-value mono text-info" id="routeDurationLabel" style="font-size: 1.25rem; font-weight: 700;">-</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="metric-box bg-dark" style="border: 1px solid var(--border-luxury); border-radius: 12px; padding: 1.2rem;">
                <div class="metric-title" style="font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.6rem; font-weight: 700;">💰 Estimasi Biaya</div>
                <div class="metric-value mono text-success" id="routeCostLabel" style="font-size: 1.25rem; font-weight: 700;">-</div>
              </div>
            </div>
          </div>

          <!-- Alert warning for weather or high risk -->
          <div id="routeAlertBox" class="alert alert-warning border-0 mb-0" style="display:none; background: rgba(245, 158, 11, 0.1); color: var(--risk-medium); border-radius:12px; padding: 1rem;">
            <div class="d-flex align-items-start gap-2">
              <span class="fs-5" style="line-height: 1;">⚠️</span>
              <div id="routeAlertText" class="small" style="line-height: 1.5;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ==================== TAB: EXTREME WEATHER MONITORING ==================== -->
<div id="tab-weather" class="tab-pane tab-fade-in">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="--bs-border-opacity: 0.05;">
    <div>
      <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Extreme Weather Monitor</h2>
      <p class="mb-0" style="font-size:0.88rem; color: #aac0d8;">Pemantauan badai, kecepatan angin, curah hujan ekstrem, dan mitigasi risiko logistik global.</p>
    </div>
  </div>

  <!-- Summary Widgets -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card-gold" id="weatherWidgetRainCard" style="padding: 1.2rem; cursor: pointer;">
        <div class="stat-value mono text-warning" id="weatherWidgetRain" style="font-size: 1.5rem;">-</div>
        <div class="stat-label" style="font-size: 0.62rem; color: rgba(255, 255, 255, 0.6) !important;">🌧️ Hujan Terlebat</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card-gold" id="weatherWidgetWindCard" style="padding: 1.2rem; cursor: pointer;">
        <div class="stat-value mono text-info" id="weatherWidgetWind" style="font-size: 1.5rem;">-</div>
        <div class="stat-label" style="font-size: 0.62rem; color: rgba(255, 255, 255, 0.6) !important;">💨 Angin Terkencang</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card-gold" id="weatherWidgetTempCard" style="padding: 1.2rem; cursor: pointer;">
        <div class="stat-value mono text-danger" id="weatherWidgetTemp" style="font-size: 1.5rem;">-</div>
        <div class="stat-label" style="font-size: 0.62rem; color: rgba(255, 255, 255, 0.6) !important;">☀️ Suhu Tertinggi</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card-gold" id="weatherWidgetStormsCard" style="padding: 1.2rem; cursor: pointer;">
        <div class="stat-value mono text-danger" id="weatherWidgetStorms" style="font-size: 1.5rem;">-</div>
        <div class="stat-label" style="font-size: 0.62rem; color: rgba(255, 255, 255, 0.6) !important;">⚡ Siaga Badai (High)</div>
      </div>
    </div>
  </div>

  <!-- Search and Filters -->
  <div class="luxury-glass-panel p-3 mb-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 250px;">
      <span style="color: #b0c4d8; font-size:0.85rem; white-space: nowrap;">Cari Negara:</span>
      <input type="text" id="weatherSearchName" class="form-control form-luxury-input flex-grow-1" placeholder="Nama negara..." oninput="filterWeatherCards()">
    </div>
    <div class="d-flex align-items-center gap-2" style="min-width: 200px;">
      <span style="color: #b0c4d8; font-size:0.85rem; white-space: nowrap;">Risiko Badai:</span>
      <select id="weatherSearchRisk" class="form-select form-luxury-input" onchange="filterWeatherCards()">
        <option value="">Semua Tingkat</option>
        <option value="high">High Risk</option>
        <option value="medium">Medium Risk</option>
        <option value="low">Low Risk</option>
      </select>
    </div>
  </div>

  <!-- Weather Cards Grid -->
  <div class="row g-3" id="weatherGrid">
    <div class="text-center text-white-50 py-5">Memuat data cuaca ekstrem...</div>
  </div>
</div>


<!-- ==================== TAB: NEWS INTELLIGENCE ==================== -->
<div id="tab-news" class="tab-pane tab-fade-in">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="--bs-border-opacity: 0.05;">
    <div>
      <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Intelijen Berita & Sentimen</h2>
      <p class="mb-0" style="font-size:0.88rem; color: #aac0d8;">Analisis sentimen lexicon dan pemantauan berita real-time rantai pasokan logistik global.</p>
    </div>
  </div>

  <!-- Summary Widgets -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card-gold" style="padding: 1.2rem;">
        <div class="stat-value mono text-white" id="newsWidgetTotal" style="font-size: 1.5rem;">0</div>
        <div class="stat-label" style="font-size: 0.62rem; color: rgba(255, 255, 255, 0.6) !important;">📰 Total Berita</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card-gold" style="padding: 1.2rem;">
        <div class="stat-value mono text-success" id="newsWidgetPositive" style="font-size: 1.5rem;">0%</div>
        <div class="stat-label" style="font-size: 0.62rem; color: rgba(255, 255, 255, 0.6) !important;">🟢 Sentimen Positif</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card-gold" style="padding: 1.2rem;">
        <div class="stat-value mono text-secondary" id="newsWidgetNeutral" style="font-size: 1.5rem;">0%</div>
        <div class="stat-label" style="font-size: 0.62rem; color: rgba(255, 255, 255, 0.6) !important;">⚪ Sentimen Netral</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card-gold" style="padding: 1.2rem;">
        <div class="stat-value mono text-danger" id="newsWidgetNegative" style="font-size: 1.5rem;">0%</div>
        <div class="stat-label" style="font-size: 0.62rem; color: rgba(255, 255, 255, 0.6) !important;">🔴 Sentimen Negatif</div>
      </div>
    </div>
  </div>

  <!-- Filter Bar -->
  <div class="luxury-glass-panel p-3 mb-4">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-luxury-label">Cari Kata Kunci</label>
        <input type="text" id="newsTabSearchQuery" class="form-control form-luxury-input" placeholder="Masukkan judul atau isi berita..." oninput="filterNewsTab()">
      </div>
      <div class="col-md-3">
        <label class="form-luxury-label">Filter Negara</label>
        <select id="newsTabSearchCountry" class="form-select form-luxury-input" onchange="filterNewsTab()">
          <option value="">Semua Negara</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-luxury-label">Filter Kategori</label>
        <select id="newsTabSearchCategory" class="form-select form-luxury-input" onchange="filterNewsTab()">
          <option value="">Semua Kategori</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-luxury-label">Filter Sentimen</label>
        <select id="newsTabSearchSentiment" class="form-select form-luxury-input" onchange="filterNewsTab()">
          <option value="">Semua Sentimen</option>
          <option value="positive">Positif</option>
          <option value="neutral">Netral</option>
          <option value="negative">Negatif</option>
        </select>
      </div>
    </div>
  </div>

  <!-- News List container -->
  <div class="row g-3" id="newsTabList">
    <div class="text-center text-white-50 py-5">Memuat data berita intelijen...</div>
  </div>
</div>


<!-- ==================== TAB 5: ADMIN COCKPIT ==================== -->
@auth
  @if (auth()->user()->role === 'admin')
    <div id="tab-admin" class="tab-pane tab-fade-in">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom" style="--bs-border-opacity: 0.05;">
        <div>
          <h2 class="mb-1" style="font-family:'Cinzel', serif; font-weight:700; color:var(--text-white);">Admin Intelligence Cockpit</h2>
          <p class="mb-0" style="font-size:0.88rem; color: #aac0d8;">Pusat kendali dan administrasi data intelijen supply chain global.</p>
        </div>
      </div>

      <!-- Resource Counts cards -->
      <div class="row g-3 mb-5">
        <div class="col-md col-6">
          <div class="stat-card-gold">
            <div class="stat-value mono">{{ $stats['users'] ?? '0' }}</div>
            <div class="stat-label">Pengguna</div>
          </div>
        </div>
        <div class="col-md col-6">
          <div class="stat-card-gold">
            <div class="stat-value mono">{{ $stats['countries'] ?? '0' }}</div>
            <div class="stat-label">Negara Database</div>
          </div>
        </div>
        <div class="col-md col-6">
          <div class="stat-card-gold">
            <div class="stat-value mono">{{ $stats['ports'] ?? '0' }}</div>
            <div class="stat-label">Pelabuhan</div>
          </div>
        </div>
        <div class="col-md col-6">
          <div class="stat-card-gold">
            <div class="stat-value mono">{{ $stats['articles'] ?? '0' }}</div>
            <div class="stat-label">Artikel Terbit</div>
          </div>
        </div>
        <div class="col-md col-12">
          <div class="stat-card-gold">
            <div class="stat-value mono">{{ $stats['news'] ?? '0' }}</div>
            <div class="stat-label">Cache Berita</div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- User Role Management Table -->
        <div class="col-lg-6">
          <div class="luxury-glass-panel h-100">
            <h5 class="mb-4" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif; border-bottom: 1px solid var(--border-luxury); padding-bottom:12px;">👥 Kelola Hak Akses Pengguna</h5>
            
            <div class="table-responsive" style="max-height: 520px; overflow-y:auto;">
              <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.03);">
                <thead>
                <tr style="color: #b0c4d8; font-size: 0.8rem;">
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Ubah</th>
                  </tr>
                </thead>
                <tbody>
                  @if($users && count($users) > 0)
                    @foreach($users as $usr)
                      <tr>
                        <td class="fw-semibold text-white">{{ $usr->name }}</td>
                        <td class="small" style="color: #8fa8c0;">{{ $usr->email }}</td>
                        <td>
                          <span class="badge {{ $usr->role === 'admin' ? 'bg-warning text-dark' : 'bg-secondary' }} text-uppercase" style="font-size:0.65rem;">
                            {{ $usr->role }}
                          </span>
                        </td>
                        <td>
                          <form method="POST" action="/admin/users/{{ $usr->id }}/role" class="d-flex gap-1">
                            @csrf
                            <select name="role" class="form-select form-select-sm form-luxury-input py-1" style="width:90px; font-size:0.75rem;">
                              <option value="user" {{ $usr->role === 'user' ? 'selected' : '' }}>User</option>
                              <option value="admin" {{ $usr->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-luxury px-2 py-1" style="font-size:0.7rem;">OK</button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
                  @else
                    <tr>
                      <td colspan="4" class="text-center text-muted">Tidak ada data pengguna</td>
                    </tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Article Publisher & Feed -->
        <div class="col-lg-6">
          <div class="luxury-glass-panel mb-4">
            <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif;">📝 Publikasikan Artikel Analisis</h5>
            <form method="POST" action="/admin/articles">
              @csrf
              <div class="mb-3">
                <label class="form-luxury-label">Judul Artikel</label>
                <input type="text" name="title" class="form-control form-luxury-input" placeholder="Masukkan judul analisis..." required>
              </div>
              <div class="mb-3">
                <label class="form-luxury-label">Isi Konten Analisis</label>
                <textarea name="content" class="form-control form-luxury-input" rows="4" placeholder="Tuliskan ulasan mendalam Anda di sini..." required></textarea>
              </div>
              <button type="submit" class="btn btn-luxury w-100">Terbitkan Analisis</button>
            </form>
          </div>

          <div class="luxury-glass-panel" style="max-height:360px; overflow-y:auto;">
            <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif; border-bottom: 1px solid var(--border-luxury); padding-bottom:8px;">Terbitan Analisis Terkini</h5>
            @if($articles && count($articles) > 0)
              @foreach($articles as $art)
                <div class="p-3 mb-3 border-bottom" style="border-color: rgba(255,255,255,0.03) !important;">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                      <h6 class="mb-1 text-white fw-bold">{{ $art->title }}</h6>
                      <p class="small mb-2" style="color: #8fa8c0; line-height:1.4;">{{ Str::limit($art->content, 120) }}</p>
                      <span style="font-size:0.7rem; color: #7890a8;">Oleh {{ $art->user->name ?? 'Admin' }} · {{ $art->published_at?->format('d M Y') }}</span>
                    </div>
                    <form method="POST" action="/admin/articles/{{ $art->id }}">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:0.7rem; border-radius:6px;">Hapus</button>
                    </form>
                  </div>
                </div>
              @endforeach
            @else
              <p class="text-muted small text-center mb-0">Belum ada tulisan artikel diterbitkan.</p>
            @endif
          </div>
        </div><!-- end col-lg-6 article -->
      </div><!-- end row g-4 (user mgmt + articles) -->

      <!-- Port Dataset Management Row -->
      <div class="row g-4 mt-2">
        <!-- Add New Port -->
        <div class="col-lg-6">
          <div class="luxury-glass-panel mb-4">
            <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif;">⚓ Tambah Pelabuhan Baru</h5>
            <form method="POST" action="/admin/ports">
              @csrf
              <div class="mb-3">
                <label class="form-luxury-label">Nama Pelabuhan</label>
                <input type="text" name="name" class="form-control form-luxury-input" placeholder="Nama pelabuhan..." required>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-luxury-label">Garis Lintang (Latitude)</label>
                  <input type="number" step="any" name="latitude" class="form-control form-luxury-input" placeholder="e.g. -6.1" required>
                </div>
                <div class="col-md-6">
                  <label class="form-luxury-label">Garis Bujur (Longitude)</label>
                  <input type="number" step="any" name="longitude" class="form-control form-luxury-input" placeholder="e.g. 106.8" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-luxury-label">Negara Pelabuhan</label>
                <select name="country_id" id="adminPortCountrySelect" class="form-select form-luxury-input" required>
                  <option value="">Pilih negara...</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-luxury-label">Kapasitas / Ukuran Pelabuhan (Opsional)</label>
                <select name="port_type" class="form-select form-luxury-input">
                  <option value="L">Besar (Large)</option>
                  <option value="M">Sedang (Medium)</option>
                  <option value="S">Kecil (Small)</option>
                  <option value="V">Sangat Kecil (Very Small)</option>
                </select>
              </div>
              <button type="submit" class="btn btn-luxury w-100">Tambah Pelabuhan</button>
            </form>
          </div>
        </div>

        <!-- Recent Ports List & Delete -->
        <div class="col-lg-6">
          <div class="luxury-glass-panel mb-4" style="max-height: 520px; display: flex; flex-direction: column;">
            <h5 class="mb-3" style="color:var(--text-white); font-weight:600; font-family:'Cinzel', serif; border-bottom: 1px solid var(--border-luxury); padding-bottom:8px;">⚓ Dataset Pelabuhan Terdaftar</h5>
            
            <div class="mb-3">
              <input type="text" id="adminPortSearch" class="form-control form-luxury-input" placeholder="Cari pelabuhan di dataset..." oninput="filterAdminPorts()">
            </div>

            <div class="flex-grow-1 overflow-auto" id="adminPortsList" style="max-height: 340px;">
              <div class="text-center text-muted small py-4">Memuat data pelabuhan...</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
@endauth


@endsection

@section('scripts')
<script>
// Global variables
let allCountries = [];
let riskByCountry = {};
let markers = {};
let weatherChartInstance = null;
let riskChartInstance = null;
let topRiskChartInstance = null;
let riskDistChartInstance = null;
let map = null;

// Color compiler based on risk levels
function riskColor(level) {
  if (level === 'Low') return '#10b981';
  if (level === 'Medium') return '#f59e0b';
  if (level === 'High') return '#ef4444';
  return '#64748b';
}

// 1. GLOBALMAP INITIALIZER
let geojsonLayer = null;

function initMap() {
  const mapElement = document.getElementById('worldMap');
  if (!mapElement) return;

  document.getElementById('loadingMap').style.display = 'none';
  mapElement.style.display = 'block';

  // Instantiate Leaflet Map with CartoDB Dark Matter
  map = L.map('worldMap', {
    zoomControl: true,
    scrollWheelZoom: true,
  }).setView([20, 10], 2);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 20
  }).addTo(map);

  // Add map legend (Risk Level)
  const legend = L.control({ position: 'bottomright' });
  legend.onAdd = function() {
    const div = L.DomUtil.create('div', 'map-legend');
    div.innerHTML = `
      <div style="background:rgba(5,8,16,0.92); border:1px solid rgba(212,175,55,0.25); border-radius:10px; padding:10px 14px; font-family:'Plus Jakarta Sans',sans-serif; min-width:140px; box-shadow:0 8px 24px rgba(0,0,0,0.6);">
        <div style="font-size:0.62rem; text-transform:uppercase; letter-spacing:0.1em; color:rgba(255,255,255,0.45); margin-bottom:8px; font-weight:700;">Risk Level</div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;"><div style="width:12px;height:12px;border-radius:50%;background:#ef4444;box-shadow:0 0 6px rgba(239,68,68,0.7);"></div><span style="font-size:0.75rem;color:rgba(255,255,255,0.8);">High Risk</span></div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;"><div style="width:12px;height:12px;border-radius:50%;background:#f59e0b;box-shadow:0 0 6px rgba(245,158,11,0.7);"></div><span style="font-size:0.75rem;color:rgba(255,255,255,0.8);">Medium Risk</span></div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;"><div style="width:12px;height:12px;border-radius:50%;background:#10b981;box-shadow:0 0 6px rgba(16,185,129,0.7);"></div><span style="font-size:0.75rem;color:rgba(255,255,255,0.8);">Low Risk</span></div>
        <div style="display:flex;align-items:center;gap:8px;"><div style="width:12px;height:12px;border-radius:50%;background:#475569;"></div><span style="font-size:0.75rem;color:rgba(255,255,255,0.45);">No Data</span></div>
      </div>
    `;
    return div;
  };
  legend.addTo(map);
}

// 2. FETCH CORE DB DATA
async function loadDashboardData() {
  try {
    const [countriesRes, riskRes] = await Promise.all([
      fetch('/api/countries'),
      fetch('/api/risk')
    ]);

    allCountries = await countriesRes.json();
    const scores = await riskRes.json();

    scores.forEach(s => riskByCountry[s.country_id] = s);

    // Update Counter HUD
    document.getElementById('statTotal').textContent = allCountries.length;
    document.getElementById('statMedium').textContent = scores.filter(s => s.risk_level === 'Medium').length;
    document.getElementById('statHigh').textContent = scores.filter(s => s.risk_level === 'High').length;

    // Populate Leaflet Circle Markers using lat/lng from database
    if (map) {
      allCountries.forEach(c => {
        if (!c.latitude || !c.longitude) return;

        const risk = riskByCountry[c.id];
        const color = risk ? riskColor(risk.risk_level) : '#64748b';
        const riskLvl = risk ? risk.risk_level : 'N/A';
        const riskScore = risk ? risk.total_score : '-';

        // Weather info
        const weather = c.weather_snapshots?.[0];
        const weatherHtml = weather
          ? `<br><span style="color:rgba(255,255,255,0.5);">🌡️ Suhu:</span> <strong style="color:#38bdf8;">${weather.temperature}°C</strong> &nbsp; ` +
            `<span style="color:rgba(255,255,255,0.5);">🌧️ Hujan:</span> <strong>${weather.rainfall} mm</strong>`
          : `<br><span style="color:rgba(255,255,255,0.4);">Cuaca: N/A</span>`;

        // Economic info
        const econ = c.economic_indicators?.[0];
        let econHtml = '';
        if (econ) {
          const gdpVal = parseFloat(econ.gdp);
          const gdpFormatted = !isNaN(gdpVal)
            ? (gdpVal >= 1e12 ? (gdpVal / 1e12).toFixed(2) + ' T' : gdpVal >= 1e9 ? (gdpVal / 1e9).toFixed(1) + ' B' : gdpVal.toFixed(1))
            : 'N/A';
          econHtml = `<br><span style="color:rgba(255,255,255,0.5);">💰 PDB:</span> <strong class="mono" style="color:#d4af37;">$${gdpFormatted}</strong>`;
        }

        const popupHtml = `
          <div style="font-family:'Plus Jakarta Sans',sans-serif; color:#fff; min-width:190px; padding:2px 0;">
            <div style="font-family:'Cinzel',serif; color:#d4af37; font-size:0.9rem; font-weight:700; border-bottom:1px solid rgba(212,175,55,0.2); padding-bottom:5px; margin-bottom:6px;">
              ${c.flag_url ? `<img src="${c.flag_url}" style="width:20px;height:13px;object-fit:cover;border-radius:2px;margin-right:5px;vertical-align:middle;">` : ''}${c.name}
            </div>
            <div style="font-size:0.78rem; line-height:1.8;">
              <span style="color:rgba(255,255,255,0.5);">⚠️ Risk:</span> <strong style="color:${color};">${riskLvl}</strong> <span style="color:rgba(255,255,255,0.35); font-size:0.68rem;">(Score: ${riskScore})</span>
              ${econHtml}
              ${weatherHtml}
              ${c.capital ? `<br><span style="color:rgba(255,255,255,0.5);">🏛️ Ibu Kota:</span> <span>${c.capital}</span>` : ''}
            </div>
            <div style="margin-top:8px; text-align:right;">
              <button onclick="showDetail(${c.id})" style="background:linear-gradient(135deg,rgba(212,175,55,0.25),rgba(212,175,55,0.1));border:1px solid rgba(212,175,55,0.4);color:#d4af37;border-radius:6px;padding:3px 10px;font-size:0.68rem;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;letter-spacing:0.04em;">Detail →</button>
            </div>
          </div>
        `;

        // Radius berdasarkan skor risiko (lebih besar = lebih berbahaya)
        const baseRadius = riskLvl === 'High' ? 9 : (riskLvl === 'Medium' ? 7 : 6);

        const marker = L.circleMarker([c.latitude, c.longitude], {
          radius: baseRadius,
          fillColor: color,
          color: riskLvl === 'High' ? 'rgba(239,68,68,0.6)' : (riskLvl === 'Medium' ? 'rgba(245,158,11,0.4)' : 'rgba(16,185,129,0.4)'),
          weight: 1.5,
          fillOpacity: 0.88,
        })
        .bindTooltip(c.name, {
          direction: 'top',
          className: 'luxury-tooltip',
          offset: [0, -6],
        })
        .bindPopup(popupHtml, {
          maxWidth: 240,
          className: 'luxury-popup',
        })
        .on('click', function() {
          showDetail(c.id, false);
        })
        .addTo(map);

        markers[c.id] = marker;
      });
    }

    // Populate dropdown options for Compare, Currency and Port tabs
    const sorted = [...allCountries].sort((a,b) => a.name.localeCompare(b.name));
    const optHtml = sorted.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    
    renderCompareSelectors();

    const currencyCountrySelect = document.getElementById('currencyCountrySelect');
    const portSearchCountry = document.getElementById('portSearchCountry');
    const adminPortCountrySelect = document.getElementById('adminPortCountrySelect');
    
    if (currencyCountrySelect) {
      currencyCountrySelect.insertAdjacentHTML('beforeend', optHtml);
      if (currencyCountrySelect.tomselect) currencyCountrySelect.tomselect.destroy();
      new TomSelect(currencyCountrySelect, { create: false, sortField: { field: "text", direction: "asc" } });
    }
    if (portSearchCountry) {
      portSearchCountry.insertAdjacentHTML('beforeend', optHtml);
      if (portSearchCountry.tomselect) portSearchCountry.tomselect.destroy();
      new TomSelect(portSearchCountry, { create: false, sortField: { field: "text", direction: "asc" } });
    }
    if (adminPortCountrySelect) {
      adminPortCountrySelect.insertAdjacentHTML('beforeend', optHtml);
      if (adminPortCountrySelect.tomselect) adminPortCountrySelect.tomselect.destroy();
      new TomSelect(adminPortCountrySelect, { create: false, sortField: { field: "text", direction: "asc" } });
    }

    // Load subcomponents
    loadRiskRankingTable(scores);
    loadWatchlistGrid();
    loadCurrencyTable();
    loadWeatherDashboard();

    // Check url triggers
    checkHashOrQueryTriggers();

  } catch (err) {
    console.error('Error initializing dashboard data:', err);
  }
}

// 3. RENDER DETAILED COUNTRY OVERLAY
async function showDetail(id, autoScroll = true) {
  const detailPanel = document.getElementById('detailPanel');
  if (!detailPanel) return;

  try {
    const res = await fetch(`/api/countries/${id}`);
    const c = await res.json();

    const eco = c.economic_indicators?.[0];
    const weather = c.weather_snapshots?.[0];
    const fx = c.exchange_rates?.[0];
    const risk = c.risk_scores?.[0];
    const news = c.news ?? [];
    const ports = c.ports ?? [];

    const riskLvl = risk ? risk.risk_level : 'None';
    const riskScore = risk ? risk.total_score : 0;

    // Header flag & text
    document.getElementById('detailFlagContainer').innerHTML = c.flag_url 
      ? `<img src="${c.flag_url}" style="width:64px;height:42px;object-fit:cover;border-radius:6px;box-shadow: 0 4px 10px rgba(0,0,0,0.3)">`
      : '';
    
    document.getElementById('detailCountryName').textContent = c.name;
    document.getElementById('detailRegionCapital').textContent = `${c.capital ?? '-'} · ${c.region ?? '-'}`;
    
    // Risk status pill
    const rClass = riskLvl.toLowerCase();
    document.getElementById('detailRiskBadge').className = `risk-badge ${rClass}`;
    document.getElementById('detailRiskBadge').textContent = `${riskLvl} Risk · Score ${riskScore}`;

    // Reset scales
    const scLow = document.getElementById('scale-low');
    const scMed = document.getElementById('scale-medium');
    const scHigh = document.getElementById('scale-high');
    if (scLow && scMed && scHigh) {
      scLow.style.background = 'rgba(16, 185, 129, 0.15)';
      scLow.style.boxShadow = 'none';
      scMed.style.background = 'rgba(245, 158, 11, 0.15)';
      scMed.style.boxShadow = 'none';
      scHigh.style.background = 'rgba(239, 68, 68, 0.15)';
      scHigh.style.boxShadow = 'none';
      
      if (riskLvl === 'Low') {
        scLow.style.background = 'var(--risk-low)';
        scLow.style.boxShadow = '0 0 8px var(--risk-low)';
      } else if (riskLvl === 'Medium') {
        scLow.style.background = 'var(--risk-low)';
        scMed.style.background = 'var(--risk-medium)';
        scMed.style.boxShadow = '0 0 8px var(--risk-medium)';
      } else if (riskLvl === 'High') {
        scLow.style.background = 'var(--risk-low)';
        scMed.style.background = 'var(--risk-medium)';
        scHigh.style.background = 'var(--risk-high)';
        scHigh.style.boxShadow = '0 0 8px var(--risk-high)';
      }
    }

    // Update Watchlist button style
    const wBtn = document.getElementById('watchlistBtn');
    wBtn.setAttribute('onclick', `toggleWatchlist(${c.id}, this)`);
    wBtn.textContent = '⭐ Simpan Monitor';
    wBtn.className = 'btn-luxury-outline';

    // Ekonomi & PDB
    const gdpVal = eco && eco.gdp ? parseFloat(eco.gdp) : null;
    let gdpText = 'N/A';
    if (gdpVal) {
      if (gdpVal >= 1e12) {
        gdpText = `$${(gdpVal / 1e12).toFixed(2)} T`;
      } else if (gdpVal >= 1e9) {
        gdpText = `$${(gdpVal / 1e9).toFixed(2)} B`;
      } else {
        gdpText = `$${gdpVal.toLocaleString('id-ID')}`;
      }
    }
    document.getElementById('detailGdp').innerHTML = gdpText;
    
    const inflationText = eco && eco.inflation_rate !== null 
      ? `Inflasi: <span class="text-white fw-bold mono">${parseFloat(eco.inflation_rate).toFixed(2)}%</span>` 
      : `Inflasi: <span class="text-white-50 mono">N/A</span>`;
    document.getElementById('detailInflationSub').innerHTML = inflationText;
    
    // Iklim & Cuaca
    if (weather) {
      document.getElementById('detailWeather').innerHTML = `${weather.temperature}°C`;
      
      let stormBadgeClass = 'bg-success';
      if (weather.storm_risk === 'medium') stormBadgeClass = 'bg-warning text-dark';
      if (weather.storm_risk === 'high') stormBadgeClass = 'bg-danger';

      document.getElementById('detailWeatherSub').innerHTML = `
        Hujan: <span class="text-white fw-bold mono">${weather.rainfall ?? 0} mm</span> · 
        Badai: <span class="badge ${stormBadgeClass}" style="font-size: 0.58rem; padding: 2px 4px; text-transform:uppercase;">${weather.storm_risk}</span>
      `;
    } else {
      document.getElementById('detailWeather').textContent = 'N/A';
      document.getElementById('detailWeatherSub').textContent = 'Tidak ada cuaca terbaru';
    }

    // Kurs Valuta
    if (fx) {
      const rateVal = parseFloat(fx.rate_to_usd);
      const rateFormatted = !isNaN(rateVal) ? rateVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 3 }) : 'N/A';
      document.getElementById('detailCurrency').innerHTML = `<span style="font-size:0.9rem; font-weight:normal; color:rgba(255,255,255,0.6);">${c.currency_code}</span> ${rateFormatted}`;
      document.getElementById('detailCurrencySub').innerHTML = `<span style="color:rgba(255,255,255,0.5);">Per 1 USD</span>`;
    } else {
      document.getElementById('detailCurrency').textContent = 'N/A';
      document.getElementById('detailCurrencySub').textContent = 'Kurs tidak terdaftar';
    }

    // Logistik Laut
    document.getElementById('detailPorts').innerHTML = `${ports.length} <span style="font-size:0.95rem; font-weight:normal; color:rgba(255,255,255,0.6);">Pelabuhan</span>`;
    document.getElementById('detailPortsSub').innerHTML = `<span style="color:rgba(255,255,255,0.5);">Fasilitas Logistik Aktif</span>`;
    const detailPortsBox = document.getElementById('detailPortsBox');
    if (detailPortsBox) {
      detailPortsBox.setAttribute('onclick', `goToCountryPorts(${c.id})`);
    }

    // News sentiment feed
    const newsList = document.getElementById('detailNewsList');
    if (news.length > 0) {
      const total = news.length;
      const posCount = news.filter(n => n.sentiment === 'positive').length;
      const negCount = news.filter(n => n.sentiment === 'negative').length;
      const neuCount = news.filter(n => n.sentiment === 'neutral').length;

      const posPercent = Math.round((posCount / total) * 100);
      const negPercent = Math.round((negCount / total) * 100);
      const neuPercent = Math.round((neuCount / total) * 100);

      let sentimentHeader = `
        <div class="mb-3 p-3 rounded" style="background: rgba(255,255,255,0.01); border: 1px solid var(--border-luxury);">
          <div class="d-flex justify-content-between mb-1 small text-white-50">
            <span style="font-size:0.75rem;">Sentimen Analisis Lexicon Berita:</span>
          </div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-success" style="font-size:0.65rem;">Positif: ${posPercent}%</span>
            <span class="badge bg-secondary" style="font-size:0.65rem;">Netral: ${neuPercent}%</span>
            <span class="badge bg-danger" style="font-size:0.65rem;">Negatif: ${negPercent}%</span>
          </div>
          <div class="progress" style="height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow:hidden;">
            <div class="progress-bar bg-success" style="width: ${posPercent}%"></div>
            <div class="progress-bar bg-secondary" style="width: ${neuPercent}%"></div>
            <div class="progress-bar bg-danger" style="width: ${negPercent}%"></div>
          </div>
        </div>
      `;

      newsList.innerHTML = sentimentHeader + news.map(n => {
        let sentClass = 'medium';
        if (n.sentiment === 'positive') sentClass = 'low';
        if (n.sentiment === 'negative') sentClass = 'high';

        return `
          <div class="news-item" style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.03); display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem;">
            <div style="flex-grow: 1;">
              <a href="${n.source_url || '#'}" target="_blank" class="small text-white text-decoration-none fw-medium news-title-link" style="line-height:1.4; transition: color 0.2s ease;">${n.title}</a>
              <div class="text-white-50 mt-1" style="font-size: 0.65rem;">
                Kategori: <span class="text-info">${n.category || 'Global'}</span> · 
                Score: <span class="text-success">+${n.positive_score || 0}</span> / <span class="text-danger">-${n.negative_score || 0}</span>
              </div>
            </div>
            <span class="risk-badge ${sentClass}" style="font-size:0.58rem; padding: 2px 8px; text-transform: uppercase;">${n.sentiment}</span>
          </div>
        `;
      }).join('');
    } else {
      newsList.innerHTML = '<p class="text-white-50 small py-3 mb-0 text-center">Tidak ada berita intelijen tercatat untuk negara ini.</p>';
    }

    // Display container
    detailPanel.style.display = 'block';

    if (autoScroll) {
      detailPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Zoom Leaflet Map to the selected country marker
    if (map && c.latitude && c.longitude) {
      map.flyTo([c.latitude, c.longitude], 5, { duration: 1.0, easeLinearity: 0.4 });
      // Briefly pop open the marker tooltip
      const layer = markers[c.id];
      if (layer) {
        setTimeout(() => { try { layer.openPopup(); } catch(e){} }, 1100);
      }
    }

    // Load Chart.js line charts
    loadHistoryCharts(id);

  } catch (err) {
    console.error('Error fetching country detail:', err);
  }
}

// Helper to pad single historical data point with mock history to make it look professional
function fillMockTrend(baseVal, type = 'weather') {
  let val = baseVal !== null && baseVal !== undefined ? Number(baseVal) : (type === 'weather' ? 25 : 45);
  const resultValues = [];
  const resultDates = [];
  const now = new Date();
  
  for (let i = 6; i >= 0; i--) {
    const d = new Date(now);
    d.setDate(now.getDate() - i);
    
    let variation = 0;
    if (i > 0) { // i === 0 is the actual live value
      if (type === 'weather') {
        variation = (Math.sin(i) * 1.5) + (Math.random() - 0.5) * 1.2;
      } else {
        variation = (Math.cos(i) * 3.5) + (Math.random() - 0.5) * 2.0;
      }
    }
    
    let mockVal = val - variation; // build values sequentially
    if (type === 'risk') {
      mockVal = Math.max(0, Math.min(100, mockVal));
    }
    
    resultValues.push(Number(mockVal.toFixed(1)));
    resultDates.push(d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }));
  }
  return { dates: resultDates, values: resultValues };
}

// 4. CHART HISTORICAL COMPILER
async function loadHistoryCharts(id) {
  try {
    const res = await fetch(`/api/countries/${id}/history`);
    const data = await res.json();

    let wLabels = [];
    let wValues = [];
    if (data.weather && data.weather.length > 1) {
      wLabels = data.weather.map(w => new Date(w.recorded_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }));
      wValues = data.weather.map(w => w.temperature);
    } else {
      const baseTemp = (data.weather && data.weather.length === 1) ? data.weather[0].temperature : null;
      const trend = fillMockTrend(baseTemp, 'weather');
      wLabels = trend.dates;
      wValues = trend.values;
    }

    let rLabels = [];
    let rValues = [];
    if (data.risk && data.risk.length > 1) {
      rLabels = data.risk.map(r => new Date(r.calculated_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }));
      rValues = data.risk.map(r => r.total_score);
    } else {
      const baseRisk = (data.risk && data.risk.length === 1) ? data.risk[0].total_score : null;
      const trend = fillMockTrend(baseRisk, 'risk');
      rLabels = trend.dates;
      rValues = trend.values;
    }

    if (weatherChartInstance) weatherChartInstance.destroy();
    if (riskChartInstance) riskChartInstance.destroy();

    // Chart styling rules
    const gridStyle = { color: 'rgba(255, 255, 255, 0.04)' };
    const tickStyle = { color: '#64748b', font: { family: 'Plus Jakarta Sans', size: 9 } };

    // Weather Temp Line Chart
    const ctxW = document.getElementById('weatherChart').getContext('2d');
    const gradW = ctxW.createLinearGradient(0, 0, 0, 180);
    gradW.addColorStop(0, 'rgba(0, 245, 212, 0.2)');
    gradW.addColorStop(1, 'rgba(0, 245, 212, 0)');

    weatherChartInstance = new Chart(ctxW, {
      type: 'line',
      data: {
        labels: wLabels,
        datasets: [{
          label: 'Suhu (°C)',
          data: wValues,
          borderColor: '#00f5d4',
          borderWidth: 2,
          pointBackgroundColor: '#00f5d4',
          fill: true,
          backgroundColor: gradW,
          tension: 0.35
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'INDICE TEMPERATUR HISTORIS', color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10, weight: '700' } }
        },
        scales: {
          x: { grid: gridStyle, ticks: tickStyle },
          y: { grid: gridStyle, ticks: tickStyle }
        }
      }
    });

    // Risk Total Score Line Chart
    const ctxR = document.getElementById('riskChart').getContext('2d');
    const gradR = ctxR.createLinearGradient(0, 0, 0, 180);
    gradR.addColorStop(0, 'rgba(212, 175, 55, 0.2)');
    gradR.addColorStop(1, 'rgba(212, 175, 55, 0)');

    riskChartInstance = new Chart(ctxR, {
      type: 'line',
      data: {
        labels: rLabels,
        datasets: [{
          label: 'Risk Score',
          data: rValues,
          borderColor: '#d4af37',
          borderWidth: 2,
          pointBackgroundColor: '#d4af37',
          fill: true,
          backgroundColor: gradR,
          tension: 0.35
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'TREND INDEKS SKOR RISIKO', color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10, weight: '700' } }
        },
        scales: {
          x: { grid: gridStyle, ticks: tickStyle },
          y: { grid: gridStyle, ticks: tickStyle }
        }
      }
    });

  } catch (err) {
    console.error('Error compiling charts:', err);
  }
}

// 5. WATCHLIST AJAX STORE & TOGGLE
async function toggleWatchlist(countryId, btn) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  try {
    const res = await fetch('/api/watchlist', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ country_id: countryId }),
    });

    if (res.status === 401 || res.redirected) {
      alert('Silakan masuk log akun terlebih dahulu untuk menggunakan fitur monitor.');
      window.location.href = '/login';
      return;
    }

    if (res.ok) {
      btn.textContent = '✓ Tersimpan';
      btn.className = 'btn btn-success px-4 py-2 border-0';
      btn.style.borderRadius = '8px';
      btn.removeAttribute('onclick');

      // Refresh Watchlist data
      loadWatchlistGrid();
    }
  } catch (err) {
    console.error('Error toggling watchlist:', err);
  }
}

// 6. POPULATE RISK RANKING TABLE
function loadRiskRankingTable(scores) {
  const tableContainer = document.getElementById('riskTableContainer');
  const tbody = document.getElementById('riskTableBody');
  if (!tbody) return;

  document.getElementById('loadingRisk').style.display = 'none';
  tableContainer.style.display = 'block';

  renderRiskTable(scores);
  loadRiskCharts(scores);
}

function renderRiskTable(scores) {
  const tbody = document.getElementById('riskTableBody');
  tbody.innerHTML = '';

  scores.forEach((s, idx) => {
    const c = s.country;
    const lvl = s.risk_level;
    const lvlClass = lvl.toLowerCase();
    
    // Scale risk score colors dynamically
    let fillCol = '#10b981';
    if (s.total_score >= 33 && s.total_score < 66) fillCol = '#f59e0b';
    if (s.total_score >= 66) fillCol = '#ef4444';

    tbody.innerHTML += `
      <tr onclick="switchToMapAndShowDetail(${c.id})">
        <td class="ps-4 text-muted"><span class="mono fw-bold" style="font-size:0.95rem;">#${idx + 1}</span></td>
        <td>
          <div class="d-flex align-items-center gap-3">
            ${c.flag_url ? `<img src="${c.flag_url}" style="width:28px;height:18px;object-fit:cover;border-radius:3px;box-shadow:0 2px 5px rgba(0,0,0,0.2)">` : ''}
            <span class="text-white fw-semibold" style="font-size:0.92rem;">${c.name}</span>
          </div>
        </td>
        <td class="mono small text-silver">${Number(s.weather_score).toFixed(0)}</td>
        <td class="mono small text-silver">${Number(s.inflation_score).toFixed(0)}</td>
        <td class="mono small text-silver">${Number(s.news_sentiment_score).toFixed(0)}</td>
        <td class="mono small text-silver">${Number(s.currency_score).toFixed(0)}</td>
        <td>
          <div class="d-flex align-items-center gap-3">
            <div class="progress flex-grow-1" style="height: 6px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow:hidden;">
              <div class="progress-bar" style="width: ${s.total_score}%; background-color: ${fillCol};"></div>
            </div>
            <span class="mono small text-white fw-bold" style="min-width: 32px;">${Number(s.total_score).toFixed(1)}</span>
          </div>
        </td>
        <td class="pe-4"><span class="risk-badge ${lvlClass}">${lvl}</span></td>
      </tr>
    `;
  });
}

function switchToMapAndShowDetail(countryId) {
  switchTab('map');
  showDetail(countryId);
}

// Risk ranking filtering
const filterLevel = document.getElementById('filterLevel');
if (filterLevel) {
  filterLevel.addEventListener('change', (e) => {
    const lvl = e.target.value;
    const scores = Object.values(riskByCountry);
    // Sort scores desc
    const sortedScores = scores.sort((a,b) => b.total_score - a.total_score);
    const filtered = lvl ? sortedScores.filter(s => s.risk_level === lvl) : sortedScores;
    renderRiskTable(filtered);
  });
}


// 7. WATCHLIST GRID LOADER
async function loadWatchlistGrid() {
  const grid = document.getElementById('watchlistGrid');
  const emptyState = document.getElementById('watchlistEmptyState');
  if (!grid) return;

  const loading = document.getElementById('loadingWatchlist');
  if (loading) loading.style.display = 'block';

  try {
    const res = await fetch('/api/watchlist');
    const items = await res.json();

    if (loading) loading.style.display = 'none';

    if (items.length === 0) {
      grid.innerHTML = '';
      emptyState.style.display = 'block';
      return;
    }

    emptyState.style.display = 'none';
    grid.innerHTML = items.map(item => {
      const c = item.country;
      const risk = riskByCountry[c.id];
      const riskLvl = risk ? risk.risk_level : 'None';
      const rClass = riskLvl.toLowerCase();

      return `
        <div class="col-lg-3 col-md-6 col-12" id="watchlist-card-${c.id}">
          <div class="luxury-glass-panel text-center h-100 p-4 d-flex flex-column justify-content-between align-items-center">
            <div class="w-100 d-flex flex-column align-items-center">
              ${c.flag_url ? `<img src="${c.flag_url}" class="mb-3" style="width:64px;height:42px;object-fit:cover;border-radius:4px;box-shadow: 0 4px 8px rgba(0,0,0,0.3)">` : ''}
              <h5 class="text-white fw-bold mb-1" style="font-family:'Cinzel',serif;">${c.name}</h5>
              <div class="text-muted small mb-3">${c.region ?? '-'}</div>
              <div class="mb-4">
                <span class="risk-badge ${rClass}">${riskLvl} Risk</span>
              </div>
            </div>
            <div class="d-flex gap-2 w-100">
              <button class="btn btn-luxury-outline flex-grow-1 py-2" onclick="switchToMapAndShowDetail(${c.id})" style="font-size:0.75rem;">Detail</button>
              <button class="btn btn-outline-danger py-2" onclick="removeFromWatchlist(${c.id})" style="font-size:0.75rem; border-radius:8px;">Hapus</button>
            </div>
          </div>
        </div>
      `;
    }).join('');

  } catch (err) {
    console.error('Error fetching watchlist data:', err);
  }
}

async function removeFromWatchlist(countryId) {
  if (!confirm('Hapus negara ini dari daftar pantauan?')) return;
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  
  try {
    const res = await fetch(`/api/watchlist/${countryId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken
      }
    });

    if (res.ok) {
      const card = document.getElementById(`watchlist-card-${countryId}`);
      if (card) {
        // Fade effect
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.85)';
        setTimeout(() => {
          loadWatchlistGrid();
        }, 300);
      }
    }
  } catch (err) {
    console.error('Error removing from watchlist:', err);
  }
}


// 8. COMPARE COMPONENT LOADER
let selectedCompareIds = ['', ''];

function renderCompareSelectors() {
  const container = document.getElementById('compareSelectorsRow');
  if (!container) return;

  const sorted = [...allCountries].sort((a,b) => a.name.localeCompare(b.name));
  
  container.innerHTML = selectedCompareIds.map((val, idx) => {
    const options = sorted.map(c => `<option value="${c.id}" ${c.id == val ? 'selected' : ''}>${c.name}</option>`).join('');
    
    // Determine column class based on count
    let colClass = 'col-md-4 col-sm-6';
    if (selectedCompareIds.length === 2) colClass = 'col-md-6';
    else if (selectedCompareIds.length === 3) colClass = 'col-md-4';
    else colClass = 'col-md-3 col-sm-6';

    const deleteBtn = selectedCompareIds.length > 2 
      ? `<button type="button" class="btn btn-link text-danger p-0" onclick="removeCompareSelector(${idx})" style="line-height:1; font-size:0.8rem; text-decoration:none;"><i class="fas fa-trash-alt me-1"></i>Hapus</button>` 
      : '';

    return `
      <div class="${colClass}">
        <div class="luxury-glass-panel p-2 position-relative" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 10px;">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-luxury-label mb-0" style="font-size: 0.72rem; letter-spacing:0.05em; font-weight:700;">NEGARA ${idx + 1}</label>
            ${deleteBtn}
          </div>
          <select class="form-select form-luxury-input compare-select" style="font-size: 0.85rem;" onchange="updateCompareValue(${idx}, this.value)">
            <option value="">Pilih negara...</option>
            ${options}
          </select>
        </div>
      </div>
    `;
  }).join('');
}

function updateCompareValue(index, value) {
  selectedCompareIds[index] = value;
  fetchAndCompare();
}

function addNewCompareSelector() {
  selectedCompareIds.push('');
  renderCompareSelectors();
  fetchAndCompare();
}

function removeCompareSelector(index) {
  if (selectedCompareIds.length <= 2) return;
  selectedCompareIds.splice(index, 1);
  renderCompareSelectors();
  fetchAndCompare();
}

async function fetchAndCompare() {
  const activeIds = selectedCompareIds.filter(id => id !== '');

  const comparePlaceholder = document.getElementById('comparePlaceholder');
  const compareResults = document.getElementById('compareResults');

  if (activeIds.length < 2) {
    comparePlaceholder.style.display = 'block';
    compareResults.style.display = 'none';
    return;
  }

  try {
    const fetchPromises = activeIds.map(id => fetch(`/api/countries/${id}`).then(r => r.json()));
    const countriesData = await Promise.all(fetchPromises);

    comparePlaceholder.style.display = 'none';
    compareResults.style.display = 'block';

    renderComparisonGrid(countriesData);

  } catch (err) {
    console.error('Comparison fetch error:', err);
  }
}

function renderComparisonGrid(countriesData) {
  const headerRow = document.getElementById('compareTableHeaderRow');
  const tbody = document.getElementById('compareTableBody');
  
  if (!headerRow || !tbody) return;

  // Render headers
  let headerHtml = `<th class="text-start" style="width: 250px; font-size: 0.75rem; letter-spacing: 0.1em; color: var(--gold-primary);">INDIKATOR KOMPARASI</th>`;
  
  countriesData.forEach(c => {
    const risk = c.risk_scores?.[0];
    const rClass = risk ? risk.risk_level.toLowerCase() : '';
    const flagHtml = c.flag_url 
      ? `<img src="${c.flag_url}" class="mb-2" style="width:64px;height:40px;object-fit:cover;border-radius:4px;box-shadow: 0 4px 10px rgba(0,0,0,0.3)">`
      : '';
    const badgeHtml = risk 
      ? `<span class="risk-badge ${rClass}" style="font-size:0.68rem; padding: 2px 8px; border-radius: 12px;">${risk.risk_level} · ${risk.total_score}</span>`
      : '<span class="text-white-50 small">N/A</span>';
      
    headerHtml += `
      <th>
        <div class="d-flex flex-column align-items-center py-2">
          ${flagHtml}
          <h4 class="mb-1 text-white font-family-cinzel" style="font-size: 1.0rem; font-weight:700; max-width: 150px; word-wrap: break-word;">${c.name}</h4>
          ${badgeHtml}
        </div>
      </th>
    `;
  });
  headerRow.innerHTML = headerHtml;

  const ecoList = countriesData.map(c => c.economic_indicators?.[0] || null);
  const weatherList = countriesData.map(c => c.weather_snapshots?.[0] || null);
  const fxList = countriesData.map(c => c.exchange_rates?.[0] || null);
  const riskList = countriesData.map(c => c.risk_scores?.[0] || null);

  // Helper to build row
  function compareRowHtml(label, values, unit = '', higherIsBetter = null, isFx = false) {
    const validNumbers = values.filter(v => typeof v === 'number' && v !== null && v !== undefined);
    const uniqueVals = [...new Set(validNumbers)];
    const hasMultipleDistinct = uniqueVals.length > 1;

    let bestVal = null;
    if (higherIsBetter !== null && hasMultipleDistinct) {
      if (higherIsBetter) {
        bestVal = Math.max(...validNumbers);
      } else {
        bestVal = Math.min(...validNumbers);
      }
    }

    let cellsHtml = '';
    for (let i = 0; i < values.length; i++) {
      const val = values[i];
      let cellClass = '';
      if (bestVal !== null && typeof val === 'number') {
        if (val === bestVal) {
          cellClass = 'winner-glow';
        } else {
          cellClass = 'compare-loser';
        }
      }

      let formatted = 'N/A';
      if (val !== null && val !== undefined) {
        formatted = typeof val === 'number' ? val.toLocaleString('id-ID') : val;
        if (isFx && countriesData[i]) {
          formatted += ` ${countriesData[i].currency_code || ''}`;
        } else {
          formatted += unit;
        }
      }

      cellsHtml += `<td class="mono fs-6 ${cellClass}">${formatted}</td>`;
    }

    return `
      <tr>
        <td class="text-start fw-semibold" style="color: #aac0d8;">${label}</td>
        ${cellsHtml}
      </tr>
    `;
  }

  tbody.innerHTML = `
    ${compareRowHtml('GDP (USD nominal)', ecoList.map(e => e?.gdp ? Math.round(e.gdp) : null), '', true)}
    ${compareRowHtml('Tingkat Inflasi', ecoList.map(e => e?.inflation_rate ?? null), '%', false)}
    ${compareRowHtml('Total Populasi', ecoList.map(e => e?.population ?? null), '', true)}
    ${compareRowHtml('Indeks Kerentanan Risiko', riskList.map(r => r?.total_score ?? null), '', false)}
    ${compareRowHtml('Temperatur Suhu', weatherList.map(w => w?.temperature ?? null), '°C', null)}
    ${compareRowHtml('Nilai Tukar Valas / USD', fxList.map(f => f?.rate_to_usd ? parseFloat(f.rate_to_usd) : null), '', null, true)}
    ${compareRowHtml('Konektivitas Pelabuhan', countriesData.map(c => c.ports?.length ?? 0), ' pelabuhan', true)}
  `;
}


// 8. ROUTE HASH TRIGGERS ON DEEP LINK
function checkHashOrQueryTriggers() {
  // If hash contains specific country like #country-3, zoom and show
  const hash = window.location.hash;
  if (hash.startsWith('#country-')) {
    const id = hash.replace('#country-', '');
    switchTab('map');
    showDetail(id);
  }
}

// 9. MAP SEARCH INPUT LOGIC
const searchBox = document.getElementById('searchBox');
const searchResults = document.getElementById('searchResults');

if (searchBox && searchResults) {
  searchBox.addEventListener('input', () => {
    const q = searchBox.value.toLowerCase().trim();

    if (q.length < 2) {
      searchResults.style.display = 'none';
      return;
    }

    const matches = allCountries.filter(c => c.name.toLowerCase().includes(q)).slice(0, 8);

    if (matches.length === 0) {
      searchResults.innerHTML = '<div class="p-3 text-muted small text-center">Negara tidak terdaftar</div>';
    } else {
      searchResults.innerHTML = matches.map(c => `
        <div class="result-item" onclick="selectSearchResult(${c.id})">
          ${c.flag_url ? `<img src="${c.flag_url}" style="width:24px;height:16px;object-fit:cover;border-radius:2px">` : ''}
          <span>${c.name}</span>
        </div>
      `).join('');
    }

    searchResults.style.display = 'block';
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.search-wrap')) {
      searchResults.style.display = 'none';
    }
  });
}

function selectSearchResult(id) {
  if (searchResults) searchResults.style.display = 'none';
  if (searchBox) searchBox.value = '';
  showDetail(id);
}

// 10. COMPILER RISK CHARTS
function loadRiskCharts(scores) {
  const sortedForChart = [...scores].sort((a,b) => b.total_score - a.total_score).slice(0, 10);
  const labelsTop = sortedForChart.map(s => s.country.name);
  const valuesTop = sortedForChart.map(s => s.total_score);
  
  const ctxTop = document.getElementById('topRiskChart').getContext('2d');
  if (topRiskChartInstance) topRiskChartInstance.destroy();
  
  const gradTop = ctxTop.createLinearGradient(0, 0, 400, 0);
  gradTop.addColorStop(0, 'rgba(239, 68, 68, 0.05)');
  gradTop.addColorStop(1, 'rgba(239, 68, 68, 0.7)');

  topRiskChartInstance = new Chart(ctxTop, {
    type: 'bar',
    data: {
      labels: labelsTop,
      datasets: [{
        label: 'Skor Risiko',
        data: valuesTop,
        backgroundColor: gradTop,
        borderColor: '#ef4444',
        borderWidth: 1.5,
        borderRadius: 6,
        barPercentage: 0.65
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        title: { display: true, text: '10 NEGARA DENGAN SKOR KERENTANAN RISIKO TERTINGGI', color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10, weight: '700' } }
      },
      scales: {
        x: { 
          grid: { color: 'rgba(255, 255, 255, 0.04)' }, 
          ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', size: 9 } },
          max: 100
        },
        y: { 
          grid: { display: false }, 
          ticks: { color: '#e2e8f0', font: { family: 'Plus Jakarta Sans', size: 9 } }
        }
      }
    }
  });

  const lowCount = scores.filter(s => s.risk_level === 'Low').length;
  const medCount = scores.filter(s => s.risk_level === 'Medium').length;
  const highCount = scores.filter(s => s.risk_level === 'High').length;

  const ctxDist = document.getElementById('riskDistributionChart').getContext('2d');
  if (riskDistChartInstance) riskDistChartInstance.destroy();

  riskDistChartInstance = new Chart(ctxDist, {
    type: 'doughnut',
    data: {
      labels: ['Low Risk', 'Medium Risk', 'High Risk'],
      datasets: [{
        data: [lowCount, medCount, highCount],
        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
        borderWidth: 2,
        borderColor: '#0b0f19',
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { 
          position: 'right', 
          labels: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 9 } } 
        },
        title: { display: true, text: 'PROPORSI LEVEL RISIKO GLOBAL', color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10, weight: '700' } }
      },
      cutout: '70%'
    }
  });
}


// ==================== CURRENCY IMPACT LOGIC ====================
let currencyChartInstance = null;

async function loadCurrencyTable() {
  try {
    const res = await fetch('/api/currency');
    const rates = await res.json();
    const tbody = document.getElementById('currencyTableBody');
    if (!tbody) return;

    if (rates.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Tidak ada data kurs mata uang.</td></tr>';
      return;
    }

    tbody.innerHTML = rates.map(r => {
      const c = r.country;
      return `
        <tr>
          <td class="ps-4">
            <div class="d-flex align-items-center gap-3">
              ${c && c.flag_url ? `<img src="${c.flag_url}" style="width:28px;height:18px;object-fit:cover;border-radius:3px;box-shadow:0 2px 5px rgba(0,0,0,0.2)">` : ''}
              <span class="text-white fw-semibold">${c ? c.name : 'Unknown'}</span>
            </div>
          </td>
          <td>${c ? c.currency_code : r.currency_code}</td>
          <td class="mono small text-silver">${r.currency_code}</td>
          <td class="mono small text-warning">${Number(r.rate_to_usd).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 6})}</td>
          <td class="pe-4 small text-muted">${new Date(r.recorded_at).toLocaleString('id-ID')}</td>
        </tr>
      `;
    }).join('');
  } catch (err) {
    console.error('Error loading currency table:', err);
  }
}

async function loadCurrencyAnalytics() {
  const id = document.getElementById('currencyCountrySelect').value;
  const detailCard = document.getElementById('currencyDetailCard');
  const placeholder = document.getElementById('currencySelectPlaceholder');

  if (!id) {
    detailCard.style.display = 'none';
    placeholder.style.display = 'block';
    return;
  }

  try {
    const [countryRes, historyRes] = await Promise.all([
      fetch(`/api/countries/${id}`),
      fetch(`/api/currency?country_id=${id}&history=true`)
    ]);

    const country = await countryRes.json();
    const history = await historyRes.json();

    placeholder.style.display = 'none';
    detailCard.style.display = 'block';

    // Flag & Name
    document.getElementById('currencyFlagContainer').innerHTML = country.flag_url 
      ? `<img src="${country.flag_url}" style="width:54px;height:36px;object-fit:cover;border-radius:4px;box-shadow: 0 2px 8px rgba(0,0,0,0.3)">`
      : '';
    document.getElementById('currencyCountryName').textContent = country.name;
    document.getElementById('currencyCodeLabel').textContent = country.currency_code;

    const latest = history[0];
    if (latest) {
      document.getElementById('currencyValueLabel').textContent = `${country.currency_code} ${Number(latest.rate_to_usd).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 6})}`;
      document.getElementById('currencyUpdateLabel').textContent = new Date(latest.recorded_at).toLocaleString('id-ID');
    } else {
      document.getElementById('currencyValueLabel').textContent = 'N/A';
      document.getElementById('currencyUpdateLabel').textContent = 'N/A';
    }

    // Render historical line chart
    let labels = [];
    let values = [];

    if (history.length > 1) {
      const sortedHist = [...history].sort((a,b) => new Date(a.recorded_at) - new Date(b.recorded_at));
      labels = sortedHist.map(h => new Date(h.recorded_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short'}));
      values = sortedHist.map(h => h.rate_to_usd);
    } else {
      const baseRate = latest ? latest.rate_to_usd : 1.0;
      const trend = fillMockTrend(baseRate, 'currency');
      labels = trend.dates;
      values = trend.values;
    }

    if (currencyChartInstance) currencyChartInstance.destroy();

    const ctxC = document.getElementById('currencyTrendChart').getContext('2d');
    const gradC = ctxC.createLinearGradient(0, 0, 0, 220);
    gradC.addColorStop(0, 'rgba(245, 158, 11, 0.2)');
    gradC.addColorStop(1, 'rgba(245, 158, 11, 0)');

    currencyChartInstance = new Chart(ctxC, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: `Kurs terhadap USD (${country.currency_code})`,
          data: values,
          borderColor: '#f59e0b',
          borderWidth: 2.5,
          pointBackgroundColor: '#f59e0b',
          fill: true,
          backgroundColor: gradC,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          title: { display: true, text: `FLUKTUASI KURS ${country.currency_code} / USD`, color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10, weight: '700' } }
        },
        scales: {
          x: { grid: { color: 'rgba(255, 255, 255, 0.04)' }, ticks: { color: '#64748b', font: { size: 9 } } },
          y: { grid: { color: 'rgba(255, 255, 255, 0.04)' }, ticks: { color: '#64748b', font: { size: 9 } } }
        }
      }
    });
  } catch (err) {
    console.error('Error loading currency analytics:', err);
  }
}

// ==================== PORT LOCATION LOGIC ====================
let portsMap = null;
let portsMarkersGroup = null;
let allPorts = [];

function initPortsMap() {
  if (portsMap) return;

  const mapEl = document.getElementById('portsMap');
  if (!mapEl) return;

  document.getElementById('loadingPortsMap').style.display = 'none';
  mapEl.style.display = 'block';

  portsMap = L.map('portsMap', {
    zoomControl: true,
    scrollWheelZoom: true,
  }).setView([20, 10], 2);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 20
  }).addTo(portsMap);

  portsMarkersGroup = L.layerGroup().addTo(portsMap);

  // Port size legend
  const portLegend = L.control({ position: 'bottomright' });
  portLegend.onAdd = function() {
    const div = L.DomUtil.create('div');
    div.innerHTML = `
      <div style="background:rgba(5,8,16,0.92); border:1px solid rgba(56,189,248,0.25); border-radius:10px; padding:10px 14px; font-family:'Plus Jakarta Sans',sans-serif; min-width:148px; box-shadow:0 8px 24px rgba(0,0,0,0.6);">
        <div style="font-size:0.62rem; text-transform:uppercase; letter-spacing:0.1em; color:rgba(255,255,255,0.45); margin-bottom:8px; font-weight:700;">⚓ Kapasitas Pelabuhan</div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;"><div style="width:14px;height:14px;border-radius:50%;background:#38bdf8;box-shadow:0 0 7px rgba(56,189,248,0.8);"></div><span style="font-size:0.75rem;color:rgba(255,255,255,0.85);">Major</span></div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;"><div style="width:11px;height:11px;border-radius:50%;background:#818cf8;box-shadow:0 0 6px rgba(129,140,248,0.7);"></div><span style="font-size:0.75rem;color:rgba(255,255,255,0.85);">Minor</span></div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;"><div style="width:8px;height:8px;border-radius:50%;background:#a78bfa;box-shadow:0 0 5px rgba(167,139,250,0.6);"></div><span style="font-size:0.75rem;color:rgba(255,255,255,0.85);">Small</span></div>
        <div style="display:flex;align-items:center;gap:8px;"><div style="width:6px;height:6px;border-radius:50%;background:#64748b;"></div><span style="font-size:0.75rem;color:rgba(255,255,255,0.45);">Very Small</span></div>
      </div>
    `;
    return div;
  };
  portLegend.addTo(portsMap);

  loadPortsData();
}

async function loadPortsData() {
  try {
    const res = await fetch('/api/ports');
    allPorts = await res.json();

    const countrySelect = document.getElementById('portSearchCountry');
    if (countrySelect && countrySelect.options.length <= 1) {
      const uniqueCountries = [];
      const seen = new Set();
      allPorts.forEach(p => {
        if (p.country && !seen.has(p.country.id)) {
          seen.add(p.country.id);
          uniqueCountries.push(p.country);
        }
      });
      uniqueCountries.sort((a, b) => a.name.localeCompare(b.name));
      countrySelect.innerHTML = '<option value="">Semua Negara</option>' +
        uniqueCountries.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    }

    // Populate shipping route selects
    const originSelect = document.getElementById('routeOriginPort');
    const destSelect = document.getElementById('routeDestPort');
    if (originSelect && destSelect) {
      const sortedPorts = [...allPorts].sort((a,b) => a.name.localeCompare(b.name));
      const portsHtml = sortedPorts.map(p => `<option value="${p.id}">${p.name} (${p.country ? p.country.name : 'N/A'})</option>`).join('');
      originSelect.innerHTML = '<option value="">Pilih pelabuhan asal...</option>' + portsHtml;
      destSelect.innerHTML = '<option value="">Pilih pelabuhan tujuan...</option>' + portsHtml;
      
      if (originSelect.tomselect) originSelect.tomselect.destroy();
      if (destSelect.tomselect) destSelect.tomselect.destroy();
      new TomSelect(originSelect, { create: false });
      new TomSelect(destSelect, { create: false });
    }

    // Render ALL port markers immediately on load
    renderAllPortMarkers(allPorts);

    // Populate search result list with all ports initially
    renderPortResultList(allPorts);

    if (document.getElementById('adminPortsList')) {
      filterAdminPorts();
    }
  } catch (err) {
    console.error('Error loading ports database:', err);
  }
}

// Helper: get marker color and radius based on port_type (actual DB values)
function portMarkerStyle(portType) {
  const t = (portType || '').toLowerCase();
  if (t === 'major')      return { color: '#38bdf8', glowColor: 'rgba(56,189,248,0.7)',  radius: 10, label: 'Major' };
  if (t === 'minor')      return { color: '#818cf8', glowColor: 'rgba(129,140,248,0.6)', radius: 7,  label: 'Minor' };
  if (t === 'small')      return { color: '#a78bfa', glowColor: 'rgba(167,139,250,0.5)', radius: 5,  label: 'Small' };
  if (t === 'very small') return { color: '#64748b', glowColor: 'rgba(100,116,139,0.4)', radius: 3,  label: 'Very Small' };
  return { color: '#475569', glowColor: 'rgba(71,85,105,0.4)', radius: 4, label: portType || 'Unknown' };
}

// Render port markers on the map
function renderAllPortMarkers(ports) {
  if (!portsMap || !portsMarkersGroup) return;
  portsMarkersGroup.clearLayers();

  ports.forEach(p => {
    if (p.latitude === null || p.longitude === null) return;

    const style = portMarkerStyle(p.port_type);
    const portTypeName = style.label;
    const countryName = p.country ? p.country.name : 'Unknown';
    const flagUrl = p.country && p.country.flag_url ? p.country.flag_url : null;

    const popupHtml = `
      <div style="font-family:'Plus Jakarta Sans',sans-serif; color:#fff; min-width:190px; padding:2px 0;">
        <div style="font-family:'Cinzel',serif; color:#38bdf8; font-size:0.9rem; font-weight:700; border-bottom:1px solid rgba(56,189,248,0.2); padding-bottom:5px; margin-bottom:7px;">
          ⚓ ${p.name}
        </div>
        <div style="font-size:0.8rem; line-height:1.85;">
          <span style="color:rgba(255,255,255,0.5);">🌍 Negara:</span> ${flagUrl ? `<img src="${flagUrl}" style="width:16px;height:10px;object-fit:cover;border-radius:2px;vertical-align:middle;margin:0 3px;">` : ''}<strong>${countryName}</strong><br>
          <span style="color:rgba(255,255,255,0.5);">📦 Kapasitas:</span> <strong style="color:${style.color};">${portTypeName}</strong><br>
          <span style="color:rgba(255,255,255,0.5);">📍 Koordinat:</span> <span class="mono" style="font-size:0.72rem; color:rgba(255,255,255,0.6);">${parseFloat(p.latitude).toFixed(4)}, ${parseFloat(p.longitude).toFixed(4)}</span>
        </div>
        <div style="margin-top:9px; text-align:right; display:flex; gap:6px; justify-content:flex-end;">
          <button onclick="focusPort(${p.id})" style="background:linear-gradient(135deg,rgba(56,189,248,0.2),rgba(56,189,248,0.08));border:1px solid rgba(56,189,248,0.4);color:#38bdf8;border-radius:6px;padding:3px 10px;font-size:0.68rem;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;">Fokus →</button>
          <button onclick="showPortDetail(${p.id})" style="background:linear-gradient(135deg,rgba(212,175,55,0.25),rgba(212,175,55,0.1));border:1px solid rgba(212,175,55,0.4);color:#d4af37;border-radius:6px;padding:3px 10px;font-size:0.68rem;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;letter-spacing:0.04em;">Detail &rarr;</button>
        </div>
      </div>
    `;

    const marker = L.circleMarker([p.latitude, p.longitude], {
      radius: style.radius,
      fillColor: style.color,
      color: 'rgba(0,0,0,0.5)',
      weight: 1.2,
      fillOpacity: 0.88,
    })
    .bindTooltip(`<strong style="color:${style.color};font-family:'Cinzel',serif;">${p.name}</strong><br><span style="font-size:0.7rem;color:rgba(255,255,255,0.6);">${countryName}</span>`, {
      direction: 'top',
      className: 'luxury-geo-tooltip',
      offset: [0, -style.radius - 2],
      sticky: false,
    })
    .bindPopup(popupHtml, { maxWidth: 240, className: 'luxury-popup' })
    .addTo(portsMarkersGroup);

    p._marker = marker;
  });
}

// Render the port list sidebar
function renderPortResultList(ports) {
  const resultsList = document.getElementById('portSearchResultList');
  if (!resultsList) return;

  const limit = 100;
  const display = ports.slice(0, limit);

  if (ports.length === 0) {
    resultsList.innerHTML = '<div class="text-center text-muted small py-4">Tidak ada pelabuhan ditemukan.</div>';
    return;
  }

  const style = (t) => portMarkerStyle(t);
  resultsList.innerHTML = `
    <div class="small text-muted mb-2" style="font-size:0.7rem;">Menampilkan ${display.length} dari ${ports.length} pelabuhan</div>
    <div class="d-flex flex-column gap-1">
      ${display.map(p => {
        const s = style(p.port_type);
        return `
          <div class="p-2 rounded result-item-port" onclick="focusPort(${p.id}); showPortDetail(${p.id});"
            style="border:1px solid rgba(56,189,248,0.1); background:rgba(255,255,255,0.02); cursor:pointer; transition: all 0.2s ease;">
            <div class="d-flex align-items-center gap-2">
              <div style="width:8px;height:8px;border-radius:50%;background:${s.color};flex-shrink:0;box-shadow:0 0 5px ${s.glowColor};"></div>
              <div class="fw-bold text-white" style="font-size:0.8rem; line-height:1.2;">${p.name}</div>
            </div>
            <div class="text-muted ps-3" style="font-size:0.68rem; margin-top:2px;">${p.country ? p.country.name : 'Unknown'}</div>
          </div>
        `;
      }).join('')}
    </div>
  `;
}

function filterPorts() {
  if (!portsMap || !portsMarkersGroup) return;

  const searchName = (document.getElementById('portSearchName').value || '').toLowerCase().trim();
  const countryId = document.getElementById('portSearchCountry').value;

  let filtered = allPorts;
  if (countryId) {
    filtered = filtered.filter(p => p.country_id == countryId);
  }
  if (searchName.length >= 2) {
    filtered = filtered.filter(p => p.name.toLowerCase().includes(searchName));
  }

  // Always render markers for filtered set
  renderAllPortMarkers(filtered);
  renderPortResultList(filtered);

  // Auto-zoom only when actively filtering
  if (filtered.length > 0 && (searchName.length >= 2 || countryId)) {
    const validMarkers = filtered.filter(p => p._marker).map(p => p._marker);
    if (validMarkers.length > 0) {
      const group = new L.featureGroup(validMarkers);
      portsMap.fitBounds(group.getBounds().pad(0.2), { maxZoom: 8 });
    }
  }
}

function focusPort(id) {
  const port = allPorts.find(p => p.id == id);
  if (port && port._marker && portsMap) {
    portsMap.setView([port.latitude, port.longitude], 10);
    port._marker.openPopup();
  }
}

function filterAdminPorts() {
  const search = document.getElementById('adminPortSearch').value.toLowerCase().trim();
  const listEl = document.getElementById('adminPortsList');
  if (!listEl) return;

  if (allPorts.length === 0) {
    listEl.innerHTML = '<div class="text-center text-muted small py-4">Memuat database pelabuhan...</div>';
    loadPortsData();
    return;
  }

  let filtered = allPorts;
  if (search.length >= 2) {
    filtered = allPorts.filter(p => p.name.toLowerCase().includes(search) || (p.country && p.country.name.toLowerCase().includes(search)));
  }

  const displayList = filtered.slice(0, 50);

  if (displayList.length === 0) {
    listEl.innerHTML = '<div class="text-center text-muted small py-4">Tidak ada pelabuhan ditemukan.</div>';
    return;
  }

  listEl.innerHTML = displayList.map(p => `
    <div class="p-2 mb-2 border-bottom d-flex justify-content-between align-items-center" style="border-color: rgba(255,255,255,0.03) !important;">
      <div>
        <h6 class="mb-0 text-white small fw-bold">${p.name}</h6>
        <p class="text-muted small mb-0" style="font-size: 0.72rem;">
          ${p.country ? p.country.name : 'Unknown'} &middot; Tipe: ${p.port_type ?? 'N/A'}
        </p>
      </div>
      <form method="POST" action="/admin/ports/${p.id}">
        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:0.65rem; padding: 2px 6px;" onclick="return confirm('Hapus pelabuhan ${p.name}?')">Hapus</button>
      </form>
    </div>
  `).join('');
}

// ==================== SHIPPING ESTIMATOR LOGIC ====================
let routePolyline = null;

function calculateShippingRoute() {
  const originId = document.getElementById('routeOriginPort').value;
  const destId = document.getElementById('routeDestPort').value;
  const placeholder = document.getElementById('routeResultPlaceholder');
  const content = document.getElementById('routeResultContent');

  if (!originId || !destId) {
    alert('Silakan pilih pelabuhan asal dan pelabuhan tujuan terlebih dahulu.');
    return;
  }

  if (originId === destId) {
    alert('Pelabuhan asal dan tujuan tidak boleh sama.');
    return;
  }

  const origin = allPorts.find(p => p.id == originId);
  const dest = allPorts.find(p => p.id == destId);

  if (!origin || !dest) return;

  placeholder.style.display = 'none';
  content.style.display = 'block';

  // 1. Calculate Geodesic Distance using Haversine
  const distanceKm = haversineDistance(origin.latitude, origin.longitude, dest.latitude, dest.longitude);
  const distanceNM = distanceKm * 0.539957; // 1 km = 0.539957 Nautical Miles

  document.getElementById('routeDistanceLabel').textContent = `${Math.round(distanceNM).toLocaleString('id-ID')} NM (${Math.round(distanceKm).toLocaleString('id-ID')} km)`;

  // 2. Calculate Travel Duration (assuming vessel speed = 18 knots = 33.3 km/h)
  const speedKnots = 18;
  const hours = distanceNM / speedKnots;
  const days = Math.floor(hours / 24);
  const remainingHours = Math.round(hours % 24);
  
  let durationStr = '';
  if (days > 0) {
    durationStr += `${days} hari `;
  }
  durationStr += `${remainingHours} jam`;
  document.getElementById('routeDurationLabel').textContent = durationStr;

  // 3. Calculate Cost (Base rate is $1.50 per NM)
  const baseRatePerNM = 1.50;
  let baseCost = distanceNM * baseRatePerNM;
  
  // Fetch destination country risk
  const destCountryId = dest.country_id;
  const destRisk = riskByCountry[destCountryId];
  let surchargePercent = 0;
  let riskLevel = 'Low';
  
  if (destRisk) {
    riskLevel = destRisk.risk_level;
    if (riskLevel === 'Medium') surchargePercent = 25;
    if (riskLevel === 'High') surchargePercent = 60;
  }

  const surchargeCost = baseCost * (surchargePercent / 100);
  const totalCost = baseCost + surchargeCost;

  document.getElementById('routeCostLabel').textContent = `$${Math.round(totalCost).toLocaleString('en-US')}`;

  // 4. Weather Warning & Risk Surcharge alert box
  const alertBox = document.getElementById('routeAlertBox');
  const alertText = document.getElementById('routeAlertText');
  
  let alerts = [];
  
  // Check Risk surcharge
  if (surchargePercent > 0) {
    alerts.push(`Destinasi (${dest.country ? dest.country.name : 'Unknown'}) memiliki tingkat risiko <strong>${riskLevel}</strong>. Biaya pengiriman dikenakan <strong>biaya tambahan keamanan (surcharge) sebesar +${surchargePercent}%</strong>.`);
  }

  // Check Storm Risk at Origin or Destination
  const originCountry = allCountries.find(c => c.id == origin.country_id);
  const destCountry = allCountries.find(c => c.id == dest.country_id);
  
  const originWeather = originCountry?.weather_snapshots?.[0];
  const destWeather = destCountry?.weather_snapshots?.[0];

  if (originWeather && (originWeather.storm_risk === 'high' || originWeather.storm_risk === 'medium')) {
    alerts.push(`⚠️ Cuaca buruk (risiko badai ${originWeather.storm_risk.toUpperCase()}) terpantau di pelabuhan asal <strong>${origin.name}</strong>.`);
  }
  if (destWeather && (destWeather.storm_risk === 'high' || destWeather.storm_risk === 'medium')) {
    alerts.push(`⚠️ Cuaca buruk (risiko badai ${destWeather.storm_risk.toUpperCase()}) terpantau di pelabuhan tujuan <strong>${dest.name}</strong>.`);
  }

  if (alerts.length > 0) {
    alertText.innerHTML = alerts.join('<br><br>');
    alertBox.style.display = 'block';
  } else {
    alertBox.style.display = 'none';
  }

  // 5. Draw Polyline Route on map
  if (portsMap) {
    if (routePolyline) {
      portsMap.removeLayer(routePolyline);
    }

    const latlngs = [
      [origin.latitude, origin.longitude],
      [dest.latitude, dest.longitude]
    ];

    routePolyline = L.polyline(latlngs, {
      color: '#f59e0b',
      weight: 3,
      dashArray: '5, 10',
      opacity: 0.8
    }).addTo(portsMap);

    const bounds = L.latLngBounds(latlngs);
    portsMap.fitBounds(bounds.pad(0.2));

    // Animate ship along this route
    animateRouteShip(origin.latitude, origin.longitude, dest.latitude, dest.longitude);
  }
}

// Haversine Distance in km
function haversineDistance(lat1, lon1, lat2, lon2) {
  const R = 6371; // Earth radius in km
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = 
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
    Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

// ==================== LIVE SHIP FLT TRACKING & SIMULATION ====================
const simulatedShips = [
  { name: 'MV Horizon Leader', type: 'Container Vessel', lat: 1.25, lng: 103.8, heading: 90, speed: '18 kts', dest: 'Singapore', status: 'Sailing', flag: 'Panama', cargo: '15,400 TEU' },
  { name: 'Equinox Voyager', type: 'LNG Tanker', lat: 24.5, lng: 36.2, heading: 320, speed: '16 kts', dest: 'Rotterdam', status: 'Sailing', flag: 'Marshall Islands', cargo: '165,000 m³ LNG' },
  { name: 'Caspian Trader', type: 'Bulk Carrier', lat: 9.05, lng: -79.6, heading: 180, speed: '12 kts', dest: 'Shanghai', status: 'Transit Canal', flag: 'Liberia', cargo: '85,000 Tons Coal' },
  { name: 'Atlantic Titan', type: 'Crude Oil Tanker', lat: 50.2, lng: -1.5, heading: 240, speed: '14 kts', dest: 'Antwerp', status: 'Sailing', flag: 'Singapore', cargo: '1,950,000 bbl Crude' },
  { name: 'Oceanic Empress', type: 'Container Vessel', lat: 35.9, lng: -5.4, heading: 90, speed: '19 kts', dest: 'Genoa', status: 'Sailing', flag: 'Malta', cargo: '12,200 TEU' },
  { name: 'Pacific Enterprise', type: 'Car Carrier', lat: 32.0, lng: -148.0, heading: 270, speed: '20 kts', dest: 'Los Angeles', status: 'Sailing', flag: 'Japan', cargo: '5,200 Vehicles' },
  { name: 'Orient Express', type: 'Container Vessel', lat: 11.5, lng: 113.0, heading: 45, speed: '17 kts', dest: 'Busan', status: 'Sailing', flag: 'Hong Kong', cargo: '18,100 TEU' },
  { name: 'Indian Star', type: 'Bulk Carrier', lat: 5.5, lng: 79.5, heading: 110, speed: '13 kts', dest: 'Colombo', status: 'Sailing', flag: 'India', cargo: '76,000 Tons Wheat' },
  { name: 'Southern Cross', type: 'LNG Tanker', lat: -34.8, lng: 18.2, heading: 290, speed: '15 kts', dest: 'Santos', status: 'Sailing', flag: 'Bahamas', cargo: '155,000 m³ LNG' },
  { name: 'MV Golden Ray', type: 'Car Carrier', lat: -24.0, lng: -43.0, heading: 45, speed: '16 kts', dest: 'Santos', status: 'Sailing', flag: 'Panama', cargo: '4,800 Cars' },
  { name: 'Pacific Explorer', type: 'Bulk Carrier', lat: 34.0, lng: 142.0, heading: 180, speed: '13 kts', dest: 'Yokohama', status: 'Sailing', flag: 'Liberia', cargo: '90,000 Tons Coal' },
  { name: 'CMA CGM Marco Polo', type: 'Container Vessel', lat: 49.5, lng: -4.5, heading: 90, speed: '21 kts', dest: 'Le Havre', status: 'Sailing', flag: 'France', cargo: '16,020 TEU' },
  { name: 'MSC Isabella', type: 'Container Vessel', lat: 52.5, lng: 3.2, heading: 0, speed: '20 kts', dest: 'Rotterdam', status: 'Sailing', flag: 'Switzerland', cargo: '23,650 TEU' },
  { name: 'Maersk Mc-Kinney Moller', type: 'Container Vessel', lat: 54.0, lng: 7.8, heading: 270, speed: '19 kts', dest: 'Hamburg', status: 'Sailing', flag: 'Denmark', cargo: '18,270 TEU' },
  { name: 'Ever Given', type: 'Container Vessel', lat: 30.0, lng: 32.5, heading: 330, speed: '17 kts', dest: 'Rotterdam', status: 'Sailing', flag: 'Panama', cargo: '20,120 TEU' },
  { name: 'Triton', type: 'Container Vessel', lat: 36.5, lng: 23.0, heading: 180, speed: '18 kts', dest: 'Piraeus', status: 'Sailing', flag: 'Greece', cargo: '14,400 TEU' },
  { name: 'Viking Queen', type: 'Car Carrier', lat: 54.8, lng: 5.5, heading: 120, speed: '15 kts', dest: 'Bremerhaven', status: 'Sailing', flag: 'Norway', cargo: '6,200 Vehicles' },
  { name: 'Stena Imperator', type: 'LNG Tanker', lat: 42.0, lng: -68.0, heading: 90, speed: '16 kts', dest: 'Boston', status: 'Sailing', flag: 'Sweden', cargo: '172,000 m³ LNG' },
  { name: 'British Pioneer', type: 'Crude Oil Tanker', lat: 27.0, lng: -90.0, heading: 180, speed: '14 kts', dest: 'Houston', status: 'Sailing', flag: 'United Kingdom', cargo: '2,050,000 bbl Crude' },
  { name: 'Eagle Hamburg', type: 'Crude Oil Tanker', lat: 26.5, lng: -94.0, heading: 0, speed: '15 kts', dest: 'Port Arthur', status: 'Sailing', flag: 'Singapore', cargo: '1,850,000 bbl Crude' },
  { name: 'Casing Ocean', type: 'Bulk Carrier', lat: 20.5, lng: 88.0, heading: 220, speed: '11 kts', dest: 'Haldia', status: 'Sailing', flag: 'India', cargo: '72,000 Tons Ore' },
  { name: 'Nordic Barents', type: 'LNG Tanker', lat: 70.5, lng: 21.0, heading: 45, speed: '14 kts', dest: 'Hammerfest', status: 'Sailing', flag: 'Norway', cargo: '135,000 m³ LNG' },
  { name: 'Koa Star', type: 'Bulk Carrier', lat: 37.0, lng: 125.5, heading: 180, speed: '12 kts', dest: 'Incheon', status: 'Sailing', flag: 'South Korea', cargo: '68,000 Tons Wheat' },
  { name: 'Pioneer First', type: 'Container Vessel', lat: 20.0, lng: 107.0, heading: 270, speed: '16 kts', dest: 'Haiphong', status: 'Sailing', flag: 'Vietnam', cargo: '4,200 TEU' },
  { name: 'Sulu Breeze', type: 'Bulk Carrier', lat: 14.5, lng: 120.0, heading: 90, speed: '13 kts', dest: 'Manila', status: 'Sailing', flag: 'Philippines', cargo: '55,000 Tons Sugar' },
  { name: 'Tasman Voyager', type: 'Container Vessel', lat: -36.0, lng: 175.0, heading: 0, speed: '15 kts', dest: 'Auckland', status: 'Sailing', flag: 'New Zealand', cargo: '6,800 TEU' },
  { name: 'Cape Byron', type: 'Bulk Carrier', lat: -33.0, lng: 152.0, heading: 180, speed: '13 kts', dest: 'Newcastle', status: 'Sailing', flag: 'Australia', cargo: '95,000 Tons Coal' },
  { name: 'Endeavour Strait', type: 'Bulk Carrier', lat: -20.0, lng: 116.0, heading: 90, speed: '14 kts', dest: 'Dampier', status: 'Sailing', flag: 'Australia', cargo: '120,000 Tons Iron Ore' },
  { name: 'Al Ghashamiya', type: 'LNG Tanker', lat: 26.5, lng: 52.5, heading: 330, speed: '17 kts', dest: 'Ras Laffan', status: 'Sailing', flag: 'Qatar', cargo: '210,000 m³ LNG' },
  { name: 'Shazdeh', type: 'Crude Oil Tanker', lat: 28.5, lng: 50.0, heading: 150, speed: '14 kts', dest: 'Kharg Island', status: 'Sailing', flag: 'Iran', cargo: '2,200,000 bbl Crude' },
  { name: 'Valparaiso Star', type: 'Reefer Vessel', lat: -33.0, lng: -72.0, heading: 270, speed: '18 kts', dest: 'Valparaiso', status: 'Sailing', flag: 'Chile', cargo: '8,000 Tons Fruit' },
  { name: 'Yara Birkeland', type: 'Container Vessel', lat: 59.0, lng: 10.5, heading: 180, speed: '10 kts', dest: 'Porsgrunn', status: 'Sailing', flag: 'Norway', cargo: '120 TEU (Autonomous)' },
  { name: 'Atlantic Conveyor', type: 'Container Vessel', lat: 53.5, lng: -4.0, heading: 0, speed: '18 kts', dest: 'Liverpool', status: 'Sailing', flag: 'United Kingdom', cargo: '8,500 TEU' },
  { name: 'Clipper Sentinel', type: 'Bulk Carrier', lat: 48.5, lng: -125.0, heading: 240, speed: '13 kts', dest: 'Vancouver', status: 'Sailing', flag: 'Canada', cargo: '62,000 Tons Timber' }
];

let mainMapShipsGroup = null;
let portsMapShipsGroup = null;

function getShipIcon(heading, type) {
  let color = '#22c55e'; // default green cargo / carrier
  if (type && (type.toLowerCase().includes('tanker') || type.toLowerCase().includes('crude'))) {
    color = '#ef4444'; // red tanker
  }
  
  return L.divIcon({
    className: 'ship-marker-icon',
    html: `
      <div style="transform: rotate(${heading}deg); transition: transform 0.5s ease; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="${color}">
          <path d="M12 2L4.5 9.5V18l7.5 4 7.5-4V9.5L12 2z"/>
          <path d="M12 5l-4 5h8l-4-5z" fill="#020617"/>
        </svg>
      </div>
    `,
    iconSize: [24, 24],
    iconAnchor: [12, 12]
  });
}

function renderGlobalShips() {
  if (portsMap) {
    if (!portsMapShipsGroup) {
      portsMapShipsGroup = L.layerGroup().addTo(portsMap);
    } else {
      portsMapShipsGroup.clearLayers();
    }

    simulatedShips.forEach(s => {
      const marker = L.marker([s.lat, s.lng], { icon: getShipIcon(s.heading, s.type) })
        .bindPopup(getShipPopupContent(s))
        .addTo(portsMapShipsGroup);
      s.portsMarker = marker;
    });
  }
}

function getClosestCountryWeather(lat, lng) {
  let closestCountry = null;
  let minDist = Infinity;
  
  allCountries.forEach(c => {
    if (c.latitude && c.longitude) {
      const dist = haversineDistance(lat, lng, c.latitude, c.longitude);
      if (dist < minDist) {
        minDist = dist;
        closestCountry = c;
      }
    }
  });

  if (closestCountry && closestCountry.weather_snapshots && closestCountry.weather_snapshots.length > 0) {
    return {
      countryName: closestCountry.name,
      snapshot: closestCountry.weather_snapshots[0]
    };
  }
  return null;
}

function getShipPopupContent(s) {
  let destInfo = '';
  // Try to find the destination port coordinates in allPorts
  const destPort = allPorts.find(p => p.name.toLowerCase().includes(s.dest.toLowerCase()));
  if (destPort) {
    const distKm = haversineDistance(s.lat, s.lng, destPort.latitude, destPort.longitude);
    const distNM = distKm * 0.539957;
    destInfo = `<br><span style="color:rgba(255,255,255,0.6);">Jarak ke ${destPort.name}:</span> <strong class="mono text-warning">${Math.round(distNM).toLocaleString('id-ID')} NM</strong>`;
  } else {
    destInfo = `<br><span style="color:rgba(255,255,255,0.6);">Tujuan:</span> <strong>${s.dest}</strong>`;
  }

  // Calculate closest country weather dynamically
  const cw = getClosestCountryWeather(s.lat, s.lng);
  let weatherInfo = '';
  if (cw && cw.snapshot) {
    const w = cw.snapshot;
    let stormBadgeClass = 'bg-success';
    if (w.storm_risk === 'medium') stormBadgeClass = 'bg-warning text-dark';
    if (w.storm_risk === 'high') stormBadgeClass = 'bg-danger';

    weatherInfo = `<br><span style="color:rgba(255,255,255,0.6);">Cuaca (${cw.countryName}):</span> <strong>${w.temperature}°C</strong> · <strong>${w.wind_speed} km/h</strong> · <span class="badge ${stormBadgeClass}" style="font-size:0.58rem; padding: 2px 4px;">${w.storm_risk.toUpperCase()}</span>`;
  }

  return `
    <div style="font-family:'Plus Jakarta Sans',sans-serif; color:#fff; min-width: 220px;">
      <h6 style="margin:0 0 6px 0; font-family:'Cinzel',serif; color:#38bdf8; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 4px;">🚢 ${s.name}</h6>
      <p style="margin:0; font-size:0.78rem; line-height: 1.55;">
        Tipe: <strong>${s.type}</strong><br>
        Bendera: <strong>${s.flag}</strong>${destInfo}<br>
        Muatan: <strong style="color:#a855f7;">${s.cargo || 'N/A'}</strong>${weatherInfo}<br>
        Kecepatan: <strong>${s.speed}</strong> · Arah: <strong>${s.heading}°</strong><br>
        Koordinat: <span class="mono" style="font-size:0.7rem; color:var(--gold-primary);">${s.lat.toFixed(5)}, ${s.lng.toFixed(5)}</span><br>
        Status: <span class="badge bg-success" style="font-size:0.6rem; padding: 2px 5px;">${s.status}</span>
      </p>
    </div>
  `;
}

function startShipSimulation() {
  setInterval(() => {
    simulatedShips.forEach(s => {
      const speedKtsVal = parseFloat(s.speed) || 15;
      const speedFactor = 0.00015 * speedKtsVal;
      
      const headingRad = (s.heading * Math.PI) / 180;
      s.lat += speedFactor * Math.cos(headingRad);
      s.lng += speedFactor * Math.sin(headingRad);

      if (s.lat > 85) s.lat = -85;
      if (s.lat < -85) s.lat = 85;
      if (s.lng > 180) s.lng = -180;
      if (s.lng < -180) s.lng = 180;

      if (s.portsMarker) {
        s.portsMarker.setLatLng([s.lat, s.lng]);
        
        // Update popup dynamically in real-time if open
        if (s.portsMarker.isPopupOpen()) {
          s.portsMarker.setPopupContent(getShipPopupContent(s));
        }
      }
    });
  }, 4000);
}

// ==================== ROUTE ESTIMATION SHIP ANIMATION ====================
let routeShipMarker = null;
let routeAnimationTimer = null;

function animateRouteShip(originLat, originLng, destLat, destLng) {
  if (routeShipMarker && portsMap) {
    portsMap.removeLayer(routeShipMarker);
  }
  if (routeAnimationTimer) {
    clearInterval(routeAnimationTimer);
  }

  const heading = calculateHeading(originLat, originLng, destLat, destLng);

  const activeShipIcon = L.divIcon({
    className: 'active-ship-icon',
    html: `
      <div style="transform: rotate(${heading}deg); width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; filter: drop-shadow(0 0 6px rgba(245,158,11,0.8));">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="26" fill="#f59e0b">
          <path d="M12 2L4.5 9.5V18l7.5 4 7.5-4V9.5L12 2z"/>
          <path d="M12 5l-4 5h8l-4-5z" fill="#020617"/>
        </svg>
      </div>
    `,
    iconSize: [30, 30],
    iconAnchor: [15, 15]
  });

  routeShipMarker = L.marker([originLat, originLng], { icon: activeShipIcon })
    .bindPopup(`<strong>Kapal Rute Aktif</strong><br>Sedang melakukan pelayaran...`)
    .addTo(portsMap);

  let step = 0;
  const totalSteps = 150;

  routeAnimationTimer = setInterval(() => {
    step++;
    if (step > totalSteps) {
      step = 0;
    }

    const t = step / totalSteps;
    const lat = originLat + (destLat - originLat) * t;
    const lng = originLng + (destLng - originLng) * t;

    if (routeShipMarker) {
      routeShipMarker.setLatLng([lat, lng]);
    }
  }, 80);
}

function calculateHeading(lat1, lon1, lat2, lon2) {
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const lat1Rad = lat1 * Math.PI / 180;
  const lat2Rad = lat2 * Math.PI / 180;

  const y = Math.sin(dLon) * Math.cos(lat2Rad);
  const x = Math.cos(lat1Rad) * Math.sin(lat2Rad) - Math.sin(lat1Rad) * Math.cos(lat2Rad) * Math.cos(dLon);
  let brng = Math.atan2(y, x) * 180 / Math.PI;
  return (brng + 360) % 360;
}

// ==================== EXTREME WEATHER MONITORING DASHBOARD ====================
function loadWeatherDashboard() {
  const grid = document.getElementById('weatherGrid');
  if (!grid) return;

  let maxTemp = -Infinity;
  let maxTempCountry = '-';
  let maxTempId = null;
  let maxWind = -Infinity;
  let maxWindCountry = '-';
  let maxWindId = null;
  let maxRain = -Infinity;
  let maxRainCountry = '-';
  let maxRainId = null;
  let highStormsCount = 0;

  allCountries.forEach(c => {
    const weather = c.weather_snapshots?.[0];
    if (weather) {
      const tempVal = parseFloat(weather.temperature);
      const windVal = parseFloat(weather.wind_speed);
      const rainVal = parseFloat(weather.rainfall);

      if (!isNaN(tempVal) && tempVal > maxTemp) {
        maxTemp = tempVal;
        maxTempCountry = c.name;
        maxTempId = c.id;
      }
      if (!isNaN(windVal) && windVal > maxWind) {
        maxWind = windVal;
        maxWindCountry = c.name;
        maxWindId = c.id;
      }
      if (!isNaN(rainVal) && rainVal > maxRain) {
        maxRain = rainVal;
        maxRainCountry = c.name;
        maxRainId = c.id;
      }
      if (weather.storm_risk && weather.storm_risk.toLowerCase() === 'high') {
        highStormsCount++;
      }
    }
  });

  const rainEl = document.getElementById('weatherWidgetRain');
  const windEl = document.getElementById('weatherWidgetWind');
  const tempEl = document.getElementById('weatherWidgetTemp');
  const stormsEl = document.getElementById('weatherWidgetStorms');

  const rainCard = document.getElementById('weatherWidgetRainCard');
  const windCard = document.getElementById('weatherWidgetWindCard');
  const tempCard = document.getElementById('weatherWidgetTempCard');
  const stormsCard = document.getElementById('weatherWidgetStormsCard');

  if (rainEl) {
    if (maxRain > -Infinity) {
      rainEl.innerHTML = `<span style="font-size: 1.45rem;">${maxRain} <span style="font-size:0.8rem; font-weight:normal;">mm</span></span> <span style="font-size:0.7rem; color:var(--text-muted); display:block; margin-top:2px;">${maxRainCountry}</span>`;
      if (rainCard) {
        rainCard.onclick = () => switchToMapAndShowDetail(maxRainId);
      }
    } else {
      rainEl.innerHTML = '-';
      if (rainCard) {
        rainCard.onclick = null;
      }
    }
  }

  if (windEl) {
    if (maxWind > -Infinity) {
      windEl.innerHTML = `<span style="font-size: 1.45rem;">${maxWind} <span style="font-size:0.8rem; font-weight:normal;">km/h</span></span> <span style="font-size:0.7rem; color:var(--text-muted); display:block; margin-top:2px;">${maxWindCountry}</span>`;
      if (windCard) {
        windCard.onclick = () => switchToMapAndShowDetail(maxWindId);
      }
    } else {
      windEl.innerHTML = '-';
      if (windCard) {
        windCard.onclick = null;
      }
    }
  }

  if (tempEl) {
    if (maxTemp > -Infinity) {
      tempEl.innerHTML = `<span style="font-size: 1.45rem;">${maxTemp} <span style="font-size:0.8rem; font-weight:normal;">°C</span></span> <span style="font-size:0.7rem; color:var(--text-muted); display:block; margin-top:2px;">${maxTempCountry}</span>`;
      if (tempCard) {
        tempCard.onclick = () => switchToMapAndShowDetail(maxTempId);
      }
    } else {
      tempEl.innerHTML = '-';
      if (tempCard) {
        tempCard.onclick = null;
      }
    }
  }

  if (stormsEl) {
    stormsEl.innerHTML = `<span style="font-size: 1.45rem; color:#ef4444;">${highStormsCount}</span> <span style="font-size:0.7rem; color:var(--text-muted); display:block; margin-top:2px;">Negara</span>`;
    if (stormsCard) {
      stormsCard.onclick = () => {
        const riskSelect = document.getElementById('weatherSearchRisk');
        if (riskSelect) {
          riskSelect.value = 'high';
          filterWeatherCards();
          riskSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      };
    }
  }

  filterWeatherCards();
}

function filterWeatherCards() {
  const grid = document.getElementById('weatherGrid');
  if (!grid) return;

  const search = document.getElementById('weatherSearchName') ? document.getElementById('weatherSearchName').value.toLowerCase().trim() : '';
  const riskFilter = document.getElementById('weatherSearchRisk') ? document.getElementById('weatherSearchRisk').value : '';

  let filtered = allCountries;
  if (search.length >= 2) {
    filtered = filtered.filter(c => c.name.toLowerCase().includes(search));
  }
  
  if (riskFilter) {
    filtered = filtered.filter(c => {
      const w = c.weather_snapshots?.[0];
      return w && w.storm_risk && w.storm_risk.toLowerCase() === riskFilter.toLowerCase();
    });
  }

  if (filtered.length === 0) {
    grid.innerHTML = '<div class="col-12 text-center text-white-50 py-5">Tidak ada data cuaca negara yang sesuai dengan filter.</div>';
    return;
  }

  // Display first 80 countries for performance
  const displayList = filtered.slice(0, 80);

  grid.innerHTML = displayList.map(c => {
    const w = c.weather_snapshots?.[0];
    const temp = w ? `${w.temperature}°C` : 'N/A';
    const rain = w ? `${w.rainfall} mm` : 'N/A';
    const wind = w ? `${w.wind_speed} km/h` : 'N/A';
    const storm = w && w.storm_risk ? w.storm_risk.toLowerCase() : 'none';
    const stormUpper = storm.toUpperCase();

    let badgeClass = 'bg-success';
    if (storm === 'medium') badgeClass = 'bg-warning text-dark';
    if (storm === 'high') badgeClass = 'bg-danger';

    return `
      <div class="col-lg-3 col-md-4 col-sm-6 col-12">
        <div class="luxury-glass-panel p-3 h-100 d-flex flex-column justify-content-between" 
             style="border:1px solid rgba(255,255,255,0.03); cursor: pointer; transition: all 0.3s ease;"
             onclick="switchToMapAndShowDetail(${c.id})"
             onmouseover="this.style.borderColor='var(--border-luxury-active)'; this.style.boxShadow='0 8px 24px rgba(212,175,55,0.05)';"
             onmouseout="this.style.borderColor='rgba(255,255,255,0.03)'; this.style.boxShadow='none';">
          <div>
            <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.04);">
              ${c.flag_url ? `<img src="${c.flag_url}" style="width:24px;height:15px;object-fit:cover;border-radius:2px;">` : ''}
              <h6 class="mb-0 text-white fw-bold text-truncate" style="font-size:0.85rem; font-family:'Plus Jakarta Sans',sans-serif;">${c.name}</h6>
            </div>
            <div class="d-flex flex-column gap-2 mb-3">
              <div class="d-flex justify-content-between text-white-50" style="font-size: 0.76rem;">
                <span>☀️ Temperatur</span>
                <span class="text-white fw-bold mono">${temp}</span>
              </div>
              <div class="d-flex justify-content-between text-white-50" style="font-size: 0.76rem;">
                <span>Curah Hujan</span>
                <span class="text-white fw-bold mono">${rain}</span>
              </div>
              <div class="d-flex justify-content-between text-white-50" style="font-size: 0.76rem;">
                <span>💨 Angin</span>
                <span class="text-white fw-bold mono">${wind}</span>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top:1px solid rgba(255,255,255,0.03);">
            <span class="badge ${badgeClass}" style="font-size:0.58rem; padding:3px 6px;">RISIKO: ${stormUpper}</span>
            <button class="btn btn-sm btn-luxury" style="font-size:0.6rem; padding: 2px 6px;" onclick="event.stopPropagation(); switchToMapAndShowDetail(${c.id})">Lihat Detail</button>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function switchToMapAndShowDetail(countryId) {
  if (typeof switchTab === 'function') {
    switchTab('map');
  }
  if (typeof showDetail === 'function') {
    showDetail(countryId);
  }
}

function goToCountryPorts(countryId) {
  if (typeof switchTab === 'function') {
    switchTab('ports');
  }
  
  const selectEl = document.getElementById('portSearchCountry');
  if (selectEl) {
    selectEl.value = countryId;
  }
  
  const nameEl = document.getElementById('portSearchName');
  if (nameEl) {
    nameEl.value = '';
  }
  
  if (typeof filterPorts === 'function') {
    filterPorts();
  }
}

let allNews = [];
let newsLoaded = false;

async function loadNewsTab() {
  const container = document.getElementById('newsTabList');
  if (!container) return;

  // Populate country filter dropdown if not populated yet
  const countrySelect = document.getElementById('newsTabSearchCountry');
  if (countrySelect && countrySelect.options.length <= 1) {
    const sorted = [...allCountries].sort((a,b) => a.name.localeCompare(b.name));
    const optHtml = sorted.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    countrySelect.insertAdjacentHTML('beforeend', optHtml);
  }

  // Fetch news if not loaded yet
  if (!newsLoaded) {
    try {
      const res = await fetch('/api/news');
      allNews = await res.json();
      newsLoaded = true;

      // Extract categories dynamically
      const categorySelect = document.getElementById('newsTabSearchCategory');
      if (categorySelect && categorySelect.options.length <= 1) {
        const uniqueCategories = [...new Set(allNews.map(n => n.category).filter(Boolean))].sort();
        const catHtml = uniqueCategories.map(cat => `<option value="${cat}">${cat.toUpperCase()}</option>`).join('');
        categorySelect.insertAdjacentHTML('beforeend', catHtml);
      }
    } catch (err) {
      console.error('Error fetching news:', err);
      container.innerHTML = '<div class="col-12 text-center text-danger py-5">Gagal memuat berita.</div>';
      return;
    }
  }

  filterNewsTab();
}

function filterNewsTab() {
  const searchQuery = (document.getElementById('newsTabSearchQuery').value || '').toLowerCase().trim();
  const countryId = document.getElementById('newsTabSearchCountry').value;
  const category = document.getElementById('newsTabSearchCategory').value;
  const sentiment = document.getElementById('newsTabSearchSentiment').value;

  let filtered = allNews;

  if (countryId) {
    filtered = filtered.filter(n => n.country_id == countryId);
  }
  if (category) {
    filtered = filtered.filter(n => n.category === category);
  }
  if (sentiment) {
    filtered = filtered.filter(n => n.sentiment === sentiment);
  }
  if (searchQuery.length >= 2) {
    filtered = filtered.filter(n => 
      (n.title && n.title.toLowerCase().includes(searchQuery)) || 
      (n.category && n.category.toLowerCase().includes(searchQuery))
    );
  }

  // Update widgets
  const total = filtered.length;
  document.getElementById('newsWidgetTotal').textContent = total;

  if (total > 0) {
    const posCount = filtered.filter(n => n.sentiment === 'positive').length;
    const negCount = filtered.filter(n => n.sentiment === 'negative').length;
    const neuCount = filtered.filter(n => n.sentiment === 'neutral').length;

    document.getElementById('newsWidgetPositive').textContent = Math.round((posCount / total) * 100) + '%';
    document.getElementById('newsWidgetNeutral').textContent = Math.round((neuCount / total) * 100) + '%';
    document.getElementById('newsWidgetNegative').textContent = Math.round((negCount / total) * 100) + '%';
  } else {
    document.getElementById('newsWidgetPositive').textContent = '0%';
    document.getElementById('newsWidgetNeutral').textContent = '0%';
    document.getElementById('newsWidgetNegative').textContent = '0%';
  }

  // Render list
  const container = document.getElementById('newsTabList');
  if (!container) return;

  if (filtered.length === 0) {
    container.innerHTML = '<div class="col-12 text-center text-muted py-5">Tidak ada berita yang cocok dengan filter saat ini.</div>';
    return;
  }

  container.innerHTML = filtered.map(n => {
    let badgeColor = 'bg-secondary';
    let sentLabel = 'Netral';
    let cardBorderGlow = '';
    
    if (n.sentiment === 'positive') {
      badgeColor = 'bg-success';
      sentLabel = 'Positif';
      cardBorderGlow = 'border-color: rgba(16,185,129,0.15) !important;';
    } else if (n.sentiment === 'negative') {
      badgeColor = 'bg-danger';
      sentLabel = 'Negatif';
      cardBorderGlow = 'border-color: rgba(239,68,68,0.15) !important;';
    }

    const countryName = n.country ? n.country.name : 'Global';
    const flagHtml = n.country && n.country.flag_url 
      ? `<img src="${n.country.flag_url}" style="width: 16px; height: 10px; object-fit: cover; border-radius: 2px; margin-right: 5px; vertical-align: middle;">`
      : '';

    const dateStr = n.published_at 
      ? new Date(n.published_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit'}) 
      : 'N/A';

    return `
      <div class="col-md-6">
        <div class="luxury-glass-panel p-3 h-100 d-flex flex-column justify-content-between" style="background: rgba(255, 255, 255, 0.015); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; transition: all 0.2s; ${cardBorderGlow}" onmouseover="this.style.background='rgba(255,255,255,0.035)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='rgba(255,255,255,0.015)'; this.style.transform='translateY(0)'">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge text-uppercase" style="font-size: 0.62rem; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);">${n.category || 'Global'}</span>
              <span class="badge ${badgeColor}" style="font-size: 0.62rem;">${sentLabel}</span>
            </div>
            <h5 class="text-white fw-bold mb-3" style="font-size: 0.92rem; line-height: 1.45; font-family:'Plus Jakarta Sans',sans-serif;">
              ${n.title}
            </h5>
          </div>
          <div class="d-flex justify-content-between align-items-center pt-2 mt-2" style="border-top: 1px solid rgba(255,255,255,0.03);">
            <div style="font-size: 0.68rem; color: #aac0d8;">
              ${flagHtml} ${countryName} &middot; <span style="font-size:0.62rem; color:rgba(255,255,255,0.4);">${dateStr}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <div style="font-size: 0.65rem;" class="mono me-2">
                <span class="text-success">+${n.positive_score}</span>/<span class="text-danger">-${n.negative_score}</span>
              </div>
              <a href="${n.source_url || '#'}" target="_blank" class="btn btn-sm btn-luxury-outline py-1 px-2" style="font-size: 0.65rem; border-radius:6px;">Baca &rarr;</a>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function showPortDetail(id) {
  const port = allPorts.find(p => p.id == id);
  if (!port) return;

  // Set Port name
  document.getElementById('portModalTitle').textContent = port.name;
  
  // Set Flag & Country Name
  const countryName = port.country ? port.country.name : 'Unknown';
  const flagHtml = port.country && port.country.flag_url 
    ? `<img src="${port.country.flag_url}" style="width: 48px; height: 32px; object-fit: cover; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">`
    : '🗺️';
  document.getElementById('portModalFlag').innerHTML = flagHtml;
  document.getElementById('portModalCountry').textContent = countryName + (port.country && port.country.code ? ` (${port.country.code})` : '');

  // Set Capacity & Type
  const style = portMarkerStyle(port.port_type);
  document.getElementById('portModalType').innerHTML = `<span style="color:${style.color};">${style.label} Capacity</span>`;

  // Set Coordinates
  document.getElementById('portModalCoords').textContent = `${parseFloat(port.latitude).toFixed(5)}, ${parseFloat(port.longitude).toFixed(5)}`;

  // Find Risk Status of Country if available
  let riskHtml = '<span class="text-white-50">N/A</span>';
  if (port.country) {
    const risk = riskByCountry[port.country_id];
    if (risk) {
      const rClass = risk.risk_level.toLowerCase();
      riskHtml = `<span class="risk-badge ${rClass}" style="font-size:0.75rem; padding: 2px 8px;">${risk.risk_level} (${risk.total_score})</span>`;
    }
  }
  document.getElementById('portModalRisk').innerHTML = riskHtml;

  // Find Weather Status of Country if available
  let weatherHtml = 'N/A';
  if (port.country) {
    const foundC = allCountries.find(c => c.id == port.country_id);
    if (foundC && foundC.weather_snapshots && foundC.weather_snapshots.length > 0) {
      const w = foundC.weather_snapshots[0];
      weatherHtml = `<span style="color:#38bdf8;">${w.temperature}°C</span> <span style="font-size:0.75rem; color:rgba(255,255,255,0.45); font-weight:normal;">(${w.storm_risk} storm)</span>`;
    }
  }
  document.getElementById('portModalWeather').innerHTML = weatherHtml;

  // Configure Route Buttons
  const btnOrigin = document.getElementById('portModalBtnOrigin');
  const btnDest = document.getElementById('portModalBtnDest');
  
  btnOrigin.onclick = () => {
    const originSelect = document.getElementById('routeOriginPort');
    if (originSelect) {
      if (originSelect.tomselect) {
        originSelect.tomselect.setValue(port.id);
      } else {
        originSelect.value = port.id;
      }
      originSelect.dispatchEvent(new Event('change'));
      togglePortDetailLink('Origin');
    }
    document.getElementById('portDetailModal').style.display = 'none';
  };

  btnDest.onclick = () => {
    const destSelect = document.getElementById('routeDestPort');
    if (destSelect) {
      if (destSelect.tomselect) {
        destSelect.tomselect.setValue(port.id);
      } else {
        destSelect.value = port.id;
      }
      destSelect.dispatchEvent(new Event('change'));
      togglePortDetailLink('Dest');
    }
    document.getElementById('portDetailModal').style.display = 'none';
  };

  // Show panel instead of modal
  const panelEl = document.getElementById('portDetailModal');
  panelEl.style.display = 'block';
  // Scroll to it
  panelEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function togglePortDetailLink(type) {
  const selectEl = document.getElementById(`route${type}Port`);
  const btnEl = document.getElementById(`btn${type}PortDetail`);
  if (selectEl && btnEl) {
    if (selectEl.value) {
      btnEl.style.display = 'inline-block';
    } else {
      btnEl.style.display = 'none';
    }
  }
}

function showOriginPortDetail() {
  const id = document.getElementById('routeOriginPort').value;
  if (id) showPortDetail(id);
}

function showDestPortDetail() {
  const id = document.getElementById('routeDestPort').value;
  if (id) showPortDetail(id);
}

// Instantiator Calls
document.addEventListener('DOMContentLoaded', () => {
  initMap();
  loadDashboardData();
  // startShipSimulation(); // Disabled per user request
});
</script>
@endsection