<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'address',
        'lead_time_days',
    ];

    public function drugs(): BelongsToMany
    {
        return $this->belongsToMany(Drug::class);
    }
}

