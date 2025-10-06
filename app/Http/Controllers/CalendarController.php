<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'type' => 'nullable|in:all,academic,holiday,exam,event,class'
        ]);

        $user = auth()->user();
        $query = Event::with('class.department', 'class.teacher');

        // Filter by date range
        $query->where(function ($q) use ($request) {
            $q->whereBetween('start_date', [$request->start, $request->end])
              ->orWhereBetween('end_date', [$request->start, $request->end])
              ->orWhere(function ($q) use ($request) {
                  $q->where('start_date', '<=', $request->start)
                    ->where('end_date', '>=', $request->end);
              });
        });

        // Filter by type if specified
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // For students, only show events for their classes
        if ($user->isStudent()) {
            $studentClassIds = $user->attendances()->pluck('class_id')->unique();
            $query->where(function ($q) use ($studentClassIds) {
                $q->whereNull('class_id')
                  ->orWhereIn('class_id', $studentClassIds);
            });
        }

        // For teachers, only show events for their classes or general events
        if ($user->isTeacher()) {
            $teacherClassIds = $user->teacherClasses()->pluck('id');
            $query->where(function ($q) use ($teacherClassIds) {
                $q->whereNull('class_id')
                  ->orWhereIn('class_id', $teacherClassIds);
            });
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
            'type' => 'required|in:academic,holiday,exam,event,meeting,class',
            'class_id' => 'nullable|exists:classes,id',
            'color' => 'nullable|string|max:7',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'recurring' => 'boolean',
            'recurring_pattern' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_until' => 'nullable|date'
        ]);

        DB::beginTransaction();

        try {
            $event = Event::create([
                'title' => $request->title,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'type' => $request->type,
                'class_id' => $request->class_id,
                'color' => $request->color ?? $this->getDefaultColor($request->type),
                'all_day' => $request->boolean('all_day'),
                'location' => $request->location,
                'recurring' => $request->boolean('recurring'),
                'recurring_pattern' => $request->recurring_pattern,
                'recurring_until' => $request->recurring_until,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Event created successfully',
                'event' => $event->load('class.department', 'class.teacher')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Event $event)
    {
        $event->load('class.department', 'class.teacher', 'creator');
        return response()->json($event);
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'type' => 'sometimes|required|in:academic,holiday,exam,event,meeting,class',
            'class_id' => 'nullable|exists:classes,id',
            'color' => 'nullable|string|max:7',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'recurring' => 'boolean',
            'recurring_pattern' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_until' => 'nullable|date'
        ]);

        $event->update($request->all());

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event->load('class.department', 'class.teacher', 'creator')
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:events,id'
        ]);

        Event::whereIn('id', $request->event_ids)->delete();

        return response()->json([
            'message' => 'Events deleted successfully'
        ]);
    }

    public function upcomingEvents(Request $request)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
            'days' => 'nullable|integer|min:1|max:365'
        ]);

        $limit = $request->get('limit', 10);
        $days = $request->get('days', 30);

        $user = auth()->user();
        $query = Event::with('class.department', 'class.teacher')
                     ->where('start_date', '>=', now())
                     ->where('start_date', '<=', now()->addDays($days))
                     ->orderBy('start_date');

        // Filter events based on user role
        if ($user->isStudent()) {
            $studentClassIds = $user->attendances()->pluck('class_id')->unique();
            $query->where(function ($q) use ($studentClassIds) {
                $q->whereNull('class_id')
                  ->orWhereIn('class_id', $studentClassIds);
            });
        }

        if ($user->isTeacher()) {
            $teacherClassIds = $user->teacherClasses()->pluck('id');
            $query->where(function ($q) use ($teacherClassIds) {
                $q->whereNull('class_id')
                  ->orWhereIn('class_id', $teacherClassIds);
            });
        }

        $events = $query->limit($limit)->get();

        return response()->json($events);
    }

    public function eventStatistics(Request $request)
    {
        $user = auth()->user();
        $query = Event::query();

        // Filter events based on user role
        if ($user->isStudent()) {
            $studentClassIds = $user->attendances()->pluck('class_id')->unique();
            $query->where(function ($q) use ($studentClassIds) {
                $q->whereNull('class_id')
                  ->orWhereIn('class_id', $studentClassIds);
            });
        }

        if ($user->isTeacher()) {
            $teacherClassIds = $user->teacherClasses()->pluck('id');
            $query->where(function ($q) use ($teacherClassIds) {
                $q->whereNull('class_id')
                  ->orWhereIn('class_id', $teacherClassIds);
            });
        }

        $stats = [
            'total_events' => $query->count(),
            'events_by_type' => $query->selectRaw('type, COUNT(*) as count')
                                    ->groupBy('type')
                                    ->get()
                                    ->pluck('count', 'type'),
            'upcoming_events' => $query->where('start_date', '>=', now())->count(),
            'events_this_month' => $query->whereYear('start_date', now()->year)
                                       ->whereMonth('start_date', now()->month)
                                       ->count(),
        ];

        return response()->json($stats);
    }

    private function getDefaultColor($type)
    {
        $colors = [
            'academic' => '#3b82f6', // blue
            'holiday' => '#ef4444', // red
            'exam' => '#f59e0b', // amber
            'event' => '#10b981', // green
            'meeting' => '#8b5cf6', // violet
            'class' => '#06b6d4' // cyan
        ];

        return $colors[$type] ?? '#6b7280'; // gray as default
    }
}