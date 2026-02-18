<?php

namespace App\Http\Controllers;

use App\ApiResponses;
use App\Contracts\v1\Auth\AuthContract;
use App\Http\Requests\v1\Auth\LoginRequest;
use App\Http\Requests\v1\Auth\RegisterRequest;
use App\Http\Resources\v1\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponses;

    protected $authService;

    public function __construct()
    {
        $this->authService = app(AuthContract::class);
    }

    public function login(LoginRequest $request)
    {
        try {
            $validated = $request->validated();
            $response = $this->authService->login($validated);

            return $this->successResponse(
                [
                    'user' => new UserResource($response['user']), 
                    'token' => $response['token'],
                ],
                'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function register(RegisterRequest $request)
    {
        try {
            $validated = $request->validated();
            $response = $this->authService->register($validated);

            return $this->successResponse(
                [
                    'user' => new UserResource($response['user']), 
                    'token' => $response['token'],
                ],
                'Register successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function user()
    {
        try {
            $user = auth()->user();
            return $this->successResponse(
                [
                    'user' => new UserResource($user), 
                ],
                'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function logout()
    {
        try {
            $response = $this->authService->logout();

            return $this->successResponse($response, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function refresh()
    {
        try {
            $response = $this->authService->refresh();

            return $this->successResponse($response, 'Refresh successful');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
