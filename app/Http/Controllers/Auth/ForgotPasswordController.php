<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\AccountForgotPasswordMail;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        
        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $user = User::where('email', $request->email)->where('status', 1)->first();

        if(!$user) {
            return errorResponse('Email not found', 404);
        }

        do {
            $token = bin2hex(random_bytes(30));
        } while (PasswordResetToken::where('token', $token)->exists());

        $password_reset = new PasswordResetToken();
        $password_reset->email = $request->email;
        $password_reset->token = $token;
        $password_reset->save();

        $mail = [
            'user' => $user,
            'token' => $token
        ];

        send_email(new AccountForgotPasswordMail($mail), $user->email);

        return successResponse('Email sent successfully', 200, [
            'email' => $request->email,
            'token' => $token
        ]);
    }
}
