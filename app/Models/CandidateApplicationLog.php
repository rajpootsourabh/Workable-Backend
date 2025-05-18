<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateApplicationLog extends Model
{
    protected $fillable = [
        'candidate_application_id',
        'from_stage',
        'to_stage',
        'changed_by',
        'changed_at',
        'note',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'from_stage' => 'integer',
        'to_stage' => 'integer',
    ];

    // Relationships
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Optional helper: map stage ID to name
    public static function stageLabel(int $stage): string
    {
        return match ($stage) {
            1 => 'Sourced',
            2 => 'Applied',
            3 => 'Phone Screen',
            4 => 'Assessment',
            5 => 'Interview',
            6 => 'Offer',
            7 => 'Hired',
            default => 'Unknown',
        };
    }

    public function getFromStageLabelAttribute(): string
    {
        return $this->from_stage ? self::stageLabel($this->from_stage) : '-';
    }

    public function getToStageLabelAttribute(): string
    {
        return self::stageLabel($this->to_stage);
    }
}
