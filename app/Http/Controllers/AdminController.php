<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminController extends Controller
{
    public function register(RegisterAdminRequest $request)
    {
        if (Admin::count() >= 6) {
            return response()->json([
                'message' => 'Maximum number of admin accounts (6) has been reached.',
            ], 403);
        }

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

    public function update(UpdateAdminRequest $request, $id)
    {
        $admin = Admin::findOrFail($id);

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
