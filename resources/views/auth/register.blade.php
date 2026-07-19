@extends('layouts.app')

@section('title', 'Daftar Akun - Global Logistics Dashboard')

@section('content')
<div class="luxury-card p-5" style="max-width: 520px; width: 100%; border: 1px solid var(--border-luxury-active); background: rgba(5, 8, 16, 0.85); box-shadow: 0 15px 50px rgba(212, 175, 55, 0.08); border-radius:20px;">
  <div class="text-center mb-5">
    <span class="gold-text-glow" style="font-size: 1.6rem; display:block;">GLOBAL LOGISTICS DASHBOARD</span>
    <small class="brand-subtitle" style="font-size:0.65rem; letter-spacing:0.25em;">SUPPLY CHAIN RISK MONITOR</small>
    <h4 class="mt-4 mb-0 text-white" style="font-weight: 600; letter-spacing: 0.04em;">Registrasi Kredensial Baru</h4>
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

  <form method="POST" action="/register">
    @csrf
    <div class="mb-3">
      <label class="form-luxury-label">Nama Lengkap</label>
      <input type="text" name="name" class="form-control form-luxury-input" placeholder="Masukkan nama lengkap..." value="{{ old('name') }}" required autocomplete="name" autofocus>
    </div>
    <div class="mb-3">
      <label class="form-luxury-label">Alamat Email Kerja</label>
      <input type="email" name="email" class="form-control form-luxury-input" placeholder="name@company.com" value="{{ old('email') }}" required autocomplete="email">
    </div>
    <div class="mb-3">
      <label class="form-luxury-label">Kata Sandi Baru</label>
      <input type="password" name="password" class="form-control form-luxury-input" placeholder="Min. 6 karakter..." required autocomplete="new-password">
    </div>
    <div class="mb-4">
      <label class="form-luxury-label">Konfirmasi Kata Sandi</label>
      <input type="password" name="password_confirmation" class="form-control form-luxury-input" placeholder="Ulangi password..." required>
    </div>
    
    <button type="submit" class="btn btn-luxury w-100 py-3 mt-2" style="font-size:0.9rem;">Daftarkan Akun Baru</button>
  </form>

  <div class="text-center mt-4 pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.03) !important;">
    <p class="text-muted small mb-0">Sudah memiliki akun terdaftar? <a href="/login" class="text-warning text-decoration-none fw-semibold">Login di sini</a></p>
  </div>
</div>
@endsection