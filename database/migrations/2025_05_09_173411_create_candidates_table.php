<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();

            // Correct type matching companies.id
            $table->unsignedBigInteger('company_id')->nullable();

            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('designation', 255);
            $table->decimal('experience', 4, 1);
            $table->string('phone', 20);
            $table->string('location', 255);
            $table->decimal('current_ctc', 10, 2);
            $table->decimal('expected_ctc', 10, 2);
            $table->string('profile_pic')->nullable();
            $table->string('resume')->nullable();

            // Correct type matching sources.id
            $table->unsignedBigInteger('source_id')->nullable();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('set null');

            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
