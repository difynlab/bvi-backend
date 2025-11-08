<?php

namespace App\Http\Controllers;

use App\Models\ImportantInfo;
use App\Models\Legislation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportantInfoController extends Controller
{
    private function processData($item)
    {
        if($item->first_image) {
            $item->original_first_image = url('') . '/storage/important-info/' . $item->first_image;
            $item->blurred_first_image = url('') . '/storage/important-info/thumbnails/' . $item->first_image;
        }

        if($item->second_image) {
            $item->original_second_image = url('') . '/storage/important-info/' . $item->second_image;
            $item->blurred_second_image = url('') . '/storage/important-info/thumbnails/' . $item->second_image;
        }

        if($item->third_image) {
            $item->original_third_image = url('') . '/storage/important-info/' . $item->third_image;
            $item->blurred_third_image = url('') . '/storage/important-info/thumbnails/' . $item->third_image;
        }

        return $item;
    }

    public function index()
    {
        $item = ImportantInfo::find(1);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        $this->processData($item);

        return successResponse('success', 200, $item);
    }

    public function update(Request $request)
    {
        $item = ImportantInfo::find(1);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'first_title' => 'required|min:3',
            'first_description' => 'required|min:3',
            'first_image' => 'nullable|max:5120',

            'second_title' => 'required|min:3',
            'second_description' => 'required|min:3',
            'second_image' => 'nullable|max:5120',

            'third_title' => 'required|min:3',
            'third_description' => 'required|min:3',
            'third_image' => 'nullable|max:5120',
        ], [
            'first_image.max' => 'The first image must not be greater than 5120 kilobytes.',
            'second_image.max' => 'The second image must not be greater than 5120 kilobytes.',
            'third_image.max' => 'The third image must not be greater than 5120 kilobytes.',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->file('first_image')) {
            $processed_first_image = process_image($request->file('first_image'), 'important-info', $item->first_image);
        }
        else {
            $processed_first_image = $item->first_image;
        }

        if($request->file('second_image')) {
            $processed_second_image = process_image($request->file('second_image'), 'important-info', $item->second_image);
        }
        else {
            $processed_second_image = $item->second_image;
        }

        if($request->file('third_image')) {
            $processed_third_image = process_image($request->file('third_image'), 'important-info', $item->third_image);
        }
        else {
            $processed_third_image = $item->third_image;
        }

        $data = $request->all();
        $data['first_image'] = $processed_first_image;
        $data['second_image'] = $processed_second_image;
        $data['third_image'] = $processed_third_image;
        $item->fill($data)->save();

        $this->processData($item);

        return successResponse('Update successful', 200, $item);
    }
}