<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'surname'        => ['required', 'string', 'max:100'],
            'given_name'     => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'username'       => ['required', 'string', 'max:50', 'unique:admins,username'],
            'email'          => ['required', 'email', 'max:150', 'unique:admins,email'],
            'contact_number' => ['required', 'string', 'max:20'],
            'backup_email'   => [
                'required',
                'email',
                'max:150',
                // Must not be the same as the primary email
                'different:email',
                // Must not belong to any existing teacher
                function ($attribute, $value, $fail) {
                    if (\App\Models\Teacher::where('email', $value)->exists()) {
                        $fail('Backup email must not belong to an existing teacher account.');
                    }
                },
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'surname.required'        => 'Surname is required.',
            'given_name.required'     => 'Given name is required.',
            'username.required'       => 'Username is required.',
            'username.unique'         => 'That username is already taken.',
            'email.required'          => 'Email is required.',
            'email.unique'            => 'That email is already in use.',
            'backup_email.required'   => 'Backup email is required.',
            'backup_email.different'  => 'Backup email must be different from the primary email.',
            'contact_number.required' => 'Contact number is required.',
            'password.required'       => 'Password is required.',
            'password.confirmed'      => 'Passwords do not match.',
        ];
    }
}
