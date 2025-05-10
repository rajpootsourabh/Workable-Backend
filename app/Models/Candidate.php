<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    // public $incrementing = false;
    // protected $keyType = 'uuid';

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'designation',
        'experience',
        'phone',
        'location',
        'current_ctc',
        'expected_ctc',
        'profile_pic',
        'resume',
        'source_id',
    ];

    /**
     * Get all job applications for the candidate.
     */
    public function applications()
    {
        return $this->hasMany(CandidateApplication::class);
    }
}
