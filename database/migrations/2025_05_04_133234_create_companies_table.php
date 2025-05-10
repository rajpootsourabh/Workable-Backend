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
        Schema::create('companies', function (Blueprint $table) {
            $table->id(); //auto incrementing id
            $table->string('name', 255); // Required
            $table->string('website', 255); // Required 
            $table->integer('size'); // Required number (e.g. 30, 50), not string
            $table->string('phone_number', 50); // Required 
            $table->text('evaluating_website')->nullable(); // Optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
