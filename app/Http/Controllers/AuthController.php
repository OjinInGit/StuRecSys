<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\AdminLoginRequest;
use App\Http\Requests\Auth\TeacherLoginRequest;
use App\Http\Requests\Auth\StudentLookupRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Teacher;
use App\Models\Student;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function adminLogin(AdminLoginRequest $request)
    {
        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return $this->forbiddenResponse('Invalid admin credentials.');
        }

        $token = $admin->createToken('admin-token', ['role:admin'])->plainTextToken;

        return $this->successResponse([
            'role'  => 'admin',
            'user'  => [
                'id'        => $admin->id,
                'full_name' => $admin->full_name,
                'username'  => $admin->username,
                'email'     => $admin->email,
            ],
            'token' => $token,
        ], 'Admin login successful.');
    }

    public function teacherLogin(TeacherLoginRequest $request)
    {
        $teacher = Teacher::where('username', $request->username)->first();

        if (!$teacher || !Hash::check($request->password, $teacher->password)) {
            return $this->forbiddenResponse('Invalid teacher credentials.');
        }

        $token = $teacher->createToken('teacher-token', ['role:teacher'])->plainTextToken;

        return $this->successResponse([
            'role'  => 'teacher',
            'user'  => [
                'id'        => $teacher->id,
                'full_name' => $teacher->full_name,
                'username'  => $teacher->username,
                'email'     => $teacher->email,
            ],
            'token' => $token,
        ], 'Teacher login successful.');
    }

    public function studentLookup(StudentLookupRequest $request)
    {
        $student = Student::where('id_number', $request->id_number)
            ->with([
                'enrollments.section.gradeLevel',
                'enrollments.academicYear',
                'enrollments.gradeSummaries.subject',
            ])
            ->first();

        if (!$student) {
            return $this->notFoundResponse('No student found with that ID number.');
        }

        return $this->successResponse($student, 'Student record found.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully.');
    }
}
