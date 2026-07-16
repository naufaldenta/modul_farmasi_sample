<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrugCategory extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $fillable = ['name', 'description'];

    public function drugs()
    {
        return $this->hasMany(Drug::class);
    }
}

