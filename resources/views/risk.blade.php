@extends('layouts.app')

@section('title', 'Risk Ranking - Supply Chain Risk Platform')

@section('styles')
<style>
  .mono { font-family: 'IBM Plex Mono', monospace; }
  .risk-row { cursor: pointer; transition: background 0.1s; }
  .risk-row:hover { background: #f4f6f9; }
  .risk-pill { font-size: 0.72rem; font-weight: 600; padding: 3px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.03em; }
  .risk-pill.low { background: #e6f7f3; color: #0d9488; }
  .risk-pill.medium { background: #fef3e2; color: #b45309; }
  .risk-pill.high { background: #fde8e8; color: #c0392b; }
  .rank-number { font-weight: 700; color: #94a3b8; width: 2rem; display: inline-block; }
  .score-bar-bg { background: #e9ecef; border-radius: 4px; height: 8px; overflow: hidden; }
  .score-bar-fill { height: 100%; border-radius: 4px; }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="mb-1">📊 Risk Ranking</h2>
    <p class="text-muted mb-0">Negara diurutkan dari tingkat risiko tertinggi ke terendah.</p>
  </div>
  <select id="filterLevel" class="form-select w-auto">
    <option value="">Semua Level</option>
    <option value="High">High Risk</option>
    <option value="Medium">Medium Risk</option>
    <option value="Low">Low Risk</option>
  </select>
</div>

<div id="loading" class="text-muted">Memuat data risk score...</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0" id="riskTable" style="display:none">
      <thead>
        <tr class="text-muted small">
          <th class="ps-4">#</th>
          <th>Negara</th>
          <th>Weather</th>
          <th>Inflasi</th>
          <th>News</th>
          <th>Currency</th>
          <th style="width:180px">Total Score</th>
          <th class="pe-4">Level</th>
        </tr>
      </thead>
      <tbody id="riskTableBody"></tbody>
    </table>
  </div>
</div>

@endsection

@section('scripts')
<script>
let allScores = [];

function scoreColor(score) {
  if (score < 33) return '#0d9488';
  if (score < 66) return '#d97706';
  return '#dc3545';
}

async function loadRisk() {
  const res = await fetch('/api/risk');
  allScores = await res.json();

  document.getElementById('loading').style.display = 'none';
  document.getElementById('riskTable').style.display = 'table';

  renderTable(allScores);
}

function renderTable(scores) {
  const tbody = document.getElementById('riskTableBody');
  tbody.innerHTML = '';

  scores.forEach((s, i) => {
    const c = s.country;
    const levelClass = s.risk_level.toLowerCase();
    const color = scoreColor(s.total_score);

    tbody.innerHTML += `
      <tr class="risk-row" onclick="window.location.href='/#country-${c.id}'">
        <td class="ps-4"><span class="rank-number">${i + 1}</span></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            ${c.flag_url ? `<img src="${c.flag_url}" style="width:24px;height:16px;object-fit:cover;border-radius:2px">` : ''}
            <span class="fw-semibold">${c.name}</span>
          </div>
        </td>
        <td class="mono small">${Number(s.weather_score).toFixed(0)}</td>
        <td class="mono small">${Number(s.inflation_score).toFixed(0)}</td>
        <td class="mono small">${Number(s.news_sentiment_score).toFixed(0)}</td>
        <td class="mono small">${Number(s.currency_score).toFixed(0)}</td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="score-bar-bg flex-grow-1">
              <div class="score-bar-fill" style="width:${s.total_score}%; background:${color}"></div>
            </div>
            <span class="mono small fw-semibold" style="min-width:36px">${Number(s.total_score).toFixed(1)}</span>
          </div>
        </td>
        <td class="pe-4"><span class="risk-pill ${levelClass}">${s.risk_level}</span></td>
      </tr>
    `;
  });
}

document.getElementById('filterLevel').addEventListener('change', (e) => {
  const level = e.target.value;
  const filtered = level ? allScores.filter(s => s.risk_level === level) : allScores;
  renderTable(filtered);
});

loadRisk();
</script>
@endsection