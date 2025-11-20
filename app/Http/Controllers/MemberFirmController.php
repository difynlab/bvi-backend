<?php

namespace App\Http\Controllers;

use App\Models\MemberFirm;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemberFirmController extends Controller
{
    private function processData($item)
    {
        $item->specialization = Specialization::find($item->specialization_id);

        if($item->image) {
            $item->original_image = url('') . '/storage/member-firms/' . $item->image;
            $item->blurred_image = url('') . '/storage/member-firms/thumbnails/' . $item->image;
        }

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = MemberFirm::orderBy('id', 'desc');
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
        $member_firm = MemberFirm::where('id', $id);
        $member_firm = auth()->user()->role == 'admin' ? $member_firm : $member_firm->where('status', 1);
        $member_firm = $member_firm->first();

        if(!$member_firm) {
            return errorResponse('No data found', 200);
        }

        $this->processData($member_firm);

        return successResponse('success', 200, $member_firm);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'image' => 'required|max:5120',
            'website_link' => 'required|min:3',
            'address' => 'required|min:3',
            'contact_number' => 'required|min:3',
            'email' => 'required|min:3',
            'specialization_id' => 'required|exists:specializations,id,status,1',
            'status' => 'required|in:0,1',
        ], [
            'image.max' => 'The image must not be greater than 5120 kilobytes.',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $processed_image = process_image($request->file('image'), 'member-firms');

        $data = $request->all();
        $data['image'] = $processed_image;
        $member_firm = MemberFirm::create($data);

        $this->processData($member_firm);

        return successResponse('Create successful', 200, $member_firm);
    }

    public function update(Request $request, $id)
    {
        $member_firm = MemberFirm::find($id);

        if(!$member_firm) {
            return errorResponse('No data found', 200);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'required|min:3',
            'image' => 'nullable|max:5120',
            'website_link' => 'required|min:3',
            'address' => 'required|min:3',
            'contact_number' => 'required|min:3',
            'email' => 'required|min:3',
            'specialization_id' => 'required|exists:specializations,id,status,1',
            'status' => 'required|in:0,1',
        ], [
            'image.max' => 'The image must not be greater than 5120 kilobytes.',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->file('image')) {
            $processed_image = process_image($request->file('image'), 'member-firms', $member_firm->image);
        }
        else {
            $processed_image = $member_firm->image;
        }

        $data = $request->all();
        $data['image'] = $processed_image;
        $member_firm->fill($data)->save();

        $this->processData($member_firm);

        return successResponse('Update successful', 200, $member_firm);
    }

    public function destroy($id)
    {
        $member_firm = MemberFirm::find($id);

        if(!$member_firm) {
            return errorResponse('No data found', 200);
        }

        $member_firm->delete();

        return successResponse('Delete successful', 200);
    }
}