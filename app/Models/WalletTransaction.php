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

    const STATUS_PENDING = 1;

    const STATUS_COMPLETED = 2;

    const STATUS_CANCELLED = 3;

    public function getStatusTextAttribute($value)
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'pending',
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_CANCELLED => 'cancelled',
            default => 'pending',
        };
    }

    public static function getStatusByName($status)
    {
        return match ($status) {
            'pending' => self::STATUS_PENDING,
            'completed' => self::STATUS_COMPLETED,
            'cancelled' => self::STATUS_CANCELLED,
            default => self::STATUS_PENDING,
        };
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trade()
    {
        return $this->hasOne(Trade::class);
    }
}
