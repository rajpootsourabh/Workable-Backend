<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'employee_limit',
        'job_post_limit',
    ];

    /**
     * Features available in this plan.
     */
    public function features()
    {
        return $this->hasMany(SubscriptionPlanFeature::class);
    }

    /**
     * Companies subscribed to this plan.
     */
    public function companySubscriptions()
    {
        return $this->hasMany(CompanySubscription::class);
    }


    /**
     * Get a feature value by key.
     */
    public function getFeature(string $key, $default = null)
    {
        return $this->features()
            ->where('feature_key', $key)
            ->value('feature_value') ?? $default;
    }
}
