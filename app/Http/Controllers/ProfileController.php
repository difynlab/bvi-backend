<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Token;

class ProfileController extends Controller
{
    private function processData($user)
    {
        if($user->image) {
            $user->original_image = url('') . '/storage/users/' . $user->image;
            $user->blurred_image = url('') . '/storage/users/thumbnails/' . $user->image;
        }
        
        $user->payments = Payment::where('user_id', $user->id)->orderBy('id', 'desc')->get();

        return $user;
    }

    public function index()
    {
        $user = Auth::user();

        $this->processData($user);

        return successResponse('success', 200, $user);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3|max:255',
            'last_name' => 'required|min:3|max:255',
            'email' => 'required|email|min:3|max:255|unique:users,email,'.$user->id,
            'phone' => 'required|numeric|unique:users,phone,'.$user->id,
            'image' => 'max:5120',
        ], [
            'image.max' => 'The image must not be greater than 5120 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        // Image
            if($request->file('image')) {
                $processed_image = process_image($request->file('image'), 'users', $user->image);
            }
            else {
                $processed_image = $user->image;
            }
        // Image

        $data = $request->except(
            'image',
            'password',
            'new_password',
            'confirm_password'
        );

        if($request->password || $request->new_password || $request->confirm_password) {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
                'new_password' => 'required|min:8',
                'confirm_password' => 'required|same:new_password',
            ], [
                'confirm_password.same' => 'The confirm password field must match new password.'
            ]);

            if($validator->fails()) {
                return errorResponse('Validation failed', 400, $validator->errors());
            }

            if(!Hash::check($request->password, $user->password)) {
                return errorResponse('Incorrect password', 400);
            }

            $data['password'] = Hash::make($request->new_password);

            $user->tokens()->each(function (Token $token) {
                $token->revoke();
                $token->refreshToken?->revoke();
            });
        }

        $data['image'] = $processed_image;
        $user->fill($data)->save();

        $this->processData($user);

        return successResponse('Update successful', 200, $user);
    }
}