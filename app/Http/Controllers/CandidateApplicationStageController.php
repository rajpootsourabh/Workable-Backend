<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplication;
use App\Models\CandidateApplicationLog;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateApplicationStageController extends Controller
{
    public function moveToNextStage($applicationId)
    {
        $application = CandidateApplication::findOrFail($applicationId);
        $currentStageId = $application->stage_id;
        $nextStage = Stage::where('id', '>', $currentStageId)->orderBy('id')->first();

        if ($nextStage) {
            $application->stage_id = $nextStage->id;
            $application->save();

            return response()->json([
                'message' => 'Moved to next stage',
                'new_stage' => $nextStage->name,
            ]);
        }

        return response()->json(['message' => 'Already at final stage'], 400);
    }

    public function setStage(Request $request, $applicationId)
    {
        $request->validate([
            'stage_id' => 'required|exists:stages,id',
        ]);

        $application = CandidateApplication::findOrFail($applicationId);

        $fromStage = $application->stage_id; // Store the current stage
        $toStage = $request->input('stage_id');

        // Only log if stage actually changed
        if ($fromStage != $toStage) {
            // Update the application stage
            $application->stage_id = $toStage;
            $application->save();

            // Log the change
            CandidateApplicationLog::create([
                'candidate_application_id' => $application->id,
                'from_stage' => $fromStage,
                'to_stage' => $toStage,
                'changed_by' => Auth::id(), // Ensure user is authenticated
                'changed_at' => now(),
                'note' => $request->input('note'), // optional, if you add a note field
            ]);
        }

        return response()->json([
            'message' => 'Stage updated successfully',
            'application' => $application,
        ]);
    }
}
