<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Teacher extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'surname',
        'given_name',
        'middle_initial',
        'username',
        'email',
        'contact_number',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    // A teacher has many class advisories (one per academic year)
    public function classAdvisories()
    {
        return $this->hasMany(ClassAdvisory::class);
    }

    // Score records this teacher has recorded
    public function scoreRecords()
    {
        return $this->hasMany(ScoreRecord::class, 'recorded_by');
    }

    // Full name accessor
    public function getFullNameAttribute(): string
    {
        $mi = $this->middle_initial ? ' ' . $this->middle_initial . '.' : '';
        return "{$this->surname}, {$this->given_name}{$mi}";
    }
}
