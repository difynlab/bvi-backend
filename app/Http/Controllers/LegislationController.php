<?php

namespace App\Http\Controllers;

use App\Models\Legislation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LegislationController extends Controller
{
    private function processData($item)
    {
        $existing_files = json_decode($item->files ?? '[]', true);
        if($existing_files) {
            $existing_files = collect($existing_files)->map(function($existing_file) {
                return url('') . '/storage/legislation/' . $existing_file;
            })->toArray();

            $item->files = $existing_files;
        }

        return $item;
    }

    public function index()
    {
        $item = Legislation::select('description', 'files', 'link')->find(1);

        if(!$item) {
            return errorResponse('No data found', 404);
        }

        $this->processData($item);

        return successResponse('success', 200, $item);
    }

    public function update(Request $request)
    {
        $item = Legislation::find(1);

        if(!$item) {
            return errorResponse('No data found', 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|min:3',
            'files.*' => 'mimes:pdf|max:15360',
            'link' => 'required|min:3'
        ], [
            'files.*.max' => 'The file must not be greater than 15360 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $files = [];
        if($request->file('files')) {
            $existing_files = json_decode($item->files ?? '[]', true);
            if($existing_files) {
                foreach($existing_files as $existing_file) {
                    Storage::delete("legislation/$existing_file");
                }
            }

            foreach($request->file('files') as $file) {
                $file_name = Str::uuid()->toString().'.pdf';
                Storage::put("legislation/$file_name", file_get_contents($file));

                $files[] = $file_name;
            }
        }

        $data = $request->all();
        $data['files'] = $request->file('files') ? json_encode($files) : $item->files;
        $item->fill($data)->save();

        $this->processData($item);

        return successResponse('Update successful', 200, $item);
    }
}