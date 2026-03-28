<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'surname'                  => ['sometimes', 'string', 'max:100'],
            'given_name'               => ['sometimes', 'string', 'max:100'],
            'middle_initial'           => ['nullable', 'string', 'max:5'],
            'birthdate'                => ['sometimes', 'date', 'before:today'],
            'age'                      => ['sometimes', 'integer', 'min:1', 'max:20'],
            'gender'                   => ['sometimes', 'in:Male,Female'],
            'emergency_contact_number' => ['sometimes', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'birthdate.before' => 'Birthdate must be a date in the past.',
            'gender.in'        => 'Gender must be either Male or Female.',
            'age.min'          => 'Age must be at least 1.',
            'age.max'          => 'Age must not exceed 20.',
        ];
    }
}
