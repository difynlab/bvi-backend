<?php

namespace App\Http\Controllers;

use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventCategoryController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = EventCategory::orderBy('id', 'desc');
        $items = auth()->user()->role == 'admin' ? $items : $items->where('status', 1);
        $items = $items->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $event_category = EventCategory::where('id', $id);
        $event_category = auth()->user()->role == 'admin' ? $event_category : $event_category->where('status', 1);
        $event_category = $event_category->first();

        if(!$event_category) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $event_category);
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
        $event_category = EventCategory::create($data);

        return successResponse('Create successful', 200, $event_category);
    }

    public function update(Request $request, $id)
    {
        $event_category = EventCategory::find($id);

        if(!$event_category) {
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
        $event_category->fill($data)->save();

        return successResponse('Update successful', 200, $event_category);
    }

    public function destroy($id)
    {
        $event_category = EventCategory::find($id);

        if(!$event_category) {
            return errorResponse('No data found', 200);
        }

        $event_category->delete();

        return successResponse('Delete successful', 200);
    }
}