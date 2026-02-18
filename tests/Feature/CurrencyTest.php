<?php

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('auth_token')->plainTextToken;

    // Seed currencies if they don't exist
    if (Currency::count() === 0) {
        Currency::create([
            'name' => 'Nigerian Naira',
            'symbol' => 'ngn',
            'code' => 'ngn',
            'precision' => 2,
            'type' => 'fiat',
            'min_trade_amount' => 100,
            'max_trade_amount' => 1000000,
            'logo' => 'https://example.com/logo.png',
            'status' => 1,
        ]);

        Currency::create([
            'name' => 'US Dollar',
            'symbol' => 'usd',
            'code' => 'usd',
            'precision' => 2,
            'type' => 'fiat',
            'min_trade_amount' => 1,
            'max_trade_amount' => 1000000,
            'logo' => 'https://example.com/logo.png',
            'status' => 1,
        ]);

        Currency::create([
            'name' => 'Bitcoin',
            'symbol' => 'btc',
            'code' => 'bitcoin',
            'precision' => 8,
            'type' => 'crypto',
            'min_trade_amount' => 0.00000001,
            'max_trade_amount' => 1000000,
            'logo' => 'https://example.com/logo.png',
            'status' => 1,
        ]);
    }
});

test('authenticated user can get list of currencies', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/currencies');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Currencies retrieved successfully',
        ]);
});

test('currencies list only returns active currencies', function () {
    Currency::create([
        'name' => 'Inactive Currency',
        'symbol' => 'inactive',
        'code' => 'inactive',
        'precision' => 2,
        'type' => 'fiat',
        'min_trade_amount' => 1,
        'max_trade_amount' => 1000000,
        'logo' => 'https://example.com/logo.png',
        'status' => 0,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/currencies');

    $response->assertStatus(200);
    $data = $response->json('data');
    $inactiveCurrency = collect($data)->firstWhere('symbol', 'inactive');
    expect($inactiveCurrency)->toBeNull();
});

test('unauthenticated user cannot get currencies', function () {
    $response = $this->getJson('/api/v1/currencies');

    $response->assertStatus(401);
});

test('authenticated user can get exchange rates', function () {
    $baseCurrency = Currency::where('symbol', 'ngn')->first();
    $quoteCurrency = Currency::where('symbol', 'usd')->first();

    if ($baseCurrency && $quoteCurrency) {
        ExchangeRate::firstOrCreate([
            'base_currency_id' => $baseCurrency->id,
            'quote_currency_id' => $quoteCurrency->id,
        ], [
            'rate' => 1500.00,
            'source' => 'test',
        ]);
    }

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/exchange-rates');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Exchange rates retrieved successfully',
        ]);
});

test('unauthenticated user cannot get exchange rates', function () {
    $response = $this->getJson('/api/v1/exchange-rates');

    $response->assertStatus(401);
});

test('authenticated user can convert currency', function () {
    $baseCurrency = Currency::where('symbol', 'ngn')->first();
    $quoteCurrency = Currency::where('symbol', 'usd')->first();

    if ($baseCurrency && $quoteCurrency) {
        ExchangeRate::firstOrCreate([
            'base_currency_id' => $baseCurrency->id,
            'quote_currency_id' => $quoteCurrency->id,
        ], [
            'rate' => 1500.00,
            'source' => 'test',
        ]);
    }

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/convert-currency', [
            'base_currency' => 'ngn',
            'quote_currency' => 'usd',
            'amount' => 1500,
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data',
        ])
        ->assertJson([
            'success' => true,
        ]);
});

test('user cannot convert currency with invalid base currency', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/convert-currency', [
            'base_currency' => 'invalid',
            'quote_currency' => 'usd',
            'amount' => 1500,
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user cannot convert currency with missing amount', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/convert-currency', [
            'base_currency' => 'ngn',
            'quote_currency' => 'usd',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('unauthenticated user cannot convert currency', function () {
    $response = $this->postJson('/api/v1/convert-currency', [
        'base_currency' => 'ngn',
        'quote_currency' => 'usd',
        'amount' => 1500,
    ]);

    $response->assertStatus(401);
});
