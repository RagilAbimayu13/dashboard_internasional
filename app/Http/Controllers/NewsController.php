<?php

namespace App\Http\Controllers;

use App\Models\NewsCache;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // GET /api/news — berita terbaru, bisa difilter per kategori/negara
    public function index(Request $request)
    {
        $query = NewsCache::with('country')->latest('published_at');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        return response()->json($query->limit(50)->get());
    }
}