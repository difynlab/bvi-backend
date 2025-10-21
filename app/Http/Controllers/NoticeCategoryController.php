<?php

namespace App\Http\Controllers;

use App\Models\NoticeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoticeCategoryController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = NoticeCategory::orderBy('id', 'desc')->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 404);
        }

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $notice_category = NoticeCategory::find($id);

        if(!$notice_category) {
            return errorResponse('No data found', 404);
        }

        return successResponse('success', 200, $notice_category);
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
        $notice_category = NoticeCategory::create($data);

        return successResponse('Create successful', 200, $notice_category);
    }

    public function update(Request $request, $id)
    {
        $notice_category = NoticeCategory::find($id);

        if(!$notice_category) {
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
        $notice_category->fill($data)->save();

        return successResponse('Update successful', 200, $notice_category);
    }

    public function destroy($id)
    {
        $notice_category = NoticeCategory::find($id);

        if(!$notice_category) {
            return errorResponse('No data found', 404);
        }

        $notice_category->delete();

        return successResponse('Delete successful', 200);
    }
}