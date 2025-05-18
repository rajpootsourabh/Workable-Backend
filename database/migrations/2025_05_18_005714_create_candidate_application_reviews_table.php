<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_application_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewed_by')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('rating')->nullable(); // Optional: 1-5 or 1-10
            $table->text('feedback');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_application_reviews');
    }
};

