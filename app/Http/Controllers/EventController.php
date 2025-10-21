<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    private function processData($item)
    {
        $item->start_time = Carbon::parse($item->start_time)->format('H:i');
        $item->end_time = Carbon::parse($item->end_time)->format('H:i');
        $item->original_thumbnail = url('') . '/storage/events/' . $item->thumbnail;
        $item->blurred_thumbnail = url('') . '/storage/events/thumbnails/' . $item->thumbnail;

        return $item;
    }

    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = Event::orderBy('id', 'desc')->paginate($pagination);

        if($items->isEmpty()) {
            return errorResponse('No data found', 404);
        }

        $items->map(function($item) {
            $this->processData($item);
        });

        return successResponse('success', 200, $items);
    }

    public function show($id)
    {
        $event = Event::find($id);

        if(!$event) {
            return errorResponse('No data found', 404);
        }

        $this->processData($event);

        return successResponse('success', 200, $event);
    }
                                                                              
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'category' => 'required|in:workshop,webinar,conference',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => ['required','regex:/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/'],
            'end_time' => ['required','regex:/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/'],
            'repeat' => 'required|in:na,daily,weekly,monthly,annually',
            'content' => 'required|min:3',
            'location' => 'required|min:3',
            'register_link' => 'required|min:3',
            'thumbnail' => 'required|max:5120',
            'status' => 'required|in:0,1',
        ], [
            'thumbnail.max' => 'The thumbnail must not be greater than 5120 kilobytes.',
            'start_time.regex' => 'The time must be in the format HH:MM.',
            'end_time.regex' => 'The end time must be in the format HH:MM.',
            'date.after_or_equal' => 'The date must be today or later.',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        $processed_thumbnail = process_image($request->file('thumbnail'), 'events');

        $data = $request->all();
        $data['thumbnail'] = $processed_thumbnail;
        $event = Event::create($data);

        $this->processData($event);

        return successResponse('Create successful', 200, $event);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if(!$event) {
            return errorResponse('No data found', 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3',
            'category' => 'required|in:workshop,webinar,conference',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => ['required','regex:/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/'],
            'end_time' => ['required','regex:/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/'],
            'repeat' => 'required|in:na,daily,weekly,monthly,annually',
            'content' => 'required|min:3',
            'location' => 'required|min:3',
            'register_link' => 'required|min:3',
            'thumbnail' => 'nullable|max:5120',
            'status' => 'required|in:0,1',
        ], [
            'thumbnail.max' => 'The thumbnail must not be greater than 5120 kilobytes.',
            'start_time.regex' => 'The time must be in the format HH:MM.',
            'end_time.regex' => 'The end time must be in the format HH:MM.',
            'date.after_or_equal' => 'The date must be today or later.',
        ]);

        if($validator->fails()) {
            return errorResponse('Validation failed', 400, $validator->errors());
        }

        if($request->file('thumbnail')) {
            $processed_thumbnail = process_image($request->file('thumbnail'), 'events', $event->thumbnail);
        }
        else {
            $processed_thumbnail = $event->thumbnail;
        }

        $data = $request->all();
        $data['thumbnail'] = $processed_thumbnail;
        $event->fill($data)->save();

        $this->processData($event);

        return successResponse('Update successful', 200, $event);
    }

    public function destroy($id)
    {
        $event = Event::find($id);

        if(!$event) {
            return errorResponse('No data found', 404);
        }

        $event->delete();

        return successResponse('Delete successful', 200);
    }
}