<?php

namespace App\Http\Controllers;

use App\ApiResponses;
use App\Http\Requests\v1\Currency\ConvertCurrencyRequest;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Contracts\v1\Currency\CurrencyContract;

class CurrencyController extends Controller
{
    use ApiResponses;

    protected $currencyService;

    public function __construct()
    {
        $this->currencyService = app(CurrencyContract::class);
    }

    public function index()
    {
        try {
            $currencies = Currency::where('status', 1)->get();
            return $this->successResponse($currencies, 'Currencies retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function exchangeRates()
    {
        try {
            $exchangeRates = $this->currencyService->getAllRates();
            $exchangeRates = $exchangeRates->map(function ($exchangeRate) {
                return [
                    'base_currency' => $exchangeRate->baseCurrency->name,
                    'quote_currency' => $exchangeRate->quoteCurrency->name,
                    'rate' => strtoupper($exchangeRate->baseCurrency->symbol) . ' ' . number_format($exchangeRate->rate, $exchangeRate->baseCurrency->precision),
                ];
            });
            return $this->successResponse($exchangeRates, 'Exchange rates retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function convertCurrency(ConvertCurrencyRequest $request)
    {
        try {
            $validated = $request->validated();
            $baseCurrency = Currency::where('symbol', $validated['base_currency'])->firstOrFail();
            $quoteCurrency = Currency::where('symbol', $validated['quote_currency'])->firstOrFail();
            $amount = $validated['amount'];
            $convertedAmount = $this->currencyService->convert($baseCurrency, $quoteCurrency, $amount);
            $data = [
                'base_currency' => $baseCurrency->name,
                'quote_currency' => $quoteCurrency->name,
                'amount' => strtoupper($baseCurrency->symbol) . ' ' . number_format($amount, $baseCurrency->precision),
                'converted_amount' => strtoupper($baseCurrency->symbol) . ' ' . number_format($convertedAmount, $baseCurrency->precision),
            ];
            return $this->successResponse($data, 'Currency converted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Exchange rate not found');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

}
