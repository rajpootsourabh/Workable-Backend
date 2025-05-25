<?php

namespace App\Http\Controllers;

use App\Models\CandidateApplicationReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponse;

class CandidateApplicationReviewController extends Controller
{
    use ApiResponse;

    public function addReview(Request $request, $applicationId)
    {
        try {
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'feedback' => 'nullable|string',
            ]);

            $validated['candidate_application_id'] = $applicationId;
            $validated['reviewed_by'] = auth()->id(); // 👈 Automatically get logged-in user ID

            $review = CandidateApplicationReview::create($validated);

            return $this->successResponse($review, 'Review added successfully');
        } catch (\Exception $e) {
            Log::error('Failed to add review', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'application_id' => $applicationId,
                'user_id' => auth()->id(),
            ]);

            return $this->errorResponse('Failed to add review', 500);
        }
    }



    public function getReviews($candidateApplicationId)
    {
        try {
            $reviews = CandidateApplicationReview::with('reviewer.employee')
                ->where('candidate_application_id', $candidateApplicationId)
                ->get()
                ->map(function ($review) {
                    $user = $review->reviewer;
                    $employee = $user?->employee;

                    return [
                        'id' => $review->id,
                        'candidate_application_id' => $review->candidate_application_id,
                        'rating' => $review->rating,
                        'feedback' => $review->feedback,
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at,
                        'reviewed_by_id' => $review->reviewed_by,
                        'reviewer_name' => $employee
                            ? trim($employee->first_name . ' ' . $employee->last_name)
                            : null,
                        'reviewer_profile_pic' => $employee?->profile_image,
                        'reviewer_country' => $employee?->country,
                        'reviewer_address' => $employee?->address,
                    ];
                });

            return $this->successResponse($reviews, 'Reviews fetched successfully');
        } catch (\Exception $e) {
            Log::error('Failed to fetch reviews', [
                'error' => $e->getMessage(),
                'applicationId' => $candidateApplicationId
            ]);

            return $this->errorResponse('Failed to fetch reviews', 500);
        }
    }
}
