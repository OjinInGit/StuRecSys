<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;

class TeacherController extends Controller
{
    public function store(StoreTeacherRequest $request)
    {
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

    public function update(UpdateTeacherRequest $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $teacher->update($data);

        return response()->json([
            'message' => 'Teacher profile updated successfully.',
        ], 200);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();

        return response()->json([
            'message' => 'Teacher account deleted successfully.',
        ], 200);
    }
}
