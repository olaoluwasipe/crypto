<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\v1\Auth\AuthContract;

class AuthController extends Controller
{
    protected $authService;

    public function __construct()
    {
        $this->authService = app(AuthContract::class);
    }

    public function login(Request $request)
    {
        return $this->authService->login($request);
    }

    public function register(Request $request)
    {
        return $this->authService->register($request);
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }

    public function refresh(Request $request)
    {
        return $this->authService->refresh($request);
    }
}
