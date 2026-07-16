<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use \App\Models\Traits\SerializeDate;

    protected $fillable = [
        'category_id', 'code', 'name', 'unit', 'price', 
        'min_stock', 'max_stock', 'avg_daily_usage', 'safety_stock', 'rop', 'abc_class', 'description'
    ];

    public function category()
    {
        return $this->belongsTo(DrugCategory::class);
    }

    public function batches()
    {
        return $this->hasMany(DrugBatch::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function suppliers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
    }
}

