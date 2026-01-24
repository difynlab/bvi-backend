<?php

namespace App\Http\Controllers;

use App\Models\NewsletterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterCategoryController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = NewsletterCategory::orderBy('id', 'desc');
        $items = auth()->user()->role == 'admin' ? $items : $items->where('status', 1);
        $items = $items->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $newsletter_category = NewsletterCategory::where('id', $id);
        $newsletter_category = auth()->user()->role == 'admin' ? $newsletter_category : $newsletter_category->where('status', 1);
        $newsletter_category = $newsletter_category->first();

        if(!$newsletter_category) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $newsletter_category);
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
        $newsletter_category = NewsletterCategory::create($data);

        return successResponse('Create successful', 200, $newsletter_category);
    }

    public function update(Request $request, $id)
    {
        $newsletter_category = NewsletterCategory::find($id);

        if(!$newsletter_category) {
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
        $newsletter_category->fill($data)->save();

        return successResponse('Update successful', 200, $newsletter_category);
    }

    public function destroy($id)
    {
        $newsletter_category = NewsletterCategory::find($id);

        if(!$newsletter_category) {
            return errorResponse('No data found', 200);
        }

        $newsletter_category->delete();

        return successResponse('Delete successful', 200);
    }
}