<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', // Make sure this is present in your DB and fillable
        'manager_id',
        'title',
        'description',
        'date',
        'time',
        'visibility',
    ];

    /**
     * Each event is created by a manager (who is also an employee).
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Event belongs to an employee (creator).
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
