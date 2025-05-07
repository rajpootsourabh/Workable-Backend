<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompensationDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('compensation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            // Salary details as a decimal (nullable if validation allows null)
            $table->decimal('salary_details', 10, 2)->nullable(); // Nullable as per validation
            // Bank name (required)
            $table->string('bank_name');
            // IBAN (required)
            $table->string('iban');
            // Account number (nullable as per validation)
            $table->string('account_number')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compensation_details');
    }
}
