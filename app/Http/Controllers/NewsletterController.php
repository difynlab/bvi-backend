<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    private function processData($item)
    {
        $item->original_thumbnail = url('') . '/storage/newsletters/' . $item->thumbnail;
        $item->blurred_thumbnail = url('') . '/storage/newsletters/thumbnails/' . $item->thumbnail;
        $item->file = url('') . '/storage/newsletters/' . $item->file;

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = Newsletter::orderBy('id', 'desc')->paginate($pagination);

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
        $newsletter = Newsletter::find($id);

        if(!$newsletter) {
            return errorResponse('No data found', 404);
        }

        $this->processData($newsletter);

        return successResponse('success', 200, $newsletter);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'thumbnail' => 'required|max:5120',
            'file' => 'required|mimes:pdf|max:15360',
            'link' => 'required|min:3',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 15360 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $file = $request->file('file');
        $file_name = Str::uuid()->toString().'.pdf';
        Storage::put("newsletters/$file_name", file_get_contents($file));


        $processed_thumbnail = process_image($request->file('thumbnail'), 'newsletters');

        $data = $request->all();
        $data['thumbnail'] = $processed_thumbnail;
        $data['file'] = $file_name;
        $newsletter = Newsletter::create($data);

        $this->processData($newsletter);

        return successResponse('Create successful', 200, $newsletter);
    }

    public function update(Request $request, $id)
    {
        $newsletter = Newsletter::find($id);

        if(!$newsletter) {
            return errorResponse('No data found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'thumbnail' => 'nullable|max:5120',
            'file' => 'nullable|mimes:pdf|max:15360',
            'link' => 'required|min:3',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 15360 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->file('file')) {
            $file = $request->file('file');
            $file_name = Str::uuid()->toString().'.pdf';
            Storage::put("newsletters/$file_name", file_get_contents($file));

            Storage::delete("newsletters/$newsletter->file");
        }
        else {
            $file_name = $newsletter->file;
        }

        if($request->file('thumbnail')) {
            $processed_thumbnail = process_image($request->file('thumbnail'), 'newsletters', $newsletter->thumbnail);
        }
        else {
            $processed_thumbnail = $newsletter->thumbnail;
        }

        $data = $request->all();
        $data['thumbnail'] = $processed_thumbnail;
        $data['file'] = $file_name;
        $newsletter->fill($data)->save();

        $this->processData($newsletter);

        return successResponse('Update successful', 200, $newsletter);
    }

    public function destroy($id)
    {
        $newsletter = Newsletter::find($id);

        if(!$newsletter) {
            return errorResponse('No data found', 404);
        }

        $newsletter->delete();

        return successResponse('Delete successful', 200);
    }
}