<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrugBatch extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $guarded = ['id'];

    protected $fillable = [
        'drug_id', 'batch_number', 'expired_date', 
        'qty_received', 'qty_available'
    ];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }
}

