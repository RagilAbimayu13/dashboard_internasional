<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Country;
use App\Models\Port;
use App\Models\Article;
use App\Models\NewsCache;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the consolidated dashboard.
     */
    public function index()
    {
        $stats = null;
        $users = null;
        $articles = null;

        if (auth()->check() && auth()->user()->role === 'admin') {
            $stats = [
                'users' => User::count(),
                'countries' => Country::count(),
                'ports' => Port::count(),
                'articles' => Article::count(),
                'news' => NewsCache::count(),
            ];
            $users = User::orderBy('created_at', 'desc')->get();
            $articles = Article::with('user')->orderBy('created_at', 'desc')->get();
        }

        return view('dashboard', compact('stats', 'users', 'articles'));
    }
}
