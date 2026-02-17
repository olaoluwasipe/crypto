<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('get:exchange-rate', function () {
    $this->info('Getting exchange rate...');
    $this->call('app:get-exchange-rate');
})->purpose('Get exchange rate');

Schedule::command('app:get-exchange-rate')->everyThirtySeconds();