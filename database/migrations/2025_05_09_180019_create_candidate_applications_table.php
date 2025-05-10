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
        Schema::create('candidate_applications', function (Blueprint $table) {
            // Auto-incrementing primary key
            $table->id();

            // Foreign key references
            $table->unsignedBigInteger('candidate_id');  // References candidates table
            $table->unsignedBigInteger('job_post_id');   // References job_posts table

            // Enum for application status
            $table->enum('status', ['Applied', 'Screening', 'Interviewing', 'Offer', 'Hired', 'Rejected'])->default('Applied');

            // Timestamp for when the application was submitted
            $table->timestamp('applied_at')->useCurrent();

            // Define foreign key relationships with cascading delete
            $table->foreign('candidate_id')->references('id')->on('candidates')->onDelete('cascade');
            $table->foreign('job_post_id')->references('id')->on('job_posts')->onDelete('cascade');

            // Automatically created timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_applications');
    }
};
