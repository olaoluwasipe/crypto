<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable = [
        'type',
        'currency_id',
        'amount',
        'percentage',
        'fixed_amount',
        'min_amount',
        'max_amount',
        'status',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
