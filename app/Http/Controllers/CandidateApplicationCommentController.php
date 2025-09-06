<?php

namespace App\Http\Controllers;

use App\Mail\NewCommentNotificationMail;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use App\Models\CandidateApplicationComment;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Mail;

class CandidateApplicationCommentController extends Controller
{
    use ApiResponse;

    public function addComment(Request $request, $candidateApplicationId)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $validated['commented_by'] = auth()->id();
        $validated['candidate_application_id'] = $candidateApplicationId;

        try {
            $comment = CandidateApplicationComment::create($validated);

            // Get the candidate ID from candidate_applications
            $application = CandidateApplication::findOrFail($candidateApplicationId);
            $candidate = Candidate::findOrFail($application->candidate_id);

            // Temporary fallback email
            $candidateEmail = 'sourabh.testenvironment@gmail.com';

            // Get full name of the commenter (employee of the user)
            $employee = auth()->user()->employee;
            $commenterName = $employee
                ? trim($employee->first_name . ' ' . $employee->last_name)
                : 'Recruiter';

            // Email data
            $data = [
                'candidate_name' => $candidate->first_name . ' ' . $candidate->last_name,
                'commenter_name' => $commenterName,
                'comment_text' => $validated['comment'],
                'login_link' => "https://hustoro.com/signin",
                'sender_name' => auth()->user()->name ?? 'Recruitment Team',
                'company_name' => 'Your Company Name',
            ];

            // Send email
            Mail::to($candidateEmail)->send(new NewCommentNotificationMail($data));

            return $this->successResponse($comment, 'Comment added and email sent.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add comment', 500, ['exception' => $e->getMessage()]);
        }
    }


    public function listComments($candidateApplicationId)
    {
        $comments = CandidateApplicationComment::with('commenter.employee')
            ->where('candidate_application_id', $candidateApplicationId)
            ->get()
            ->map(function ($comment) {
                $user = $comment->commenter;
                $employee = $user?->employee;

                return [
                    'id' => $comment->id,
                    'candidate_application_id' => $comment->candidate_application_id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,

                    // User info
                    'commented_by_id' => $comment->commented_by,
                    'commented_by_email' => $user?->email,

                    // Employee info
                    'commented_by_name' => $employee
                        ? trim($employee->first_name . ' ' . $employee->last_name)
                        : null,
                    'commented_by_profile_pic' => $employee?->profile_image,
                    'commented_by_country' => $employee?->country,
                    'commented_by_address' => $employee?->address,
                ];
            });

        if ($comments->isEmpty()) {
            return $this->errorResponse('No comments found for this application.', 404);
        }

        return $this->successResponse($comments, 'Comments retrieved successfully');
    }
}
