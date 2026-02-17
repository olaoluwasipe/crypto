<?php

namespace App\Services\v1\Wallet;

use App\Contracts\v1\Wallet\WalletContract;
use App\Models\User;
use App\Models\Currency;
use App\Models\Wallet;
use App\Models\WalletTransaction;

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
        $wallet->decrement('balance', $amount);
        $this->recordTransaction($wallet, $amount, 'debit', 'Debit wallet', [
            'user_id' => $user->id,
        ]);
        return $wallet;
    }
    
    public function credit(Wallet $wallet, $amount, User $user)
    {
        $wallet->increment('balance', $amount);
        $this->recordTransaction($wallet, $amount, 'credit', 'Credit wallet', [
            'user_id' => $user->id,
        ]);
        return $wallet;
    }
    
    public function recordTransaction(Wallet $wallet, $amount, $type, $description, $metadata)
    {
        $transaction = WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
            'amount' => $amount,
        ]);
        return $transaction;
    }
}
