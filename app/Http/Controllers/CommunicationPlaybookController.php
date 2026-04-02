<?php

namespace App\Http\Controllers;

use App\Models\CommunicationPlaybook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CommunicationPlaybookController extends Controller
{
    private function processData($item)
    {
        if($item->file) {
            $item->file = url('') . '/storage/communication-playbook/' . $item->file;
        }

        return $item;
    }

    public function index()
    {
        $item = CommunicationPlaybook::find(1);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        $this->processData($item);

        return successResponse('success', 200, $item);
    }

    public function update(Request $request)
    {
        $item = CommunicationPlaybook::find(1);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'nullable|mimes:pdf|max:10240',
        ], [
            'file.max' => 'The file must not be greater than 10240 kilobytes.'
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->file('file')) {
            $file = $request->file('file');
            $file_name = Str::uuid()->toString().'.pdf';
            Storage::put("communication-playbook/$file_name", file_get_contents($file));

            Storage::delete("communication-playbook/$item->file");
        }
        else {
            $file_name = $item->file;
        }

        $data = $request->all();
        $data['file'] = $file_name;
        $item->fill($data)->save();

        $this->processData($item);

        return successResponse('Update successful', 200, $item);
    }
}