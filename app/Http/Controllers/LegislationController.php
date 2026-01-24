<?php

namespace App\Http\Controllers;

use App\Models\Legislation;
use App\Models\LegislationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LegislationController extends Controller
{
    // private function processData($item)
    // {
    //     // $existing_files = json_decode($item->files ?? '[]', true);
    //     // if($existing_files) {
    //     //     $existing_files = collect($existing_files)->map(function($existing_file) {
    //     //         return [
    //     //             'title' => $existing_file['title'] ?? null,
    //     //             'file'  => url("storage/legislation/{$existing_file['file']}"),
    //     //         ];
    //     //     })->toArray();

    //     //     $item->files = $existing_files;
    //     // }

    //     $item->links = $item->links ? json_decode($item->links) : null;

    //     return $item;
    // }

    // public function index()
    // {
    //     $item = Legislation::select('description', 'links')->find(1);

    //     if(!$item) {
    //         return errorResponse('No data found', 200);
    //     }

    //     $this->processData($item);

    //     return successResponse('success', 200, $item);
    // }

    // public function update(Request $request)
    // {
    //     $item = Legislation::find(1);

    //     if(!$item) {
    //         return errorResponse('No data found', 200);
    //     }

    //     // $validator = Validator::make($request->all(), [
    //     //     'files.*' => 'mimes:pdf|max:15360'
    //     // ], [
    //     //     'files.*.max' => 'The file must not be greater than 15360 kilobytes.'
    //     // ]);

    //     // if($validator->fails()) {
    //     //     return errorResponse('Validation failed', 400, $validator->errors());
    //     // }

    //     // $files = [];
    //     // if($request->file('files')) {
    //     //     $existing_files = json_decode($item->files ?? '[]', true);
    //     //     if($existing_files) {
    //     //         foreach($existing_files as $existing_file) {
    //     //             $file_name = $existing_file['file'];

    //     //             Storage::delete("legislation/$file_name");
    //     //         }
    //     //     }

    //     //     foreach($request->file('files') as $key => $file) {
    //     //         $file_name = Str::uuid()->toString().'.pdf';
    //     //         Storage::put("legislation/$file_name", file_get_contents($file));

    //     //         $files[] = [
    //     //             'title' => $request->titles[$key] ?? null,
    //     //             'file' => $file_name,
    //     //         ];
    //     //     }
    //     // }

    //     $data = $request->except('titles');
    //     // $data['files'] = $request->file('files') ? json_encode($files) : $item->files;
    //     $data['links'] = $request->links ? json_encode($request->links) : null;
    //     $item->fill($data)->save();

    //     $this->processData($item);

    //     return successResponse('Update successful', 200, $item);
    // }

    private function processData($item)
    {
        if($item->file) {
            $item->file = url('') . '/storage/legislations/' . $item->file;
        }

        $item->legislation_category = LegislationCategory::find($item->legislation_category_id);

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = Legislation::orderBy('id', 'desc');
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
        $legislation = Legislation::where('id', $id);
        $legislation = auth()->user()->role == 'admin' ? $legislation : $legislation->where('status', 1);
        $legislation = $legislation->first();

        if(!$legislation) {
            return errorResponse('No data found', 200);
        }

        $this->processData($legislation);

        return successResponse('success', 200, $legislation);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'file' => 'required|mimes:pdf,png,jpg,jpeg|max:5120',
            'legislation_category_id' => 'required|exists:legislation_categories,id,status,1',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 5120 kilobytes.',
            'legislation_category_id.exists' => 'The selected legislation category is invalid or its status is not active.'
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
                Storage::put("legislations/{$file_name}", file_get_contents($file));
            }
            else {
                $file_name = process_image($file, 'legislations');
            }
        }

        $data = $request->all();
        $data['file'] = $file_name;
        $legislation = Legislation::create($data);

        $this->processData($legislation);

        return successResponse('Create successful', 200, $legislation);
    }

    public function update(Request $request, $id)
    {
        $legislation = Legislation::find($id);

        if(!$legislation) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'file' => 'nullable|mimes:pdf,png,jpg,jpeg|max:5120',
            'legislation_category_id' => 'required|exists:legislation_categories,id,status,1',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 5120 kilobytes.',
            'legislation_category_id.exists' => 'The selected legislation category is invalid or its status is not active.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $file = $request->file('file');
        $file_name = $legislation->file;
        if($file) {
            $extension = strtolower($file->getClientOriginalExtension());

            if($extension === 'pdf') {
                $file_name = Str::uuid()->toString().'.pdf';
                Storage::put("legislations/$file_name", file_get_contents($file));

                Storage::delete("legislations/$legislation->file");
            }
            else {
                $file_name = process_image($file, 'legislations', $legislation->file);
            }
        }

        $data = $request->all();
        $data['file'] = $file_name;
        $legislation->fill($data)->save();

        $this->processData($legislation);

        return successResponse('Update successful', 200, $legislation);
    }

    public function destroy($id)
    {
        $legislation = Legislation::find($id);

        if(!$legislation) {
            return errorResponse('No data found', 200);
        }

        $legislation->delete();

        return successResponse('Delete successful', 200);
    }
}