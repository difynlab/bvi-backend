<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $credentials = $request->only('email', 'password');
        $credentials['status'] = 1;

        if(!Auth::attempt($credentials)) {
            return errorResponse('These credentials do not match our records.', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('BVI')->accessToken;

        return successResponse('Login successful', 200, [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request) {
        $user = Auth::user();
        $access_token = $user->token();

        if($access_token) {
            $access_token->revoke();
            $access_token->refreshToken?->revoke();
        }

        return successResponse('Logout successfully', 200);
    }
}