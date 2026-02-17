<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'user_id',
        'base_currency_id',
        'quote_currency_id',
        'base_amount',
        'quote_amount',
        'price',
        'fee',
        'fee_currency_id',
        'type',
        'status',
        'executed_at',
        'created_at',
        'updated_at',
    ];

    const STATUS_PENDING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELLED = 3;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function baseCurrency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function quoteCurrency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function feeCurrency()
    {
        return $this->belongsTo(Currency::class);
    }
}
