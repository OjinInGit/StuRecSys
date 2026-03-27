<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    // A subject has many score records
    public function scoreRecords()
    {
        return $this->hasMany(ScoreRecord::class);
    }

    // A subject has many grade summaries
    public function gradeSummaries()
    {
        return $this->hasMany(GradeSummary::class);
    }
}
