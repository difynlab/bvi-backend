<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    private function processData($item)
    {
        $item->report_category = ReportCategory::find($item->report_category_id);

        if($item->file) {
            $item->file = url('') . '/storage/reports/' . $item->file;
        }

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = Report::orderBy('id', 'desc');
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
        $report = Report::where('id', $id);
        $report = auth()->user()->role == 'admin' ? $report : $report->where('status', 1);
        $report = $report->first();

        if(!$report) {
            return errorResponse('No data found', 200);
        }

        $this->processData($report);

        return successResponse('success', 200, $report);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'file' => 'nullable|mimes:pdf|max:10240',
            'link' => 'nullable|url',
            'publish_date' => 'required|date',
            'report_category_id' => 'required|exists:report_categories,id,status,1',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 10240 kilobytes.',
            'report_category_id.exists' => 'The selected report category is invalid or its status is not active.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $file = $request->file('file');
        $file_name = Str::uuid()->toString().'.pdf';
        Storage::put("reports/$file_name", file_get_contents($file));

        $data = $request->all();
        $data['file'] = $file_name;
        $report = Report::create($data);

        $this->processData($report);

        return successResponse('Create successful', 200, $report);
    }

    public function update(Request $request, $id)
    {
        $report = Report::find($id);

        if(!$report) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'file' => 'nullable|mimes:pdf|max:10240',
            'link' => 'nullable|url',
            'publish_date' => 'required|date',
            'report_category_id' => 'required|exists:report_categories,id,status,1',
            'status' => 'required|in:0,1'
        ], [
            'file.max' => 'The file must not be greater than 10240 kilobytes.',
            'report_category_id.exists' => 'The selected report category is invalid or its status is not active.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->file('file')) {
            $file = $request->file('file');
            $file_name = Str::uuid()->toString().'.pdf';
            Storage::put("reports/$file_name", file_get_contents($file));

            Storage::delete("reports/$report->file");
        }
        else {
            $file_name = $report->file;
        }

        $data = $request->all();
        $data['file'] = $file_name;
        $report->fill($data)->save();

        $this->processData($report);

        return successResponse('Update successful', 200, $report);
    }

    public function destroy($id)
    {
        $report = Report::find($id);

        if(!$report) {
            return errorResponse('No data found', 200);
        }

        $report->delete();

        return successResponse('Delete successful', 200);
    }
}