<?php

namespace App\Http\Requests\GradeSummary;

use Illuminate\Foundation\Http\FormRequest;

class ShowGradeSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'subject_id'    => ['required', 'integer', 'exists:subjects,id'],
            'semester'      => ['required', 'integer', 'in:1,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_id.required' => 'Enrollment is required.',
            'enrollment_id.exists'   => 'The selected enrollment does not exist.',
            'subject_id.required'    => 'Subject is required.',
            'subject_id.exists'      => 'The selected subject does not exist.',
            'semester.required'      => 'Semester is required.',
            'semester.in'            => 'Semester must be 1 or 2.',
        ];
    }
}
