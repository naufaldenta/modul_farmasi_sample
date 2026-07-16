<?php

namespace App\Services;

use App\Models\StockTransaction;
use App\Models\DrugBatch;
use App\Models\Drug;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ResearchEvaluationService
{
    /**
     * Menghitung tingkat kepatuhan FEFO
     */
    public function calculateFefoCompliance(): array
    {
        $totalOut = StockTransaction::where('type', 'keluar')->count();
        $compliantOut = StockTransaction::where('type', 'keluar')->where('is_fefo_compliant', true)->count();
        
        $complianceRate = $totalOut > 0 ? round(($compliantOut / $totalOut) * 100, 2) : 0;

        return [
            'total_transactions' => $totalOut,
            'compliant_transactions' => $compliantOut,
            'compliance_rate' => $complianceRate,
        ];
    }

    /**
     * Mensimulasikan jumlah waste (expired) jika menggunakan FEFO vs FIFO
     */
    public function simulateWaste(): array
    {
        // Dalam dunia nyata, simulasi ini butuh me-replay seluruh transaksi.
        // Karena seeder sudah menggunakan FEFO secara strictly compliant, kita tahu waste FEFO aktual.
        // Kita bandingkan jumlah batch yang saat ini expired vs jika kita me-replay dengan FIFO.
        
        $actualFefoWasteValue = 0;
        $actualFefoWasteQty = 0;

        // Ambil semua sisa batch (saat ini) yang tanggal expired-nya sudah lewat
        $expiredBatches = DrugBatch::with('drug')->where('expired_date', '<', Carbon::now())
            ->where('qty_available', '>', 0)
            ->get();
            
        foreach ($expiredBatches as $batch) {
            $actualFefoWasteQty += $batch->qty_available;
            $actualFefoWasteValue += ($batch->qty_available * $batch->drug->price);
        }

        // Simulasi FIFO
        // Kumpulkan total barang masuk dan keluar per obat
        $fifoWasteQty = 0;
        $fifoWasteValue = 0;
        
        $drugs = Drug::all();
        foreach ($drugs as $drug) {
            // Kita urutkan batch masuk berdasarkan created_at (FIFO)
            $batches = DrugBatch::where('drug_id', $drug->id)->orderBy('created_at', 'asc')->get();
            
            // Hitung total pemakaian obat ini
            $totalKeluar = StockTransaction::where('drug_id', $drug->id)
                ->where('type', 'keluar')
                ->sum('qty');
                
            $remainingToIssue = $totalKeluar;
            
            // Replay pengeluaran FIFO
            foreach ($batches as $batch) {
                // Berapa banyak qty sebenarnya diterima batch ini (awal mula)
                $qty = $batch->qty_received;
                
                if ($remainingToIssue >= $qty) {
                    $remainingToIssue -= $qty;
                    // Batch habis
                    $leftover = 0;
                } else {
                    $leftover = $qty - $remainingToIssue;
                    $remainingToIssue = 0;
                }
                
                // Jika masih ada sisa, dan expired, maka jadi waste
                if ($leftover > 0 && Carbon::parse($batch->expired_date)->isPast()) {
                    $fifoWasteQty += $leftover;
                    $fifoWasteValue += ($leftover * $drug->price);
                }
            }
        }

        return [
            'fefo' => [
                'waste_qty' => $actualFefoWasteQty,
                'waste_value' => $actualFefoWasteValue,
            ],
            'fifo' => [
                'waste_qty' => $fifoWasteQty,
                'waste_value' => $fifoWasteValue,
            ],
            'savings_qty' => $fifoWasteQty - $actualFefoWasteQty,
            'savings_value' => $fifoWasteValue - $actualFefoWasteValue,
        ];
    }

    /**
     * Evaluasi Forecast menggunakan Data Split (misal: train 9 bulan, test 3 bulan terakhir)
     */
    public function evaluateForecastSplit(int $trainMonths = 9, int $testMonths = 3, int $smaPeriod = 3): array
    {
        $forecastService = new ForecastService();
        $results = [];

        $drugs = Drug::all();
        
        $totalMae = 0;
        $totalMape = 0;
        $evaluatedCount = 0;

        foreach ($drugs as $drug) {
            // Untuk simulasi evaluasi SMA
            // Ambil data bulanan
            $monthlyData = StockTransaction::select(
                DB::raw('DATE_FORMAT(transaction_date, "%Y-%m") as period'),
                DB::raw('SUM(qty) as total_qty')
            )
            ->where('drug_id', $drug->id)
            ->where('type', 'keluar')
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get();

            if ($monthlyData->count() < ($trainMonths + $testMonths)) {
                continue; // Data tidak cukup
            }

            // Split
            $testSet = $monthlyData->slice(-$testMonths);
            
            $drugMae = 0;
            $drugMape = 0;
            $validTests = 0;

            foreach ($testSet as $index => $testData) {
                // Untuk bulan $testData->period, ambil data historis sebelum bulan tersebut
                $periodDate = Carbon::createFromFormat('Y-m', $testData->period)->startOfMonth();
                $actual = (int)$testData->total_qty;

                // Train set adalah semua data sebelum $periodDate
                $trainSet = $monthlyData->filter(function ($item) use ($periodDate) {
                    return Carbon::createFromFormat('Y-m', $item->period)->startOfMonth()->lessThan($periodDate);
                });

                // Ambil N period terakhir dari trainSet
                $lastN = $trainSet->slice(-$smaPeriod)->pluck('total_qty')->toArray();
                
                if (count($lastN) === $smaPeriod) {
                    $predicted = (int)round(array_sum($lastN) / $smaPeriod);
                    
                    $error = abs($predicted - $actual);
                    $drugMae += $error;
                    if ($actual > 0) {
                        $drugMape += ($error / $actual) * 100;
                    }
                    $validTests++;
                }
            }

            if ($validTests > 0) {
                $avgDrugMae = $drugMae / $validTests;
                $avgDrugMape = $drugMape / $validTests;
                
                $results[] = [
                    'drug' => $drug->name,
                    'mae' => round($avgDrugMae, 2),
                    'mape' => round($avgDrugMape, 2)
                ];

                $totalMae += $avgDrugMae;
                $totalMape += $avgDrugMape;
                $evaluatedCount++;
            }
        }

        $overallMae = $evaluatedCount > 0 ? $totalMae / $evaluatedCount : 0;
        $overallMape = $evaluatedCount > 0 ? $totalMape / $evaluatedCount : 0;

        return [
            'overall_mae' => round($overallMae, 2),
            'overall_mape' => round($overallMape, 2),
            'evaluated_drugs' => $evaluatedCount,
            'details' => $results,
        ];
    }
}
