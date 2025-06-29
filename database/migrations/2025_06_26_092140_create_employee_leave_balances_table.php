<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeLeaveBalancesTable extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('time_off_type_id')->constrained('time_off_types')->onDelete('cascade');
            $table->year('year');
            $table->decimal('allocated_days', 5, 2)->default(0);
            $table->decimal('used_days', 5, 2)->default(0);
            $table->decimal('carried_forward', 5, 2)->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'time_off_type_id', 'year'], 'unique_employee_type_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
}
