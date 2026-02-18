<?php

namespace App\Contracts\v1\Auth;

interface AuthContract
{
    public function login(array $data);

    public function register(array $data);

    public function logout();

    public function refresh();
}
