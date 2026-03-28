<?php

namespace App\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:promoted,retained'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'A confirmation status is required.',
            'status.in'       => 'Status must be either promoted or retained.',
        ];
    }
}
