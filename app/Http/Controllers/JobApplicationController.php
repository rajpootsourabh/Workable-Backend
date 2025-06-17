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
use App\Models\JobPost;
use App\Models\Stage;
use App\Traits\ApiResponse;

class JobApplicationController extends Controller
{
    use ApiResponse;
    /**
     * Apply for a job, creating a new candidate and application record.
     */
    public function applyForJob(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->company_id) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized or invalid company."
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            // Candidate fields
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'experience' => 'required|numeric|min:0|max:99.9',
            'education' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:candidates,email',
            'country' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'current_ctc' => 'nullable|numeric|min:0',
            'expected_ctc' => 'required|numeric|min:0',
            'profile_pic' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'source_id' => 'nullable|integer|exists:sources,id',
            'company_id' => 'nullable|integer|exists:companies,id',
            'job_id' => 'required|exists:job_posts,id',
            'status' => 'nullable|in:Active,Rejected',
        ]);

        // Ensure the job post belongs to the same company
        $job = JobPost::find($validated['job_id']);
        if (!$job || $job->company_id !== $user->company_id) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized: You cannot apply for jobs outside your company."
            ], 403);
        }

        // Enforce company_id
        $validated['company_id'] = $user->company_id;

        // Handle profile pic
        if ($request->hasFile('profile_pic')) {
            $validated['profile_pic'] = $request->file('profile_pic')->storeAs(
                'candidates/profile_pics',
                uniqid() . '.' . $request->file('profile_pic')->extension(),
                'private'
            );
        }

        // Handle resume
        if ($request->hasFile('resume')) {
            $validated['resume'] = $request->file('resume')->storeAs(
                'candidates/resumes',
                uniqid() . '.' . $request->file('resume')->extension(),
                'private'
            );
        }

        // Create candidate
        $candidate = Candidate::create($validated);

        // Create application
        $application = CandidateApplication::create([
            'candidate_id' => $candidate->id,
            'job_post_id' => $validated['job_id'],
            // 'status' => $validated['status'] ?? 'Active',
        ]);

        return $this->successResponse(
            new JobApplicationResource($application),
            'Job application submitted successfully',
            201
        );
    }

    public function updateCandidateApplication(Request $request, $applicationId)
    {
        // Find the application with candidate
        $application = CandidateApplication::with('candidate')->findOrFail($applicationId);
        $candidate = $application->candidate;

        // Validate incoming fields - all nullable for partial update
        $validated = $request->validate([
            // Candidate fields (all nullable)
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'designation' => 'sometimes|nullable|string|max:255',
            'experience' => 'sometimes|nullable|numeric|min:0|max:99.9',
            'phone' => 'sometimes|nullable|string|max:20',
            'email'         => 'sometimes|email|unique:candidates,email,' . $candidate->id,
            'country'       => 'sometimes|string|max:100',
            'location' => 'sometimes|string|max:255',
            'current_ctc' => 'sometimes|nullable|numeric|min:0',
            'expected_ctc' => 'sometimes|nullable|numeric|min:0',
            'profile_pic' => 'sometimes|nullable|file|mimes:jpg,jpeg,png|max:2048',
            'resume' => 'sometimes|nullable|file|mimes:pdf,doc,docx|max:5120',
            'source_id' => 'sometimes|nullable|integer|exists:sources,id',
            'company_id' => 'sometimes|nullable|integer|exists:companies,id',

            // Application fields
            'status' => 'sometimes|in:Applied,Screening,Interviewing,Offer,Hired,Rejected',
        ]);

        // Update candidate fields if provided
        $candidate = $application->candidate;

        $candidateFields = [
            'first_name',
            'last_name',
            'designation',
            'experience',
            'phone',
            'location',
            'current_ctc',
            'expected_ctc',
            'source_id',
            'company_id'
        ];

        foreach ($candidateFields as $field) {
            if ($request->has($field)) {
                $candidate->$field = $validated[$field];
            }
        }

        // Handle profile_pic upload
        if ($request->hasFile('profile_pic')) {
            // Optionally delete old file here before storing new
            $candidate->profile_pic = $request->file('profile_pic')->storeAs(
                'candidates/profile_pics',
                uniqid() . '.' . $request->file('profile_pic')->extension(),
                'private'
            );
        }

        // Handle resume upload
        if ($request->hasFile('resume')) {
            // Optionally delete old file here before storing new
            $candidate->resume = $request->file('resume')->storeAs(
                'candidates/resumes',
                uniqid() . '.' . $request->file('resume')->extension(),
                'private'
            );
        }

        $candidate->save();

        // Update application fields if provided
        if ($request->has('status')) {
            $application->status = $validated['status'];
        }

        $application->save();

        return $this->successResponse(
            new JobApplicationResource($application->fresh()), // fresh to reload updated data
            'Application updated successfully'
        );
    }


    /**
     * Get all job applications with associated candidate and job details.
     */
    public function getApplications()
    {
        $user = Auth::user();

        if (!$user || !$user->company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized or company not set'], 403);
        }

        // Start query
        $applicationsQuery = CandidateApplication::with(['candidate', 'jobPost'])
            ->whereHas('candidate', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            });

        // If role is 5, only show applications for candidates assigned to this employee
        if ($user->role == 5) {
            if (!$user->employee_id) {
                return response()->json(['status' => 'error', 'message' => 'No employee profile linked to user'], 403);
            }

            // Filter only candidates assigned to this employee
            $applicationsQuery->whereHas('candidate', function ($query) use ($user) {
                $query->whereIn('id', function ($subQuery) use ($user) {
                    $subQuery->select('candidate_id')
                        ->from('candidate_employee_assignments')
                        ->where('employee_id', $user->employee_id);
                });
            });
        }

        // Execute query
        $applications = $applicationsQuery->get();

        // Reusable closure for file URL generation
        $generateFileUrl = function (?string $filePath) {
            if (!$filePath) {
                return null;
            }

            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));

            return url('api/v.1/files/' . $encodedPath);
        };

        // Format response
        $formattedApplications = $applications->map(function ($application) use ($generateFileUrl) {
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
                    'email' => $application->candidate->email,
                    'country' => $application->candidate->country,
                    'location' => $application->candidate->location,
                    'current_ctc' => $application->candidate->current_ctc,
                    'expected_ctc' => $application->candidate->expected_ctc,
                    'profile_pic' => $generateFileUrl($application->candidate->profile_pic),
                    'resume' => $generateFileUrl($application->candidate->resume),
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



    public function getApplicationById($applicationId)
    {
        $user = Auth::user();

        if (!$user || !$user->company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized or company not set'], 403);
        }

        $application = CandidateApplication::with(['candidate', 'jobPost.company'])->find($applicationId);

        if (!$application) {
            return response()->json(['status' => 'error', 'message' => 'Application not found'], 404);
        }

        // Check if candidate's company matches the authenticated user's company
        if ($application->candidate->company_id !== $user->company_id) {
            return response()->json(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $candidate = $application->candidate;

        // Helper to generate file URLs
        $generateFileUrl = function (?string $filePath) {
            if (!$filePath) return null;

            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            return url('api/v.1/files/' . $encodedPath);
        };

        $formattedApplication = [
            'id' => $application->id,
            'candidate_id' => $application->candidate_id,
            'job_post_id' => $application->job_post_id,
            'status' => $application->status,
            'current_stage' => $application->stage_id,
            'applied_at' => $application->created_at->toDateTimeString(),
            'created_at' => $application->created_at->toIso8601String(),
            'updated_at' => $application->updated_at->toIso8601String(),
            'candidate' => [
                'id' => $candidate->id,
                'company_id' => $candidate->company_id,
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'designation' => $candidate->designation,
                'experience' => $candidate->experience,
                'phone' => $candidate->phone,
                'email' => $candidate->email,
                'country' => $candidate->country,
                'location' => $candidate->location,
                'current_ctc' => $candidate->current_ctc,
                'expected_ctc' => $candidate->expected_ctc,
                'profile_pic' => $generateFileUrl($candidate->profile_pic),
                'resume' => $generateFileUrl($candidate->resume),
                'source_id' => $candidate->source_id,
                'created_at' => $candidate->created_at->toIso8601String(),
                'updated_at' => $candidate->updated_at->toIso8601String(),
            ],
            'job_post' => [
                'id' => $application->jobPost->id,
                'job_title' => $application->jobPost->job_title,
                'company_name' => optional($application->jobPost->company)->name,
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
                'benefits' => $application->jobPost->benefits,
                'requirements' => $application->jobPost->requirements,
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

        return $this->successResponse($formattedApplication, 'Application fetched successfully');
    }



    public function disqualify(Request $request, $applicationId)
    {
        $request->validate([
            'note' => 'nullable|string',
        ]);

        $application = CandidateApplication::findOrFail($applicationId);

        // Avoid duplicate disqualification
        if ($application->status === 'Rejected') {
            return response()->json([
                'message' => 'Candidate is already disqualified.',
            ], 400);
        }

        $fromStage = $application->stage_id;

        // Update application status
        $application->status = 'Rejected';
        $application->save();

        // Log the disqualification
        // CandidateApplicationLog::create([
        //     'candidate_application_id' => $application->id,
        //     'from_stage' => $fromStage,
        //     'to_stage' => $fromStage, // stage doesn't change, only status
        //     'changed_by' => Auth::id(),
        //     'changed_at' => now(),
        //     'note' => $request->input('note', 'Disqualified'),
        // ]);

        return response()->json([
            'message' => 'Candidate disqualified successfully.',
            'application' => $application,
        ]);
    }

    // public function moveToNextStage($applicationId)
    // {
    //     $application = CandidateApplication::findOrFail($applicationId);
    //     $currentStage = Stage::find($application->stage_id);

    //     $nextStage = Stage::where('id', '>', $currentStage->id)->orderBy('id')->first();

    //     if ($nextStage) {
    //         $application->stage_id = $nextStage->id;
    //         $application->save();

    //         return response()->json([
    //             'message' => 'Moved to next stage',
    //             'new_stage' => $nextStage->name,
    //         ]);
    //     }

    //     return response()->json(['message' => 'Already at final stage'], 400);
    // }


    // public function setStage(Request $request, $applicationId)
    // {
    //     $stageId = $request->input('stage_id');

    //     if (!Stage::find($stageId)) {
    //         return response()->json(['error' => 'Invalid stage ID'], 400);
    //     }

    //     $application = CandidateApplication::findOrFail($applicationId);
    //     $application->stage_id = $stageId;
    //     $application->save();

    //     return response()->json([
    //         'message' => 'Stage updated successfully',
    //         'application' => $application,
    //     ]);
    // }

    public function getApplicationsForJob(Request $request, $jobPostId)
    {
        $user = Auth::user();

        // Restrict access to only jobs belonging to the user's company
        $job = JobPost::findOrFail($jobPostId);
        if ($job->company_id !== $user->company_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to this job\'s applications.'
            ], 403);
        }

        $query = CandidateApplication::with(['candidate'])
            ->where('job_post_id', $jobPostId)
            ->where('status', 'Active');

        if ($request->filled('location')) {
            $query->whereHas('candidate', fn($q) => $q->where('location', 'like', '%' . $request->location . '%'));
        }

        if ($request->filled('experience_min')) {
            $query->whereHas('candidate', fn($q) => $q->where('experience', '>=', $request->experience_min));
        }

        if ($request->filled('experience_max')) {
            $query->whereHas('candidate', fn($q) => $q->where('experience', '<=', $request->experience_max));
        }

        if ($request->filled('search')) {
            $query->whereHas('candidate', function ($q) use ($request) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->search . '%'])
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('designation', 'like', '%' . $request->search . '%');
            });
        }

        $page = max(1, (int) $request->get('page', 1));
        $applications = $query->latest()->paginate(10, ['*'], 'page', $page);

        if ($page > $applications->lastPage()) {
            return response()->json([
                'job' => $job,
                'job_applications' => [],
                'candidates' => [],
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                ],
            ]);
        }

        $generateFileUrl = function (?string $filePath) {
            if (!$filePath) return null;
            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            return url('api/v.1/files/' . $encodedPath);
        };

        $job_application = collect($applications->items())->map(fn($app) => [
            'id' => $app->id,
            'applied_at' => $app->applied_at,
            'status' => $app->status,
            'stage_id' => $app->stage_id,
        ]);

        $candidates = collect($applications->items())->map(function ($app) use ($generateFileUrl) {
            $candidate = $app->candidate;
            return [
                'id' => $candidate->id,
                'company_id' => $candidate->company_id,
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'designation' => $candidate->designation,
                'experience' => $candidate->experience,
                'education' => $candidate->education,
                'phone' => $candidate->phone,
                'email' => $candidate->email,
                'country' => $candidate->country,
                'location' => $candidate->location,
                'current_ctc' => $candidate->current_ctc,
                'expected_ctc' => $candidate->expected_ctc,
                'profile_pic' => $generateFileUrl($candidate->profile_pic),
                'resume' => $generateFileUrl($candidate->resume),
                'source_id' => $candidate->source_id,
                'created_at' => $candidate->created_at->toIso8601String(),
                'updated_at' => $candidate->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'job' => $job,
            'job_application' => $job_application,
            'candidates' => $candidates,
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }
}
