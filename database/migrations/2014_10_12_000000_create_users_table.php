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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('companyName');
            $table->string('companyWebsite');
            $table->string('companySize');
            $table->string('phoneNumber')->unique();
            $table->text('evaluatingWebsite')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('role')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('is_active')->default(1)->comment(' 1 yes, 0 no');
            $table->rememberToken();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
