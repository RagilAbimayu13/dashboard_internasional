@extends('layouts.app')

@section('title', 'Kelola Artikel - Admin')

@section('content')
<a href="/admin" class="text-decoration-none small">← Kembali ke Admin Dashboard</a>
<h2 class="mb-4 mt-2">📝 Kelola Artikel</h2>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm p-4 mb-4">
  <h5 class="mb-3">Tulis Artikel Baru</h5>
  <form method="POST" action="/admin/articles">
    @csrf
    <div class="mb-3">
      <label class="form-label">Judul</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Isi Artikel</label>
      <textarea name="content" class="form-control" rows="5" required></textarea>
    </div>
    <button type="submit" class="btn btn-dark">Publikasikan</button>
  </form>
</div>

<h5 class="mb-3">Artikel Terpublikasi</h5>
@forelse ($articles as $article)
  <div class="card border-0 shadow-sm p-3 mb-2">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h6 class="mb-1">{{ $article->title }}</h6>
        <p class="text-muted small mb-1">{{ $article->content }}</p>
        <span class="text-muted small">oleh {{ $article->user->name ?? '-' }} · {{ $article->published_at?->format('d M Y') }}</span>
      </div>
      <form method="POST" action="/admin/articles/{{ $article->id }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
      </form>
    </div>
  </div>
@empty
  <p class="text-muted">Belum ada artikel.</p>
@endforelse
@endsection