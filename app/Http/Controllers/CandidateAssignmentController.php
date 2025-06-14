<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class CandidateAssignmentController extends Controller
{
    use ApiResponse;

    /**
     * Assign a candidate to an employee.
     */
    public function assign(Request $request, $candidateId)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $candidate = Candidate::find($candidateId);
        $employee = Employee::find($validated['employee_id']);

        if (!$candidate) {
            return $this->errorResponse('Candidate not found.', 404);
        }

        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        if ($candidate->company_id !== $user->company_id || $employee->company_id !== $user->company_id) {
            return $this->errorResponse('Unauthorized: Candidate or employee does not belong to your company.', 403);
        }

        if ($employee->assignedCandidates()->where('candidate_id', $candidate->id)->exists()) {
            return $this->errorResponse('Candidate already assigned to this employee.', 409);
        }

        $employee->assignedCandidates()->attach($candidate->id, [
            'assigned_by' => $user->id,
            'notes' => $validated['notes'] ?? null,
            'assigned_at' => now(),
        ]);

        return $this->successResponse(null, 'Candidate assigned successfully.', 201);
    }

    /**
     * Retrieve all employees to whom the given candidate has been assigned.
     */
    public function showAssignments($candidateId)
    {
        $user = Auth::user();
        $candidate = Candidate::with('assignedEmployees')->find($candidateId);

        if (!$candidate) {
            return $this->errorResponse('Candidate not found.', 404);
        }

        if ($candidate->company_id !== $user->company_id) {
            return $this->errorResponse('Unauthorized: Candidate does not belong to your company.', 403);
        }

        $employees = $candidate->assignedEmployees->makeHidden('pivot');

        return $this->successResponse($employees);
    }

    /**
     * List all candidates assigned to a specific employee.
     */
    public function getAssignedCandidatesForEmployee($employeeId)
    {
        $user = Auth::user();

        $employee = Employee::with(['assignedCandidates.applications.stage'])->find($employeeId);

        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        if ($employee->company_id !== $user->company_id) {
            return $this->errorResponse('Unauthorized: Employee does not belong to your company.', 403);
        }

        $generateFileUrl = function (?string $filePath) {
            if (!$filePath) {
                return null;
            }
            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            return url('api/v.1/files/' . $encodedPath);
        };

        $candidates = $employee->assignedCandidates->map(function ($candidate) use ($generateFileUrl) {
            $candidate->makeHidden('pivot');
            $candidate->profile_pic = $generateFileUrl($candidate->profile_pic);
            $candidate->resume = $generateFileUrl($candidate->resume);

            $latestApplication = $candidate->applications->sortByDesc('created_at')->first();
            $candidate->stage = $latestApplication?->stage?->name ?? null;

            // 🚫 Remove the full applications relationship from the output
            $candidate->unsetRelation('applications');

            return $candidate;
        });

        return $this->successResponse($candidates);
    }



    /**
     * Remove an assignment.
     */

    public function unassign($candidateId, $employeeId)
    {
        $user = Auth::user();

        $candidate = Candidate::find($candidateId);
        $employee = Employee::find($employeeId);

        if (!$candidate) {
            return $this->errorResponse('Candidate not found.', 404);
        }

        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        if ($candidate->company_id !== $user->company_id || $employee->company_id !== $user->company_id) {
            return $this->errorResponse('Unauthorized: Candidate or employee does not belong to your company.', 403);
        }

        $employee->assignedCandidates()->detach($candidate->id);

        return $this->successResponse(null, 'Assignment removed.');
    }
}
