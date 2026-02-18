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
use Illuminate\Support\Str;

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
            $baseCurrency = Currency::where('symbol', $data['wallet'])->firstOrFail();
            $quoteCurrency = Currency::where('symbol', $data['currency'])->firstOrFail();
            // Check if amount is less than min trade amount
            if ($amount < $quoteCurrency->min_trade_amount) {
                throw new \Exception('Amount is less than '.$quoteCurrency->min_trade_amount.' '.$quoteCurrency->symbol);
            }
            // Check if amount is greater than max trade amount
            if ($amount > $quoteCurrency->max_trade_amount) {
                throw new \Exception('Amount is greater than '.$quoteCurrency->max_trade_amount.' '.$quoteCurrency->symbol);
            }
            return DB::transaction(function () use ($user, $amount, $baseCurrency, $quoteCurrency) {

                $wallet = $this->walletService->lockWallet($user, $baseCurrency);
                $quoteWallet = $this->walletService->lockWallet($user, $quoteCurrency);

                $rate = $this->currencyService->getRate($baseCurrency, $quoteCurrency);
            
                $cryptoAmount = $this->currencyService->convert($wallet->currency, $quoteCurrency, $amount);
            
                $fee = $this->feeService->calculateBuyFee($quoteCurrency, $cryptoAmount);
            
                $this->walletService->ensureSufficientBalance($wallet, $cryptoAmount + $fee);

                $totalAmount = $cryptoAmount + $fee;
            
                $debitTransaction = $this->walletService->debit($wallet, $totalAmount, $user);
            
                $creditTransaction = $this->walletService->credit($quoteWallet, $amount, $user);
            
                $transaction = $this->recordTrade(
                    $user, 
                $wallet->currency, 
                $quoteCurrency, 
                $amount, 
                $cryptoAmount, 
                $fee, 
                $quoteCurrency, 
                'buy', 
                $rate, 
                $totalAmount, 
                $creditTransaction, 
                $debitTransaction);

                return $transaction; // Successfully executed the trade
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

    public function recordTrade(User $user, 
            Currency $baseCurrency, 
            Currency $quoteCurrency, 
            $amount, 
            $cryptoAmount, 
            $fee, 
            $feeCurrency, 
            $type, 
            $rate, 
            $totalAmount, 
            $creditTransaction, 
            $debitTransaction
    )
    {
        $trade = Trade::create([
            'user_id' => $user->id,
            'reference' => Str::uuid(),
            'base_currency_id' => $baseCurrency->id,
            'quote_currency_id' => $quoteCurrency->id,
            'base_amount' => $cryptoAmount,
            'quote_amount' => $amount,
            'price' => $totalAmount,
            'fee' => $fee,
            'rate' => $rate->rate,
            'credit_transaction_id' => $creditTransaction->id,
            'debit_transaction_id' => $debitTransaction->id,
            'fee_currency_id' => $feeCurrency->id,
            'type' => $type,
            'status' => Trade::STATUS_PENDING,
            'executed_at' => now(),
        ]);
        return $trade;
    }
}
