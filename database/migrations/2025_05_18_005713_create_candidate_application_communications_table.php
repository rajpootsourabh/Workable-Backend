<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('candidate_application_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_application_id');
            $table->foreign('candidate_application_id', 'c_app_comm_cand_app_id_fk')
                ->references('id')->on('candidate_applications')
                ->onDelete('cascade');
            $table->foreignId('sent_by')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['email', 'sms']);
            $table->string('subject')->nullable(); // for email only
            $table->text('message');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_application_communications');
    }
};
