<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Country;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * GET /api/currency
     * Returns latest exchange rates for all countries, or history if query param is set.
     */
    public function index(Request $request)
    {
        $query = ExchangeRate::with('country');

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->has('currency_code')) {
            $query->where('currency_code', $request->currency_code);
        }

        // Check if history is requested
        if ($request->has('history') && $request->history === 'true') {
            $rates = $query->orderBy('recorded_at', 'desc')->get();
            return response()->json($rates);
        }

        // Return latest rate per country
        $rates = ExchangeRate::with('country')
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('exchange_rates')
                    ->groupBy('country_id');
            })
            ->when($request->has('country_id'), function ($q) use ($request) {
                return $q->where('country_id', $request->country_id);
            })
            ->when($request->has('currency_code'), function ($q) use ($request) {
                return $q->where('currency_code', $request->currency_code);
            })
            ->get();

        return response()->json($rates);
    }
}
