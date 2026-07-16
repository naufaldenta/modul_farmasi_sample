<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(DistributionItem::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function stockTransactions()
    {
        return $this->morphMany(StockTransaction::class, 'reference');
    }
}

