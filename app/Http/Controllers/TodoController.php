<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TodoController extends Controller
{
    // Get logged-in employee's todos
    public function index()
    {
        $today = Carbon::today();

        $todos = Todo::where('employee_id', Auth::user()->employee_id)
            ->whereDate('created_at', $today)
            ->get();

        return response()->json($todos);
    }

    // Create new todo
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $todo = Todo::create([
            'employee_id' => Auth::user()->employee_id,
            'title' => $request->title,
            'is_done' => false,
        ]);

        return response()->json($todo, 201);
    }

    // Toggle done status
    public function update(Request $request, $id)
    {
        $todo = Todo::where('employee_id', Auth::user()->employee_id)
            ->whereDate('created_at', Carbon::today())
            ->findOrFail($id);

        $todo->is_done = !$todo->is_done;
        $todo->save();

        return response()->json($todo);
    }

    // Delete a todo
    public function destroy($id)
    {
        $todo = Todo::where('employee_id', Auth::user()->employee_id)
            ->whereDate('created_at', Carbon::today())
            ->findOrFail($id);

        $todo->delete();

        return response()->json(['message' => 'Todo deleted']);
    }
}
