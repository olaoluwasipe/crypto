<?php

namespace App\Providers;

use App\Contracts\v1\Auth\AuthContract;
use App\Contracts\v1\Currency\CurrencyContract;
use App\Services\v1\Auth\AuthService;
use App\Contracts\v1\Trade\TradeContract;
use App\Services\v1\Trade\TradeService;
use App\Services\v1\Currency\CurrencyService;
use App\Services\v1\Wallet\WalletService;
use App\Contracts\v1\Wallet\WalletContract;
use App\Contracts\v1\Wallet\FeeContract;
use App\Services\v1\Wallet\FeeService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthContract::class, AuthService::class);
        $this->app->bind(TradeContract::class, TradeService::class);
        $this->app->bind(CurrencyContract::class, CurrencyService::class);
        $this->app->bind(FeeContract::class, FeeService::class);
        $this->app->bind(WalletContract::class, WalletService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
