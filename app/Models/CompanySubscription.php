<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanySubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'subscription_plan_id',
        'starts_at',
        'ends_at',
        'is_active',
        'payment_status',
        'trial',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'trial' => 'boolean',
    ];

    /**
     * Plan details.
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Company details.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if subscription is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        return $this->is_active && $this->ends_at->isFuture();
    }

    /**
     * Scope: only active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('ends_at', '>=', now());
    }
}
