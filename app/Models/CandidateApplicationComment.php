<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateApplicationComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_application_id',
        'comment',
        'commented_by',
    ];

    public function commenter()
{
    return $this->belongsTo(\App\Models\User::class, 'commented_by');
}
}
