<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = ['name'];
    public $timestamps = true;

    public function applications()
    {
        return $this->hasMany(CandidateApplication::class);
    }
}
