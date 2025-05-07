<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    // Fields that are mass assignable
    protected $fillable = [
        'employee_id',    // Foreign key linking to the Employee model
        'contact_name',   // Name of the emergency contact
        'contact_phone',   // Phone number of the emergency contact
    ];

    // Define relationship with Employee model
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');  // Each emergency contact belongs to an employee
    }
}
