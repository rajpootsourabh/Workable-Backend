<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_application_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id')->constrained()->onDelete('cascade');
            $table->foreignId('commented_by')->constrained('users')->onDelete('cascade');
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_application_comments');
    }
};
