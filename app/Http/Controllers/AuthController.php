<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;

class AuthController extends Controller
{
    // -------------------------------------------------------
    // ADMIN LOGIN
    // -------------------------------------------------------

    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid admin credentials.',
            ], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful.',
            'role'    => 'admin',
            'user'    => [
                'id'        => $admin->id,
                'full_name' => $admin->full_name,
                'username'  => $admin->username,
                'email'     => $admin->email,
            ],
            'token'   => $token,
        ], 200);
    }

    // -------------------------------------------------------
    // TEACHER LOGIN
    // -------------------------------------------------------

    public function teacherLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $teacher = Teacher::where('username', $request->username)->first();

        if (!$teacher || !Hash::check($request->password, $teacher->password)) {
            return response()->json([
                'message' => 'Invalid teacher credentials.',
            ], 401);
        }

        $token = $teacher->createToken('teacher-token')->plainTextToken;

        return response()->json([
            'message' => 'Teacher login successful.',
            'role'    => 'teacher',
            'user'    => [
                'id'        => $teacher->id,
                'full_name' => $teacher->full_name,
                'username'  => $teacher->username,
                'email'     => $teacher->email,
            ],
            'token'   => $token,
        ], 200);
    }

    // -------------------------------------------------------
    // STUDENT RECORD LOOKUP (no password — ID number only)
    // -------------------------------------------------------

    public function studentLookup(Request $request)
    {
        $request->validate([
            'id_number' => 'required|string',
        ]);

        $student = Student::where('id_number', $request->id_number)
            ->with([
                'enrollments.section.gradeLevel',
                'enrollments.academicYear',
                'enrollments.gradeSummaries.subject',
            ])
            ->first();

        if (!$student) {
            return response()->json([
                'message' => 'No student found with that ID number.',
            ], 404);
        }

        return response()->json([
            'message' => 'Student record found.',
            'student' => $student,
        ], 200);
    }

    // -------------------------------------------------------
    // LOGOUT (Admin and Teacher)
    // -------------------------------------------------------

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }
}
