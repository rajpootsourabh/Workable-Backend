<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Fillable attributes for mass assignment
    protected $fillable = [
        'profile_image',
        'first_name',
        'middle_name',
        'last_name',
        'preferred_name',
        'country',
        'address',
        'gender',
        'birthdate',
        'marital_status',
        'phone',
        'work_email',
        'personal_email',
        'chat_video_call',
        'social_media',
        'company_id', // Foreign key reference to companies table
    ];

    /**
     * One-to-One: An employee has one job detail.
     */
    public function jobDetail()
    {
        return $this->hasOne(JobDetail::class, 'employee_id', 'id');
    }

    /**
     * One-to-One: An employee has one compensation detail.
     */
    public function compensationDetail()
    {
        return $this->hasOne(CompensationDetail::class, 'employee_id', 'id');
    }

    /**
     * One-to-One: An employee has one legal document.
     */
    public function legalDocument()
    {
        return $this->hasOne(LegalDocument::class, 'employee_id', 'id');
    }

    /**
     * One-to-One: An employee has one experience detail.
     */
    public function experienceDetail()
    {
        return $this->hasOne(ExperienceDetail::class, 'employee_id', 'id');
    }

    /**
     * One-to-One: An employee has one emergency contact.
     */
    public function emergencyContact()
    {
        return $this->hasOne(EmergencyContact::class, 'employee_id', 'id');
    }

    /**
     * Many-to-One: An employee belongs to a company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * One-to-One: An employee has one user account.
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * Many-to-Many: An employee is assigned to many candidates.
     */
    public function assignedCandidates()
    {
        return $this->belongsToMany(Candidate::class, 'candidate_employee_assignments', 'employee_id', 'candidate_id')
            ->withPivot('assigned_by', 'notes', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * One-to-Many: An employee (as manager) has many subordinates (via job_details.manager_id).
     */
    public function subordinates()
    {
        return $this->hasMany(JobDetail::class, 'manager_id');
    }

    public function isManager()
{
    return $this->subordinates()->exists();
}

    /**
     * One-to-Many: An employee can have multiple leave balance records.
     * 
     * Explanation:
     * Each employee is allocated leave balances for each time off type (e.g., Sick, PTO) and year.
     * So, an employee may have multiple `employee_leave_balances` entries — 
     * one for Sick Leave 2025, one for PTO 2025, etc.
     * 
     * That's why we use `hasMany()` — because one employee maps to many leave balance records.
     */
    public function leaveBalances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

}
