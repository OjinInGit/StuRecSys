<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'surname',
        'given_name',
        'middle_initial',
        'username',
        'email',
        'contact_number',
        'backup_email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    // Enrollments this admin created
    public function enrolledStudents()
    {
        return $this->hasMany(Enrollment::class, 'enrolled_by');
    }

    // Enrollments this admin confirmed
    public function confirmedEnrollments()
    {
        return $this->hasMany(Enrollment::class, 'confirmed_by');
    }

    // Class advisories this admin assigned
    public function classAdvisories()
    {
        return $this->hasMany(ClassAdvisory::class, 'assigned_by');
    }

    // Full name accessor
    public function getFullNameAttribute(): string
    {
        $mi = $this->middle_initial ? ' ' . $this->middle_initial . '.' : '';
        return "{$this->surname}, {$this->given_name}{$mi}";
    }
}
