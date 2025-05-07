<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobDetail extends Model
{
    use HasFactory;

    // Fields that are mass assignable
    protected $fillable = [
        'employee_id',     // Foreign key to Employee
        'job_title',       // Job title of the employee
        'hire_date',       // Date when the employee was hired
        'start_date',      // Start date of the employee's job
        'entity',          // The entity the employee works under
        'department',      // Department the employee belongs to
        'division',        // Division the employee belongs to
        'manager',         // The employee's manager
        'effective_date',  // Effective date of the job details
        'employment_type', // Full-time, part-time, contractor, etc.
        'workplace',       // The workplace (e.g., Onsite, Remote, Hybrid)
        'expiry_date',     // Expiry date of the job contract (if applicable)
        'note',            // Additional notes
        'work_schedule',   // Work schedule details
    ];

    // Define relationship with Employee model
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
