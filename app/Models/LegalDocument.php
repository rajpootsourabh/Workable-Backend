<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;

    // Fields that are mass assignable
    protected $fillable = [
        'employee_id',                     // Foreign key to Employee
        'social_security_number',         // Social Security number
        'issue_date_s_s_n',                 // Issue date of the SSN
        'ssn_file',                       // SSN document (file)
        'national_id',                    // National ID number
        'issue_date_national_id',        // Issue date of the National ID
        'national_id_file',              // National ID document (file)
        'social_insurance_number',       // Social Insurance number (optional)
        'tax_id',                         // Tax ID number
        'issue_date_tax_id',             // Issue date of the Tax ID number
        'tax_id_file',                   // Tax ID document (file)
        'citizenship',                   // Employee's citizenship status
        'nationality',                   // Nationality of the employee
        'passport',                      // Passport details
        'work_visa',                     // Work visa details (if applicable)
        'visa_details',                     // Work visa details (document or more info)
    ];

    // Define relationship with Employee model
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');  // Each legal document belongs to one employee
    }
}
