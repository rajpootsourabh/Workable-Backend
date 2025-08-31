<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');

            // Subscription lifecycle
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['trial', 'active', 'expired', 'canceled'])->default('active');
            // Replaces is_active + trial for easier querying

            // Renewal & cancellation
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();

            // Optional: allow overriding plan-level trial days for this company
            $table->integer('trial_override_days')->nullable();

            // Optional: store metadata like add-ons, perks, or custom flags
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_subscriptions');
    }
};
