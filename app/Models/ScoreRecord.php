<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreRecord extends Model
{
    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'semester',
        'period',
        'component_type',
        'sequence_number',
        'score',
        'max_score',
        'recorded_by',
    ];

    protected $casts = [
        'score'     => 'decimal:2',
        'max_score' => 'integer',
    ];

    // The enrollment this score belongs to
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // The subject this score is for
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // The teacher who recorded this score
    public function recordedBy()
    {
        return $this->belongsTo(Teacher::class, 'recorded_by');
    }

    // Compute percentile equivalent of the score
    public function getPercentileAttribute(): float
    {
        if ($this->max_score == 0) return 0;
        return round(($this->score / $this->max_score) * 100, 2);
    }
}
