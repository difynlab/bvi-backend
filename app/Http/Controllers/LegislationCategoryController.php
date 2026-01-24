<?php

namespace App\Http\Controllers;

use App\Models\LegislationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LegislationCategoryController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = LegislationCategory::orderBy('id', 'desc');
        $items = auth()->user()->role == 'admin' ? $items : $items->where('status', 1);
        $items = $items->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $legislation_category = LegislationCategory::where('id', $id);
        $legislation_category = auth()->user()->role == 'admin' ? $legislation_category : $legislation_category->where('status', 1);
        $legislation_category = $legislation_category->first();

        if(!$legislation_category) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $legislation_category);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'status' => 'required|in:0,1',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $legislation_category = LegislationCategory::create($data);

        return successResponse('Create successful', 200, $legislation_category);
    }

    public function update(Request $request, $id)
    {
        $legislation_category = LegislationCategory::find($id);

        if(!$legislation_category) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'status' => 'required|in:0,1',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $legislation_category->fill($data)->save();

        return successResponse('Update successful', 200, $legislation_category);
    }

    public function destroy($id)
    {
        $legislation_category = LegislationCategory::find($id);

        if(!$legislation_category) {
            return errorResponse('No data found', 200);
        }

        $legislation_category->delete();

        return successResponse('Delete successful', 200);
    }
}