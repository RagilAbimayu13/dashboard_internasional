<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    // GET /api/countries — daftar semua negara
    public function index()
    {
        return response()->json(Country::all());
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
}