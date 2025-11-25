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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date|after_or_equal:subscription_start_date',
            'status' => 'required|in:0,1',
        ], [
            'subscription_end_date.after_or_equal' => 'The subscription end date must be after or equal to the start date.',
        ]);

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

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date|after_or_equal:subscription_start_date',
            'status' => 'required|in:0,1',
        ], [
            'subscription_end_date.after_or_equal' => 'The subscription end date must be after or equal to the start date.',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $item->fill($data)->save();

        return successResponse('Update successful', 200, $item);
    }

}
