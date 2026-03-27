<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GradeSummary;

class GradeSummaryController extends Controller
{
    // -------------------------------------------------------
    // GET GRADE SUMMARIES FOR AN ENROLLMENT
    // -------------------------------------------------------

    public function index(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|integer|exists:enrollments,id',
        ]);

        $summaries = GradeSummary::where('enrollment_id', $request->enrollment_id)
            ->with('subject')
            ->orderBy('semester')
            ->get()
            ->map(function ($summary) {
                return [
                    'subject'        => $summary->subject->name,
                    'semester'       => $summary->semester,
                    'midterm_grade'  => $summary->midterm_grade,
                    'finals_grade'   => $summary->finals_grade,
                    'semester_grade' => $summary->semester_grade,
                ];
            });

        return response()->json([
            'grade_summaries' => $summaries,
        ], 200);
    }

    // -------------------------------------------------------
    // GET GRADE SUMMARY FOR A SPECIFIC SUBJECT AND SEMESTER
    // -------------------------------------------------------

    public function show(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|integer|exists:enrollments,id',
            'subject_id'    => 'required|integer|exists:subjects,id',
            'semester'      => 'required|integer|in:1,2',
        ]);

        $summary = GradeSummary::where([
            'enrollment_id' => $request->enrollment_id,
            'subject_id'    => $request->subject_id,
            'semester'      => $request->semester,
        ])->with('subject')->firstOrFail();

        return response()->json([
            'grade_summary' => [
                'subject'        => $summary->subject->name,
                'semester'       => $summary->semester,
                'midterm_grade'  => $summary->midterm_grade,
                'finals_grade'   => $summary->finals_grade,
                'semester_grade' => $summary->semester_grade,
            ],
        ], 200);
    }
}
