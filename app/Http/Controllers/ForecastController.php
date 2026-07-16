<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Drug;
use App\Models\DemandForecast;

class ForecastController extends Controller
{
    public function index(Request $request)
    {
        $drugId = $request->get('drug_id');
        $drugs = Drug::orderBy('name')->get();

        $forecasts = [];
        $selectedDrug = null;

        if ($drugId) {
            $selectedDrug = Drug::find($drugId);
            $forecasts = DemandForecast::where('drug_id', $drugId)
                ->orderBy('period_date', 'asc')
                ->get();
        }

        return inertia('Inventory/Forecast', [
            'drugs' => $drugs,
            'selectedDrug' => $selectedDrug,
            'forecasts' => $forecasts,
        ]);
    }

    public function generate(Request $request, \App\Services\ForecastService $forecastService)
    {
        $validated = $request->validate([
            'method' => 'required|in:sma,wma,linear_regression',
            'periods' => 'required|integer|min:1|max:12',
        ]);

        try {
            $forecastService->generateForecast($validated['method'], $validated['periods']);
            return back()->with('success', 'Forecasting berhasil digenerate menggunakan metode ' . strtoupper($validated['method']) . '.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan forecasting: ' . $e->getMessage());
        }
    }
}
