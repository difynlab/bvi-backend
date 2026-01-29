<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    private function processData($item)
    {
        if($item->type == 'image') {
            $item->original_image = url('') . '/storage/galleries/' . $item->image;
            $item->blurred_image = url('') . '/storage/galleries/thumbnails/' . $item->image;
        }

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = Gallery::orderBy('id', 'desc');
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
        $gallery = Gallery::where('id', $id);
        $gallery = auth()->user()->role == 'admin' ? $gallery : $gallery->where('status', 1);
        $gallery = $gallery->first();

        if(!$gallery) {
            return errorResponse('No data found', 200);
        }

        $this->processData($gallery);

        return successResponse('success', 200, $gallery);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:image,video',
            'image' => 'nullable|max:5120',
            'url' => 'nullable|url',
            'status' => 'required|in:0,1',
        ], [
            'image.max' => 'The image must not be greater than 5120 kilobytes.',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->type == 'image') {
            $validator = Validator::make($request->all(), [
                'image' => 'required|max:5120',
            ], [
                'image.max' => 'The image must not be greater than 5120 kilobytes.',
            ]);

            if($validator->fails()) {
                return errorResponse('Validation failed', 400, $validator->errors());
            }

            $image = process_image($request->file('image'), 'galleries');
        }
        else {
            $validator = Validator::make($request->all(), [
                'url' => 'required|url'
            ]);

            if($validator->fails()) {
                return errorResponse('Validation failed', 400, $validator->errors());
            }

            $url = $request->url;
        }

        $data = $request->all();
        $data['image'] = $image ?? null;
        $data['url'] = $url ?? null;
        $gallery = Gallery::create($data);

        $this->processData($gallery);

        return successResponse('Create successful', 200, $gallery);
    }

    public function update(Request $request, $id)
    {
        $gallery = Gallery::find($id);

        if(!$gallery) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:image,video',
            'image' => 'nullable|max:5120',
            'url' => 'nullable|url',
            'status' => 'required|in:0,1',
        ], [
            'image.max' => 'The image must not be greater than 5120 kilobytes.',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->type == 'image') {
            $validator = Validator::make($request->all(), [
                'image' => 'required|max:5120',
            ], [
                'image.max' => 'The image must not be greater than 5120 kilobytes.',
            ]);

            if($validator->fails()) {
                return errorResponse('Validation failed', 400, $validator->errors());
            }

            $image = process_image($request->file('image'), 'galleries', $gallery->image);
        }
        else {
            $validator = Validator::make($request->all(), [
                'url' => 'required|url'
            ]);

            if($validator->fails()) {
                return errorResponse('Validation failed', 400, $validator->errors());
            }

            $url = $request->url;
        }

        $data = $request->all();
        $data['image'] = $image ?? null;
        $data['url'] = $url ?? null;
        $gallery->fill($data)->save();

        $this->processData($gallery);

        return successResponse('Update successful', 200, $gallery);
    }

    public function destroy($id)
    {
        $gallery = Gallery::find($id);

        if(!$gallery) {
            return errorResponse('No data found', 200);
        }

        $gallery->delete();

        return successResponse('Delete successful', 200);
    }
}