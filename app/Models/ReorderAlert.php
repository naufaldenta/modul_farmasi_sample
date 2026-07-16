<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReorderAlert extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $fillable = [
        'drug_id',
        'message',
        'status',
    ];

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
}

