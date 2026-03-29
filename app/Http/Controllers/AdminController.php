<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Constants\GradingConstants;
class AdminController extends Controller
{
    use ApiResponseTrait;

    public function register(RegisterAdminRequest $request)
    {
        if (Admin::count() >= GradingConstants::MAX_ADMIN_ACCOUNTS) {
            return $this->forbiddenResponse(
                'Maximum number of admin accounts (' . GradingConstants::MAX_ADMIN_ACCOUNTS . ') has been reached.'
            );
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

        return $this->createdResponse([
            'id'        => $admin->id,
            'full_name' => $admin->full_name,
            'username'  => $admin->username,
            'email'     => $admin->email,
        ], 'Admin account created successfully.');
    }

    public function index()
    {
        $admins = Admin::select(
            'id', 'surname', 'given_name', 'middle_initial',
            'username', 'email', 'contact_number', 'backup_email',
            'created_at'
        )->get();

        return $this->successResponse($admins, 'Admins retrieved successfully.');
    }

    public function show($id)
    {
        $admin = Admin::select(
            'id', 'surname', 'given_name', 'middle_initial',
            'username', 'email', 'contact_number', 'backup_email',
            'created_at'
        )->findOrFail($id);

        return $this->successResponse($admin, 'Admin retrieved successfully.');
    }

    public function update(UpdateAdminRequest $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return $this->successResponse(null, 'Admin profile updated successfully.');
    }
}
