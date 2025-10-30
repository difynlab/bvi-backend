<?php

namespace App\Http\Controllers;

use App\Models\ReportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportCategoryController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = ReportCategory::orderBy('id', 'desc');
        $items = auth()->user()->role == 'admin' ? $items : $items->where('status', 1);
        $items = $items->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 404);
        }

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $report_category = ReportCategory::where('id', $id);
        $report_category = auth()->user()->role == 'admin' ? $report_category : $report_category->where('status', 1);
        $report_category = $report_category->first();

        if(!$report_category) {
            return errorResponse('No data found', 404);
        }

        return successResponse('success', 200, $report_category);
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
        $report_category = ReportCategory::create($data);

        return successResponse('Create successful', 200, $report_category);
    }

    public function update(Request $request, $id)
    {
        $report_category = ReportCategory::find($id);

        if(!$report_category) {
            return errorResponse('No data found', 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'status' => 'required|in:0,1',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $report_category->fill($data)->save();

        return successResponse('Update successful', 200, $report_category);
    }

    public function destroy($id)
    {
        $report_category = ReportCategory::find($id);

        if(!$report_category) {
            return errorResponse('No data found', 404);
        }

        $report_category->delete();

        return successResponse('Delete successful', 200);
    }
}