<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

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
        'company_id',  // Add the company_id to the fillable property
    ];

    // Job Details Relationship
    public function jobDetail()
    {
        return $this->hasOne(JobDetail::class, 'employee_id', 'id');
    }

    // Compensation Details Relationship
    public function compensationDetail()
    {
        return $this->hasOne(CompensationDetail::class, 'employee_id', 'id');
    }

    // Legal Documents Relationship
    public function legalDocument()
    {
        return $this->hasOne(LegalDocument::class, 'employee_id', 'id');
    }

    // Experience Details Relationship
    public function experienceDetail()
    {
        return $this->hasOne(ExperienceDetail::class, 'employee_id', 'id');
    }

    // Emergency Contact Relationship
    public function emergencyContact()
    {
        return $this->hasOne(EmergencyContact::class, 'employee_id', 'id');
    }

    // Company Relationship: Employee belongs to a Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    // User Relationship: Employee belongs to a User
    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function assignedCandidates()
    {
        return $this->belongsToMany(Candidate::class, 'candidate_employee_assignments', 'employee_id', 'candidate_id')
            ->withPivot('assigned_by', 'notes', 'assigned_at')
            ->withTimestamps();
    }
}
