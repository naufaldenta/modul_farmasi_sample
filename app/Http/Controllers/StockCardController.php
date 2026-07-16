<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Drug;
use App\Models\StockTransaction;
use Illuminate\Support\Carbon;

class StockCardController extends Controller
{
    public function index(Request $request)
    {
        $drugId = $request->get('drug_id');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $drugs = Drug::orderBy('name')->get();

        $transactions = [];
        $selectedDrug = null;

        if ($drugId) {
            $selectedDrug = Drug::find($drugId);
            
            $transactions = StockTransaction::with(['drugBatch', 'user'])
                ->where('drug_id', $drugId)
                ->whereBetween('transaction_date', [$startDate, clone Carbon::parse($endDate)->endOfDay()])
                ->orderBy('transaction_date', 'asc')
                ->get();
        }

        return inertia('Inventory/StockCard', [
            'drugs' => $drugs,
            'selectedDrug' => $selectedDrug,
            'transactions' => $transactions,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }
}
