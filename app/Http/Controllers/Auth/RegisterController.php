<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AccountRegisterMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request) {     
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3|max:20',
            'last_name' => 'required|min:3|max:20',
            'email' => 'required|email|min:3|max:50|unique:users,email',
            'phone' => 'required|min:8|max:15|regex:/^\+?[0-9]+$/|unique:users,phone',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
            'role' => 'required|in:admin,member'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->except('password_confirmation');
        $data['password'] = Hash::make($request->password);
        $data['role'] = 'member';
        $user = User::create($data);

        $mail = [
            'user' => $user
        ];

        send_email(new AccountRegisterMail($mail, 'user'), $request->email);
        send_email(new AccountRegisterMail($mail, 'admin'), config('app.admin_email'));

        Auth::login($user);
        $token = $user->createToken('BVI')->accessToken;

        return successResponse('Login successful', 200, [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}