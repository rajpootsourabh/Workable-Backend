<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Get all events accessible to employee or manager
    public function index()
    {
        $employeeId = Auth::user()->employee_id;
        $managerId = optional(Auth::user()->employee->jobDetail)->manager_id;

        $now = Carbon::now(); // full datetime now

        $events = Event::whereRaw("CONCAT(date, ' ', time) >= ?", [$now])
            ->where(function ($query) use ($employeeId, $managerId) {
                $query->where('manager_id', $employeeId);

                if ($managerId) {
                    $query->orWhere('manager_id', $managerId);
                }
            })
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        return response()->json($events);
    }

    // Store event

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => ['required', 'date', 'after_or_equal:' . Carbon::today()->toDateString()],
            'time' => 'required|string',
        ]);


        $event = Event::create([
            'manager_id' => Auth::user()->employee_id,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'time' => $request->time,
        ]);

        return response()->json($event, 201);
    }
}
