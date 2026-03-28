<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get the admin ID from the route parameter
        $id = $this->route('id');

        return [
            'surname'        => ['sometimes', 'string', 'max:100'],
            'given_name'     => ['sometimes', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'username'       => ['sometimes', 'string', 'max:50', 'unique:admins,username,' . $id],
            'email'          => ['sometimes', 'email', 'max:150', 'unique:admins,email,' . $id],
            'contact_number' => ['sometimes', 'string', 'max:20'],
            'backup_email'   => [
                'sometimes',
                'email',
                'max:150',
                'different:email',
                function ($attribute, $value, $fail) {
                    if (\App\Models\Teacher::where('email', $value)->exists()) {
                        $fail('Backup email must not belong to an existing teacher account.');
                    }
                },
            ],
            'password' => [
                'sometimes',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique'        => 'That username is already taken.',
            'email.unique'           => 'That email is already in use.',
            'backup_email.different' => 'Backup email must be different from the primary email.',
            'password.confirmed'     => 'Passwords do not match.',
        ];
    }
}
