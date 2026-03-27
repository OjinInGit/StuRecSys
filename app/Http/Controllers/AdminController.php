<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminController extends Controller
{
    // -------------------------------------------------------
    // REGISTER A NEW ADMIN
    // -------------------------------------------------------

    public function register(Request $request)
    {
        // Enforce maximum of 6 admin accounts
        if (Admin::count() >= 6) {
            return response()->json([
                'message' => 'Maximum number of admin accounts (6) has been reached.',
            ], 403);
        }

        $request->validate([
            'surname'        => 'required|string|max:100',
            'given_name'     => 'required|string|max:100',
            'middle_initial' => 'nullable|string|max:5',
            'username'       => 'required|string|max:50|unique:admins,username',
            'email'          => 'required|email|max:150|unique:admins,email',
            'contact_number' => 'required|string|max:20',
            'backup_email'   => [
                'required',
                'email',
                'max:150',
                // Backup email must not match any existing teacher email
                function ($attribute, $value, $fail) {
                    if (\App\Models\Teacher::where('email', $value)->exists()) {
                        $fail('Backup email must not belong to an existing teacher.');
                    }
                },
            ],
            'password'       => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::create([
            'surname'        => $request->surname,
            'given_name'     => $request->given_name,
            'middle_initial' => $request->middle_initial,
            'username'       => $request->username,
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'backup_email'   => $request->backup_email,
            'password'       => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Admin account created successfully.',
            'admin'   => [
                'id'        => $admin->id,
                'full_name' => $admin->full_name,
                'username'  => $admin->username,
                'email'     => $admin->email,
            ],
        ], 201);
    }

    // -------------------------------------------------------
    // GET ALL ADMINS
    // -------------------------------------------------------

    public function index()
    {
        $admins = Admin::select(
            'id', 'surname', 'given_name', 'middle_initial',
            'username', 'email', 'contact_number', 'backup_email',
            'created_at'
        )->get();

        return response()->json([
            'admins' => $admins,
        ], 200);
    }

    // -------------------------------------------------------
    // GET A SINGLE ADMIN
    // -------------------------------------------------------

    public function show($id)
    {
        $admin = Admin::select(
            'id', 'surname', 'given_name', 'middle_initial',
            'username', 'email', 'contact_number', 'backup_email',
            'created_at'
        )->findOrFail($id);

        return response()->json([
            'admin' => $admin,
        ], 200);
    }

    // -------------------------------------------------------
    // UPDATE ADMIN PROFILE
    // -------------------------------------------------------

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'surname'        => 'sometimes|string|max:100',
            'given_name'     => 'sometimes|string|max:100',
            'middle_initial' => 'nullable|string|max:5',
            'username'       => 'sometimes|string|max:50|unique:admins,username,' . $id,
            'email'          => 'sometimes|email|max:150|unique:admins,email,' . $id,
            'contact_number' => 'sometimes|string|max:20',
            'backup_email'   => 'sometimes|email|max:150',
            'password'       => 'sometimes|string|min:8|confirmed',
        ]);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return response()->json([
            'message' => 'Admin profile updated successfully.',
        ], 200);
    }
}
