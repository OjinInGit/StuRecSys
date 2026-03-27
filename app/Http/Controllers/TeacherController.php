<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;

class TeacherController extends Controller
{
    // -------------------------------------------------------
    // REGISTER A TEACHER (Admin only)
    // -------------------------------------------------------

    public function store(Request $request)
    {
        $request->validate([
            'surname'        => 'required|string|max:100',
            'given_name'     => 'required|string|max:100',
            'middle_initial' => 'nullable|string|max:5',
            'username'       => 'required|string|max:50|unique:teachers,username',
            'email'          => 'required|email|max:150|unique:teachers,email',
            'contact_number' => 'required|string|max:20',
            'password'       => 'required|string|min:8|confirmed',
        ]);

        $teacher = Teacher::create([
            'surname'        => $request->surname,
            'given_name'     => $request->given_name,
            'middle_initial' => $request->middle_initial,
            'username'       => $request->username,
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'password'       => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Teacher account created successfully.',
            'teacher' => [
                'id'        => $teacher->id,
                'full_name' => $teacher->full_name,
                'username'  => $teacher->username,
                'email'     => $teacher->email,
            ],
        ], 201);
    }

    // -------------------------------------------------------
    // GET ALL TEACHERS
    // -------------------------------------------------------

    public function index()
    {
        $teachers = Teacher::select(
            'id', 'surname', 'given_name', 'middle_initial',
            'username', 'email', 'contact_number', 'created_at'
        )
        ->with([
            'classAdvisories.section.gradeLevel',
            'classAdvisories.academicYear',
        ])
        ->get();

        return response()->json([
            'teachers' => $teachers,
        ], 200);
    }

    // -------------------------------------------------------
    // GET A SINGLE TEACHER
    // -------------------------------------------------------

    public function show($id)
    {
        $teacher = Teacher::select(
            'id', 'surname', 'given_name', 'middle_initial',
            'username', 'email', 'contact_number', 'created_at'
        )
        ->with([
            'classAdvisories.section.gradeLevel',
            'classAdvisories.academicYear',
        ])
        ->findOrFail($id);

        return response()->json([
            'teacher' => $teacher,
        ], 200);
    }

    // -------------------------------------------------------
    // UPDATE TEACHER PROFILE (Admin only)
    // -------------------------------------------------------

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $request->validate([
            'surname'        => 'sometimes|string|max:100',
            'given_name'     => 'sometimes|string|max:100',
            'middle_initial' => 'nullable|string|max:5',
            'username'       => 'sometimes|string|max:50|unique:teachers,username,' . $id,
            'email'          => 'sometimes|email|max:150|unique:teachers,email,' . $id,
            'contact_number' => 'sometimes|string|max:20',
            'password'       => 'sometimes|string|min:8|confirmed',
        ]);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $teacher->update($data);

        return response()->json([
            'message' => 'Teacher profile updated successfully.',
        ], 200);
    }

    // -------------------------------------------------------
    // DELETE A TEACHER (Admin only)
    // -------------------------------------------------------

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();

        return response()->json([
            'message' => 'Teacher account deleted successfully.',
        ], 200);
    }
}
