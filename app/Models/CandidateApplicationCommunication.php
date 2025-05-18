<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateApplicationCommunication extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'candidate_application_id',
        'sent_by',
        'type',
        'subject',
        'message',
    ];
}
