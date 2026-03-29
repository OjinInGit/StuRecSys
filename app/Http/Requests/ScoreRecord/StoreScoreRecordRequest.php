<?php

namespace App\Http\Requests\ScoreRecord;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\GradingConstants;

class StoreScoreRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_id'   => ['required', 'integer', 'exists:enrollments,id'],
            'subject_id'      => ['required', 'integer', 'exists:subjects,id'],
            'semester'        => ['required', 'integer', 'in:1,2'],
            'period'          => ['required', 'string', 'in:midterm,final'],
            'component_type'  => ['required', 'string', 'in:activity,quiz,exam'],
            'sequence_number' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $limits = [
                        'activity' => 6,
                        'quiz'     => 3,
                        'exam'     => 1,
                    ];
                    $type = $this->input('component_type');
                    $max  = GradingConstants::sequenceLimit($type);

                    if ($value > $max) {
                        $fail("Sequence number for a {$type} must not exceed {$max}.");
                    }
                },
            ],
            'score' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $maxScores = [
                        'activity' => 25,
                        'quiz'     => 50,
                        'exam'     => 100,
                    ];
                    $type     = $this->input('component_type');
                    $maxScore = GradingConstants::maxScore($type);

                    if ($value > $maxScore) {
                        $fail("Score for a {$type} must not exceed {$maxScore}.");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_id.required'  => 'Enrollment is required.',
            'enrollment_id.exists'    => 'The selected enrollment does not exist.',
            'subject_id.required'     => 'Subject is required.',
            'subject_id.exists'       => 'The selected subject does not exist.',
            'semester.required'       => 'Semester is required.',
            'semester.in'             => 'Semester must be 1 or 2.',
            'period.required'         => 'Period is required.',
            'period.in'               => 'Period must be either midterm or final.',
            'component_type.required' => 'Component type is required.',
            'component_type.in'       => 'Component type must be activity, quiz, or exam.',
            'sequence_number.required'=> 'Sequence number is required.',
            'score.required'          => 'Score is required.',
            'score.min'               => 'Score must not be negative.',
        ];
    }
}
