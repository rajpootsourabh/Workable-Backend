<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobPost;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class JobPostController extends Controller
{

    /**
     * List all jobs.
     */
    public function listJobs()
    {
        $user = Auth::user();

        // Check if the authenticated user has a company_id
        if (!$user || !$user->company_id) {
            return response()->json([
                "status" => "error",
                "message" => "User must belong to a company to view job listings."
            ], 403);
        }

        // Fetch only jobs belonging to the user's company
        $jobs = JobPost::where('company_id', $user->company_id)->get();

        return response()->json([
            "status" => "success",
            "data" => $jobs
        ]);
    }

    /**
     * Get a single job by ID.
     */
    public function getJob($id)
    {
        $user = Auth::user();

        // Check if the authenticated user has a company_id
        if (!$user || !$user->company_id) {
            return response()->json([
                "status" => "error",
                "message" => "User must belong to a company to view the job."
            ], 403);
        }

        // Find the job and ensure it belongs to the user's company
        $job = JobPost::with('company:id,name')
            ->where('company_id', $user->company_id)
            ->find($id);

        if (!$job) {
            return response()->json([
                "status" => "error",
                "message" => "Job not found or does not belong to your company."
            ], 404);
        }

        // Prepare response
        $data = $job->toArray();
        $data['company_name'] = $data['company']['name'] ?? null;
        unset($data['company']); // Optional

        return response()->json([
            "status" => "success",
            "data" => $data
        ]);
    }

    /**
     * Create a new job.
     */
    public function createJob(Request $request)
    {
        $user = Auth::user();

        // Check if the authenticated user has a company_id
        if (!$user || !$user->company_id) {
            return response()->json([
                "status" => "error",
                "message" => "User must belong to a company to create a job."
            ], 403);
        }

        $validatedData = $request->validate([
            'job_title'       => 'required|string|max:255',
            'job_code'        => 'required|string|max:255|unique:job_posts,job_code',
            'job_workplace'   => ['required', Rule::in(['onsite', 'hybrid', 'remote'])],
            'job_location'    => 'required|string|max:255',
            'job_department'  => 'required|string|max:255',
            'job_function'    => 'required|string|max:255',
            'job_description' => 'required|string|max:255',
            'job_requirements' => 'nullable|string',
            'job_benefits'    => 'nullable|string',
            'employment_type' => 'required|string|max:255',
            'experience'      => 'required|string|max:255',
            'education'       => 'required|string|max:255',
            'keywords'        => 'required|array|min:1',
            'keywords.*'      => 'string|max:50',
            'annual_salary_from' => 'required|numeric|min:0',
            'annual_salary_to'   => 'required|numeric|gte:annual_salary_from',
            'currency'        => 'required|string|max:10',
        ]);

        JobPost::create([
            'company_id'       => $user->company_id, // Set from Authenticated User
            'job_title'        => $validatedData['job_title'],
            'job_code'         => $validatedData['job_code'],
            'job_workplace'    => $validatedData['job_workplace'],
            'job_location'     => $validatedData['job_location'],
            'job_department'   => $validatedData['job_department'],
            'requirements'     => $validatedData['job_requirements'],
            'benefits'         => $validatedData['job_benefits'],
            'job_function'     => $validatedData['job_function'],
            'description'     => $validatedData['job_description'],
            'employment_type'  => $validatedData['employment_type'],
            'experience'       => $validatedData['experience'],
            'education'        => $validatedData['education'],
            'keywords'         => implode(',', $validatedData['keywords']),
            'from_salary'      => $validatedData['annual_salary_from'],
            'to_salary'        => $validatedData['annual_salary_to'],
            'currency'         => $validatedData['currency'],
            'create_by'        =>  Auth::id()
        ]);

        return response()->json(["status" => "success", 'message' => 'Job created successfully'], 201);
    }

    /**
     * Update an existing job.
     */
    public function updateJob(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || !$user->company_id) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized or invalid company."
            ], 403);
        }

        $job = JobPost::find($id);
        if (!$job) {
            return response()->json(["status" => "error", 'message' => 'Job not found'], 404);
        }

        // Check if the job belongs to the same company
        if ($job->company_id !== $user->company_id) {
            return response()->json(["status" => "error", 'message' => 'You do not have permission to update this job'], 403);
        }

        $validatedData = $request->validate([
            'job_title'       => 'sometimes|string|max:255',
            'job_code'        => 'sometimes|string|max:255|unique:job_posts,job_code,' . $id,
            'job_workplace'   => ['sometimes', Rule::in(['onsite', 'hybrid', 'remote'])],
            'job_location'    => 'sometimes|string|max:255',
            'job_department'  => 'sometimes|string|max:255',
            'job_description' => 'sometimes|string|max:255',
            'job_function'    => 'sometimes|string|max:255',
            'job_requirements'    => 'sometimes|string|max:255',
            'job_benefits'    => 'sometimes|string|max:255',
            'employment_type' => 'sometimes|string|max:255',
            'experience'      => 'sometimes|string|max:255',
            'education'       => 'sometimes|string|max:255',
            'keywords'        => 'sometimes|array|min:1',
            'keywords.*'      => 'string|max:50',
            'annual_salary_from' => 'sometimes|numeric|min:0',
            'annual_salary_to'   => 'sometimes|numeric|gte:annual_salary_from',
            'currency'        => 'sometimes|string|max:10',
        ]);

        $job->update([
            'company_id'       => $user->company_id, // Overwrite with authenticated user's company
            'job_title'        => $validatedData['job_title'] ?? $job->job_title,
            'job_code'         => $validatedData['job_code'] ?? $job->job_code,
            'job_workplace'    => $validatedData['job_workplace'] ?? $job->job_workplace,
            'job_location'     => $validatedData['job_location'] ?? $job->job_location,
            'job_department'   => $validatedData['job_department'] ?? $job->job_department,
            'description'      => $validatedData['job_description'] ?? $job->description,
            'requirements' => $validatedData['job_requirements'] ?? $job->requirements,
            'benefits' => $validatedData['job_benefits'] ?? $job->benefits,
            'job_function'     => $validatedData['job_function'] ?? $job->job_function,
            'employment_type'  => $validatedData['employment_type'] ?? $job->employment_type,
            'experience'       => $validatedData['experience'] ?? $job->experience,
            'education'        => $validatedData['education'] ?? $job->education,
            'keywords'         => isset($validatedData['keywords']) ? implode(',', $validatedData['keywords']) : $job->keywords,
            'from_salary'      => $validatedData['annual_salary_from'] ?? $job->from_salary,
            'to_salary'        => $validatedData['annual_salary_to'] ?? $job->to_salary,
            'currency'         => $validatedData['currency'] ?? $job->currency,
            'update_by'        => Auth::id()
        ]);

        return response()->json(["status" => "success", 'message' => 'Job updated successfully']);
    }

    /**
     * Delete a job.
     */
    public function deleteJob($id)
    {
        $user = Auth::user();

        if (!$user || !$user->company_id) {
            return response()->json([
                "status" => "error",
                "message" => "Unauthorized or invalid company."
            ], 403);
        }

        $job = JobPost::find($id);

        if (!$job) {
            return response()->json(["status" => "error", "message" => "Job not found"], 404);
        }

        // 🔐 Ensure the job belongs to the authenticated user's company
        if ($job->company_id !== $user->company_id) {
            return response()->json(["status" => "error", "message" => "You do not have permission to delete this job"], 403);
        }

        $job->delete();

        return response()->json(["status" => "success", "message" => "Job deleted successfully"]);
    }
}
