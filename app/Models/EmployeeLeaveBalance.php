<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'time_off_type_id',
        'year',
        'allocated_days',
        'used_days',
        'carried_forward',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function timeOffType()
    {
        return $this->belongsTo(TimeOffType::class);
    }
}
