<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpecializationController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = Specialization::orderBy('id', 'desc');
        $items = auth()->user()->role == 'admin' ? $items : $items->where('status', 1);
        $items = $items->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $specialization = Specialization::where('id', $id);
        $specialization = auth()->user()->role == 'admin' ? $specialization : $specialization->where('status', 1);
        $specialization = $specialization->first();

        if(!$specialization) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $specialization);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'status' => 'required|in:0,1',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $specialization = Specialization::create($data);

        return successResponse('Create successful', 200, $specialization);
    }

    public function update(Request $request, $id)
    {
        $specialization = Specialization::find($id);

        if(!$specialization) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'status' => 'required|in:0,1',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $specialization->fill($data)->save();

        return successResponse('Update successful', 200, $specialization);
    }

    public function destroy($id)
    {
        $specialization = Specialization::find($id);

        if(!$specialization) {
            return errorResponse('No data found', 200);
        }

        $specialization->delete();

        return successResponse('Delete successful', 200);
    }
}