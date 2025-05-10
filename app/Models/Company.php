<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Company extends Model
{
    use HasFactory;

    // Allow mass-assignment for specific fields
    protected $fillable = [
        'name',
        'website',
        'size',
        'phone_number',
        'evaluating_website',
    ];

    /**
     * Relationship: A company can have many users.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
