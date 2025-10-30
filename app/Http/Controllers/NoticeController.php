<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NoticeController extends Controller
{
    private function processData($item)
    {
        $item->notice_category = NoticeCategory::find($item->notice_category_id);
        $item->original_thumbnail = url('') . '/storage/notices/' . $item->thumbnail;
        $item->blurred_thumbnail = url('') . '/storage/notices/thumbnails/' . $item->thumbnail;
        $item->file = url('') . '/storage/notices/' . $item->file;

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = Notice::orderBy('id', 'desc');
        $items = auth()->user()->role == 'admin' ? $items : $items->where('status', 1);
        $items = $items->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 404);
        }

        $items->map(function($item) {
            $this->processData($item);
        });

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $notice = Notice::where('id', $id);
        $notice = auth()->user()->role == 'admin' ? $notice : $notice->where('status', 1);
        $notice = $notice->first();

        if(!$notice) {
            return errorResponse('No data found', 404);
        }

        $this->processData($notice);

        return successResponse('success', 200, $notice);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'thumbnail' => 'required|max:5120',
            'file' => 'required|mimes:pdf|max:15360',
            'link' => 'required|min:3',
            'notice_category_id' => 'required|exists:notice_categories,id,status,1',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 15360 kilobytes.',
            'notice_category_id.exists' => 'The selected notice category is invalid or its status is not active.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $file = $request->file('file');
        $file_name = Str::uuid()->toString().'.pdf';
        Storage::put("notices/$file_name", file_get_contents($file));


        $processed_thumbnail = process_image($request->file('thumbnail'), 'notices');

        $data = $request->all();
        $data['thumbnail'] = $processed_thumbnail;
        $data['file'] = $file_name;
        $notice = Notice::create($data);

        $this->processData($notice);

        return successResponse('Create successful', 200, $notice);
    }

    public function update(Request $request, $id)
    {
        $notice = Notice::find($id);

        if(!$notice) {
            return errorResponse('No data found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'thumbnail' => 'nullable|max:5120',
            'file' => 'nullable|mimes:pdf|max:15360',
            'link' => 'required|min:3',
            'notice_category_id' => 'required|exists:notice_categories,id,status,1',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 15360 kilobytes.',
            'notice_category_id.exists' => 'The selected notice category is invalid or its status is not active.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->file('file')) {
            $file = $request->file('file');
            $file_name = Str::uuid()->toString().'.pdf';
            Storage::put("notices/$file_name", file_get_contents($file));

            Storage::delete("notices/$notice->file");
        }
        else {
            $file_name = $notice->file;
        }

        if($request->file('thumbnail')) {
            $processed_thumbnail = process_image($request->file('thumbnail'), 'notices', $notice->thumbnail);
        }
        else {
            $processed_thumbnail = $notice->thumbnail;
        }

        $data = $request->all();
        $data['thumbnail'] = $processed_thumbnail;
        $data['file'] = $file_name;
        $notice->fill($data)->save();

        $this->processData($notice);

        return successResponse('Update successful', 200, $notice);
    }

    public function destroy($id)
    {
        $notice = Notice::find($id);

        if(!$notice) {
            return errorResponse('No data found', 404);
        }

        $notice->delete();

        return successResponse('Delete successful', 200);
    }
}