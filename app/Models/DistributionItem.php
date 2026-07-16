<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionItem extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $guarded = ['id'];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
}

