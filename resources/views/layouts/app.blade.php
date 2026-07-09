<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Supply Chain Risk Dashboard')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
  body { background: #f4f6f9; }
  .navbar-brand { font-weight: 600; }
  .card-hover { transition: box-shadow 0.15s; cursor: pointer; }
  .card-hover:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.1); }
  .risk-low { color: #198754; font-weight: 600; }
  .risk-medium { color: #b8860b; font-weight: 600; }
  .risk-high { color: #dc3545; font-weight: 600; }
</style>
@yield('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="/">🌍 Supply Chain Risk Platform</a>
    <div class="navbar-nav">
      <a class="nav-link" href="/">Dashboard</a>
      <a class="nav-link" href="/risk">Risk Ranking</a>
      <a class="nav-link" href="/compare">Bandingkan</a>
    </div>
  </div>
</nav>

<div class="container pb-5">
  @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@yield('scripts')

</body>
</html>