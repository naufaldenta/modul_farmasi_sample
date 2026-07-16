<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\DrugBatch;
use App\Models\StockTransaction;
use App\Models\Distribution;
use Illuminate\Support\Facades\DB;
use Exception;

class FefoEngineService
{
    /**
     * Issues stock from batches using FEFO algorithm.
     * 
     * @param int $drugId
     * @param int $qtyNeeded
     * @param \App\Models\Distribution $distribution
     * @return void
     * @throws Exception
     */
    public function issue(int $drugId, int $qtyNeeded, Distribution $distribution): void
    {
        $startTime = microtime(true);

        $batches = DrugBatch::where('drug_id', $drugId)
            ->where('qty_available', '>', 0)
            ->where('expired_date', '>=', today())
            ->orderBy('expired_date', 'asc')
            ->lockForUpdate()
            ->get();

        $remaining = $qtyNeeded;

        foreach ($batches as $batch) {
            $take = min($batch->qty_available, $remaining);
            $batch->qty_available -= $take;
            $batch->save();

            StockTransaction::create([
                'drug_id' => $drugId,
                'drug_batch_id' => $batch->id,
                'type' => 'keluar',
                'qty' => $take,
                'reference_type' => Distribution::class,
                'reference_id' => $distribution->id,
                'user_id' => auth()->id(),
                'transaction_date' => now(),
                'is_fefo_compliant' => true,
            ]);

            $remaining -= $take;

            if ($remaining === 0) {
                break;
            }
        }

        if ($remaining > 0) {
            $drug = Drug::find($drugId);
            throw new Exception("Stok tidak mencukupi untuk {$drug->name}. Dibutuhkan {$qtyNeeded}, namun yang tersedia (belum expired) hanya " . ($qtyNeeded - $remaining) . ".");
        }

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        \Illuminate\Support\Facades\Log::info("FEFO Engine issued {$qtyNeeded} for drug {$drugId} in {$durationMs} ms");
    }
}
