<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'code',
        'precision',
        'type',
        'min_trade_amount',
        'max_trade_amount',
        'logo',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getLogoAttribute($value)
    {
        return url('storage/' . $value);
    }
}
