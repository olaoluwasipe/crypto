<?php

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Trade;
use App\Models\User;
use App\Models\Wallet;

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

    // Create wallets for the user
    $currencies = Currency::where('status', 1)->get();
    foreach ($currencies as $currency) {
        Wallet::firstOrCreate([
            'user_id' => $this->user->id,
            'currency_id' => $currency->id,
        ], [
            'balance' => 1000000,
            'status' => 1,
        ]);
    }

    // Create exchange rate
    $baseCurrency = Currency::where('symbol', 'ngn')->first();
    $quoteCurrency = Currency::where('symbol', 'btc')->first();

    if ($baseCurrency && $quoteCurrency) {
        ExchangeRate::firstOrCreate([
            'base_currency_id' => $baseCurrency->id,
            'quote_currency_id' => $quoteCurrency->id,
        ], [
            'rate' => 0.0000001,
        ]);
    }
});

test('authenticated user can buy cryptocurrency', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/trades/buy', [
            'amount' => 1000,
            'wallet' => 'ngn',
            'currency' => 'btc',
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'reference',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Trade successful',
        ]);
});

test('user cannot buy with invalid wallet currency', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/trades/buy', [
            'amount' => 1000,
            'wallet' => 'invalid',
            'currency' => 'btc',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user cannot buy with invalid currency', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/trades/buy', [
            'amount' => 1000,
            'wallet' => 'ngn',
            'currency' => 'invalid',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user cannot buy with negative amount', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/trades/buy', [
            'amount' => -1000,
            'wallet' => 'ngn',
            'currency' => 'btc',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('authenticated user can sell cryptocurrency', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/trades/sell', [
            'amount' => 0.001,
            'wallet' => 'btc',
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'reference',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Trade successful',
        ]);
});

test('user cannot sell with invalid wallet currency', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/trades/sell', [
            'amount' => 0.001,
            'wallet' => 'invalid',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('authenticated user can get their trades', function () {
    $baseCurrency = Currency::where('symbol', 'ngn')->first();
    $quoteCurrency = Currency::where('symbol', 'btc')->first();

    Trade::factory()->create([
        'user_id' => $this->user->id,
        'base_currency_id' => $baseCurrency->id,
        'quote_currency_id' => $quoteCurrency->id,
        'type' => 'buy',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/trades');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'pagination',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Transactions retrieved successfully',
        ]);
});

test('authenticated user can get a specific trade by reference', function () {
    $baseCurrency = Currency::where('symbol', 'ngn')->first();
    $quoteCurrency = Currency::where('symbol', 'btc')->first();

    $trade = Trade::factory()->create([
        'user_id' => $this->user->id,
        'base_currency_id' => $baseCurrency->id,
        'quote_currency_id' => $quoteCurrency->id,
        'type' => 'buy',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/trades/{$trade->reference}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'reference',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Transaction retrieved successfully',
        ]);
});

test('user cannot access another user trade', function () {
    $otherUser = User::factory()->create();
    $baseCurrency = Currency::where('symbol', 'ngn')->first();
    $quoteCurrency = Currency::where('symbol', 'btc')->first();

    $trade = Trade::factory()->create([
        'user_id' => $otherUser->id,
        'base_currency_id' => $baseCurrency->id,
        'quote_currency_id' => $quoteCurrency->id,
        'type' => 'buy',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/trades/{$trade->reference}");

    // This should either return 404 or 403 depending on implementation
    expect($response->status())->toBeIn([404, 403, 400]);
});

test('unauthenticated user cannot buy cryptocurrency', function () {
    $response = $this->postJson('/api/v1/trades/buy', [
        'amount' => 1000,
        'wallet' => 'ngn',
        'currency' => 'btc',
    ]);

    $response->assertStatus(401);
});

test('unauthenticated user cannot sell cryptocurrency', function () {
    $response = $this->postJson('/api/v1/trades/sell', [
        'amount' => 0.001,
        'wallet' => 'btc',
    ]);

    $response->assertStatus(401);
});

test('unauthenticated user cannot get trades', function () {
    $response = $this->getJson('/api/v1/trades');

    $response->assertStatus(401);
});
