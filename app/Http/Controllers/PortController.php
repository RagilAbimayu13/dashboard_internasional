<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    // GET /api/ports — semua pelabuhan (dengan info negara)
    // GET /api/ports?country_id=5 — filter per negara
    public function index(Request $request)
    {
        $query = Port::with('country');

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        return response()->json($query->get());
    }
}