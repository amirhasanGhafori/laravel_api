<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Http\Requests\ApiLoginRequest;
use App\Http\Requests\ApiRegisterRequest;
use App\Models\User;
use App\traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid Credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);
        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken('Api token for '. $user->email,['*'],now()->addMonth())->plainTextToken
            ]
        );
    }

    public function register(ApiRegisterRequest $request)
    {
        return $this->ok($request->input('username'),[]);
    }


    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return $this->ok('logout successfyly');
    }
}
