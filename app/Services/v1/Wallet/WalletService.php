<?php

namespace App\Services\v1\Wallet;

use App\Contracts\v1\Wallet\WalletContract;
use App\Models\User;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;

class WalletService implements WalletContract
{
    public function lockWallet(User $user, Currency $currency)
    {
        if (!$currency) {
            throw new \Exception('Currency not found');
        }

        $wallet = $user->wallets()->where('currency_id', $currency->id)->lockForUpdate()->first();
        if (!$wallet) {
            throw new \Exception('Wallet not found');
        }
        // $wallet->status = 0;
        // $wallet->save();
        return $wallet;
    }

    public function ensureSufficientBalance(Wallet $wallet, $amount)
    {
        if ($wallet->balance < $amount) {
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

    private function generateReference($type) {
        $type = $type == WalletTransaction::TYPE_DEBIT ? 'DR' : 'CR';
        return $type . '-' . Str::random(10);
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
            'idempotency_key' => Str::uuid(),
            'prev_balance' => $prevBalance ?? $wallet->balance,
            'new_balance' => $wallet->balance,
        ]);
        return $transaction;
    }
}
