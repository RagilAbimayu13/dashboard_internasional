@extends('layouts.app')

@section('title', 'Dashboard - Supply Chain Risk Platform')

@section('styles')
<style>
  :root {
    --navy-deep: #0b1b2b;
    --navy-mid: #13293d;
    --cyan-accent: #2dd4bf;
  }
  body { font-family: 'Inter', -apple-system, sans-serif; }
  .mono { font-family: 'IBM Plex Mono', monospace; }

  .hero-panel {
    background: linear-gradient(135deg, var(--navy-deep), var(--navy-mid));
    border-radius: 14px;
    padding: 2rem 2.5rem;
    color: #e8f1f5;
    margin-bottom: 1.5rem;
  }
  .hero-panel .stat-value { font-size: 1.8rem; font-weight: 700; }
  .hero-panel .stat-label { font-size: 0.72rem; opacity: 0.65; text-transform: uppercase; letter-spacing: 0.05em; }

  #worldMap { height: 560px; border-radius: 12px; z-index: 1; }

  .search-wrap { position: relative; }
  #searchResults {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
    background: white; border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    max-height: 280px; overflow-y: auto; display: none; margin-top: 4px;
  }
  #searchResults .result-item { padding: 10px 16px; cursor: pointer; display:flex; align-items:center; gap:10px; }
  #searchResults .result-item:hover { background: #f4f6f9; }

  .risk-pill { font-size: 0.7rem; font-weight: 600; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.03em; }
  .risk-pill.low { background: #e6f7f3; color: #0d9488; }
  .risk-pill.medium { background: #fef3e2; color: #b45309; }
  .risk-pill.high { background: #fde8e8; color: #c0392b; }

  #detailPanel { display: none; }
  .data-block { border-left: 3px solid var(--cyan-accent); padding-left: 1rem; }
</style>
@endsection

@section('content')

<div class="hero-panel">
  <div class="row align-items-center">
    <div class="col-md-6">
      <h2 class="mb-1" style="font-weight:700;">Global Risk Intelligence</h2>
      <p class="mb-0" style="opacity:0.7;">Klik titik di peta untuk melihat detail risiko tiap negara.</p>
    </div>
    <div class="col-md-6">
      <div class="row text-center text-md-end">
        <div class="col-4"><span class="stat-value mono" id="statTotal">-</span><div class="stat-label">Negara</div></div>
        <div class="col-4"><span class="stat-value mono" style="color:#f5a623" id="statMedium">-</span><div class="stat-label">Medium Risk</div></div>
        <div class="col-4"><span class="stat-value mono" style="color:#ef5350" id="statHigh">-</span><div class="stat-label">High Risk</div></div>
      </div>
    </div>
  </div>
</div>

<div class="search-wrap mb-3">
  <input type="text" id="searchBox" class="form-control form-control-lg" placeholder="Cari negara..." autocomplete="off">
  <div id="searchResults"></div>
</div>

<div id="loading" class="text-muted mb-2">Memuat peta...</div>
<div id="worldMap"></div>

<div id="detailPanel" class="card mt-4 border-0 shadow-sm">
  <div class="card-body p-4">
    <button class="btn btn-sm btn-outline-secondary mb-3" onclick="closeDetail()">✕ Tutup</button>
    <div id="detailContent"></div>
  </div>
</div>

@endsection

@section('scripts')
<script>
let allCountries = [];
let riskByCountry = {};
let markers = {};

const map = L.map('worldMap').setView([10, 20], 2);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors',
  maxZoom: 18,
}).addTo(map);

function riskColor(level) {
  if (level === 'Low') return '#0d9488';
  if (level === 'Medium') return '#d97706';
  if (level === 'High') return '#dc3545';
  return '#94a3b8';
}

async function init() {
  const [countriesRes, riskRes] = await Promise.all([
    fetch('/api/countries'),
    fetch('/api/risk'),
  ]);
  allCountries = await countriesRes.json();
  const scores = await riskRes.json();

  scores.forEach(s => riskByCountry[s.country_id] = s);

  document.getElementById('statTotal').textContent = allCountries.length;
  document.getElementById('statMedium').textContent = scores.filter(s => s.risk_level === 'Medium').length;
  document.getElementById('statHigh').textContent = scores.filter(s => s.risk_level === 'High').length;

  allCountries.forEach(c => {
    if (!c.latitude || !c.longitude) return;

    const risk = riskByCountry[c.id];
    const color = risk ? riskColor(risk.risk_level) : '#94a3b8';

    const marker = L.circleMarker([c.latitude, c.longitude], {
      radius: 6,
      fillColor: color,
      color: '#fff',
      weight: 1,
      fillOpacity: 0.85,
    })
    .bindTooltip(c.name)
    .on('click', () => showDetail(c.id))
    .addTo(map);

    markers[c.id] = marker;
  });

  document.getElementById('loading').style.display = 'none';
}

async function showDetail(id) {
  const res = await fetch(`/api/countries/${id}`);
  const c = await res.json();

  const eco = c.economic_indicators?.[0];
  const weather = c.weather_snapshots?.[0];
  const fx = c.exchange_rates?.[0];
  const risk = c.risk_scores?.[0];
  const news = c.news ?? [];
  const ports = c.ports ?? [];

  const riskClass = risk ? risk.risk_level.toLowerCase() : '';

  document.getElementById('detailContent').innerHTML = `
    <div class="d-flex align-items-center gap-3 mb-4">
      ${c.flag_url ? `<img src="${c.flag_url}" style="width:56px;height:38px;object-fit:cover;border-radius:4px">` : ''}
      <div>
        <h3 class="mb-0">${c.name}</h3>
        <span class="text-muted">${c.capital ?? '-'} · ${c.region ?? '-'}</span>
      </div>
      ${risk ? `<span class="risk-pill ${riskClass} ms-auto">${risk.risk_level} Risk · ${risk.total_score}</span>` : ''}
    </div>

    <div class="row g-4">
      <div class="col-md-3 data-block">
        <h6>📊 Ekonomi</h6>
        ${eco ? `
          <p class="mb-1 mono">GDP: $${Number(eco.gdp).toLocaleString()}</p>
          <p class="mb-1 mono">Inflasi: ${eco.inflation_rate}%</p>
          <p class="mb-0 mono">Populasi: ${Number(eco.population).toLocaleString()}</p>
        ` : '<p class="text-muted small">Tidak tersedia</p>'}
      </div>
      <div class="col-md-3 data-block">
        <h6>🌦️ Cuaca</h6>
        ${weather ? `
          <p class="mb-1 mono">${weather.temperature}°C</p>
          <p class="mb-1 mono">Hujan: ${weather.rainfall}mm</p>
          <p class="mb-0 mono">Angin: ${weather.wind_speed}km/h</p>
        ` : '<p class="text-muted small">Tidak tersedia</p>'}
      </div>
      <div class="col-md-3 data-block">
        <h6>💱 Kurs</h6>
        ${fx ? `<p class="mb-0 mono">${c.currency_code}: ${Number(fx.rate_to_usd).toLocaleString()} / USD</p>` : '<p class="text-muted small">Tidak tersedia</p>'}
      </div>
      <div class="col-md-3 data-block">
        <h6>⚓ Pelabuhan</h6>
        <p class="mb-0 mono">${ports.length} pelabuhan tercatat</p>
      </div>
    </div>

    <hr class="my-4">

    <h6>📰 Berita Terkait</h6>
    ${news.length > 0 ? news.map(n => `
      <div class="border-bottom py-2 d-flex justify-content-between align-items-start">
        <span class="small">${n.title}</span>
        <span class="risk-pill ${n.sentiment === 'negative' ? 'high' : n.sentiment === 'positive' ? 'low' : 'medium'}">${n.sentiment}</span>
      </div>
    `).join('') : '<p class="text-muted small">Belum ada berita spesifik untuk negara ini.</p>'}
  `;

  document.getElementById('detailPanel').style.display = 'block';
  document.getElementById('detailPanel').scrollIntoView({ behavior: 'smooth' });

  map.flyTo([c.latitude, c.longitude], 4, { duration: 0.8 });
  if (markers[c.id]) markers[c.id].openTooltip();
}

function closeDetail() {
  document.getElementById('detailPanel').style.display = 'none';
}

const searchBox = document.getElementById('searchBox');
const searchResults = document.getElementById('searchResults');

searchBox.addEventListener('input', () => {
  const q = searchBox.value.toLowerCase().trim();

  if (q.length < 2) {
    searchResults.style.display = 'none';
    return;
  }

  const matches = allCountries.filter(c => c.name.toLowerCase().includes(q)).slice(0, 8);

  if (matches.length === 0) {
    searchResults.innerHTML = '<div class="p-3 text-muted small">Tidak ditemukan</div>';
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

function selectSearchResult(id) {
  searchResults.style.display = 'none';
  searchBox.value = '';
  showDetail(id);
}

document.addEventListener('click', (e) => {
  if (!e.target.closest('.search-wrap')) {
    searchResults.style.display = 'none';
  }
});

init();
</script>
@endsection