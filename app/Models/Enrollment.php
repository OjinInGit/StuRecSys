<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'section_id',
        'academic_year_id',
        'enrolled_by',
        'status',
        'gpa',
        'grade_status',
        'is_confirmed',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
        'gpa'          => 'decimal:2',
    ];

    // The student in this enrollment
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // The section this enrollment is assigned to
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // The academic year of this enrollment
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // The admin who enrolled the student
    public function enrolledBy()
    {
        return $this->belongsTo(Admin::class, 'enrolled_by');
    }

    // The admin who confirmed the enrollment
    public function confirmedBy()
    {
        return $this->belongsTo(Admin::class, 'confirmed_by');
    }

    // All score records under this enrollment
    public function scoreRecords()
    {
        return $this->hasMany(ScoreRecord::class);
    }

    // All grade summaries under this enrollment
    public function gradeSummaries()
    {
        return $this->hasMany(GradeSummary::class);
    }

    // Compute and assign GPA and grade status
    // Call this after both semesters' grade summaries are complete
    public function computeGpa(): void
    {
        $semesterGrades = $this->gradeSummaries()
            ->whereNotNull('semester_grade')
            ->pluck('semester_grade');

        if ($semesterGrades->count() === 0) return;

        // GPA = mean of all subject semester grades across both semesters
        $gpa = round($semesterGrades->avg(), 2);

        $this->gpa = $gpa;
        $this->grade_status = $this->resolveGradeStatus($gpa);
        $this->save();
    }

    // Resolve letter grade from GPA
    private function resolveGradeStatus(float $gpa): string
    {
        return match (true) {
            $gpa >= 95              => 'A',
            $gpa >= 90              => 'B',
            $gpa >= 85              => 'C',
            $gpa >= 80              => 'D',
            $gpa >= 75              => 'E',
            default                 => 'F',
        };
    }
}
