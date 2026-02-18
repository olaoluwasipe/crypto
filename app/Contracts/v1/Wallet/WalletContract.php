<?php

namespace App\Contracts\v1\Wallet;

use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;

interface WalletContract
{
    public function lockWallet(User $user, Currency $currency);

    public function ensureSufficientBalance(Wallet $wallet, $amount);

    public function debit(Wallet $wallet, $amount, User $user);

    public function credit(Wallet $wallet, $amount, User $user);

    public function addMoney(array $data);

    public function transactions(array $filters = []);

    public function recordTransaction(Wallet $wallet, $amount, $type, $description, $metadata);
}
