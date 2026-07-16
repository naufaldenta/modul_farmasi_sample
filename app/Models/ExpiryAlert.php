<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpiryAlert extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $fillable = [
        'drug_batch_id',
        'alert_level',
        'status',
        'notes',
    ];

    public function drugBatch()
    {
        return $this->belongsTo(DrugBatch::class);
    }
}

