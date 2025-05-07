<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompensationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'salary_details',
        'bank_name',
        'iban',
        'account_number',
    ];

    // Define relationship with Employee model
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
