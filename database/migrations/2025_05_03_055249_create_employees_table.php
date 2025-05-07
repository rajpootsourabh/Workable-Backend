<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // First name and last name are required
            $table->string('first_name');
            $table->string('last_name');
            // Middle name, preferred name, and others are nullable
            $table->string('middle_name')->nullable();
            $table->string('preferred_name')->nullable();
            // Country, address, and social media are nullable
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->text('social_media')->nullable();
            // Gender is nullable but has a limited set of values (Male, Female, Others)
            $table->string('gender')->nullable();
            // Birthdate is nullable
            $table->date('birthdate')->nullable();
            // Marital status is required (Single, Married, etc.)
            $table->string('marital_status');
            // Phone is nullable
            $table->string('phone')->nullable();
            // Work email and personal email are nullable and unique
            $table->string('work_email')->nullable()->unique();
            $table->string('personal_email')->nullable()->unique();
            // Chat/video call is nullable
            $table->string('chat_video_call')->nullable();
            // Profile image is nullable
            $table->string('profile_image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
