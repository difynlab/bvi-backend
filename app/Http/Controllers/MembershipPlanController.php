<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MembershipPlanController extends Controller
{
    private function processData($item)
    {
        $item->perks = json_decode($item->perks);

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = MembershipPlan::orderBy('id', 'desc');
        $items = auth()->user()->role == 'admin' ? $items : $items->where('status', 1);
        $items = $items->paginate($pagination);

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
        $membership_plan = MembershipPlan::where('id', $id);
        $membership_plan = auth()->user()->role == 'admin' ? $membership_plan : $membership_plan->where('status', 1);
        $membership_plan = $membership_plan->first();

        if(!$membership_plan) {
            return errorResponse('No data found', 200);
        }

        $this->processData($membership_plan);

        return successResponse('success', 200, $membership_plan);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'description' => 'required|min:3',
            'eligibility_criteria' => 'required|min:3',
            'perks' => 'required|array',
            'perks.*' => 'required|min:3',
            'status' => 'required|in:0,1',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $data['perks'] = json_encode($request->perks);
        $membership_plan = MembershipPlan::create($data);

        $this->processData($membership_plan);

        return successResponse('Create successful', 200, $membership_plan);
    }

    public function update(Request $request, $id)
    {
        $membership_plan = MembershipPlan::find($id);

        if(!$membership_plan) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'description' => 'required|min:3',
            'eligibility_criteria' => 'required|min:3',
            'perks' => 'required|array',
            'perks.*' => 'required|min:3',
            'status' => 'required|in:0,1',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $data['perks'] = json_encode($request->perks);
        $membership_plan->fill($data)->save();

        $this->processData($membership_plan);

        return successResponse('Update successful', 200, $membership_plan);
    }

    public function destroy($id)
    {
        $membership_plan = MembershipPlan::find($id);

        if(!$membership_plan) {
            return errorResponse('No data found', 200);
        }

        $membership_plan->delete();

        return successResponse('Delete successful', 200);
    }
}