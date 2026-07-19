@extends('layouts.app')

@section('title', 'Kelola User - Admin')

@section('content')
<a href="/admin" class="text-decoration-none small">← Kembali ke Admin Dashboard</a>
<h2 class="mb-4 mt-2">👥 Kelola User</h2>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm">
  <table class="table mb-0">
    <thead>
      <tr class="text-muted small">
        <th class="ps-4">Nama</th>
        <th>Email</th>
        <th>Role</th>
        <th class="pe-4">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($users as $user)
        <tr>
          <td class="ps-4">{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td><span class="badge {{ $user->role === 'admin' ? 'bg-dark' : 'bg-secondary' }}">{{ $user->role }}</span></td>
          <td class="pe-4">
            <form method="POST" action="/admin/users/{{ $user->id }}/role" class="d-flex gap-2">
              @csrf
              <select name="role" class="form-select form-select-sm" style="width:120px">
                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>user</option>
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
              </select>
              <button type="submit" class="btn btn-sm btn-outline-dark">Update</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection