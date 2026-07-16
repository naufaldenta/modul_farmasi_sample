<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $guarded = ['id'];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function drugBatch()
    {
        return $this->belongsTo(DrugBatch::class);
    }
}

