<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ReorderAlert;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class InventoryEvaluationService
{
    /**
     * Evaluate inventory for all drugs, update ROP/Safety Stock,
     * and trigger Auto-PO if necessary.
     */
    public function evaluateInventory(int $days = 30, int $bufferDays = 3)
    {
        $drugs = Drug::with('suppliers')->get();

        foreach ($drugs as $drug) {
            // 1. Calculate Average Daily Usage
            $avgDailyUsage = $this->calculateAvgDailyUsage($drug->id, $days);

            // 2. Find fastest supplier lead time
            $fastestSupplier = $drug->suppliers()->orderBy('lead_time_days', 'asc')->first();
            $leadTimeDays = $fastestSupplier ? $fastestSupplier->lead_time_days : 1; // fallback 1 day

            // 3. Calculate Safety Stock
            $safetyStock = $avgDailyUsage * $bufferDays;

            // 4. Calculate ROP
            $rop = ($avgDailyUsage * $leadTimeDays) + $safetyStock;

            // 5. Update Drug
            $drug->update([
                'avg_daily_usage' => $avgDailyUsage,
                'safety_stock' => $safetyStock,
                'rop' => $rop,
            ]);

            // 6. Check for Reorder
            $totalActiveStock = $drug->batches()
                ->where('qty_available', '>', 0)
                ->where('expired_date', '>=', Carbon::today())
                ->sum('qty_available');

            if ($totalActiveStock <= $rop) {
                $this->triggerAutoPO($drug, $fastestSupplier, $rop, $totalActiveStock);
            }
        }
    }

    /**
     * Calculate Average Daily Usage based on stock transactions of type 'keluar'
     */
    public function calculateAvgDailyUsage(int $drugId, int $days): float
    {
        $startDate = Carbon::today()->subDays($days);

        $totalKeluar = StockTransaction::where('drug_id', $drugId)
            ->where('type', 'keluar')
            ->where('transaction_date', '>=', $startDate)
            ->sum('qty');

        return round($totalKeluar / $days, 2);
    }

    /**
     * Trigger Auto-PO and create Reorder Alert
     */
    protected function triggerAutoPO(Drug $drug, $supplier, $rop, $totalActiveStock)
    {
        // Prevent duplicate draft auto-PO for the same drug
        $hasPendingAutoPO = PurchaseOrderItem::where('drug_id', $drug->id)
            ->whereHas('purchaseOrder', function ($q) {
                $q->where('is_auto', true)
                  ->whereIn('status', ['draft', 'approved']); // If it's already received, we can order again.
            })->exists();

        if ($hasPendingAutoPO) {
            return;
        }

        DB::transaction(function () use ($drug, $supplier, $rop, $totalActiveStock) {
            // Calculate order qty (e.g. order enough to reach ROP + safety stock)
            // Or just a fixed formula. Let's order (ROP - active) + safety stock. 
            // Better: Order ROP * 2 to be safe, or just 100 as default if avg is low.
            $orderQty = max($rop * 2, 10); 
            $orderQty = round($orderQty);

            if ($supplier) {
                // Find existing draft PO for this supplier that is_auto, to group them
                $po = PurchaseOrder::where('supplier_id', $supplier->id)
                    ->where('is_auto', true)
                    ->where('status', 'draft')
                    ->first();

                if (!$po) {
                    $po = PurchaseOrder::create([
                        'supplier_id' => $supplier->id,
                        'po_number' => 'PO-AUTO-' . date('YmdHis') . '-' . rand(100, 999),
                        'po_date' => Carbon::today(),
                        'status' => 'draft',
                        'is_auto' => true,
                        'created_by' => 1, // System or Admin ID
                    ]);
                }

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'drug_id' => $drug->id,
                    'qty' => $orderQty,
                    'price' => $drug->price, // fallback to drug price
                ]);
            }

            ReorderAlert::create([
                'drug_id' => $drug->id,
                'message' => "Stok {$drug->name} ({$totalActiveStock}) di bawah ROP ({$rop}). " . ($supplier ? "Draft PO Otomatis dibuat." : "Tidak ada supplier, PO gagal."),
                'status' => 'unread',
            ]);
        });
    }

    /**
     * Classify ABC based on Usage Value
     */
    public function classifyABC(int $periodDays = 30)
    {
        $drugs = Drug::all();
        $usageValues = [];
        $totalUsageValue = 0;

        foreach ($drugs as $drug) {
            // value = avg_daily_usage * price * period_days
            $value = $drug->avg_daily_usage * $drug->price * $periodDays;
            $usageValues[] = [
                'drug' => $drug,
                'value' => $value,
            ];
            $totalUsageValue += $value;
        }

        // Sort descending by value
        usort($usageValues, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        $cumulativeValue = 0;

        foreach ($usageValues as $item) {
            $cumulativeValue += $item['value'];
            
            // Handle division by zero if totalUsageValue is 0
            $percentage = $totalUsageValue > 0 ? ($cumulativeValue / $totalUsageValue) * 100 : 100;

            if ($percentage <= 80) {
                $class = 'A';
            } elseif ($percentage <= 95) {
                $class = 'B';
            } else {
                $class = 'C';
            }

            $item['drug']->update(['abc_class' => $class]);
        }
    }
}
