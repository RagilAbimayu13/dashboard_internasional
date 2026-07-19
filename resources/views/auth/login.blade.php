@extends('layouts.app')

@section('title', 'Akses Masuk - Global Logistics Dashboard')

@section('content')
<div class="luxury-card p-5" style="max-width: 480px; width: 100%; border: 1px solid var(--border-luxury-active); background: rgba(5, 8, 16, 0.85); box-shadow: 0 15px 50px rgba(212, 175, 55, 0.08); border-radius:20px;">
  <div class="text-center mb-5">
    <span class="gold-text-glow" style="font-size: 1.6rem; display:block;">GLOBAL LOGISTICS DASHBOARD</span>
    <small class="brand-subtitle" style="font-size:0.65rem; letter-spacing:0.25em;">SUPPLY CHAIN RISK MONITOR</small>
    <h4 class="mt-4 mb-0 text-white" style="font-weight: 600; letter-spacing: 0.04em;">Akses Masuk Dashboard</h4>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger border-0 small mb-4" style="background: rgba(239, 68, 68, 0.15); color: var(--risk-high); border-radius: 8px;">
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="/login">
    @csrf
    <div class="mb-4">
      <label class="form-luxury-label">Alamat Email Kredensial</label>
      <input type="email" name="email" class="form-control form-luxury-input" placeholder="name@company.com" value="{{ old('email') }}" required autocomplete="email" autofocus>
    </div>
    <div class="mb-4">
      <label class="form-luxury-label">Kata Sandi Akses</label>
      <input type="password" name="password" class="form-control form-luxury-input" placeholder="Masukkan password..." required>
    </div>
    
    <button type="submit" class="btn btn-luxury w-100 py-3 mt-3" style="font-size:0.9rem;">Autentikasi & Masuk</button>
  </form>

  <div class="text-center mt-4 pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.03) !important;">
    <p class="text-muted small mb-0">Belum memiliki kredensial akses? <a href="/register" class="text-warning text-decoration-none fw-semibold">Daftar Akun Baru</a></p>
  </div>
</div>
@endsection