<?php

namespace App\Contracts\v1\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Currency;

interface WalletContract
{
    public function lockWallet(User $user, Currency $currency);
    public function ensureSufficientBalance(Wallet $wallet, $amount);
    public function debit(Wallet $wallet, $amount, User $user);
    public function credit(Wallet $wallet, $amount, User $user);
    // public function creditCrypto(User $user, Currency $currency, $amount);
    // public function debitCrypto(User $user, Currency $currency, $amount);
    public function recordTransaction(Wallet $wallet, $amount, $type, $description, $metadata);
}
