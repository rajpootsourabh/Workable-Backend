<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('candidate_applications', function (Blueprint $table) {
            // Drop old enum 'status' column first
            if (Schema::hasColumn('candidate_applications', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('stage_id')->default(1); // Stage 1 = Sourced
            $table->enum('status', ['Active', 'Rejected'])->default('Active');
        });
    }

    public function down(): void
    {
        // First, drop the newly added columns
        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->dropColumn(['stage_id', 'status']);
        });

        // Then, add back the original enum column
        Schema::table('candidate_applications', function (Blueprint $table) {
            $table->enum('status', ['Applied', 'Screening', 'Interviewing', 'Offer', 'Hired', 'Rejected'])->default('Applied');
        });
    }
};
