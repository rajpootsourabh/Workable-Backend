<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('job_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            // Job title, hire date, start date, etc.
            $table->string('job_title'); // Required
            $table->date('hire_date')->nullable(); // Nullable
            $table->date('start_date'); // Required
            $table->string('entity')->nullable(); // Nullable
            $table->string('department')->nullable(); // Nullable
            $table->string('division')->nullable(); // Nullable
            $table->string('manager')->nullable(); // Nullable
            $table->date('effective_date'); // Required
            $table->string('employment_type'); // Required
            $table->string('workplace')->nullable();// Nullable
            $table->date('expiry_date')->nullable(); // Nullable
            $table->text('note')->nullable(); // Nullable
            $table->string('work_schedule')->nullable(); // Nullable
            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_details');
    }
}
