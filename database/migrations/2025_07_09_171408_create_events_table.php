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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('manager_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('time');

            $table->enum('visibility', ['team', 'global'])->default('team');

            $table->timestamps();

            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
