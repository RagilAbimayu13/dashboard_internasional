<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    // GET /api/countries — daftar semua negara
    public function index()
    {
        $countries = Country::with([
            'weatherSnapshots' => fn ($q) => $q->latest('recorded_at')->limit(1),
            'riskScores' => fn ($q) => $q->latest('calculated_at')->limit(1),
            'economicIndicators' => fn ($q) => $q->latest('recorded_at')->limit(1)
        ])->get();

        return response()->json($countries);
    }

    // GET /api/countries/{id} — profil lengkap 1 negara
    public function show($id)
    {
        $country = Country::with([
            'economicIndicators' => fn ($q) => $q->latest('recorded_at')->limit(1),
            'weatherSnapshots' => fn ($q) => $q->latest('recorded_at')->limit(1),
            'exchangeRates' => fn ($q) => $q->latest('recorded_at')->limit(1),
            'riskScores' => fn ($q) => $q->latest('calculated_at')->limit(1),
            'news' => fn ($q) => $q->latest('published_at')->limit(5),
            'ports',
        ])->findOrFail($id);

        return response()->json($country);
    }

    // GET /api/countries/{id}/history — data historis untuk grafik trend
    public function history($id)
    {
        $country = Country::findOrFail($id);

        $weatherHistory = $country->weatherSnapshots()
            ->orderBy('recorded_at')
            ->get(['temperature', 'recorded_at']);

        $riskHistory = $country->riskScores()
            ->orderBy('calculated_at')
            ->get(['total_score', 'calculated_at']);

        $economicHistory = $country->economicIndicators()
            ->orderBy('recorded_at')
            ->get(['gdp', 'inflation_rate', 'recorded_at']);

        $currencyHistory = $country->exchangeRates()
            ->orderBy('recorded_at')
            ->get(['rate_to_usd', 'recorded_at']);

        return response()->json([
            'weather' => $weatherHistory,
            'risk' => $riskHistory,
            'economic' => $economicHistory,
            'currency' => $currencyHistory,
        ]);
    }
}