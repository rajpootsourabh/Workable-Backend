<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplicationCommunication;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class CandidateApplicationCommunicationController extends Controller
{
    use ApiResponse;

    public function sendCommunication(Request $request)
    {
        try {
            $validated = $request->validate([
                'candidate_application_id' => 'required|exists:candidate_applications,id',
                'sent_by' => 'required|exists:users,id',
                'type' => 'required|in:email,sms',
                'subject' => 'nullable|string',
                'message' => 'required|string',
            ]);

            $communication = CandidateApplicationCommunication::create($validated);

            return $this->successResponse($communication, 'Communication sent successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send communication', 500, $e->getMessage());
        }
    }

    public function getCommunications($candidateApplicationId)
    {
        try {
            $communications = CandidateApplicationCommunication::where('candidate_application_id', $candidateApplicationId)->get();

            return $this->successResponse($communications, 'Communications fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch communications', 500, $e->getMessage());
        }
    }
}
