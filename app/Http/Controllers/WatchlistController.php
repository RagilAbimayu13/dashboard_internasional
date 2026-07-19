<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    // Halaman tampilkan watchlist
    public function index()
    {
        return view('watchlist');
    }

    // POST /api/watchlist — tambah negara ke watchlist
    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $watchlist = Watchlist::firstOrCreate([
            'user_id' => auth()->id(),
            'country_id' => $validated['country_id'],
        ]);

        return response()->json(['success' => true, 'watchlist' => $watchlist]);
    }

    // GET /api/watchlist — daftar negara favorit user yang login
    public function apiIndex()
    {
        $watchlist = Watchlist::where('user_id', auth()->id())
            ->with('country')
            ->get();

        return response()->json($watchlist);
    }

    // DELETE /api/watchlist/{id} — hapus dari watchlist
    public function destroy($id)
    {
        Watchlist::where('user_id', auth()->id())
            ->where('country_id', $id)
            ->delete();

        return response()->json(['success' => true]);
    }
}