<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLegalDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            // Foreign key to employees table
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            // SSN (Social Security Number) and related fields
            $table->string('social_security_number');
            $table->date('issue_date_s_s_n')->nullable();            // Renamed from ssn_issue_date
            $table->string('ssn_file')->nullable();                // Renamed from ssn_document

            // National ID and related fields
            $table->string('national_id');
            $table->date('issue_date_national_id')->nullable();    // Renamed from national_id_issue_date
            $table->string('national_id_file')->nullable();        // Renamed from national_id_document

            // Social insurance number and tax-related fields
            $table->string('social_insurance_number')->nullable();
            $table->string('tax_id');
            $table->date('issue_date_tax_id')->nullable();         // Renamed from tin_issue_date
            $table->string('tax_id_file')->nullable();             // Renamed from tin_document

            // Other personal information
            $table->string('citizenship')->nullable();
            $table->string('nationality')->nullable();
            $table->string('passport')->nullable();                // Renamed from passport_details
            $table->string('work_visa')->nullable();
            $table->string('visa_details')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('legal_documents');
    }
}
