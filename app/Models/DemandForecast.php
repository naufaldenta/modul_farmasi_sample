<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandForecast extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $guarded = ['id'];

    protected $casts = [
        'period_date' => 'date',
        'predicted_qty' => 'decimal:2',
        'mae' => 'decimal:2',
        'mape' => 'decimal:2',
    ];

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
}

