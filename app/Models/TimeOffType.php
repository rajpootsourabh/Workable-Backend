<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeOffType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_paid',
        'requires_attachment',
        'max_days',
    ];

    // Optional: If you want reverse relationship
    public function timeOffRequests()
    {
        return $this->hasMany(TimeOffRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }
}
