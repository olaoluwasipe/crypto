<?php

namespace App\Services\v1\Trade;

use App\Contracts\v1\Trade\TradeContract;
use App\Contracts\v1\Wallet\FeeContract;
use App\Contracts\v1\Wallet\WalletContract;
use App\Contracts\v1\Currency\CurrencyContract;
use App\Models\Currency;
use App\Models\Trade;
use App\Models\User;
use DB;

class TradeService implements TradeContract
{

    protected $walletService;
    protected $currencyService;
    protected $feeService;

    public function __construct()
    {
        $this->walletService = app(WalletContract::class);
        $this->currencyService = app(CurrencyContract::class);
        $this->feeService = app(FeeContract::class);
    }

    public function buy(array $data)
    {
        try {
            $user = auth()->user();
            $amount = $data['amount'];
            $currency = Currency::where('symbol', $data['currency'])->firstOrFail();
            // Check if amount is less than min trade amount
            if ($amount < $currency->min_trade_amount) {
                throw new \Exception('Amount is less than '.$currency->min_trade_amount.' '.$currency->symbol);
            }
            // Check if amount is greater than max trade amount
            if ($amount > $currency->max_trade_amount) {
                throw new \Exception('Amount is greater than '.$currency->max_trade_amount.' '.$currency->symbol);
            }
            DB::transaction(function () use ($user, $amount, $currency) {

                $wallet = $this->walletService->lockWallet($user, $currency);
            
                $this->walletService->ensureSufficientBalance($wallet, $amount);
            
                $cryptoAmount = $this->currencyService->convert($wallet->currency, $currency, $amount);
            
                $fee = $this->feeService->calculateBuyFee($currency, $amount);
            
                $this->walletService->debit($wallet, $amount + $fee, $user);
            
                $this->walletService->credit($wallet, $cryptoAmount, $user);
            
                $this->recordTrade($user, $wallet->currency, $currency, $amount, $cryptoAmount, $fee, $currency, 'buy');

                return true; // Successfully executed the trade
            });
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function sell(array $data)
    {
        try {
            $user = auth()->user();
            $amount = $data['amount'];
            $currency = Currency::where('symbol', $data['currency'])->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function recordTrade(User $user, Currency $baseCurrency, Currency $quoteCurrency, $amount, $cryptoAmount, $fee, $feeCurrency, $type)
    {
        $trade = Trade::create([
            'user_id' => $user->id,
            'base_currency_id' => $baseCurrency->id,
            'quote_currency_id' => $quoteCurrency->id,
            'base_amount' => $amount,
            'quote_amount' => $cryptoAmount,
            'price',
            'fee' => $fee,
            'fee_currency_id' => $feeCurrency->id,
            'type' => $type,
            'status' => Trade::STATUS_PENDING,
            'executed_at' => now(),
        ]);
    }
}
