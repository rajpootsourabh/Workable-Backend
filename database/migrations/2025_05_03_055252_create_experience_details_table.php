<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExperienceDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('experience_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            // Skills, job experience, languages, and education details
            $table->text('education')->nullable(); // Nullable as per validation
            $table->text('job')->nullable(); // Nullable as per validation
            $table->text('skill')->nullable(); // Nullable as per validation
            $table->text('language')->nullable(); // Nullable as per validation
            // Resume field (storing file path or URL)
            $table->string('resume')->nullable(); // Nullable as per validation
            $table->timestamps();
        });
    }
}
