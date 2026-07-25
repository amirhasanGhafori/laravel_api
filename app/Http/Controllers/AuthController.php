<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiLoginRequest;
use App\Http\Requests\ApiRegisterRequest;
use App\traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function login()
    {
        return $this->ok('hello Login');
    }


    public function loginUser(ApiLoginRequest $request)
    {
        return $this->ok($request->input('email'));
    }

    public function register(ApiRegisterRequest $request)
    {
        return $this->ok($request->input('username'));
    }
}
