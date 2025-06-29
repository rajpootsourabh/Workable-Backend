<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeOffRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'manager_id',
        'time_off_type_id',
        'start_date',
        'end_date',
        'first_day_type',
        'last_day_type',
        'total_days',
        'note',
        'attachment',
        'status',
        'manager_note',    
        'updated_by',
    ];

    public function scopeOverlappingWith($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_date', '<', $start)
                        ->where('end_date', '>', $end);
                });
        });
    }

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function timeOffType()
    {
        return $this->belongsTo(TimeOffType::class);
    }

    public function updatedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
}
