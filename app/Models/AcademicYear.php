<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'year_start',
        'year_end',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // An academic year has many enrollments
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // An academic year has many class advisories
    public function classAdvisories()
    {
        return $this->hasMany(ClassAdvisory::class);
    }
}
