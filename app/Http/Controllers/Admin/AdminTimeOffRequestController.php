<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TimeOffRequest;
use App\Traits\ApiResponse;

class AdminTimeOffRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->query('per_page', 10);
            $page = (int) $request->query('page', 1);

            $search = $request->query('search', null);
            $leaveType = $request->query('leave_type', 'all');
            $leaveStatus = $request->query('status', 'all');
            $sortOrder = $request->query('sort', 'desc'); // optional: asc/desc on request_date

            $query = TimeOffRequest::with([
                'employee:id,first_name,last_name,company_id',
                'employee.company:id,name',
                'timeOffType:id,name'
            ]);

            // 🔹 Search filter (employee name or company)
            if (!empty($search)) {
                $search = strtolower($search);
                $query->where(function ($q) use ($search) {
                    $q->whereHas('employee', function ($e) use ($search) {
                        $e->whereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ["%{$search}%"])
                            ->orWhereHas('company', function ($c) use ($search) {
                                $c->whereRaw("LOWER(name) LIKE ?", ["%{$search}%"]);
                            });
                    });
                });
            }

            // 🔹 Leave type filter (case-insensitive)
            if ($leaveType !== 'all') {
                $query->whereHas('timeOffType', function ($q) use ($leaveType) {
                    $q->whereRaw('LOWER(name) = ?', [strtolower($leaveType)]);
                });
            }

            // 🔹 Status filter (case-insensitive)
            if ($leaveStatus !== 'all') {
                $query->whereRaw('LOWER(status) = ?', [strtolower($leaveStatus)]);
            }

            // 🔹 Sort by request date
            $query->orderBy('created_at', $sortOrder);

            $timeOffRequests = $query->paginate($perPage, ['*'], 'page', $page);

            // Map to simplified structure
            $timeOffRequests->getCollection()->transform(function ($request) {
                return [
                    'id' => $request->id,
                    'employee_name' => $request->employee->first_name . ' ' . $request->employee->last_name,
                    'company_name' => $request->employee->company->name ?? null,
                    'leave_type' => $request->timeOffType->name ?? null,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'total_days' => $request->total_days,
                    'status' => $request->status,
                    'request_date' => $request->created_at->format('Y-m-d'),
                ];
            });

            $timeOffRequests->setCollection($timeOffRequests->getCollection());

            return $this->paginatedResponse($timeOffRequests, 'Time-off requests retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch time-off requests', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
