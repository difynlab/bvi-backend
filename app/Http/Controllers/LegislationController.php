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
                return [
                    'title' => $existing_file['title'] ?? null,
                    'file'  => url("storage/legislation/{$existing_file['file']}"),
                ];
            })->toArray();

            $item->files = $existing_files;
        }

        $item->links = $item->links ? json_decode($item->links) : null;

        return $item;
    }

    public function index()
    {
        $item = Legislation::select('description', 'files', 'links')->find(1);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        $this->processData($item);

        return successResponse('success', 200, $item);
    }

    public function update(Request $request)
    {
        $item = Legislation::find(1);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'files.*' => 'mimes:pdf|max:15360'
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
                    $file_name = $existing_file['file'];

                    Storage::delete("legislation/$file_name");
                }
            }

            foreach($request->file('files') as $key => $file) {
                $file_name = Str::uuid()->toString().'.pdf';
                Storage::put("legislation/$file_name", file_get_contents($file));

                $files[] = [
                    'title' => $request->titles[$key] ?? null,
                    'file' => $file_name,
                ];
            }
        }

        $data = $request->except('titles');
        $data['files'] = $request->file('files') ? json_encode($files) : $item->files;
        $data['links'] = $request->links ? json_encode($request->links) : null;
        $item->fill($data)->save();

        $this->processData($item);

        return successResponse('Update successful', 200, $item);
    }
}