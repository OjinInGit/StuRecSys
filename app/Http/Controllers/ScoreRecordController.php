<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScoreRecord;
use App\Models\Enrollment;
use App\Models\GradeSummary;

class ScoreRecordController extends Controller
{
    // -------------------------------------------------------
    // RECORD A SCORE (Teacher only)
    // -------------------------------------------------------

    public function store(Request $request)
    {
        $request->validate([
            'enrollment_id'   => 'required|integer|exists:enrollments,id',
            'subject_id'      => 'required|integer|exists:subjects,id',
            'semester'        => 'required|integer|in:1,2',
            'period'          => 'required|in:midterm,final',
            'component_type'  => 'required|in:activity,quiz,exam',
            'sequence_number' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $limits = [
                        'activity' => 6,
                        'quiz'     => 3,
                        'exam'     => 1,
                    ];
                    $max = $limits[$request->component_type] ?? 1;
                    if ($value < 1 || $value > $max) {
                        $fail("Sequence number for {$request->component_type} must be between 1 and {$max}.");
                    }
                },
            ],
            'score'           => 'required|numeric|min:0',
        ]);

        // Resolve max score based on component type
        $maxScores = [
            'activity' => 25,
            'quiz'     => 50,
            'exam'     => 100,
        ];
        $maxScore = $maxScores[$request->component_type];

        if ($request->score > $maxScore) {
            return response()->json([
                'message' => "Score cannot exceed the maximum of {$maxScore} for {$request->component_type}.",
            ], 422);
        }

        // Check if a score already exists for this exact entry
        $existing = ScoreRecord::where([
            'enrollment_id'   => $request->enrollment_id,
            'subject_id'      => $request->subject_id,
            'semester'        => $request->semester,
            'period'          => $request->period,
            'component_type'  => $request->component_type,
            'sequence_number' => $request->sequence_number,
        ])->first();

        if ($existing) {
            return response()->json([
                'message' => 'A score for this entry already exists. Use the update endpoint to modify it.',
            ], 422);
        }

        $scoreRecord = ScoreRecord::create([
            'enrollment_id'   => $request->enrollment_id,
            'subject_id'      => $request->subject_id,
            'semester'        => $request->semester,
            'period'          => $request->period,
            'component_type'  => $request->component_type,
            'sequence_number' => $request->sequence_number,
            'score'           => $request->score,
            'max_score'       => $maxScore,
            'recorded_by'     => $request->user()->id,
        ]);

        // Automatically recompute grade summary after saving score
        $this->recomputeGradeSummary(
            $request->enrollment_id,
            $request->subject_id,
            $request->semester
        );

        return response()->json([
            'message'      => 'Score recorded successfully.',
            'score_record' => $scoreRecord,
            'percentile'   => $scoreRecord->percentile . '%',
        ], 201);
    }

    // -------------------------------------------------------
    // UPDATE AN EXISTING SCORE (Teacher only)
    // -------------------------------------------------------

    public function update(Request $request, $id)
    {
        $scoreRecord = ScoreRecord::findOrFail($id);

        $request->validate([
            'score' => 'required|numeric|min:0|max:' . $scoreRecord->max_score,
        ]);

        $scoreRecord->update([
            'score'       => $request->score,
            'recorded_by' => $request->user()->id,
        ]);

        // Recompute grade summary after update
        $this->recomputeGradeSummary(
            $scoreRecord->enrollment_id,
            $scoreRecord->subject_id,
            $scoreRecord->semester
        );

        return response()->json([
            'message'      => 'Score updated successfully.',
            'score_record' => $scoreRecord,
            'percentile'   => $scoreRecord->percentile . '%',
        ], 200);
    }

    // -------------------------------------------------------
    // GET ALL SCORES FOR AN ENROLLMENT (per subject per semester)
    // -------------------------------------------------------

    public function index(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|integer|exists:enrollments,id',
            'subject_id'    => 'required|integer|exists:subjects,id',
            'semester'      => 'required|integer|in:1,2',
        ]);

        $scores = ScoreRecord::where([
            'enrollment_id' => $request->enrollment_id,
            'subject_id'    => $request->subject_id,
            'semester'      => $request->semester,
        ])
        ->orderBy('period')
        ->orderBy('component_type')
        ->orderBy('sequence_number')
        ->get()
        ->map(function ($record) {
            return [
                'id'              => $record->id,
                'period'          => $record->period,
                'component_type'  => $record->component_type,
                'sequence_number' => $record->sequence_number,
                'score'           => $record->score,
                'max_score'       => $record->max_score,
                'percentile'      => $record->percentile . '%',
            ];
        });

        return response()->json([
            'scores' => $scores,
        ], 200);
    }

    // -------------------------------------------------------
    // INTERNAL: Recompute grade summary after score changes
    // -------------------------------------------------------

    private function recomputeGradeSummary(
        int $enrollmentId,
        int $subjectId,
        int $semester
    ): void {
        $summary = GradeSummary::firstOrCreate([
            'enrollment_id' => $enrollmentId,
            'subject_id'    => $subjectId,
            'semester'      => $semester,
        ]);

        $summary->computeGrades();

        // Recompute overall GPA on the enrollment
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $enrollment->computeGpa();
    }
}
