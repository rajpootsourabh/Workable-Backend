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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->string('job_code')->unique();
            $table->string('job_location');
            $table->enum('job_workplace', ['onsite', 'hybrid', 'remote']);
            $table->string('office_location')->nullable();
            $table->text('description')->nullable();
            $table->string('company_industry')->nullable();
            $table->string('job_function')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('experience')->nullable();
            $table->string('education')->nullable();
            $table->string('keywords')->nullable();
            $table->string('job_department')->nullable();
            $table->decimal('from_salary', 10, 2)->default(0);
            $table->decimal('to_salary', 10, 2)->default(0);
            $table->string('currency')->default("USD");
            $table->integer('create_by');
            $table->integer('update_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
