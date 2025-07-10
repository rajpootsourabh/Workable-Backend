<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Get all events accessible to employee or manager
    public function index()
    {
        $employeeId = Auth::user()->employee_id;

        // Get manager_id of the logged-in employee (null if not assigned)
        $managerId = optional(Auth::user()->employee->jobDetail)->manager_id;

        $events = Event::where(function ($query) use ($employeeId, $managerId) {
            $query->where('manager_id', $employeeId); // Manager sees their own events

            if ($managerId) {
                $query->orWhere('manager_id', $managerId); // Team members see their manager's events
            }
        })->get();

        return response()->json($events);
    }


    // Store event
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required|string',
        ]);

        $event = Event::create([
            'manager_id' => Auth::user()->employee_id, // ✅ correct field name
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'time' => $request->time,
        ]);

        return response()->json($event, 201);
    }
}
