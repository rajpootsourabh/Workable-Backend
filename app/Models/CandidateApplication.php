<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'job_post_id',
        'status',
        'applied_at',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }
}
