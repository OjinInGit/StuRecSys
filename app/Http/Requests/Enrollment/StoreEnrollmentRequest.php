<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_number'                => ['required', 'string', 'max:20', 'unique:students,id_number'],
            'surname'                  => ['required', 'string', 'max:100'],
            'given_name'               => ['required', 'string', 'max:100'],
            'middle_initial'           => ['nullable', 'string', 'max:5'],
            'birthdate'                => ['required', 'date', 'before:today'],
            'age'                      => ['required', 'integer', 'min:1', 'max:20'],
            'gender'                   => ['required', 'in:Male,Female'],
            'emergency_contact_number' => ['required', 'string', 'max:20'],
            'grade_level_id'           => ['required', 'integer', 'exists:grade_levels,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_number.required'       => 'Student ID number is required.',
            'id_number.unique'         => 'That ID number is already assigned to another student.',
            'surname.required'         => 'Surname is required.',
            'given_name.required'      => 'Given name is required.',
            'birthdate.required'       => 'Birthdate is required.',
            'birthdate.before'         => 'Birthdate must be a date in the past.',
            'age.required'             => 'Age is required.',
            'gender.required'          => 'Gender is required.',
            'gender.in'                => 'Gender must be either Male or Female.',
            'emergency_contact_number.required' => 'Emergency contact number is required.',
            'grade_level_id.required'  => 'Grade level is required.',
            'grade_level_id.exists'    => 'The selected grade level does not exist.',
        ];
    }
}
