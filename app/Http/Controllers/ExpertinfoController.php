<?php

namespace App\Http\Controllers;

use App\Models\ExpertInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpertInfoController extends Controller
{
    public function index()
    {
        $item = ExpertInfo::find(1);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        return successResponse('success', 200, $item);
    }

    public function update(Request $request)
    {
        $item = ExpertInfo::find(1);

        if(!$item) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|min:3',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $data = $request->all();
        $item->fill($data)->save();

        return successResponse('Update successful', 200, $item);
    }
}