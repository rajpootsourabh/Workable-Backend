<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class CandidateController extends Controller
{
    use ApiResponse;

    /**
     * Get all candidates for the authenticated user's company.
     */
    public function listCandidates(Request $request)
    {
        $user = Auth::user();

        // Closure to generate public URLs for stored files
        $generateFileUrl = function (?string $filePath) {
            if (!$filePath) {
                return null;
            }
            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $filePath)));
            return url('api/v.1/files/' . $encodedPath);
        };

        // Start your base query scoped to the user's company
        $query = Candidate::where('company_id', $user->company_id);

        // If a search term is provided, filter across name, email & designation
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name',  'LIKE', "%{$search}%")
                    ->orWhere('email',      'LIKE', "%{$search}%")
                    ->orWhere('designation', 'LIKE', "%{$search}%");
            });
        }

        // Fetch & format
        $candidates = $query->get()->map(function ($candidate) use ($generateFileUrl) {
            $candidate->profile_pic = $generateFileUrl($candidate->profile_pic);
            $candidate->resume      = $generateFileUrl($candidate->resume);
            return $candidate;
        });

        return $this->successResponse(
            $candidates,
            'Candidate list retrieved successfully.'
        );
    }
}
