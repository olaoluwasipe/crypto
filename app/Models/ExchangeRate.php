<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'base_currency_id',
        'quote_currency_id',
        'rate',
        'source',
        'status',
        'created_at',
        'updated_at',
    ];

    public function baseCurrency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function quoteCurrency()
    {
        return $this->belongsTo(Currency::class);
    }
}
