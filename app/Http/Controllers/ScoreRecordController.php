<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScoreRecord\StoreScoreRecordRequest;
use App\Http\Requests\ScoreRecord\UpdateScoreRecordRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Models\ScoreRecord;
use App\Models\Enrollment;
use App\Models\GradeSummary;
use App\Constants\GradingConstants;

class ScoreRecordController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreScoreRecordRequest $request)
    {
        $maxScore = GradingConstants::maxScore($request->component_type);

        $existing = ScoreRecord::where([
            'enrollment_id'   => $request->enrollment_id,
            'subject_id'      => $request->subject_id,
            'semester'        => $request->semester,
            'period'          => $request->period,
            'component_type'  => $request->component_type,
            'sequence_number' => $request->sequence_number,
        ])->first();

        if ($existing) {
            return $this->validationErrorResponse(
                'A score for this entry already exists. Use the update endpoint to modify it.'
            );
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

        return $this->createdResponse([
            'score_record' => $scoreRecord,
            'percentile'   => $scoreRecord->percentile . '%',
        ], 'Score recorded successfully.');
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

        return $this->successResponse([
            'score_record' => $scoreRecord,
            'percentile'   => $scoreRecord->percentile . '%',
        ], 'Score updated successfully.');
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

        return $this->successResponse($scores, 'Score records retrieved successfully.');
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
