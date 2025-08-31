<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanFeature;

class SubscriptionPlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $featuresByPlan = [
            'Free Trial' => [
                'max_employees' => 5,
                'max_job_posts' => 3,
                'resume_download' => false,
                'priority_support' => false,
                'api_access' => false,
            ],
            'Silver' => [
                'max_employees' => 20,
                'max_job_posts' => 10,
                'resume_download' => true,
                'priority_support' => false,
                'api_access' => false,
            ],
            'Gold' => [
                'max_employees' => 100,
                'max_job_posts' => 50,
                'resume_download' => true,
                'priority_support' => true,
                'api_access' => false,
            ],
            'Diamond' => [
                'max_employees' => 'unlimited',
                'max_job_posts' => 'unlimited',
                'resume_download' => true,
                'priority_support' => true,
                'api_access' => true,
            ],
        ];

        foreach ($featuresByPlan as $planName => $features) {
            $plan = SubscriptionPlan::where('name', $planName)->first();
            if (!$plan) continue;

            foreach ($features as $key => $value) {
                SubscriptionPlanFeature::create([
                    'subscription_plan_id' => $plan->id,
                    'feature_key' => $key,
                    'feature_value' => json_encode($value), // store as JSON
                    'description' => null,                 // optional
                    'is_active' => true,                    // default enabled
                    'metadata' => null,                     // optional future-proof
                ]);
            }
        }
    }
}
