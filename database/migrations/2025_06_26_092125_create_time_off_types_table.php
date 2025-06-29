<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeOffTypesTable extends Migration
{
    public function up(): void
    {
        Schema::create('time_off_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->boolean('requires_attachment')->default(false);
            $table->unsignedInteger('max_days')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_off_types');
    }
}
