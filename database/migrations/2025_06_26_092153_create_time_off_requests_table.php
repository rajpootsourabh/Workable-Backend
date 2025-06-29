<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeOffRequestsTable extends Migration
{
    public function up(): void
    {
        Schema::create('time_off_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('time_off_type_id')->constrained('time_off_types')->onDelete('cascade');

            $table->date('start_date');
            $table->date('end_date');
            $table->enum('first_day_type', ['full', 'half'])->default('full');
            $table->enum('last_day_type', ['full', 'half'])->default('full');
            $table->decimal('total_days', 5, 2);

            $table->text('note')->nullable();
            // Store uploaded file path if attachment is needed (e.g., sick leave)
            $table->string('attachment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('manager_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_off_requests');
    }
}
