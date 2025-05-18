<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplicationLog;
use App\Models\CandidateApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;

class CandidateApplicationLogController extends Controller
{
    use ApiResponse;

    public function logStageChange(Request $request)
    {
        $validated = $request->validate([
            'candidate_application_id' => 'required|exists:candidate_applications,id',
            'changed_by' => 'required|exists:users,id',
            'to_stage' => 'required|exists:stages,id',
            'note' => 'nullable|string',
        ]);

        $application = CandidateApplication::findOrFail($validated['candidate_application_id']);

        if ((int)$application->stage_id === (int)$validated['to_stage']) {
            return $this->errorResponse('The candidate is already in the selected stage.', 422);
        }

        DB::beginTransaction();

        try {
            $fromStage = $application->stage_id;

            // Update stage
            $application->stage_id = $validated['to_stage'];
            $application->save();

            // Log the change
            $log = CandidateApplicationLog::create([
                'candidate_application_id' => $application->id,
                'from_stage' => $fromStage,
                'to_stage' => $validated['to_stage'],
                'changed_by' => $validated['changed_by'],
                'note' => $validated['note'] ?? null,
            ]);

            DB::commit();

            return $this->successResponse($log, 'Stage updated and logged');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update stage', 500, $e->getMessage());
        }
    }


public function getLogs($candidateApplicationId)
{
    try {
        $logs = CandidateApplicationLog::with('changedBy.employee')
            ->where('candidate_application_id', $candidateApplicationId)
            ->get()
            ->map(function ($log) {
                $user = $log->changedBy;
                $employee = $user?->employee;

                $fullName = $employee
                    ? trim($employee->first_name . ' ' . $employee->last_name)
                    : ($user?->name ?? null); // fallback to user name if employee not found

                return [
                    'id' => $log->id,
                    'candidate_application_id' => $log->candidate_application_id,
                    'from_stage' => $log->from_stage,
                    'from_stage_label' => $log->from_stage_label ?? null,
                    'to_stage' => $log->to_stage,
                    'to_stage_label' => $log->to_stage_label ?? null,
                    'changed_by' => $fullName,
                    'changed_by_id' => $log->changed_by,
                    'changed_at' => $log->changed_at,
                    'note' => $log->note,
                    'created_at' => $log->created_at,
                    'updated_at' => $log->updated_at,
                ];
            });

        return $this->successResponse($logs, 'Logs fetched successfully');
    } catch (\Throwable $e) {
        return $this->errorResponse('Error fetching logs', 500, $e->getMessage());
    }
}



}
