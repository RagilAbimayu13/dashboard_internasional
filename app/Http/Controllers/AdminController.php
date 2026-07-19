<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Country;
use App\Models\NewsCache;
use App\Models\Port;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // GET /admin — ringkasan
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'countries' => Country::count(),
            'ports' => Port::count(),
            'articles' => Article::count(),
            'news' => NewsCache::count(),
        ];

        return view('admin.index', compact('stats'));
    }

    // GET /admin/users
    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    // POST /admin/users/{id}/role — ubah role user
    public function updateRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user = User::findOrFail($id);
        $user->update(['role' => $validated['role']]);

        return back()->with('success', "Role {$user->name} berhasil diubah menjadi {$validated['role']}.");
    }

    // GET /admin/articles
    public function articles()
    {
        $articles = Article::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.articles', compact('articles'));
    }

    // POST /admin/articles — buat artikel baru
    public function storeArticle(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Article::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'published_at' => now(),
        ]);

        return back()->with('success', 'Artikel berhasil dipublikasikan.');
    }

    // DELETE /admin/articles/{id}
    public function destroyArticle($id)
    {
        Article::findOrFail($id)->delete();
        return back()->with('success', 'Artikel berhasil dihapus.');
    }

    // POST /admin/ports — tambah pelabuhan baru
    public function storePort(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'port_type' => 'nullable|string|max:50',
        ]);

        Port::create($validated);

        return back()->with('success', 'Data pelabuhan baru berhasil ditambahkan.');
    }

    // DELETE /admin/ports/{id} — hapus pelabuhan
    public function destroyPort($id)
    {
        Port::findOrFail($id)->delete();
        return back()->with('success', 'Data pelabuhan berhasil dihapus.');
    }
}