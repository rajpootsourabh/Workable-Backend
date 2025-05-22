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

    public function logs()
    {
        return $this->hasMany(CandidateApplicationLog::class);
    }
    public function comments()
    {
        return $this->hasMany(CandidateApplicationComment::class);
    }

    public function communications()
    {
        return $this->hasMany(CandidateApplicationCommunication::class);
    }

    public function reviews()
    {
        return $this->hasMany(CandidateApplicationReview::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
}
