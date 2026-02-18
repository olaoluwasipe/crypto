<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'base_currency_id',
        'quote_currency_id',
        'base_amount',
        'quote_amount',
        'price',
        'fee',
        'rate',
        'credit_transaction_id',
        'debit_transaction_id',
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

    public function getStatusTextAttribute($value)
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_CANCELLED => 'cancelled',
            default => 'pending',
        };
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

    public function creditTransaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function debitTransaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
