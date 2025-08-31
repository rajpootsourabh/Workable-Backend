<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'description' => '15-day free trial with limited access.',
                'price' => 0,
                'duration_days' => 15,
                'employee_limit' => 5,
                'job_post_limit' => 3,
                'currency_code' => 'USD',
                'trial_days' => 15,
                'is_active' => true,
                'version' => 1,
                'billing_cycle' => 'monthly',
                'metadata' => json_encode(['can_upgrade' => true]),
                'is_featured' => false,
            ],
            [
                'name' => 'Silver',
                'description' => 'Basic plan with essential features.',
                'price' => 200.00,
                'duration_days' => 30,
                'employee_limit' => 20,
                'job_post_limit' => 10,
                'currency_code' => 'USD',
                'trial_days' => 0,
                'is_active' => true,
                'version' => 1,
                'billing_cycle' => 'monthly',
                'metadata' => json_encode(['support' => 'email']),
                'is_featured' => false,
            ],
            [
                'name' => 'Gold',
                'description' => 'Advanced plan for growing teams.',
                'price' => 400.00,
                'duration_days' => 30,
                'employee_limit' => 100,
                'job_post_limit' => 50,
                'currency_code' => 'USD',
                'trial_days' => 0,
                'is_active' => true,
                'version' => 1,
                'billing_cycle' => 'monthly',
                'metadata' => json_encode(['support' => 'priority', 'analytics' => true]),
                'is_featured' => true,
            ],
            [
                'name' => 'Diamond',
                'description' => 'All features with unlimited access.',
                'price' => 500.00,
                'duration_days' => 30,
                'employee_limit' => null,
                'job_post_limit' => null,
                'currency_code' => 'USD',
                'trial_days' => 0,
                'is_active' => true,
                'version' => 1,
                'billing_cycle' => 'monthly',
                'metadata' => json_encode(['support' => 'priority', 'analytics' => true, 'custom_reports' => true]),
                'is_featured' => true,
            ]
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
