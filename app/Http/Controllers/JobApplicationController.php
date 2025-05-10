<?php

namespace App\Http\Controllers;

use App\Http\Resources\JobApplicationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Candidate;
use App\Models\CandidateApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Company;
use App\Traits\ApiResponse;

class JobApplicationController extends Controller
{
    use ApiResponse;
    /**
     * Apply for a job, creating a new candidate and application record.
     */
    public function applyForJob(Request $request)
    {
        // Log::info('Job application attempt started', ['request' => $request->all()]);

        // Fetch the authenticated user's company_id
        $companyId = Auth::user()->company_id;
        // Log::info('Fetched company_id from authenticated user', ['company_id' => $companyId]);

        // Validate combined candidate + job application fields
        Log::info('Validating request data');
        $validated = $request->validate([
            // Candidate fields
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'experience' => 'nullable|numeric|min:0|max:99.9',
            'phone' => 'nullable|string|max:20',
            'location' => 'required|string|max:255',
            'current_ctc' => 'nullable|numeric|min:0',
            'expected_ctc' => 'nullable|numeric|min:0',
            'profile_pic' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'source_id' => 'nullable|integer|exists:sources,id', // Make source_id nullable and check existence
            'company_id' => 'nullable|integer|exists:companies,id', // Make company_id nullable
            'job_id' => 'nullable|exists:job_posts,id', // Make job_id nullable and check existence
            'status' => 'nullable|in:Applied,Screening,Interviewing,Offer,Hired,Rejected',
        ]);

        // Log::info('Request data validated', ['validated' => $validated]);

        // Add company_id if not provided in the request
        $validated['company_id'] = $validated['company_id'] ?? $companyId;
        // Log::info('Company ID added to validated data', ['company_id' => $validated['company_id']]);

        // Handle file uploads and store the file names
        if ($request->hasFile('profile_pic')) {
            // Log::info('Profile pic found, storing file');
            $validated['profile_pic'] = $request->file('profile_pic')->storeAs('candidates/profile_pics', uniqid() . '.' . $request->file('profile_pic')->extension(), 'public');
            // Log::info('Profile pic stored', ['profile_pic' => $validated['profile_pic']]);
        }

        if ($request->hasFile('resume')) {
            // Log::info('Resume found, storing file');
            $validated['resume'] = $request->file('resume')->storeAs('candidates/resumes', uniqid() . '.' . $request->file('resume')->extension(), 'public');
            // Log::info('Resume stored', ['resume' => $validated['resume']]);
        }

        // Create candidate
        // Log::info('Creating candidate record');
        $candidate = Candidate::create($validated);
        // Log::info('Candidate created', ['candidate' => $candidate]);

        // Create application
        // Log::info('Creating application record');
        $application = CandidateApplication::create([
            'candidate_id' => $candidate->id,
            'job_post_id' => $validated['job_id'],
            'status' => $validated['status'] ?? 'Applied',
        ]);
        // Log::info('Application created', ['application' => $application]);

        return $this->successResponse(
            new JobApplicationResource($application),
            'Job application submitted successfully',
            201
        );
    }

    /**
     * Get all job applications with associated candidate and job details.
     */
    public function getApplications()
    {
        $applications = CandidateApplication::with(['candidate', 'jobPost'])->get();

        // Format the data as needed
        $formattedApplications = $applications->map(function ($application) {
            return [
                'id' => $application->id,
                'candidate_id' => $application->candidate_id,
                'job_post_id' => $application->job_post_id,
                'status' => $application->status,
                'applied_at' => $application->created_at->toDateTimeString(),
                'created_at' => $application->created_at->toIso8601String(),
                'updated_at' => $application->updated_at->toIso8601String(),
                'candidate' => [
                    'id' => $application->candidate->id,
                    'company_id' => $application->candidate->company_id,
                    'first_name' => $application->candidate->first_name,
                    'last_name' => $application->candidate->last_name,
                    'designation' => $application->candidate->designation,
                    'experience' => $application->candidate->experience,
                    'phone' => $application->candidate->phone,
                    'location' => $application->candidate->location,
                    'current_ctc' => $application->candidate->current_ctc,
                    'expected_ctc' => $application->candidate->expected_ctc,
                    'profile_pic' => $application->candidate->profile_pic
                        ? url('storage/' . $application->candidate->profile_pic)  // Return the full URL
                        : null,

                    'resume' => $application->candidate->resume,
                    'source_id' => $application->candidate->source_id,
                    'created_at' => $application->candidate->created_at->toIso8601String(),
                    'updated_at' => $application->candidate->updated_at->toIso8601String(),
                ],
                'job_post' => [
                    'id' => $application->jobPost->id,
                    'job_title' => $application->jobPost->job_title,
                    'job_code' => $application->jobPost->job_code,
                    'job_location' => $application->jobPost->job_location,
                    'job_workplace' => $application->jobPost->job_workplace,
                    'office_location' => $application->jobPost->office_location,
                    'description' => $application->jobPost->description,
                    'company_industry' => $application->jobPost->company_industry,
                    'job_function' => $application->jobPost->job_function,
                    'employment_type' => $application->jobPost->employment_type,
                    'experience' => $application->jobPost->experience,
                    'education' => $application->jobPost->education,
                    'keywords' => $application->jobPost->keywords,
                    'job_department' => $application->jobPost->job_department,
                    'from_salary' => $application->jobPost->from_salary,
                    'to_salary' => $application->jobPost->to_salary,
                    'currency' => $application->jobPost->currency,
                    'create_by' => $application->jobPost->create_by,
                    'update_by' => $application->jobPost->update_by,
                    'created_at' => $application->jobPost->created_at->toIso8601String(),
                    'updated_at' => $application->jobPost->updated_at->toIso8601String(),
                ]
            ];
        });

        return $this->successResponse($formattedApplications, 'Job applications fetched successfully');
    }
}
