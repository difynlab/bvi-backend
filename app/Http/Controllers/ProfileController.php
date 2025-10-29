<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    private function processData($item)
    {
        $item->original_image = url('') . '/storage/users/' . $item->image;
        $item->blurred_image = url('') . '/storage/users/thumbnails/' . $item->image;

        return $item;
    }

    public function index()
    {
        $item = Auth::user();

        $this->processData($item);

        return successResponse('success', 200, $item);
    }

    public function update(Request $request)
    {
        $item = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3|max:255',
            'last_name' => 'required|min:3|max:255',
            'email' => 'required|email|min:3|max:255|unique:users,email,'.$item->id,
            'phone' => 'required|numeric|unique:users,phone,'.$item->id,
            'image' => 'max:5120',
        ], [
            'image.max' => 'The image must not be greater than 5120 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        // Image
            if($request->file('image')) {
                $processed_image = process_image($request->file('image'), 'users', $item->image);
            }
            else {
                $processed_image = $item->image;
            }
        // Image

        $data = $request->except(
            'old_image',
            'new_image',
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

            if(!Hash::check($request->password, $item->password)) {
                return errorResponse('Incorrect password', 400);
            }

            $data['password'] = Hash::make($request->new_password);
        }

        $data['image'] = $processed_image;
        $item->fill($data)->save();

        $this->processData($item);

        // Auth::logoutOtherDevices($request->password);
        // Auth::logout();

        return successResponse('Update successful', 200, $item);
    }
}