<?php

namespace App\Services\v1\Auth;

use App\Contracts\v1\Auth\AuthContract;
use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use Hash;

class AuthService implements AuthContract
{
    /**
     * Create a new class instance.
     */
    public function login(array $data)
    {
        try {
            $user = User::where('email', $data['email'])->first();
            if (! $user) {
                throw new \Exception('User not found');
            }
            if (! Hash::check($data['password'], $user->password)) {
                throw new \Exception('Invalid password');
            }
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function register(array $data)
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $currencies = Currency::where('status', 1)->get();
            foreach ($currencies as $currency) {
                Wallet::create([
                    'user_id' => $user->id,
                    'currency_id' => $currency->id,
                    'balance' => 0,
                    'status' => 1,
                ]);
            }
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function logout(array $data)
    {
        try {
            $user = auth()->user();
            $user->tokens()->delete();

            return [
                'message' => 'Logout successful',
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function refresh(array $data)
    {
        try {
            $user = auth()->user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
