<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'surname'        => ['sometimes', 'string', 'max:100'],
            'given_name'     => ['sometimes', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'username'       => ['sometimes', 'string', 'max:50', 'unique:teachers,username,' . $id],
            'email'          => ['sometimes', 'email', 'max:150', 'unique:teachers,email,' . $id],
            'contact_number' => ['sometimes', 'string', 'max:20'],
            'password'       => [
                'sometimes',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'That username is already taken.',
            'email.unique'    => 'That email is already in use.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }
}
