<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeSummary\ShowGradeSummaryRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Models\GradeSummary;

class GradeSummaryController extends Controller
{
    use ApiResponseTrait;

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

        return $this->successResponse($summaries, 'Grade summaries retrieved successfully.');
    }

    public function show(ShowGradeSummaryRequest $request)
    {
        $summary = GradeSummary::where([
            'enrollment_id' => $request->enrollment_id,
            'subject_id'    => $request->subject_id,
            'semester'      => $request->semester,
        ])->with('subject')->firstOrFail();

        return $this->successResponse([
            'subject'        => $summary->subject->name,
            'semester'       => $summary->semester,
            'midterm_grade'  => $summary->midterm_grade,
            'finals_grade'   => $summary->finals_grade,
            'semester_grade' => $summary->semester_grade,
        ], 'Grade summary retrieved successfully.');
    }
}
