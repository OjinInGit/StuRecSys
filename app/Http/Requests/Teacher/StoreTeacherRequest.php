<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreTeacherRequest extends FormRequest
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
            'username'       => ['required', 'string', 'max:50', 'unique:teachers,username'],
            'email'          => ['required', 'email', 'max:150', 'unique:teachers,email'],
            'contact_number' => ['required', 'string', 'max:20'],
            'password'       => [
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
            'contact_number.required' => 'Contact number is required.',
            'password.required'       => 'Password is required.',
            'password.confirmed'      => 'Passwords do not match.',
        ];
    }
}
