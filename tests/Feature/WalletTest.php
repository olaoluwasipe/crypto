<?php

use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

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
            'balance' => 0,
            'status' => 1,
        ]);
    }
});

test('authenticated user can add money to wallet', function () {
    $currency = Currency::where('symbol', 'ngn')->first();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/add-money', [
            'amount' => 1000,
            'currency' => 'ngn',
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'amount',
                'wallet',
                'type',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Money added successfully',
        ]);

    $this->assertDatabaseHas('wallet_transactions', [
        'amount' => 1000,
    ]);
});

test('user cannot add money with amount less than minimum', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/add-money', [
            'amount' => 5,
            'currency' => 'ngn',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user cannot add money with invalid currency', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/add-money', [
            'amount' => 1000,
            'currency' => 'invalid',
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('unauthenticated user cannot add money', function () {
    $response = $this->postJson('/api/v1/add-money', [
        'amount' => 1000,
        'currency' => 'ngn',
    ]);

    $response->assertStatus(401);
});

test('authenticated user can get wallet transactions', function () {
    $currency = Currency::where('symbol', 'ngn')->first();
    $wallet = Wallet::where('user_id', $this->user->id)
        ->where('currency_id', $currency->id)
        ->first();

    WalletTransaction::factory()->create([
        'wallet_id' => $wallet->id,
        'amount' => 1000,
        'type' => 'credit',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/transactions');

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

test('authenticated user can filter wallet transactions by currency', function () {
    $currency = Currency::where('symbol', 'ngn')->first();
    $wallet = Wallet::where('user_id', $this->user->id)
        ->where('currency_id', $currency->id)
        ->first();

    WalletTransaction::factory()->create([
        'wallet_id' => $wallet->id,
        'amount' => 1000,
        'type' => 'credit',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/transactions?currency=ngn');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('unauthenticated user cannot get wallet transactions', function () {
    $response = $this->getJson('/api/v1/transactions');

    $response->assertStatus(401);
});
