<?php

namespace App\Http\Controllers;

use App\Mail\CommunicationMail;
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
                Mail::to($toEmail)->send(new CommunicationMail($communicationData));
            }

            return $this->successResponse($communication, 'Communication sent successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send communication', 500, $e->getMessage());
        }
    }


    public function getCommunications($candidateApplicationId)
    {
        try {
            $communications = CandidateApplicationCommunication::with([
                'sender:id,first_name,last_name,employee_id,profile_image',
                'sender.employee:id,first_name,last_name,profile_image'
            ])
                ->where('candidate_application_id', $candidateApplicationId)
                ->get()
                ->map(function ($comm) {
                    $user = $comm->sender;
                    $employee = $user?->employee;

                    $firstName = $user?->first_name ?? $employee?->first_name ?? 'User';
                    $lastName  = $user?->last_name ?? $employee?->last_name ?? '';
                    $profile   = $user?->profile_image ?? $employee?->profile_image;

                    return [
                        'id' => $comm->id,
                        'candidate_application_id' => $comm->candidate_application_id,
                        'sent_by' => $comm->sent_by,
                        'type' => $comm->type,
                        'subject' => $comm->subject,
                        'message' => $comm->message,
                        'sent_at' => $comm->sent_at,
                        'created_at' => $comm->created_at,
                        'updated_at' => $comm->updated_at,

                        // flattened fields
                        'sender_name' => trim($firstName . ' ' . $lastName),
                        'sender_profile_image' => $profile ? generateFileUrl($profile) : null,
                    ];
                });

            return $this->successResponse($communications, 'Communications fetched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch communications', 500, $e->getMessage());
        }
    }
}
