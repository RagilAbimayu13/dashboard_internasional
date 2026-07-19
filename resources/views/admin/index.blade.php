@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<h2 class="mb-4">🛠️ Admin Dashboard</h2>

<div class="row g-3 mb-4">
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm p-3">
      <div class="text-muted small">Total User</div>
      <div class="fs-3 fw-bold">{{ $stats['users'] }}</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm p-3">
      <div class="text-muted small">Negara</div>
      <div class="fs-3 fw-bold">{{ $stats['countries'] }}</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm p-3">
      <div class="text-muted small">Pelabuhan</div>
      <div class="fs-3 fw-bold">{{ $stats['ports'] }}</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="card border-0 shadow-sm p-3">
      <div class="text-muted small">Berita</div>
      <div class="fs-3 fw-bold">{{ $stats['news'] }}</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <a href="/admin/users" class="card border-0 shadow-sm p-4 text-decoration-none text-dark card-hover">
      <h5 class="mb-1">👥 Kelola User</h5>
      <p class="text-muted mb-0 small">Lihat dan ubah role pengguna</p>
    </a>
  </div>
  <div class="col-md-6">
    <a href="/admin/articles" class="card border-0 shadow-sm p-4 text-decoration-none text-dark card-hover">
      <h5 class="mb-1">📝 Kelola Artikel</h5>
      <p class="text-muted mb-0 small">Tulis dan kelola artikel analisis</p>
    </a>
  </div>
</div>
@endsection