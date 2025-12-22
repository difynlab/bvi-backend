<?php

namespace App\Http\Controllers;

use App\Models\LegislationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LegislationFileController extends Controller
{
    private function processData($item)
    {
        if($item->file) {
            $item->file = url('') . '/storage/legislations/' . $item->file;
        }

        return $item;
    }

    public function index()
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = LegislationFile::orderBy('id', 'desc');
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
        $legislation_file = LegislationFile::where('id', $id);
        $legislation_file = auth()->user()->role == 'admin' ? $legislation_file : $legislation_file->where('status', 1);
        $legislation_file = $legislation_file->first();

        if(!$legislation_file) {
            return errorResponse('No data found', 200);
        }

        $this->processData($legislation_file);

        return successResponse('success', 200, $legislation_file);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'file' => 'required|mimes:pdf|max:15360',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 15360 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $file_name = null;
        if($request->file('file')) {
            $file_name = Str::uuid()->toString().'.pdf';
            Storage::put("legislations/$file_name", file_get_contents($request->file('file')));
        }

        $data = $request->all();
        $data['file'] = $file_name;
        $legislation_file = LegislationFile::create($data);

        $this->processData($legislation_file);

        return successResponse('Create successful', 200, $legislation_file);
    }

    public function update(Request $request, $id)
    {
        $legislation_file = LegislationFile::find($id);

        if(!$legislation_file) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'file' => 'nullable|mimes:pdf|max:15360',
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
            Storage::put("legislations/$file_name", file_get_contents($file));

            Storage::delete("legislations/$legislation_file->file");
        }
        else {
            $file_name = $legislation_file->file;
        }

        $data = $request->all();
        $data['file'] = $file_name;
        $legislation_file->fill($data)->save();

        $this->processData($legislation_file);

        return successResponse('Update successful', 200, $legislation_file);
    }

    public function destroy($id)
    {
        $legislation_file = LegislationFile::find($id);

        if(!$legislation_file) {
            return errorResponse('No data found', 200);
        }

        $legislation_file->delete();

        return successResponse('Delete successful', 200);
    }
}