<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class CompanyObserver
{
    /**
     * Handle the Company "created" event.
     */
    public $afterCommit = true;

    public function created(Company $company): void
    {
        // Prevent duplicate subscriptions (e.g., if created manually)
        if ($company->subscriptions()->exists()) {
            return;
        }

        // Prefer a free trial plan
        $trialPlan = SubscriptionPlan::where('trial_days', '>', 0)->where('is_active', true)->first();

        // Fallback to cheapest active paid plan
        $fallbackPlan = SubscriptionPlan::where('is_active', true)->orderBy('price')->first();

        $plan = $trialPlan ?? $fallbackPlan;

        if (!$plan) {
            return; // No available plans
        }

        // Determine trial duration
        $trialDays = $trialPlan ? $plan->trial_days : 0;
        $startsAt  = now();
        $endsAt    = $startsAt->copy()->addDays($trialDays > 0 ? $trialDays : $plan->duration_days);

        CompanySubscription::create([
            'company_id'           => $company->id,
            'subscription_plan_id' => $plan->id,
            'starts_at'            => $startsAt,
            'ends_at'              => $endsAt,
            'status'               => $trialPlan ? 'trial' : 'active',
            'auto_renew'           => $trialPlan ? false : true,
            'grace_ends_at'        => $trialPlan ? $endsAt->copy()->addDays(3) : null, // 3-day grace for trials
            'trial_override_days'  => $trialPlan ? $trialDays : null,
            'metadata'             => [
                'assigned_by' => 'system',
                'note'        => $trialPlan ? 'Auto-assigned trial on company creation' : 'Auto-assigned paid plan',
            ],
        ]);
    }

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "deleted" event.
     */
    public function deleted(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "restored" event.
     */
    public function restored(Company $company): void
    {
        //
    }

    /**
     * Handle the Company "force deleted" event.
     */
    public function forceDeleted(Company $company): void
    {
        //
    }
}
