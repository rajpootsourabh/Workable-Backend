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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration_days');                // 30, 90, etc.
            $table->integer('employee_limit')->nullable();
            $table->integer('job_post_limit')->nullable();

            // 🔑 Flexibility fields
            $table->string('currency_code', 10)->default('USD');
            $table->integer('trial_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);

            // Future proof additions
            $table->enum('billing_cycle', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->json('metadata')->nullable();
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
