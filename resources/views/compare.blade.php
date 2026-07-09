@extends('layouts.app')

@section('title', 'Bandingkan Negara - Supply Chain Risk Platform')

@section('styles')
<style>
  .mono { font-family: 'IBM Plex Mono', monospace; }
  .compare-select-wrap { position: relative; }
  .compare-results { display: none; }
  .metric-row { border-bottom: 1px solid #eee; padding: 14px 0; }
  .metric-label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.04em; }
  .winner { position: relative; }
  .winner::after { content: '👑'; margin-left: 6px; font-size: 0.85rem; }
  .risk-pill { font-size: 0.72rem; font-weight: 600; padding: 3px 12px; border-radius: 20px; text-transform: uppercase; }
  .risk-pill.low { background: #e6f7f3; color: #0d9488; }
  .risk-pill.medium { background: #fef3e2; color: #b45309; }
  .risk-pill.high { background: #fde8e8; color: #c0392b; }
</style>
@endsection

@section('content')

<h2 class="mb-1">🔍 Bandingkan Negara</h2>
<p class="text-muted mb-4">Pilih 2 negara untuk membandingkan indikator ekonomi, cuaca, dan risiko.</p>

<div class="row g-3 mb-4">
  <div class="col-md-5">
    <label class="form-label small text-muted">Negara Pertama</label>
    <select id="countryA" class="form-select form-select-lg"><option value="">Pilih negara...</option></select>
  </div>
  <div class="col-md-2 d-flex align-items-end justify-content-center">
    <span class="fs-4 text-muted mb-2">vs</span>
  </div>
  <div class="col-md-5">
    <label class="form-label small text-muted">Negara Kedua</label>
    <select id="countryB" class="form-select form-select-lg"><option value="">Pilih negara...</option></select>
  </div>
</div>

<div id="placeholder" class="text-center text-muted py-5">
  Pilih 2 negara di atas untuk melihat perbandingan.
</div>

<div id="compareResults" class="compare-results">
  <div class="row mb-4 text-center">
    <div class="col-6">
      <h4 id="nameA">-</h4>
      <span id="riskPillA"></span>
    </div>
    <div class="col-6">
      <h4 id="nameB">-</h4>
      <span id="riskPillB"></span>
    </div>
  </div>

  <div id="metricsTable"></div>
</div>

@endsection

@section('scripts')
<script>
let allCountries = [];

async function init() {
  const res = await fetch('/api/countries');
  allCountries = (await res.json()).sort((a, b) => a.name.localeCompare(b.name));

  const optionsHtml = allCountries.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  document.getElementById('countryA').insertAdjacentHTML('beforeend', optionsHtml);
  document.getElementById('countryB').insertAdjacentHTML('beforeend', optionsHtml);
}

document.getElementById('countryA').addEventListener('change', tryCompare);
document.getElementById('countryB').addEventListener('change', tryCompare);

async function tryCompare() {
  const idA = document.getElementById('countryA').value;
  const idB = document.getElementById('countryB').value;

  if (!idA || !idB) return;

  const [resA, resB] = await Promise.all([
    fetch(`/api/countries/${idA}`),
    fetch(`/api/countries/${idB}`),
  ]);
  const a = await resA.json();
  const b = await resB.json();

  renderComparison(a, b);
}

function metricRow(label, valA, valB, unit = '', higherIsBetter = null) {
  let winA = '', winB = '';

  if (higherIsBetter !== null && valA !== null && valB !== null && valA !== valB) {
    const aWins = higherIsBetter ? valA > valB : valA < valB;
    winA = aWins ? 'winner' : '';
    winB = !aWins ? 'winner' : '';
  }

  const fmt = (v) => v === null || v === undefined ? '-' : (typeof v === 'number' ? v.toLocaleString() : v) + unit;

  return `
    <div class="row metric-row">
      <div class="col-4 metric-label align-self-center">${label}</div>
      <div class="col-4 text-center mono ${winA}">${fmt(valA)}</div>
      <div class="col-4 text-center mono ${winB}">${fmt(valB)}</div>
    </div>
  `;
}

function riskPillHtml(risk) {
  if (!risk) return '<span class="text-muted small">Belum ada data</span>';
  return `<span class="risk-pill ${risk.risk_level.toLowerCase()}">${risk.risk_level} · ${risk.total_score}</span>`;
}

function renderComparison(a, b) {
  document.getElementById('placeholder').style.display = 'none';
  document.getElementById('compareResults').style.display = 'block';

  document.getElementById('nameA').textContent = a.name;
  document.getElementById('nameB').textContent = b.name;

  const riskA = a.risk_scores?.[0];
  const riskB = b.risk_scores?.[0];
  document.getElementById('riskPillA').innerHTML = riskPillHtml(riskA);
  document.getElementById('riskPillB').innerHTML = riskPillHtml(riskB);

  const ecoA = a.economic_indicators?.[0];
  const ecoB = b.economic_indicators?.[0];
  const weatherA = a.weather_snapshots?.[0];
  const weatherB = b.weather_snapshots?.[0];
  const fxA = a.exchange_rates?.[0];
  const fxB = b.exchange_rates?.[0];

  document.getElementById('metricsTable').innerHTML = `
    <div class="row metric-label mb-2">
      <div class="col-4"></div>
      <div class="col-4 text-center">${a.name}</div>
      <div class="col-4 text-center">${b.name}</div>
    </div>
    ${metricRow('GDP (USD)', ecoA?.gdp ? Math.round(ecoA.gdp) : null, ecoB?.gdp ? Math.round(ecoB.gdp) : null, '', true)}
    ${metricRow('Inflasi', ecoA?.inflation_rate ?? null, ecoB?.inflation_rate ?? null, '%', false)}
    ${metricRow('Populasi', ecoA?.population ?? null, ecoB?.population ?? null, '', true)}
    ${metricRow('Risk Score', riskA?.total_score ?? null, riskB?.total_score ?? null, '', false)}
    ${metricRow('Suhu', weatherA?.temperature ?? null, weatherB?.temperature ?? null, '°C', null)}
    ${metricRow('Kurs / USD', fxA?.rate_to_usd ? Math.round(fxA.rate_to_usd) : null, fxB?.rate_to_usd ? Math.round(fxB.rate_to_usd) : null, '', null)}
    ${metricRow('Jumlah Pelabuhan', a.ports?.length ?? 0, b.ports?.length ?? 0, '', true)}
  `;
}

init();
</script>
@endsection