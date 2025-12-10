<?php

namespace App\Http\Controllers;

use App\Models\MemberFirm;
use App\Models\MembershipPlan;
use App\Models\MemberSubscriptionDetail;
use App\Models\Payment;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Token;

class MemberController extends Controller
{
    private function processData($item)
    {
        if($item->image) {
            $item->original_image = url('') . '/storage/users/' . $item->image;
            $item->blurred_image = url('') . '/storage/users/thumbnails/' . $item->image;
        }

        $payments = Payment::where('user_id', $item->id)->orderBy('id', 'desc')->get();

        $item->payments = $payments->map(function($item) {
            $membership_plan = MembershipPlan::find($item->membership_plan_id);
            $membership_plan->perks = json_decode($membership_plan['perks']);
            $membership_plan->pricing = json_decode($membership_plan['pricing']);

            $item->membership_plan_id = $membership_plan;

            return $item;
        });

        $member_firms = json_decode($item->member_firms);

        $new_member_firms = [];
        $item->member_firms = $new_member_firms;
        if($member_firms) {
            foreach ($member_firms as $key => $member_firm) {
                $firm = MemberFirm::find($member_firm);
                $firm->specialization_id = Specialization::find($firm->specialization_id);
                $new_member_firms[] = $firm;
            }

            $item->member_firms = $new_member_firms;
        }

        $item->member_subscription_details = MemberSubscriptionDetail::where('user_id', $item->id)->first();
        
        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = User::whereNot('id', auth()->user()->id)->orderBy('id', 'desc')->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 200);
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
            return errorResponse('No data found', 200);
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
            'member_firms' => 'nullable',
            'member_firms.*' => 'exists:member_firms,id,status,1',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ], [
            'member_firm.*.exists' => 'The selected member firm is invalid or its status is not active.',
            'image.max' => 'The image must not be greater than 5120 kilobytes.',
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
        $data['member_firms'] = $request->member_firms ? json_encode($request->member_firms) : null;
        $data['password'] = Hash::make($request->password);

        $member = User::create($data);

        $this->processData($member);

        return successResponse('Create successful', 200, $member);
    }

    public function update(Request $request, $id)
    {
        $member = User::find($id);

        if(!$member) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3|max:255',
            'last_name' => 'required|min:3|max:255',
            'email' => 'required|email|min:3|max:255|unique:users,email,'.$member->id,
            'phone' => 'required|numeric|unique:users,phone,'.$member->id,
            'image' => 'max:5120',
            'member_firms' => 'nullable',
            'member_firms.*' => 'exists:member_firms,id,status,1',
        ], [
            'member_firm.*.exists' => 'The selected member firm is invalid or its status is not active.',
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
        $data['member_firms'] = $request->member_firms ? json_encode($request->member_firms) : null;
        $member->fill($data)->save();

        $this->processData($member);

        return successResponse('Update successful', 200, $member);
    }

    public function destroy($id)
    {
        $member = User::find($id);

        if(!$member) {
            return errorResponse('No data found', 200);
        }

        $member->delete();

        return successResponse('Delete successful', 200);
    }

    public function renewMembership(Request $request, $id)
    {
        $member = User::find($id);

        if(!$member) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'membership_plan_id' => 'required|exists:membership_plans,id,status,1',
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|in:0,1,2'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $data['user_id'] = $member->id;
        Payment::create($data);

        $this->processData($member);

        return successResponse('Create successful', 200, $member);
    }

    public function updateMembership(Request $request, $id, $payment_id)
    {
        $member = User::find($id);

        if(!$member) {
            return errorResponse('No data found', 200);
        }

        $payment = Payment::find($payment_id);

        if(!$payment) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1,2'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $payment->fill($data)->save();

        $this->processData($member);

        return successResponse('Create successful', 200, $member);
    }
}