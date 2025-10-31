<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Token;

class PaymentController extends Controller
{
    private function processData($item)
    {
        if($item->image) {
            $item->original_image = url('') . '/storage/users/' . $item->image;
            $item->blurred_image = url('') . '/storage/users/thumbnails/' . $item->image;
        }

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = User::whereNot('id', auth()->user()->id)->orderBy('id', 'desc')->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 404);
        }

        $items->map(function($item) {
            $this->processData($item);
        });

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $member = User::find($id);

        if(!$member) {
            return errorResponse('No data found', 404);
        }

        $this->processData($member);

        return successResponse('success', 200, $member);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3|max:255',
            'last_name' => 'required|min:3|max:255',
            'email' => 'required|email|min:3|max:255|unique:users,email,',
            'phone' => 'required|numeric|unique:users,phone,',
            'image' => 'max:5120',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ], [
            'image.max' => 'The image must not be greater than 5120 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $processed_image = process_image($request->file('image'), 'users');

        $data = $request->except(
            'image',
            'password',
            'confirm_password'
        );
        $data['image'] = $processed_image;
        $data['password'] = Hash::make($request->password);

        $member = User::create($data);

        $this->processData($member);

        return successResponse('Create successful', 200, $member);
    }

    public function update(Request $request, $id)
    {
        $member = User::find($id);

        if(!$member) {
            return errorResponse('No data found', 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3|max:255',
            'last_name' => 'required|min:3|max:255',
            'email' => 'required|email|min:3|max:255|unique:users,email,'.$member->id,
            'phone' => 'required|numeric|unique:users,phone,'.$member->id,
            'image' => 'max:5120',
        ], [
            'image.max' => 'The image must not be greater than 5120 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        // Image
            if($request->file('image')) {
                $processed_image = process_image($request->file('image'), 'users', $member->image);
            }
            else {
                $processed_image = $member->image;
            }
        // Image

        $data = $request->except(
            'image',
            'password',
            'confirm_password'
        );

        if($request->password || $request->confirm_password) {
            $validator = Validator::make($request->all(), [
                'password' => 'required',
                'confirm_password' => 'required|same:password',
            ]);

            if($validator->fails()) {
                return errorResponse('Validation failed', 400, $validator->errors());
            }

            $data['password'] = Hash::make($request->password);

            $member->tokens()->each(function (Token $token) {
                $token->revoke();
                $token->refreshToken?->revoke();
            });
        }

        $data['image'] = $processed_image;
        $member->fill($data)->save();

        $this->processData($member);

        return successResponse('Update successful', 200, $member);
    }

    public function destroy($id)
    {
        $member = User::find($id);

        if(!$member) {
            return errorResponse('No data found', 404);
        }

        $member->delete();

        return successResponse('Delete successful', 200);
    }
}