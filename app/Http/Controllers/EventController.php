<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 6;
        $page = $request->page ?? 1;

        $items = Event::orderBy('id', 'desc')->paginate($pagination);

        $items->map(function($item) {
            $item->original_image = url('') . '/storage/events/' . $item->thumbnail;
            $item->blurred_image = url('') . '/storage/events/thumbnails/' . $item->thumbnail;
        });

        return successResponse('success', 200, $items);
    }

    public function show(Event $event)
    {
        // $event->start_time = Carbon::parse($event->start_time)->format('H:i');
        // $event->end_time = Carbon::parse($event->end_time)->format('H:i');
        $event->original_image = url('') . '/storage/events/' . $event->thumbnail;
        $event->blurred_image = url('') . '/storage/events/thumbnails/' . $event->thumbnail;

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
            'new_thumbnail' => 'required|max:5120',
            'status' => 'required|in:0,1,2',
        ], [
            'new_thumbnail.max' => 'The thumbnail must not be greater than 5120 kilobytes.',
            'start_time.regex' => 'The time must be in the format HH:MM.',
            'end_time.regex' => 'The end time must be in the format HH:MM.',
            'date.after_or_equal' => 'The date must be today or later.',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with([
                'error' => 'Creation Failed!',
                'message' => 'Your information has not been updated.'
            ]);
        }

        $processed_thumbnail = process_image($request->file('new_thumbnail'), 'events');

        $data = $request->except(
            'old_thumbnail',
            'new_thumbnail',
        );
        $data['thumbnail'] = $processed_thumbnail;
        $event = Event::create($data); 

        return redirect()->route('admin.events.index')->with([
            'success' => "Create Successful!",
            'message' => 'All changes have been successfully updated and saved.'
        ]);
    }

    public function edit(Event $event)
    {
        $event->start_time = Carbon::parse($event->start_time)->format('H:i');
        $event->end_time = Carbon::parse($event->end_time)->format('H:i');

        return response()->json($event);
    }

    public function update(Request $request, Event $event)
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
            'new_thumbnail' => 'nullable|max:5120',
            'status' => 'required|in:0,1,2',
        ], [
            'new_thumbnail.max' => 'The thumbnail must not be greater than 5120 kilobytes.',
            'start_time.regex' => 'The time must be in the format HH:MM.',
            'end_time.regex' => 'The end time must be in the format HH:MM.',
            'date.after_or_equal' => 'The date must be today or later.',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with([
                'error' => 'Update Failed!',
                'message' => 'Your information has not been updated.'
            ]);
        }

        if($request->file('new_thumbnail')) {
            $processed_thumbnail = process_image($request->file('new_thumbnail'), 'events', $request->old_thumbnail);
        }
        else {
            $processed_thumbnail = $request->old_thumbnail;
        }

        $data = $request->except(
            'old_thumbnail',
            'new_thumbnail',
        );
        $data['thumbnail'] = $processed_thumbnail;
        $event->fill($data)->save();
        
        return redirect()->back()->with([
            'success' => "Update Successful!",
            'message' => 'All changes have been successfully updated and saved.'
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->back()->with([
            'success' => 'Successfully deleted',
            'message' => 'This information is removed from the system.'
        ]);
    }
}