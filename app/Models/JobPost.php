<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function applications()
    {
        return $this->hasMany(CandidateApplication::class, 'job_post_id');
    }

    public function company()
{
    return $this->belongsTo(Company::class);
}
}
