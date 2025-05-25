<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplication;
use App\Models\CandidateApplicationCommunication;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Mail;

class CandidateApplicationCommunicationController extends Controller
{
    use ApiResponse;

    public function sendCommunication(Request $request)
    {
        try {
            // Step 1: Validate request
            $validated = $request->validate([
                'candidate_application_id' => 'required|exists:candidate_applications,id',
                'type' => 'required|in:email,sms',
                'subject' => 'nullable|string',
                'message' => 'required|string',
            ]);

            // Step 2: Fetch candidate email via relationship
            $application = CandidateApplication::with('candidate')->findOrFail($validated['candidate_application_id']);
            $toEmail = $application->candidate->email;

            if (!$toEmail) {
                return $this->errorResponse('Candidate email not found', 422);
            }

            // Step 3: Prepare communication data
            $communicationData = [
                'candidate_application_id' => $validated['candidate_application_id'],
                'sent_by' => auth()->id(),
                'type' => $validated['type'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
            ];

            // Step 4: Store record in DB
            $communication = CandidateApplicationCommunication::create($communicationData);

            // Step 5: Only send email *after* DB save
            if ($communication && $validated['type'] === 'email') {
                Mail::raw($validated['message'], function ($message) use ($toEmail, $validated) {
                    $message->to($toEmail)
                        ->subject($validated['subject'] ?? '(No Subject)');
                });
            }

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
