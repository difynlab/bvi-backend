<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Token;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
            'token' => 'required'
        ]);
    
        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $user = User::where('email', $request->email)->where('status', 1)->first();
        if(!$user) {
            return errorResponse('Email not found', 404);
        }

        $reset_password = PasswordResetToken::where('email', $request->email)->orderBy('created_at', 'desc')->first();

        if(!$reset_password || $reset_password->token !== $request->token) {
            return errorResponse('Invalid reset request', 400, $validator->errors());
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $user->tokens()->each(function (Token $token) {
            $token->revoke();
            $token->refreshToken?->revoke();
        });

        return successResponse('Password successfully changed', 200, [
            'email' => $request->email
        ]);
    }
}
