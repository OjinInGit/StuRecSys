<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'grade_level_id',
        'name',
        'capacity',
    ];

    // A section belongs to a grade level
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    // A section has many enrollments
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // A section has many class advisories (one per academic year)
    public function classAdvisories()
    {
        return $this->hasMany(ClassAdvisory::class);
    }

    // A section's current class advisory
    public function activeAdvisory()
    {
        return $this->hasOne(ClassAdvisory::class)
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', 1);
            });
    }
}
