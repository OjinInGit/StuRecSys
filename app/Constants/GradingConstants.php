<?php

namespace App\Constants;

class GradingConstants
{
    // -------------------------------------------------------
    // Component Max Scores
    // -------------------------------------------------------
    const MAX_SCORE_ACTIVITY = 25;
    const MAX_SCORE_QUIZ     = 50;
    const MAX_SCORE_EXAM     = 100;

    // -------------------------------------------------------
    // Component Sequence Limits
    // -------------------------------------------------------
    const MAX_ACTIVITIES_PER_PERIOD = 6;
    const MAX_QUIZZES_PER_PERIOD    = 3;
    const MAX_EXAMS_PER_PERIOD      = 1;

    // -------------------------------------------------------
    // Grading Weights (as decimals)
    // -------------------------------------------------------
    const WEIGHT_ACTIVITIES = 0.50;
    const WEIGHT_QUIZZES    = 0.30;
    const WEIGHT_EXAMS      = 0.20;

    // -------------------------------------------------------
    // Grade Status Thresholds
    // -------------------------------------------------------
    const GRADE_A_MIN = 95.00;
    const GRADE_B_MIN = 90.00;
    const GRADE_C_MIN = 85.00;
    const GRADE_D_MIN = 80.00;
    const GRADE_E_MIN = 75.00;

    // -------------------------------------------------------
    // System Limits
    // -------------------------------------------------------
    const MAX_ADMIN_ACCOUNTS    = 6;
    const MAX_STUDENTS_PER_SECTION = 3;

    // -------------------------------------------------------
    // Helpers: resolve max score by component type
    // -------------------------------------------------------
    public static function maxScore(string $componentType): int
    {
        return match($componentType) {
            'activity' => self::MAX_SCORE_ACTIVITY,
            'quiz'     => self::MAX_SCORE_QUIZ,
            'exam'     => self::MAX_SCORE_EXAM,
            default    => 100,
        };
    }

    // -------------------------------------------------------
    // Helpers: resolve sequence limit by component type
    // -------------------------------------------------------
    public static function sequenceLimit(string $componentType): int
    {
        return match($componentType) {
            'activity' => self::MAX_ACTIVITIES_PER_PERIOD,
            'quiz'     => self::MAX_QUIZZES_PER_PERIOD,
            'exam'     => self::MAX_EXAMS_PER_PERIOD,
            default    => 1,
        };
    }

    // -------------------------------------------------------
    // Helpers: resolve grade status letter from GPA
    // -------------------------------------------------------
    public static function gradeStatus(float $gpa): string
    {
        return match(true) {
            $gpa >= self::GRADE_A_MIN => 'A',
            $gpa >= self::GRADE_B_MIN => 'B',
            $gpa >= self::GRADE_C_MIN => 'C',
            $gpa >= self::GRADE_D_MIN => 'D',
            $gpa >= self::GRADE_E_MIN => 'E',
            default                   => 'F',
        };
    }
}
