<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\DemandForecast;
use App\Models\StockTransaction;
use Illuminate\Support\Carbon;

class ForecastService
{
    /**
     * Generate forecast for the next month based on historical data.
     * SMA (Simple Moving Average) method.
     */
    public function generateForecast($method = 'sma', $historyPeriods = 3)
    {
        $nextMonthDate = Carbon::now()->addMonth()->startOfMonth();
        $drugs = Drug::all();

        foreach ($drugs as $drug) {
            $predictedQty = 0;
            if ($method === 'sma') {
                $predictedQty = $this->calculateSMA($drug->id, $historyPeriods);
            } elseif ($method === 'wma') {
                $predictedQty = $this->calculateWMA($drug->id, $historyPeriods);
            } elseif ($method === 'linear_regression') {
                $predictedQty = $this->calculateLinearRegression($drug->id, $historyPeriods);
            }
            
            DemandForecast::updateOrCreate(
                [
                    'drug_id' => $drug->id,
                    'period_date' => $nextMonthDate->format('Y-m-d'),
                    'method' => $method,
                ],
                [
                    'predicted_qty' => max(0, round($predictedQty)), // Prevent negative forecast
                    'actual_qty' => null,
                    'mae' => null,
                    'mape' => null,
                ]
            );
        }
    }

    /**
     * Evaluate past forecasts where actual_qty is null, 
     * but the period has already passed.
     */
    public function evaluateForecasts()
    {
        // Get all forecasts where period is in the past month (or earlier) and actual_qty is null
        $startOfCurrentMonth = Carbon::now()->startOfMonth();
        
        $pendingForecasts = DemandForecast::where('period_date', '<', $startOfCurrentMonth)
            ->whereNull('actual_qty')
            ->get();

        foreach ($pendingForecasts as $forecast) {
            $periodDate = Carbon::parse($forecast->period_date);
            $start = $periodDate->copy()->startOfMonth();
            $end = $periodDate->copy()->endOfMonth();

            // Calculate actual qty out for this period
            $actualQty = StockTransaction::where('drug_id', $forecast->drug_id)
                ->where('type', 'keluar')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('qty');

            $mae = abs($actualQty - $forecast->predicted_qty);
            
            $mape = null;
            if ($actualQty > 0) {
                $mape = ($mae / $actualQty) * 100;
            }

            $forecast->update([
                'actual_qty' => $actualQty,
                'mae' => $mae,
                'mape' => $mape,
            ]);
        }
    }

    /**
     * Helper to calculate Simple Moving Average
     */
    private function calculateSMA($drugId, $months)
    {
        $startOfCurrentMonth = Carbon::now()->startOfMonth();
        $startOfHistory = $startOfCurrentMonth->copy()->subMonths($months);

        // Sum 'keluar' transactions in the past $months months
        $totalQty = StockTransaction::where('drug_id', $drugId)
            ->where('type', 'keluar')
            ->where('transaction_date', '>=', $startOfHistory)
            ->where('transaction_date', '<', $startOfCurrentMonth)
            ->sum('qty');

        if ($months > 0) {
            return $totalQty / $months;
        }

        return 0;
    }

    /**
     * Helper to calculate Weighted Moving Average
     */
    private function calculateWMA($drugId, $months)
    {
        if ($months <= 0) return 0;
        
        $sumQtyWeights = 0;
        $sumWeights = 0;
        
        $startOfCurrentMonth = Carbon::now()->startOfMonth();
        
        for ($i = 1; $i <= $months; $i++) {
            // $i = 1 is the oldest month, $i = $months is the most recent past month
            $targetMonth = $startOfCurrentMonth->copy()->subMonths($months - $i + 1);
            $start = $targetMonth->copy()->startOfMonth();
            $end = $targetMonth->copy()->endOfMonth();
            
            $qty = StockTransaction::where('drug_id', $drugId)
                ->where('type', 'keluar')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('qty');
                
            $sumQtyWeights += $qty * $i;
            $sumWeights += $i;
        }
        
        return $sumQtyWeights / $sumWeights;
    }

    /**
     * Helper to calculate Linear Regression
     */
    private function calculateLinearRegression($drugId, $months)
    {
        if ($months <= 1) return $this->calculateSMA($drugId, $months);
        
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        $n = $months;
        
        $startOfCurrentMonth = Carbon::now()->startOfMonth();
        
        for ($i = 1; $i <= $months; $i++) {
            $targetMonth = $startOfCurrentMonth->copy()->subMonths($months - $i + 1);
            $start = $targetMonth->copy()->startOfMonth();
            $end = $targetMonth->copy()->endOfMonth();
            
            $qty = StockTransaction::where('drug_id', $drugId)
                ->where('type', 'keluar')
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('qty');
                
            $x = $i;
            $y = $qty;
            
            $sumX += $x;
            $sumY += $y;
            $sumXY += ($x * $y);
            $sumX2 += ($x * $x);
        }
        
        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if ($denominator == 0) {
            return $this->calculateSMA($drugId, $months); // Fallback if no variance in X
        }
        
        $m = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        $b = ($sumY - ($m * $sumX)) / $n;
        
        // Predict for the next period (x = n + 1)
        $predicted = ($m * ($n + 1)) + $b;
        
        return $predicted;
    }
}
