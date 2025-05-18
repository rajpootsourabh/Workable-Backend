<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplicationComment;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class CandidateApplicationCommentController extends Controller
{
    use ApiResponse;

    public function addComment(Request $request, $candidateApplicationId)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        // Get authenticated user ID as the commenter
        $validated['commented_by'] = auth()->id();
        $validated['candidate_application_id'] = $candidateApplicationId;

        try {
            $comment = CandidateApplicationComment::create($validated);
            return $this->successResponse($comment, 'Comment added successfully', 201);
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
