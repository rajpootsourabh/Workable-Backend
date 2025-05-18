<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class JobApplicationStatsController extends Controller
{
    public function getApplicationCountsByStage(): JsonResponse
    {
        // Fetch stage names with their IDs
        $stages = DB::table('stages')->pluck('name', 'id');

        $results = DB::table('job_posts as jp')
            ->leftJoin('candidate_applications as ca', 'ca.job_post_id', '=', 'jp.id')
            ->select(
                'jp.id as job_id',
                'jp.job_title',
                'ca.stage_id',
                DB::raw('COUNT(ca.id) as count')
            )
            ->groupBy('jp.id', 'jp.job_title', 'ca.stage_id')
            ->get();

        // Transform data
        $grouped = [];
        foreach ($results as $row) {
            $jobId = $row->job_id;

            if (!isset($grouped[$jobId])) {
                $grouped[$jobId] = [
                    'job_id' => $jobId,
                    'job_title' => $row->job_title,
                    'total' => 0,
                    'stages' => [],
                ];
            }

            $stageName = $stages[$row->stage_id] ?? 'Unknown';

            $grouped[$jobId]['stages'][$stageName] = (int)$row->count;
            $grouped[$jobId]['total'] += (int)$row->count;
        }

        return response()->json([
            'status' => 'success',
            'data' => array_values($grouped),
        ]);
    }
}
