<?php

namespace App\Http\Requests\ScoreRecord;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ScoreRecord;

class UpdateScoreRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Retrieve the existing score record to get its max_score
        $scoreRecord = ScoreRecord::find($this->route('id'));
        $maxScore    = $scoreRecord ? $scoreRecord->max_score : 100;

        return [
            'score' => ['required', 'numeric', 'min:0', 'max:' . $maxScore],
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => 'Score is required.',
            'score.numeric'  => 'Score must be a number.',
            'score.min'      => 'Score must not be negative.',
            'score.max'      => 'Score exceeds the maximum allowed for this component.',
        ];
    }
}
