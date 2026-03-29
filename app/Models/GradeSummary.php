<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Constants\GradingConstants;

class GradeSummary extends Model
{
    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'semester',
        'midterm_grade',
        'finals_grade',
        'semester_grade',
    ];

    protected $casts = [
        'midterm_grade'  => 'decimal:2',
        'finals_grade'   => 'decimal:2',
        'semester_grade' => 'decimal:2',
    ];

    // The enrollment this summary belongs to
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // The subject this summary is for
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Compute grades from score records and save
        public function computeGrades(): void
    {
        foreach (['midterm', 'final'] as $period) {

            $scores = ScoreRecord::where('enrollment_id', $this->enrollment_id)
                ->where('subject_id', $this->subject_id)
                ->where('semester', $this->semester)
                ->where('period', $period)
                ->get();

            $activityScores = $scores->where('component_type', 'activity');
            $quizScores     = $scores->where('component_type', 'quiz');
            $examScore      = $scores->where('component_type', 'exam')->first();

            $activityWeighted = $activityScores->isNotEmpty()
                ? ($activityScores->sum('score') /
                    (GradingConstants::MAX_ACTIVITIES_PER_PERIOD * GradingConstants::MAX_SCORE_ACTIVITY))
                    * 100 * GradingConstants::WEIGHT_ACTIVITIES
                : 0;

            $quizWeighted = $quizScores->isNotEmpty()
                ? ($quizScores->sum('score') /
                    (GradingConstants::MAX_QUIZZES_PER_PERIOD * GradingConstants::MAX_SCORE_QUIZ))
                    * 100 * GradingConstants::WEIGHT_QUIZZES
                : 0;

            $examWeighted = $examScore
                ? ($examScore->score / GradingConstants::MAX_SCORE_EXAM)
                    * 100 * GradingConstants::WEIGHT_EXAMS
                : 0;

            $periodGrade = round($activityWeighted + $quizWeighted + $examWeighted, 2);

            if ($period === 'midterm') {
                $this->midterm_grade = $periodGrade;
            } else {
                $this->finals_grade = $periodGrade;
            }
        }

        if (!is_null($this->midterm_grade) && !is_null($this->finals_grade)) {
            $this->semester_grade = round(
                ($this->midterm_grade + $this->finals_grade) / 2, 2
            );
        }

        $this->save();
    }
}
