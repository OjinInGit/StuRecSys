<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeSummary\ShowGradeSummaryRequest;
use Illuminate\Http\Request;
use App\Models\GradeSummary;

class GradeSummaryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
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

    public function show(ShowGradeSummaryRequest $request)
    {
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
