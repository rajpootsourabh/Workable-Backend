<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceDetail extends Model
{
    protected $fillable = [
        'employee_id',
        'education',
        'job',
        'skill',
        'language',
        'resume',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}