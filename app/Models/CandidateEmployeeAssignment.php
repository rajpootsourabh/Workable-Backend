<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateEmployeeAssignment extends Model
{
    protected $fillable = [
        'employee_id', 'candidate_id', 'assigned_by', 'status', 'notes', 'assigned_at',
    ];

    public function candidate() {
        return $this->belongsTo(Candidate::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function assigner() {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

