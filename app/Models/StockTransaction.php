<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $fillable = [
        'drug_id', 'drug_batch_id', 'type', 'qty', 
        'reference_type', 'reference_id', 'user_id', 'transaction_date', 'is_fefo_compliant'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'is_fefo_compliant' => 'boolean',
    ];

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function drugBatch()
    {
        return $this->belongsTo(DrugBatch::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

