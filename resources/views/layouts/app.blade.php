<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Supply Chain Risk Dashboard')</title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Cinzel:wght@500;700;900&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<!-- CSS Frameworks & Libraries -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<!-- Custom Premium CSS -->
<style>
  :root {
    --bg-darkest: #030712;
    --bg-panel: rgba(11, 15, 25, 0.75);
    --border-luxury: rgba(255, 255, 255, 0.08);
    --border-luxury-active: rgba(212, 175, 55, 0.35);
    --gold-primary: #d4af37;
    --gold-secondary: #e0c878;
    --gold-glow: rgba(212, 175, 55, 0.2);
    --text-white: #ffffff;
    --text-silver: #e8edf5;
    --text-muted: #b8c5d6;  /* Lighter muted text — was #94a3b8 */
    --text-sub: #8fa3be;     /* Subtle subtext */
    
    --risk-low: #10b981;
    --risk-medium: #f59e0b;
    --risk-high: #ef4444;
  }

  body {
    background: radial-gradient(circle at 50% 50%, #0c111d 0%, #030712 100%) no-repeat fixed;
    background-image: 
      radial-gradient(circle at 50% 50%, #0c111d 0%, #030712 100%),
      linear-gradient(rgba(255, 255, 255, 0.007) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255, 255, 255, 0.007) 1px, transparent 1px);
    background-size: 100% 100%, 32px 32px, 32px 32px;
    color: var(--text-silver); /* Base body text — bright silver */
    font-family: 'Plus Jakarta Sans', sans-serif;
    min-height: 100vh;
    margin: 0;
    overflow-x: hidden;
  }

  /* Custom Leaflet Luxury Tooltip & Popup styling */
  .leaflet-tooltip.luxury-tooltip {
    background: rgba(4, 6, 12, 0.95) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--gold-primary) !important;
    color: var(--text-white) !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
    font-size: 0.78rem !important;
    font-weight: 600 !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6) !important;
  }
  .leaflet-tooltip-top:before {
    border-top-color: var(--gold-primary) !important;
  }

  /* Thin Premium Scrollbars */
  ::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  ::-webkit-scrollbar-track {
    background: var(--bg-darkest);
  }
  ::-webkit-scrollbar-thumb {
    background: var(--gold-secondary);
    border-radius: 10px;
  }
  ::-webkit-scrollbar-thumb:hover {
    background: var(--gold-primary);
  }

  .mono {
    font-family: 'IBM Plex Mono', monospace;
  }

  /* Luxury Sidebar Layout */
  .luxury-wrapper {
    display: flex;
    min-height: 100vh;
  }

  .sidebar {
    background: rgba(4, 6, 12, 0.85);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border-right: 1px solid var(--border-luxury);
    width: 280px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    display: flex;
    flex-direction: column;
    padding: 2.5rem 1.5rem;
  }

  .sidebar-brand {
    padding-bottom: 2.5rem;
    border-bottom: 1px solid var(--border-luxury);
    margin-bottom: 2rem;
    display: flex;
    flex-direction: column;
  }

  .gold-text-glow {
    font-family: 'Cinzel', serif;
    font-weight: 700;
    font-size: 1.3rem;
    letter-spacing: 0.1em;
    background: linear-gradient(135deg, #f3e5ab 0%, #d4af37 50%, #c5a880 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 10px rgba(212, 175, 55, 0.25);
  }

  .brand-subtitle {
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.22em;
    color: #a8b8cc; /* Bright enough to read clearly */
    margin-top: 0.3rem;
    font-weight: 700;
  }

  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    flex-grow: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
  }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 1.25rem;
    color: #c5d2e0; /* Brighter nav item text — was var(--text-muted) */
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    border-radius: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
    position: relative;
  }

  .nav-item:hover {
    color: var(--text-white);
    background: rgba(212, 175, 55, 0.06);
    border-color: rgba(212, 175, 55, 0.15);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3), inset 0 0 12px rgba(212, 175, 55, 0.04);
    transform: translateX(4px);
  }

  .nav-item.active {
    border-color: var(--border-luxury-active);
    color: var(--gold-primary);
    background: rgba(212, 175, 55, 0.07);
    box-shadow: 0 4px 20px rgba(212, 175, 55, 0.08), inset 0 0 12px rgba(212, 175, 55, 0.04);
  }

  .nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 20%;
    height: 60%;
    width: 3px;
    background: linear-gradient(180deg, #f3e5ab, var(--gold-primary));
    box-shadow: 0 0 12px var(--gold-primary), 0 0 20px rgba(212, 175, 55, 0.4);
    border-radius: 0 4px 4px 0;
  }

  .sidebar-footer {
    border-top: 1px solid var(--border-luxury);
    padding-top: 1.5rem;
    margin-top: auto;
  }

  .user-profile-widget {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border-luxury);
    border-radius: 12px;
  }

  .user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gold-secondary), var(--gold-primary));
    color: var(--bg-darkest);
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 10px var(--gold-glow);
  }

  .user-details {
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .user-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-white);
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
  }

  .user-role {
    font-size: 0.7rem;
    color: var(--gold-secondary);
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.05em;
  }

  /* Main Workspace Area */
  .main-content {
    margin-left: 280px;
    flex-grow: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: transparent;
  }

  .top-bar {
    height: 70px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2.5rem;
    background: rgba(3, 7, 18, 0.55);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    box-shadow: 0 1px 0 rgba(212, 175, 55, 0.05);
  }

  .top-bar-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #c8d6e5; /* Bright readable text — was var(--text-muted) */
    letter-spacing: 0.02em;
  }

  .live-time-indicator {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--gold-secondary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .pulse-dot {
    width: 6px;
    height: 6px;
    background-color: var(--risk-low);
    border-radius: 50%;
    box-shadow: 0 0 8px var(--risk-low);
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
  }

  .content-body {
    padding: 2.5rem;
    flex-grow: 1;
  }

  /* Glassmorphism Luxury Card */
  .luxury-card {
    background: var(--bg-panel);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border: 1px solid var(--border-luxury);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
    border-radius: 16px;
    padding: 2rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .luxury-card:hover {
    border-color: var(--border-luxury-active);
    box-shadow: 0 16px 40px rgba(212, 175, 55, 0.05), 0 0 1px 1px var(--border-luxury-active) inset;
    transform: translateY(-2px);
  }

  /* Gold Luxury Buttons */
  .btn-luxury {
    background: linear-gradient(135deg, #e0c878, var(--gold-primary), #c5a028);
    color: #050810 !important;
    font-weight: 800;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border: none;
    border-radius: 9px;
    padding: 0.75rem 1.5rem;
    box-shadow: 0 4px 20px rgba(212, 175, 55, 0.3), 0 1px 0 rgba(255,255,255,0.15) inset;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .btn-luxury:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(212, 175, 55, 0.5);
    filter: brightness(1.1);
  }

  .btn-luxury-outline {
    background: rgba(212, 175, 55, 0.04);
    color: #e8c84a !important; /* Brighter gold for better visibility */
    font-weight: 700;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border: 1px solid rgba(212, 175, 55, 0.35);
    border-radius: 9px;
    padding: 0.7rem 1.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .btn-luxury-outline:hover {
    background: rgba(212, 175, 55, 0.1);
    border-color: var(--gold-primary);
    box-shadow: 0 0 16px rgba(212, 175, 55, 0.2), 0 4px 12px rgba(0,0,0,0.3);
    transform: translateY(-1px);
  }

  /* Luxury Form Inputs */
  .form-luxury-input {
    background: rgba(8, 12, 22, 0.6) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: #f0f4f8 !important; /* Bright white text in inputs */
    border-radius: 10px !important;
    padding: 0.75rem 1.1rem !important;
    transition: all 0.3s ease !important;
    font-size: 0.9rem !important;
  }

  .form-luxury-input::placeholder {
    color: rgba(180, 200, 225, 0.45) !important;
  }

  .form-luxury-input:focus {
    border-color: var(--gold-primary) !important;
    box-shadow: 0 0 14px rgba(212, 175, 55, 0.18) !important;
    background: rgba(4, 6, 12, 0.8) !important;
    outline: none;
    color: #ffffff !important;
  }

  /* Select options */
  .form-luxury-input option {
    background: #0c111d;
    color: #e0e8f0;
  }

  .form-luxury-label {
    font-size: 0.73rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #a8bcce; /* Visible label color — was var(--text-muted) */
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: block;
  }

  /* Tom Select Customization for Luxury Dark Theme */
  .ts-control {
    background-color: var(--bg-panel) !important;
    border: 1px solid var(--border-luxury) !important;
    color: var(--text-white) !important;
    border-radius: 8px !important;
    padding: 0.6rem 1rem !important;
  }
  .ts-dropdown {
    background-color: #0c111d !important;
    border: 1px solid var(--gold-primary) !important;
    color: var(--text-silver) !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5) !important;
  }
  .ts-dropdown .option {
    padding: 8px 12px;
  }
  .ts-dropdown .option:hover, .ts-dropdown .option.active {
    background-color: rgba(212, 175, 55, 0.2) !important;
    color: var(--text-white) !important;
  }
  .ts-control > input {
    color: var(--text-white) !important;
  }


  /* Risk Pill Badges */
  .risk-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-block;
  }

  .risk-badge.low {
    background: rgba(16, 185, 129, 0.1);
    color: var(--risk-low);
    border: 1px solid rgba(16, 185, 129, 0.2);
  }

  .risk-badge.medium {
    background: rgba(245, 158, 11, 0.1);
    color: var(--risk-medium);
    border: 1px solid rgba(245, 158, 11, 0.2);
  }

  .risk-badge.high {
    background: rgba(239, 68, 68, 0.1);
    color: var(--risk-high);
    border: 1px solid rgba(239, 68, 68, 0.2);
  }

  /* Fade-in Animation for Tabs */
  .tab-fade-in {
    animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* ===== FX TICKER STRIP ===== */
  .ticker-strip {
    display: flex;
    align-items: center;
    overflow: hidden;
    width: 320px;
    position: relative;
    mask-image: linear-gradient(90deg, transparent, black 8%, black 92%, transparent);
    -webkit-mask-image: linear-gradient(90deg, transparent, black 8%, black 92%, transparent);
  }
  .ticker-inner {
    display: flex;
    animation: tickerScroll 24s linear infinite;
    white-space: nowrap;
  }
  .ticker-item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0 1.4rem;
    font-size: 0.72rem;
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 600;
    color: #b8cce0; /* Readable ticker text — was var(--text-muted) */
    border-right: 1px solid rgba(255, 255, 255, 0.06);
  }
  .ticker-item:last-child { border-right: none; }
  .ticker-up   { color: var(--risk-low); }
  .ticker-down { color: var(--risk-high); }
  .ticker-sym  { color: var(--gold-secondary); }
  @keyframes tickerScroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
  }

  /* Sidebar status badge */
  .sidebar-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: var(--risk-low);
    background: rgba(16, 185, 129, 0.07);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 20px;
    padding: 3px 10px;
    margin-top: 0.5rem;
    align-self: flex-start;
    font-weight: 700;
  }
  .sidebar-status-badge .sb-dot {
    width: 5px; height: 5px;
    background: var(--risk-low);
    border-radius: 50%;
    box-shadow: 0 0 5px var(--risk-low);
    animation: pulse 2s infinite;
  }

  /* ===== GLOBAL BOOTSTRAP CONTRAST OVERRIDES ===== */
  /* Bootstrap's .text-muted (#6c757d) is invisible on dark backgrounds */
  .text-muted {
    color: #adc0d4 !important;
  }
  /* .text-white-50 (rgba(255,255,255,0.5)) needs more opacity on deep darks */
  .text-white-50 {
    color: rgba(210, 228, 248, 0.72) !important;
  }
  /* .small text should remain legible */
  .small {
    font-size: 0.85em;
  }
</style>
@yield('styles')
</head>
<body>

@if(request()->is('login') || request()->is('register'))
  <!-- Isolated Centered Layout for Auth Pages -->
  <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 2rem;">
    @yield('content')
  </div>
@else
  <!-- Main Grid Frame -->
  <div class="luxury-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <span class="gold-text-glow">🌍 GLOBAL LOGISTICS DASHBOARD</span>
        <small class="brand-subtitle">SUPPLY CHAIN RISK INTERNASIONAL</small>
        <div class="sidebar-status-badge"><span class="sb-dot"></span> GLOBAL MONITOR</div>
      </div>
      
      <nav class="sidebar-nav">
        <a href="/" class="nav-item active" data-tab="map">
          <span class="nav-icon">🗺️</span>
          <span class="nav-label">Global Map Panel</span>
        </a>
        <a href="/risk" class="nav-item" data-tab="risk">
          <span class="nav-icon">📊</span>
          <span class="nav-label">Risk Analytics</span>
        </a>
        <a href="/compare" class="nav-item" data-tab="compare">
          <span class="nav-icon">🔄</span>
          <span class="nav-label">Country Comparator</span>
        </a>
        <a href="/currency" class="nav-item" data-tab="currency">
          <span class="nav-icon">💵</span>
          <span class="nav-label">Currency Impact</span>
        </a>
        <a href="/ports" class="nav-item" data-tab="ports">
          <span class="nav-icon">⚓</span>
          <span class="nav-label">Port Location</span>
        </a>
        <a href="/weather" class="nav-item" data-tab="weather">
          <span class="nav-icon">⛈️</span>
          <span class="nav-label">Weather Alerts</span>
        </a>
        <a href="/news" class="nav-item" data-tab="news">
          <span class="nav-icon">📰</span>
          <span class="nav-label">Intelijen Berita</span>
        </a>
        
        @auth
          <a href="/watchlist" class="nav-item" data-tab="watchlist">
            <span class="nav-icon">⭐</span>
            <span class="nav-label">Watchlist Monitor</span>
          </a>
          @if (auth()->user()->role === 'admin')
            <a href="/admin" class="nav-item" data-tab="admin">
              <span class="nav-icon">🛡️</span>
              <span class="nav-label">Admin Terminal</span>
            </a>
          @endif
        @else
          <a href="/watchlist" class="nav-item" data-tab="watchlist">
            <span class="nav-icon">⭐</span>
            <span class="nav-label">Watchlist Monitor</span>
          </a>
        @endauth
      </nav>

      <div class="sidebar-footer">
        @auth
          <div class="user-profile-widget mb-3">
            <div class="user-avatar">
              {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="user-details">
              <span class="user-name">{{ auth()->user()->name }}</span>
              <span class="user-role">{{ auth()->user()->role }}</span>
            </div>
          </div>
          <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="btn-luxury-outline w-100 py-2">Logout</button>
          </form>
        @else
          <div class="d-flex flex-column gap-2">
            <a href="/login" class="btn-luxury text-center text-decoration-none py-2">Login</a>
            <a href="/register" class="btn-luxury-outline text-center text-decoration-none py-2">Daftar</a>
          </div>
        @endauth
      </div>
    </aside>

    <!-- Content Panel Workspace -->
    <main class="main-content">
      <!-- Top Cockpit Bar -->
      <header class="top-bar">
        <div class="top-bar-title">Supply Chain Risk Monitoring System</div>
        <!-- Live FX Ticker -->
        <div class="ticker-strip d-none d-lg-flex">
          <div class="ticker-inner" id="fxTicker"></div>
        </div>
        <div class="live-time-indicator">
          <div class="pulse-dot"></div>
          <span class="mono" id="currentDate">Memuat waktu...</span>
        </div>
      </header>

      <!-- Main Render Body -->
      <div class="content-body">
        @if (session('success'))
          <div class="alert alert-success border-0 shadow-sm mb-4" style="background: rgba(16, 185, 129, 0.15); color: var(--risk-low); border-radius: 12px; backdrop-filter: blur(5px);">
            {{ session('success') }}
          </div>
        @endif

        @yield('content')
      </div>
    </main>
  </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
  // Real-time ticking calendar clock
  function updateTime() {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    const dateStr = new Date().toLocaleDateString('id-ID', options);
    const clockEl = document.getElementById('currentDate');
    if (clockEl) {
      clockEl.textContent = dateStr;
    }
  }
  setInterval(updateTime, 1000);
  updateTime();

  // FX Ticker simulation (decorative live-feed feel)
  (function buildTicker() {
    const pairs = [
      { sym: 'USD/IDR', base: 16320,  dir: 'up',   chg: '+0.15%' },
      { sym: 'EUR/USD', base: 1.0824, dir: 'down', chg: '-0.08%' },
      { sym: 'USD/CNY', base: 7.2541, dir: 'up',   chg: '+0.21%' },
      { sym: 'JPY/USD', base: 0.0066, dir: 'down', chg: '-0.12%' },
      { sym: 'GBP/USD', base: 1.2715, dir: 'up',   chg: '+0.04%' },
      { sym: 'USD/SGD', base: 1.3461, dir: 'down', chg: '-0.07%' },
      { sym: 'XAU/USD', base: 2341.5, dir: 'up',   chg: '+0.32%' },
    ];

    function fmt(n) {
      return n >= 1000 ? n.toLocaleString('en', {minimumFractionDigits: 0}) : n.toFixed(4).replace(/\.?0+$/, '');
    }

    const el = document.getElementById('fxTicker');
    if (!el) return;

    // Build two copies for infinite loop
    let html = '';
    const allPairs = [...pairs, ...pairs];
    allPairs.forEach(p => {
      const arrow = p.dir === 'up' ? '▲' : '▼';
      const cls   = p.dir === 'up' ? 'ticker-up' : 'ticker-down';
      html += `<span class="ticker-item"><span class="ticker-sym">${p.sym}</span> <span>${fmt(p.base)}</span> <span class="${cls}">${arrow} ${p.chg}</span></span>`;
    });
    el.innerHTML = html;

    // Mild fluctuations every 5 seconds
    setInterval(() => {
      el.querySelectorAll('.ticker-item').forEach((item, i) => {
        const p = pairs[i % pairs.length];
        const delta = (Math.random() - 0.5) * p.base * 0.0005;
        p.base = Math.max(0.0001, p.base + delta);
        const arrow = delta >= 0 ? '▲' : '▼';
        const cls   = delta >= 0 ? 'ticker-up' : 'ticker-down';
        const pct   = ((Math.abs(delta) / p.base) * 100).toFixed(2);
        item.innerHTML = `<span class="ticker-sym">${p.sym}</span> <span>${fmt(p.base)}</span> <span class="${cls}">${arrow} ${pct}%</span>`;
      });
    }, 5000);
  })();

  // Unified Dashboard tab switching logic
  function switchTab(tabId) {
    // 1. Highlight clicked navigation item
    document.querySelectorAll('.sidebar-nav .nav-item').forEach(el => {
      if (el.getAttribute('data-tab') === tabId) {
        el.classList.add('active');
      } else {
        el.classList.remove('active');
      }
    });

    // 2. Hide all tab content panes, show current one
    const sections = ['map', 'risk', 'compare', 'watchlist', 'admin', 'currency', 'ports', 'weather', 'news'];
    sections.forEach(s => {
      const panel = document.getElementById('tab-' + s);
      if (panel) {
        if (s === tabId) {
          panel.style.setProperty('display', 'block', 'important');
          panel.classList.add('tab-fade-in');
        } else {
          panel.style.setProperty('display', 'none', 'important');
          panel.classList.remove('tab-fade-in');
        }
      }
    });

    // 3. Force leaflet map to re-draw viewport size calculations if Map is visible
    if (tabId === 'map' && typeof map !== 'undefined' && map !== null) {
      setTimeout(() => { map.invalidateSize(); }, 150);
    }
    if (tabId === 'ports') {
      if (typeof initPortsMap === 'function') {
        initPortsMap();
      }
      if (typeof portsMap !== 'undefined' && portsMap !== null) {
        setTimeout(() => { portsMap.invalidateSize(); }, 150);
      }
    }
    if (tabId === 'admin') {
  if (typeof filterAdminPorts === 'function') {
    filterAdminPorts();
  }
}
    if (tabId === 'news') {
      if (typeof loadNewsTab === 'function') {
        loadNewsTab();
      }
    }

    // 4. Update url browser address without reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.pushState({}, '', url);
  }

  // Bind sidebar click listeners
  document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
    item.addEventListener('click', (e) => {
      // If we are currently on the dashboard, run tab switching locally
      if (window.location.pathname === '/') {
        e.preventDefault();
        const tabId = item.getAttribute('data-tab');
        switchTab(tabId);
      }
    });
  });

  // Automatically activate the correct tab based on query parameters on load
  document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname === '/') {
      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get('tab') || 'map';
      switchTab(tabParam);
    } else {
      // If we are on subpages (e.g. /login or /register), clear active states on sidebar items
      document.querySelectorAll('.sidebar-nav .nav-item').forEach(el => el.classList.remove('active'));
    }
  });
</script>
@yield('scripts')

</body>
</html>