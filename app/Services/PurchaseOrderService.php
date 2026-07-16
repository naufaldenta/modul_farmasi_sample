<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderService
{
    public function generatePoNumber()
    {
        $date = now()->format('Ymd');
        $count = PurchaseOrder::whereDate('created_at', today())->count() + 1;
        return 'PO-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function createPurchaseOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'po_number' => $data['po_number'] ?? $this->generatePoNumber(),
                'po_date' => $data['po_date'],
                'status' => 'draft',
                'is_auto' => false,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'drug_id' => $item['drug_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);
            }

            return $po;
        });
    }

    public function approvePurchaseOrder(PurchaseOrder $po)
    {
        $po->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);
        
        return $po;
    }
}
