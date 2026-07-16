<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\DrugBatch;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoodsReceiptService
{
    public function generateReceiptNumber()
    {
        $date = now()->format('Ymd');
        $count = GoodsReceipt::whereDate('created_at', today())->count() + 1;
        return 'GR-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function createGoodsReceipt(array $data)
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::findOrFail($data['purchase_order_id']);

            $gr = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'receipt_number' => $data['receipt_number'] ?? $this->generateReceiptNumber(),
                'receipt_date' => $data['receipt_date'],
                'received_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                // Find or create DrugBatch
                $drugBatch = DrugBatch::firstOrCreate(
                    [
                        'drug_id' => $itemData['drug_id'],
                        'batch_number' => $itemData['batch_number'],
                    ],
                    [
                        'expired_date' => $itemData['expired_date'],
                        'qty_received' => 0,
                        'qty_available' => 0,
                    ]
                );

                // Add quantity to batch
                $drugBatch->qty_received += $itemData['qty'];
                $drugBatch->qty_available += $itemData['qty'];
                $drugBatch->save();

                // Create Goods Receipt Item
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr->id,
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'],
                    'drug_batch_id' => $drugBatch->id,
                    'batch_number' => $itemData['batch_number'],
                    'expired_date' => $itemData['expired_date'],
                    'qty' => $itemData['qty'],
                ]);

                // Create Stock Transaction (Ledger)
                StockTransaction::create([
                    'drug_id' => $itemData['drug_id'],
                    'drug_batch_id' => $drugBatch->id,
                    'type' => 'masuk',
                    'qty' => $itemData['qty'],
                    'reference_type' => GoodsReceipt::class,
                    'reference_id' => $gr->id,
                    'user_id' => auth()->id(),
                    'transaction_date' => $data['receipt_date'],
                ]);
            }

            // Update PO Status
            // Check if all requested items are fully received. For simplicity, we just mark as received.
            // (If this was a complex system, we would calculate total received vs requested).
            $po->update(['status' => 'received']);

            return $gr;
        });
    }
}
