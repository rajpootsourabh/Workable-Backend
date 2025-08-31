<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdminCompanyController extends Controller
{
    use ApiResponse; // ✅ Use the trait for consistent responses

    /**
     * Return a paginated list of companies with key details.
     */
    public function listCompanies(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            $search = $request->input('search');
            $status = $request->input('status'); // "all", "pending", "approved", "rejected"
            $sortOrder = $request->input('sort', 'asc'); // asc or desc

            $query = Company::select(
                'id',
                'name',
                'website',
                'size',
                'phone_number',
                'company_logo',
                'company_description',
                'evaluating_website',
                'status',
                DB::raw('created_at as joined_at')
            )
                ->withCount('employees as total_employees');

            // 🔹 Search filter
            if (!empty($search)) {
                $search = strtolower($search);
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            }

            // 🔹 Status filter
            if (!empty($status) && strtolower($status) !== 'all') {
                $query->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            }

            // 🔹 Sort (registration date)
            $query->orderBy('created_at', $sortOrder);

            $companies = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->paginatedResponse($companies, 'Companies retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch companies', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Approve or reject a company
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected'
            ]);

            $company = Company::findOrFail($id);

            $company->status = $request->status;
            if ($request->status === 'approved') {
                $company->approved_at = now();
            } else {
                $company->approved_at = null; // reset if rejected
            }
            $company->save();

            return $this->successResponse(
                $company,
                "Company status updated to {$request->status} successfully"
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update company status', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
