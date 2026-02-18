<?php

namespace App\Services\v1\Wallet;

use App\Contracts\v1\Wallet\WalletContract;
use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;

class WalletService implements WalletContract
{
    public function lockWallet(User $user, Currency $currency)
    {
        if (! $currency) {
            throw new \Exception('Currency not found');
        }

        $wallet = $user->wallets()->where('currency_id', $currency->id)->lockForUpdate()->first();
        if (! $wallet) {
            throw new \Exception('Wallet not found');
        }

        // $wallet->status = 0;
        // $wallet->save();
        return $wallet;
    }

    public function ensureSufficientBalance(Wallet $wallet, $amount)
    {
        $allDebits = $wallet->transactions()->where('type', 'debit')->sum('amount');
        $allCredits = $wallet->transactions()->where('type', 'credit')->sum('amount');
        $balance = $allCredits - $allDebits;
        if ($balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        return $wallet;
    }

    public function debit(Wallet $wallet, $amount, User $user)
    {
        $prevBalance = $wallet->balance;
        $wallet->decrement('balance', $amount);
        $transaction = $this->recordTransaction($wallet, $amount, 'debit', 'Debit wallet', [
            'user_id' => $user->id,
        ], $prevBalance);

        return $transaction;
    }

    public function credit(Wallet $wallet, $amount, User $user)
    {
        $prevBalance = $wallet->balance;
        $wallet->increment('balance', $amount);
        $transaction = $this->recordTransaction($wallet, $amount, 'credit', 'Credit wallet', [
            'user_id' => $user->id,
        ], $prevBalance);

        return $transaction;
    }

    private function generateReference($type)
    {
        $type = $type == WalletTransaction::TYPE_DEBIT ? 'DR' : 'CR';

        return $type.'-'.Str::random(10);
    }

    public function recordTransaction(Wallet $wallet, $amount, $type, $description, $metadata = [], $prevBalance = null, $reference = null)
    {
        $reference ??= $this->generateReference($type);
        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'reference' => $reference,
            'description' => $description,
            'metadata' => $metadata,
            'amount' => $amount,
            'status' => WalletTransaction::STATUS_COMPLETED,
            'idempotency_key' => Str::uuid(),
            'prev_balance' => $prevBalance ?? $wallet->balance,
            'new_balance' => $wallet->balance,
        ]);

        return $transaction;
    }

    public function addMoney(array $data)
    {
        $user = auth()->user();
        $currency = getCurrencyBySymbol($data['currency']);
        $wallet = $this->lockWallet($user, $currency);
        $transaction = $this->credit($wallet, $data['amount'], $user);

        return $transaction;
    }

    public function transactions(array $filters = [])
    {
        $user = auth()->user();

        $query = WalletTransaction::whereHas('wallet', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with('wallet.currency')
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', WalletTransaction::getStatusByName($filters['status']));
            })
            ->when(isset($filters['type']), function ($query) use ($filters) {
                $query->where('type', $filters['type']);
            })
            ->when(isset($filters['wallet']), function ($query) use ($filters) {
                $query->whereHas('wallet.currency', function ($query) use ($filters) {
                    $query->where('symbol', strtolower($filters['wallet']));
                });
            })
            ->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 10;

        return $query->paginate($perPage);
    }
}
