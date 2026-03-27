<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassAdvisory extends Model
{
    protected $fillable = [
        'teacher_id',
        'section_id',
        'academic_year_id',
        'assigned_by',
    ];

    // The teacher assigned to this advisory
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // The section this advisory is for
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // The academic year this advisory belongs to
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // The admin who made this assignment
    public function assignedBy()
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }
}
