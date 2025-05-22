<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplication;
use App\Models\Stage;
use Illuminate\Http\Request;

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
        $application->stage_id = $request->input('stage_id');
        $application->save();

        return response()->json([
            'message' => 'Stage updated successfully',
            'application' => $application,
        ]);
    }
}
