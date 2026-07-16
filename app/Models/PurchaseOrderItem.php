<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $guarded = ['id'];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
}

