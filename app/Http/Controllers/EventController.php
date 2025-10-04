<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('class');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $events = $query->orderBy('start_date')->get();

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:academic,holiday,exam,event,meeting',
            'class_id' => 'nullable|exists:classes,id'
        ]);

        $event = Event::create($request->all());

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event->load('class')
        ], 201);
    }

    public function show(Event $event)
    {
        $event->load('class');
        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'type' => 'sometimes|required|in:academic,holiday,exam,event,meeting',
            'class_id' => 'nullable|exists:classes,id'
        ]);

        $event->update($request->all());

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event->load('class')
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }

    public function calendarEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        $events = Event::whereBetween('start_date', [$request->start, $request->end])
                      ->orWhereBetween('end_date', [$request->start, $request->end])
                      ->with('class')
                      ->get()
                      ->map(function ($event) {
                          return [
                              'id' => $event->id,
                              'title' => $event->title,
                              'start' => $event->start_date,
                              'end' => $event->end_date,
                              'type' => $event->type,
                              'description' => $event->description,
                              'className' => 'event-type-' . $event->type,
                              'extendedProps' => [
                                  'class' => $event->class,
                                  'type' => $event->type
                              ]
                          ];
                      });

        return response()->json($events);
    }
}
