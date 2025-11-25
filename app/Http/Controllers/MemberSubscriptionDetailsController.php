<?php

namespace App\Http\Controllers;

use App\Models\MemberSubscriptionDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemberSubscriptionDetailsController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = MemberSubscriptionDetails::orderBy('id', 'desc');
        
        if(auth()->user()->role != 'admin') {
            $items = $items->where('user_id', auth()->user()->id);
        }
        
        $items = $items->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $item = MemberSubscriptionDetails::where('id', $id);
        
        if(auth()->user()->role != 'admin') {
            $item = $item->where('user_id', auth()->user()->id);
        }
        
        $item = $item->first();

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $item);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->storeRules(),
            $this->messages()
        );

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $item = MemberSubscriptionDetails::create($data);

        return successResponse('Create successful', 200, $item);
    }

    public function update(Request $request, $id)
    {
        $item = MemberSubscriptionDetails::find($id);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        if(auth()->user()->role != 'admin' && $item->user_id != auth()->user()->id) {
            return errorResponse('Unauthorized', 403);
        }

        $validator = Validator::make(
            $request->all(),
            $this->updateRules(),
            $this->messages()
        );

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $item->fill($data)->save();

        return successResponse('Update successful', 200, $item);
    }

    protected function storeRules(): array
    {
        return array_merge(
            $this->sharedRules(),
            [
                'membership_signature' => 'required|max:5120',
                'signature' => 'required|max:5120',
            ]
        );
    }

    protected function updateRules(): array
    {
        $rules = $this->sharedRules();
        $rules['membership_signature'] = 'nullable|max:5120';
        $rules['signature'] = 'nullable|max:5120';

        return $rules;
    }
    protected function sharedRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date|after_or_equal:subscription_start_date',
            'status' => 'required|in:0,1',

            'membership_type' => 'required|string|max:255',
            'ordinary_membership_plan' => 'required_if:membership_type,Ordinary Member|nullable|string|max:255',
            'payment_method' => 'required|string|max:255',

            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_phone' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_profile' => 'nullable|string',
            'office_presence_regions' => 'required|string',
            'business_categories' => 'required|string',
            'other_business_category' => 'nullable|string|max:255',
            'director_name' => 'required|string|max:255',
            'director_signed_at' => 'required|date',

            'lead_contact_name' => 'required|string|max:255',
            'lead_contact_phone' => 'required|string|max:255',
            'lead_contact_title' => 'required|string|max:255',
            'lead_contact_email' => 'required|email|max:255',
            'contact_2_name' => 'nullable|string|max:255',
            'contact_2_phone' => 'nullable|string|max:255',
            'contact_2_title' => 'nullable|string|max:255',
            'contact_2_email' => 'nullable|email|max:255',
            'contact_3_name' => 'nullable|string|max:255',
            'contact_3_phone' => 'nullable|string|max:255',
            'contact_3_title' => 'nullable|string|max:255',
            'contact_3_email' => 'nullable|email|max:255',
            'contact_4_name' => 'nullable|string|max:255',
            'contact_4_phone' => 'nullable|string|max:255',
            'contact_4_title' => 'nullable|string|max:255',
            'contact_4_email' => 'nullable|email|max:255',
            'contact_5_name' => 'nullable|string|max:255',
            'contact_5_phone' => 'nullable|string|max:255',
            'contact_5_title' => 'nullable|string|max:255',
            'contact_5_email' => 'nullable|email|max:255',

            'license_officer_1_name' => 'required|string|max:255',
            'license_officer_1_phone' => 'required|string|max:255',
            'license_officer_1_title' => 'required|string|max:255',
            'license_officer_1_email' => 'required|email|max:255',
            'license_officer_2_name' => 'nullable|string|max:255',
            'license_officer_2_phone' => 'nullable|string|max:255',
            'license_officer_2_title' => 'nullable|string|max:255',
            'license_officer_2_email' => 'nullable|email|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'subscription_end_date.after_or_equal' => 'Please ensure the subscription end date is greater than or equal to the start date.',
            'ordinary_membership_plan.required_if' => 'Please select a plan when Ordinary Membership is chosen.',
        ];
    }
}
