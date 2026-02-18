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
use Illuminate\Support\Facades\Log;
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
            return DB::transaction(function () use ($user, $amount, $baseCurrency, $quoteCurrency) {

                $wallet = $this->walletService->lockWallet($user, $baseCurrency);
                $quoteWallet = $this->walletService->lockWallet($user, $quoteCurrency);

                $rate = $this->currencyService->getRate($baseCurrency, $quoteCurrency);
            
                $cryptoAmount = $this->currencyService->convert($wallet->currency, $quoteCurrency, $amount);
                
                $this->validateTradeAmount($quoteCurrency, $cryptoAmount);
            
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
            Log::error('Trade failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new \Exception($e->getMessage());
        }
    }

    public function sell(array $data)
    {
        try {
            $user = auth()->user();
            $amount = $data['amount'];
            $baseCurrency = Currency::where('symbol', $data['wallet'])->firstOrFail();
            $quoteCurrency = Currency::where('symbol', $data['currency'])->firstOrFail();
            return DB::transaction(function () use ($user, $amount, $baseCurrency, $quoteCurrency) {

                $wallet = $this->walletService->lockWallet($user, $baseCurrency);
                $quoteWallet = $this->walletService->lockWallet($user, $quoteCurrency);

                $rate = $this->currencyService->getRate($quoteCurrency, $baseCurrency);
            
                $cryptoAmount = $this->currencyService->convert($quoteCurrency, $baseCurrency, $amount);
                
                $this->validateTradeAmount($quoteCurrency, $cryptoAmount);
            
                $fee = $this->feeService->calculateSellFee($quoteCurrency, $cryptoAmount);
            
                $this->walletService->ensureSufficientBalance($wallet, $amount + $fee);

                $totalAmount = $amount + $fee;
            
                $debitTransaction = $this->walletService->debit($wallet, $totalAmount, $user);
            
                $creditTransaction = $this->walletService->credit($quoteWallet, $cryptoAmount, $user);
            
                $transaction = $this->recordTrade(
                    $user, 
                $quoteCurrency, 
                $baseCurrency, 
                $amount, 
                $cryptoAmount, 
                $fee, 
                $baseCurrency, 
                'sell', 
                $rate, 
                $totalAmount, 
                $creditTransaction, 
                $debitTransaction);

                return $transaction; // Successfully executed the trade
            });
        } catch (\Exception $e) {
            Log::error('Trade failed: ' . $e->getMessage(), ['exception' => $e]);
            throw new \Exception($e->getMessage());
        }
    }

    public function transactions(array $filters = [])
    {
        $query = Trade::where('user_id', auth()->user()->id);
        $query->when(isset($filters['status']), function ($query) use ($filters) {
            $query->where('status', Trade::getStatusByName($filters['status']));
        });
        $query->when(isset($filters['type']), function ($query) use ($filters) {
            $query->where('type', $filters['type']);
        });
        $query->when(isset($filters['start_date']), function ($query) use ($filters) {
            $query->where('created_at', '>=', $filters['start_date']);
        });
        $query->when(isset($filters['end_date']), function ($query) use ($filters) {
            $query->where('created_at', '<=', $filters['end_date']);
        });
        $perPage = $filters['per_page'] ?? 10;
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function show(Trade $trade)
    {
        if ($trade->user_id !== auth()->user()->id) {
            throw new \Exception('You are not authorized to view this trade');
        }
        return $trade;
    }
    
    private function validateTradeAmount(Currency $currency, $amount): void
    {
        if ($amount < $currency->min_trade_amount) {
            throw new \Exception('Amount is less than ' . $currency->min_trade_amount . ' ' . $currency->symbol);
        }
        if ($amount > $currency->max_trade_amount) {
            throw new \Exception('Amount is greater than ' . $currency->max_trade_amount . ' ' . $currency->symbol);
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
