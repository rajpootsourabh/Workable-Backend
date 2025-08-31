<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class AdminEmployeeController extends Controller
{
    use ApiResponse;

    /**
     * List all employees with their company details.
     */
    public function listAllEmployees(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            $search = $request->input('search');
            $status = $request->input('status'); // "all", "active", "inactive"

            $query = Employee::with(['company', 'user'])
                ->join('companies', 'employees.company_id', '=', 'companies.id')
                ->leftJoin('users', 'users.employee_id', '=', 'employees.id');

            // 🔹 Search filter
            if (!empty($search)) {
                $search = strtolower($search);
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(employees.first_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(employees.last_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(employees.phone) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(employees.work_email) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(companies.name) LIKE ?', ["%{$search}%"]);
                });
            }

            // 🔹 Status filter (mapped to users.is_active)
            if (!empty($status) && strtolower($status) !== 'all') {
                if (strtolower($status) === 'active') {
                    $query->where('users.is_active', 1);
                } elseif (strtolower($status) === 'inactive') {
                    $query->where('users.is_active', 0);
                }
            }

            $employees = $query->orderBy('companies.name')
                ->orderBy('employees.first_name')
                ->paginate($perPage, ['employees.*'], 'page', $page);

            // 🔹 Transform results
            $employees->getCollection()->transform(function ($employee) {
                return [
                    'id'             => $employee->id,
                    'company_id'     => $employee->company_id,
                    'first_name'     => $employee->first_name,
                    'last_name'      => $employee->last_name,
                    'middle_name'    => $employee->middle_name,
                    'preferred_name' => $employee->preferred_name,
                    'country'        => $employee->country,
                    'address'        => $employee->address,
                    'social_media'   => $employee->social_media,
                    'gender'         => $employee->gender,
                    'birthdate'      => $employee->birthdate,
                    'marital_status' => $employee->marital_status,
                    'phone'          => $employee->phone,
                    'work_email'     => $employee->work_email,
                    'personal_email' => $employee->personal_email,
                    'chat_video_call' => $employee->chat_video_call,
                    'profile_image'  => generateFileUrl($employee->profile_image),
                    'created_at'     => $employee->created_at,
                    'updated_at'     => $employee->updated_at,
                    'status'         => $employee->status, // comes from accessor
                    'company_name'   => $employee->company->name ?? null,
                    'website'        => $employee->company->website ?? null,
                ];
            });

            return $this->paginatedResponse($employees, 'Employees fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch employees', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update specific employee fields: name, phone, work_email, and user status
     */
    public function updateEmployee(Request $request, $id)
    {
        try {
            $employee = Employee::with('user')->findOrFail($id);

            // Update only if the request has the fields
            if ($request->has('first_name')) {
                $employee->first_name = $request->input('first_name');
            }

            if ($request->has('last_name')) {
                $employee->last_name = $request->input('last_name');
            }

            if ($request->has('phone')) {
                $employee->phone = $request->input('phone');
            }

            if ($request->has('work_email')) {
                $employee->work_email = $request->input('work_email');

                // Update user's email if exists
                if ($employee->user) {
                    $employee->user->email = $request->input('work_email');
                }
            }

            if ($request->has('status') && $employee->user) {
                // status is 0/1 from frontend
                $employee->user->is_active = $request->input('status') ? 1 : 0;
            }

            $employee->save();

            if ($employee->user) {
                $employee->user->save();
            }

            return $this->successResponse([
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'phone' => $employee->phone,
                'work_email' => $employee->work_email,
                'status' => $employee->user->is_active ?? null,
            ], 'Employee updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update employee', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete an employee by ID.
     */
    public function deleteEmployee($id)
    {
        try {
            // Find the employee
            $employee = Employee::findOrFail($id);

            // Optional: Delete associated user if exists
            $user = User::where('employee_id', $employee->id)->first();
            if ($user) {
                $user->delete();
            }

            // Delete employee
            $employee->delete();

            return $this->successResponse([
                'id' => $id,
            ], 'Employee deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete employee', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
