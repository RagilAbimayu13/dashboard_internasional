@extends('layouts.app')

@section('title', 'Favorit - Supply Chain Risk Platform')

@section('content')

<h2 class="mb-1">⭐ Negara Favorit</h2>
<p class="text-muted mb-4">Negara yang sedang Anda pantau.</p>

<div id="loading" class="text-muted">Memuat...</div>
<div class="row g-3" id="watchlistGrid"></div>
<div id="emptyState" class="text-center text-muted py-5" style="display:none">
  Belum ada negara favorit. Tambahkan dari halaman detail negara di Dashboard.
</div>

@endsection

@section('scripts')
<script>
async function loadWatchlist() {
  const res = await fetch('/api/watchlist');
  const items = await res.json();

  document.getElementById('loading').style.display = 'none';

  if (items.length === 0) {
    document.getElementById('emptyState').style.display = 'block';
    return;
  }

  const grid = document.getElementById('watchlistGrid');
  grid.innerHTML = items.map(item => `
    <div class="col-md-3 col-6">
      <div class="card h-100">
        <div class="card-body text-center">
          ${item.country.flag_url ? `<img src="${item.country.flag_url}" style="width:48px;height:32px;object-fit:cover;border-radius:4px" class="mb-2">` : ''}
          <div class="fw-semibold">${item.country.name}</div>
          <div class="text-muted small mb-2">${item.country.region ?? '-'}</div>
          <button class="btn btn-sm btn-outline-danger" onclick="removeFromWatchlist(${item.country.id})">Hapus</button>
        </div>
      </div>
    </div>
  `).join('');
}

async function removeFromWatchlist(countryId) {
  await fetch(`/api/watchlist/${countryId}`, { method: 'DELETE' });
  loadWatchlist();
}

loadWatchlist();
</script>
@endsection