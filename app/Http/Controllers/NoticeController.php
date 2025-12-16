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

        if($item->file) {
            $item->file = url('') . '/storage/notices/' . $item->file;
        }

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
            return errorResponse('No data found', 200);
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
            return errorResponse('No data found', 200);
        }

        $this->processData($notice);

        return successResponse('success', 200, $notice);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'file' => 'nullable|mimes:pdf,png,jpg,jpeg|max:5120',
            'link' => 'nullable|min:3',
            'publish_date' => 'required',
            'notice_category_id' => 'required|exists:notice_categories,id,status,1',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 5120 kilobytes.',
            'notice_category_id.exists' => 'The selected notice category is invalid or its status is not active.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $file = $request->file('file');
        $file_name = null;
        if($file) {
            $extension = strtolower($file->getClientOriginalExtension());

            if($extension === 'pdf') {
                $file_name = Str::uuid()->toString() . '.pdf';
                Storage::put("notices/{$file_name}", file_get_contents($file));
            }
            else {
                $file_name = process_image($file, 'notices');
            }
        }

        $data = $request->all();
        $data['file'] = $file_name;
        $notice = Notice::create($data);

        $this->processData($notice);

        return successResponse('Create successful', 200, $notice);
    }

    public function update(Request $request, $id)
    {
        $notice = Notice::find($id);

        if(!$notice) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'file' => 'nullable|mimes:pdf,png,jpg,jpeg|max:5120',
            'link' => 'nullable|min:3',
            'publish_date' => 'required',
            'notice_category_id' => 'required|exists:notice_categories,id,status,1',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 5120 kilobytes.',
            'notice_category_id.exists' => 'The selected notice category is invalid or its status is not active.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $file = $request->file('file');
        $file_name = $notice->file;
        if($file) {
            $extension = strtolower($file->getClientOriginalExtension());

            if($extension === 'pdf') {
                $file_name = Str::uuid()->toString().'.pdf';
                Storage::put("notices/$file_name", file_get_contents($file));

                Storage::delete("notices/$notice->file");
            }
            else {
                $file_name = process_image($file, 'notices', $notice->file);
            }
        }

        $data = $request->all();
        $data['file'] = $file_name;
        $notice->fill($data)->save();

        $this->processData($notice);

        return successResponse('Update successful', 200, $notice);
    }

    public function destroy($id)
    {
        $notice = Notice::find($id);

        if(!$notice) {
            return errorResponse('No data found', 200);
        }

        $notice->delete();

        return successResponse('Delete successful', 200);
    }
}