<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->password = 'Password123!@#';
});

test('user can register with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $this->password,
        'password_confirmation' => $this->password,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'name',
                    'email',
                ],
                'token',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Register successful',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);
});

test('user cannot register with invalid email', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => $this->password,
        'password_confirmation' => $this->password,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user cannot register with weak password', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user cannot register with mismatched password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $this->password,
        'password_confirmation' => 'DifferentPassword123!@#',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user cannot register with duplicate email', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => $this->password,
        'password_confirmation' => $this->password,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make($this->password),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => $this->password,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'name',
                    'email',
                ],
                'token',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Login successful',
        ]);
});

test('user cannot login with invalid email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => $this->password,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('user cannot login with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make($this->password),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'WrongPass123!@#', // Valid format but wrong password
    ]);

    // The service throws an exception which returns 400
    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
        ]);
});

test('authenticated user can get their profile', function () {
    $user = User::factory()->create();

    // Create wallets for the user (UserResource includes wallets)
    $currencies = \App\Models\Currency::where('status', 1)->get();
    foreach ($currencies as $currency) {
        \App\Models\Wallet::create([
            'user_id' => $user->id,
            'currency_id' => $currency->id,
            'balance' => 0,
            'status' => 1,
        ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'name',
                    'email',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'User retrieved successfully',
        ]);
});

test('unauthenticated user cannot get their profile', function () {
    $response = $this->getJson('/api/v1/user');

    $response->assertStatus(401);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logout successful',
        ]);
});

test('authenticated user can refresh token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/refresh');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Refresh successful',
        ]);
});
