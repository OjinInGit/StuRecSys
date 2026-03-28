<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScoreRecord\StoreScoreRecordRequest;
use App\Http\Requests\ScoreRecord\UpdateScoreRecordRequest;
use Illuminate\Http\Request;
use App\Models\ScoreRecord;
use App\Models\Enrollment;
use App\Models\GradeSummary;

class ScoreRecordController extends Controller
{
    public function store(StoreScoreRecordRequest $request)
    {
        $maxScores = [
            'activity' => 25,
            'quiz'     => 50,
            'exam'     => 100,
        ];
        $maxScore = $maxScores[$request->component_type];

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

    public function update(UpdateScoreRecordRequest $request, $id)
    {
        $scoreRecord = ScoreRecord::findOrFail($id);

        $scoreRecord->update([
            'score'       => $request->score,
            'recorded_by' => $request->user()->id,
        ]);

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

    public function index(Request $request)
    {
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

        $enrollment = Enrollment::findOrFail($enrollmentId);
        $enrollment->computeGpa();
    }
}
