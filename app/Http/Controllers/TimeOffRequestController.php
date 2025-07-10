<?php

namespace App\Http\Controllers;

use App\Helpers\TimeOffHelper;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\TimeOffRequest;
use App\Models\User;
use App\Notifications\TimeOffApproved;
use App\Notifications\TimeOffRequested;
use App\Notifications\TimeOffStatusChanged;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeOffRequestController extends Controller
{

    public function submitTimeOffRequest(Request $request)
    {
        $user = auth()->user();
        $employeeId = $user->employee_id;

        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID not found for authenticated user.'
            ], 403);
        }

        $employee = Employee::with('jobDetail')->find($employeeId);
        $managerId = optional($employee->jobDetail)->manager_id;

        if (!$managerId) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to a manager and cannot apply for time off.'
            ], 403);
        }

        // ✅ Validate with custom error messages
        $validated = $request->validate([
            'time_off_type_id' => 'required|exists:time_off_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'first_day_type' => 'in:full,half',
            'last_day_type' => 'in:full,half',
            'note' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.after_or_equal' => 'End date must be the same as or after the start date.',
        ]);

        // Manual safety check to avoid bypassing
        $start = Carbon::parse($validated['start_date']);
        if ($start->lt(Carbon::today())) {
            return response()->json([
                'success' => false,
                'message' => 'Leave start date cannot be in the past.'
            ], 422);
        }

        // Default to 'full' if not sent
        $firstDayType = $validated['first_day_type'] ?? 'full';
        $lastDayType = $validated['last_day_type'] ?? 'full';

        $end = Carbon::parse($validated['end_date']);

        $hasOverlap = TimeOffRequest::where('employee_id', $employeeId)
            ->overlappingWith($start, $end)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasOverlap) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a time off request overlapping with the selected dates.'
            ], 409);
        }

        $totalDays = TimeOffHelper::calculateTotalDays($start, $end, $firstDayType, $lastDayType);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
            $fileName = uniqid('attachment_') . '.' . $attachment->extension();
            $attachmentPath = $attachment->storeAs('time_off_attachments', $fileName, 'private');
        }

        $requestModel = TimeOffRequest::create([
            'employee_id' => $employeeId,
            'manager_id' => $managerId,
            'time_off_type_id' => $validated['time_off_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'first_day_type' => $firstDayType,
            'last_day_type' => $lastDayType,
            'total_days' => $totalDays,
            'note' => $validated['note'] ?? null,
            'attachment' => $attachmentPath,
            'updated_by' => $employeeId,
            'status' => 'pending',
        ]);

        // Send notification to manager's user account
        $managerUser = User::where('employee_id', $managerId)->first();
        if ($managerUser) {
            $managerUser->notify(new TimeOffRequested($requestModel));
        }


        return response()->json([
            'success' => true,
            'message' => 'Time off request submitted successfully.',
            'data' => $requestModel,
        ], 201);
    }


    public function getById($id)
    {
        $request = TimeOffRequest::with(['employee', 'timeOffType'])->find($id);

        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Time off request not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $request,
        ]);
    }


    public function getByManager(Request $request)
    {
        $managerId = auth()->user()->employee_id;

        if (!$managerId) {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID not found for authenticated manager.'
            ], 403);
        }

        $query = TimeOffRequest::with(['employee.jobDetail', 'timeOffType'])
            ->where('manager_id', $managerId)
            ->orderByDesc('created_at');

        // Filter by type
        if ($request->filled('type_id')) {
            $query->where('time_off_type_id', $request->type_id);
        } elseif ($request->filled('type')) {
            $query->whereHas('timeOffType', function ($q) use ($request) {
                $q->where('name', $request->type);
            });
        }

        // Filter by start_date and end_date
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('start_date', '<=', $request->end_date)
                    ->whereDate('end_date', '>=', $request->start_date);
            });
        }

        $requests = $query->get();

        $grouped = [];

        foreach ($requests as $request) {
            $employee = $request->employee;
            $empId = $employee->id;

            if (!isset($grouped[$empId])) {
                $fullName = trim($employee->first_name . ' ' . $employee->last_name);

                $grouped[$empId] = [
                    'empId' => $empId,
                    'name' => $fullName,
                    'role' => $employee->jobDetail->job_title ?? 'N/A',
                    'avatar' => generateFileUrl($employee->profile_image) ?? null,
                    'leaves' => []
                ];
            }

            $grouped[$empId]['leaves'][] = [
                'id' => $request->id,
                'start' => $request->start_date,
                'end' => $request->end_date,
                'status' => $request->status,
                'created_at' => $request->created_at,
                'label' => $request->timeOffType->name ?? 'Unknown'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => array_values($grouped)
        ]);
    }




    public function getByEmployeeId($employeeId)
    {
        $requests = TimeOffRequest::with(['timeOffType'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user || !$user->employee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: User not linked to an employee profile.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'manager_note' => 'nullable|string',
        ]);

        $timeOff = TimeOffRequest::find($id);

        if (!$timeOff) {
            return response()->json([
                'success' => false,
                'message' => 'Time off request not found.',
            ], 404);
        }

        if ($timeOff->manager_id !== $user->employee_id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the assigned manager can approve or reject this request.',
            ], 403);
        }

        if (in_array($timeOff->status, ['approved', 'rejected', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'This time off request has already been processed.',
            ], 409);
        }

        $timeOff->update([
            'status' => $validated['status'],
            'manager_note' => $validated['manager_note'] ?? null,
            'updated_by' => $user->employee_id,
        ]);

        if ($timeOff->employee && $timeOff->employee->user) {
            $timeOff->employee->user->notify(
                new TimeOffStatusChanged($timeOff, $validated['status'])
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Time off request status updated successfully.',
            'data' => $timeOff,
        ]);
    }



    public function getUpcomingTimeOff()
    {
        $user = auth()->user();
        $employeeId = $user->employee_id;

        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID not found for authenticated user.'
            ], 403);
        }

        $today = now()->toDateString();

        $requests = TimeOffRequest::with(['timeOffType', 'updatedByEmployee']) // include updatedByEmployee
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('end_date', '>=', $today)
            ->orderBy('start_date')
            ->get()
            ->map(function ($req) {
                $modifier = $req->updatedByEmployee;
                $fullName = $modifier ? trim("{$modifier->first_name} {$modifier->last_name}") : 'N/A';

                return [
                    'id' => $req->id,
                    'date' => Carbon::parse($req->start_date)->format('d F Y'),
                    'title' => $req->timeOffType->name,
                    'days' => (int) $req->total_days . ' day' . ($req->total_days > 1 ? 's' : ''),
                    'status' => ucfirst($req->status),
                    'note' => $req->note,
                    'modifiedDate' => Carbon::parse($req->updated_at)->format('d F Y'),
                    'modifiedBy' => $fullName,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }


    public function getAllTimeOff()
    {
        $user = auth()->user();
        $employeeId = $user->employee_id;

        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID not found for authenticated user.'
            ], 403);
        }

        $requests = TimeOffRequest::with(['timeOffType', 'updatedByEmployee'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('start_date')
            ->get()
            ->map(function ($req) {
                $modifier = $req->updatedByEmployee;
                $fullName = $modifier ? trim("{$modifier->first_name} {$modifier->last_name}") : 'N/A';

                return [
                    'id' => $req->id,
                    'date' => Carbon::parse($req->start_date)->format('d F Y'),
                    'title' => $req->timeOffType->name,
                    'days' => $req->total_days . ' day' . ($req->total_days > 1 ? 's' : ''),
                    'status' => ucfirst($req->status),
                    'note' => $req->note,
                    'modifiedDate' => Carbon::parse($req->updated_at)->format('d F Y'),
                    'modifiedBy' => $fullName,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function getLeaveBalance()
    {
        $employeeId = auth()->user()->employee_id;

        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'Employee ID not found.',
            ], 403);
        }

        $year = now()->year;

        $balances = EmployeeLeaveBalance::with('timeOffType')
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->get()
            ->map(function ($balance) {
                $allocated = (float) $balance->allocated_days;
                $used = (float) $balance->used_days;
                $remaining = max(0, $allocated - $used);

                return [
                    'id' => $balance->id,
                    'type' => $balance->timeOffType->name ?? 'N/A',
                    'allocated_days' => $allocated,
                    'used_days' => $used,
                    'remaining_days' => $remaining,
                    'carried_forward' => (float) ($balance->carried_forward ?? 0),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $balances,
        ]);
    }

    public function updateLeaveBalanceById(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: User not authenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'allocated_days' => 'required|numeric|min:0|max:999.99',
        ]);

        $leaveBalance = EmployeeLeaveBalance::find($id);

        if (!$leaveBalance) {
            return response()->json([
                'success' => false,
                'message' => 'Leave balance not found.',
            ], 404);
        }

        $targetEmployeeId = $leaveBalance->employee_id;
        $adminRoles = [1, 2, 3, 4];

        // ✅ Admins (roles 1-4) can update any leave balance
        if (in_array($user->role, $adminRoles)) {
            $leaveBalance->allocated_days = $validated['allocated_days'];
            $leaveBalance->updated_at = now();
            $leaveBalance->save();

            return response()->json([
                'success' => true,
                'message' => 'Leave balance updated successfully.',
                'data' => [
                    'id' => $leaveBalance->id,
                    'allocated_days' => $leaveBalance->allocated_days,
                ],
            ]);
        }

        // ✅ Employees with role=5 (only if manager) can update self or subordinates
        if ($user->role == 5 && $user->employee_id) {
            $managerId = $user->employee_id;

            // Check if this employee is a manager (has subordinates)
            $isManager = \App\Models\JobDetail::where('manager_id', $managerId)->exists();

            if (!$isManager) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Only managers can update leave balances.',
                ], 403);
            }

            // Allow if updating own or subordinate's balance
            $isSelf = $managerId == $targetEmployeeId;
            $isSubordinate = \App\Models\JobDetail::where('manager_id', $managerId)
                ->where('employee_id', $targetEmployeeId)
                ->exists();

            if ($isSelf || $isSubordinate) {
                $leaveBalance->allocated_days = $validated['allocated_days'];
                $leaveBalance->updated_at = now();
                $leaveBalance->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Leave balance updated successfully.',
                    'data' => [
                        'id' => $leaveBalance->id,
                        'allocated_days' => $leaveBalance->allocated_days,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You can only update your own or your associates\' leave balances.',
            ], 403);
        }

        // ❌ Everyone else denied
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: You do not have permission to update this leave balance.',
        ], 403);
    }
}
