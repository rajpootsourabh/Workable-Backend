<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmergencyContactsTable extends Migration
{
    public function up()
    {
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            // Nullable contact name and phone number to align with validation rules
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable(); // changed to match validation
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('emergency_contacts');
    }
}
