<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobPost;
use App\Traits\ApiResponse; // Import the trait
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminJobController extends Controller
{
    use ApiResponse; // Use the trait

    /**
     * List all jobs for admin with pagination.
     */
    public function listAllJobs(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $page = (int) $request->get('page', 1);

            $search = $request->get('search'); // search by company or job title
            $workplaceType = $request->get('workplace', 'all');
            $employmentType = $request->get('employment', 'all');
            $salaryRange = $request->get('salary', 'all');

            $query = JobPost::with('company');

            // 🔹 Search filter (company or job title)
            if (!empty($search)) {
                $search = strtolower($search);
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(job_title) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('company', function ($c) use ($search) {
                            $c->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                        });
                });
            }

            // 🔹 Workplace type filter
            if ($workplaceType !== 'all') {
                $query->where('job_workplace', $workplaceType);
            }

            // 🔹 Employment type filter
            if ($employmentType !== 'all') {
                $query->whereRaw('LOWER(employment_type) = ?', [strtolower($employmentType)]);
            }


            // 🔹 Salary range filter
            if ($salaryRange !== 'all') {
                if (strpos($salaryRange, '-') !== false) {
                    [$min, $max] = explode('-', $salaryRange);
                    $query->whereBetween('from_salary', [(int)$min, (int)$max]);
                } elseif (str_ends_with($salaryRange, '+')) {
                    $min = rtrim($salaryRange, '+');
                    $query->where('from_salary', '>=', (int)$min);
                }
            }

            $jobs = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // 🔹 Format response
            $jobs->getCollection()->transform(function ($job) {
                return [
                    'id'              => $job->id,
                    'job_title'       => $job->job_title,
                    'job_code'        => $job->job_code,
                    'job_location'    => $job->job_location,
                    'job_workplace'   => $job->job_workplace,
                    'employment_type' => $job->employment_type,
                    'office_location' => $job->office_location,
                    'company' => [
                        'name'    => $job->company?->name ?? '-',
                        'website' => $job->company?->website ?? '-',
                    ],
                    'from_salary'     => $job->from_salary,
                    'to_salary'       => $job->to_salary,
                    'currency'        => $job->currency,
                    'created_at'      => $job->created_at->format('Y-m-d H:i:s'),
                    'updated_at'      => $job->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return $this->paginatedResponse($jobs, 'Jobs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch jobs', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }

    public function getApplicationCountsByStage(int $jobId): JsonResponse
    {
        try {
            // ✅ Fetch stage names with IDs
            $stages = DB::table('stages')->pluck('name', 'id');

            // ✅ Fetch application counts for the given job, grouped by stage
            $results = DB::table('candidate_applications as ca')
                ->where('ca.job_post_id', $jobId)
                ->select(
                    'ca.stage_id',
                    DB::raw('COUNT(ca.id) as count')
                )
                ->groupBy('ca.stage_id')
                ->get();

            // ✅ Initialize with all stages = 0
            $stageCounts = collect($stages)->mapWithKeys(fn($name) => [$name => 0])->toArray();

            // ✅ Fill counts from query results
            foreach ($results as $row) {
                $stageName = $stages[$row->stage_id] ?? 'Unknown';
                $stageCounts[$stageName] = (int)$row->count;
            }

            // ✅ Get job details
            $job = DB::table('job_posts')
                ->select('id as job_id', 'job_title')
                ->where('id', $jobId)
                ->first();

            if (!$job) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Job not found',
                ], 404);
            }

            // ✅ Build final response
            $response = [
                'job_id'    => $job->job_id,
                'job_title' => $job->job_title,
                'total'     => array_sum($stageCounts),
                'stages'    => $stageCounts,
            ];

            return response()->json([
                'status'  => 'success',
                'message' => 'Application counts by stage retrieved successfully',
                'data'    => $response,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch application counts',
                'errors'  => [
                    'exception' => $e->getMessage(),
                ],
            ], 500);
        }
    }
}
