<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'reference',
        'description',
        'metadata',
        'amount',
        'status',
        'idempotency_key',
        'prev_balance',
        'new_balance',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    const TYPE_DEBIT = 'debit';
    const TYPE_CREDIT = 'credit';

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
