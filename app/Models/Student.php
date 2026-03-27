<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'id_number',
        'surname',
        'given_name',
        'middle_initial',
        'birthdate',
        'age',
        'gender',
        'emergency_contact_number',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    // A student has many enrollments across academic years
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // Current active enrollment
    public function activeEnrollment()
    {
        return $this->hasOne(Enrollment::class)
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', 1);
            });
    }

    // Full name accessor
    public function getFullNameAttribute(): string
    {
        $mi = $this->middle_initial ? ' ' . $this->middle_initial . '.' : '';
        return "{$this->surname}, {$this->given_name}{$mi}";
    }
}
