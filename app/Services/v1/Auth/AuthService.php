<?php

namespace App\Services\v1\Auth;

use App\Contracts\v1\Auth\AuthContract;

class AuthService implements AuthContract
{
    /**
     * Create a new class instance.
     */
    public function login(Request $request)
    {
        return response()->json([
            'message' => 'Login successful',
        ], 200);
    }

    public function register(Request $request)
    {
        return response()->json([
            'message' => 'Register successful',
        ], 200);
    }

    public function logout(Request $request)
    {
        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }

    public function refresh(Request $request)
    {
        return response()->json([
            'message' => 'Refresh successful',
        ], 200);
    }
}
