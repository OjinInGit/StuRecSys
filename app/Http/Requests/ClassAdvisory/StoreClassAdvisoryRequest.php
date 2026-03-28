<?php

namespace App\Http\Requests\ClassAdvisory;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassAdvisoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'A teacher must be selected.',
            'teacher_id.exists'   => 'The selected teacher does not exist.',
            'section_id.required' => 'A section must be selected.',
            'section_id.exists'   => 'The selected section does not exist.',
        ];
    }
}
