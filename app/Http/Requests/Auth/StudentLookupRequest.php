<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StudentLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_number' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_number.required' => 'Student ID number is required.',
        ];
    }
}
