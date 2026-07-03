<?php

namespace App\Http\Controllers;

use App\Models\RiskScore;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    // GET /api/risk — daftar risk score terbaru semua negara, diurutkan dari tertinggi
    public function index()
    {
        $scores = RiskScore::with('country')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('risk_scores')
                    ->groupBy('country_id');
            })
            ->orderByDesc('total_score')
            ->get();

        return response()->json($scores);
    }
}