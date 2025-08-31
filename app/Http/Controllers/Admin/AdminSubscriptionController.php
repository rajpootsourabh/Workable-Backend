<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\CompanySubscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class AdminSubscriptionController extends Controller
{
    use ApiResponse;

    /**
     * Return company subscription transactions for frontend.
     */
    public function index(Request $request)
    {
        $query = CompanySubscription::with(['company', 'plan'])->latest();

        // 🔍 Search (by company name or phone)
        if ($search = $request->input('search')) {
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }


        // 🎯 Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // 🎯 Filter by plan
        if ($planId = $request->input('plan_id')) {
            $query->where('subscription_plan_id', $planId);
        }

        // 📄 Paginate with default 10 per page
        $subscriptions = $query->paginate(10); // always 10 per page, frontend can pass ?page=2

        // 🔄 Format data for frontend
        $subscriptions->getCollection()->transform(function ($sub) {
            return [
                'id'        => $sub->id,
                'name'      => $sub->company->name ?? 'N/A',
                'phone'     => $sub->company->phone_number ?? 'N/A',
                'plan'      => $sub->plan->name ?? 'N/A',
                'status'    => ucfirst($sub->status),
                'startDate' => optional($sub->starts_at)->format('d-m-Y'),
                'endDate'   => optional($sub->ends_at)->format('d-m-Y'),
                'amount'    => $sub->plan
                    ? $sub->plan->currency_code . ' ' . number_format($sub->plan->price, 2)
                    : 'N/A',
            ];
        });

        return $this->paginatedResponse($subscriptions, 'Subscriptions fetched successfully');
    }
}
