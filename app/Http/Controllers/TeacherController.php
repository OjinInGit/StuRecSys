<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\StoreTeacherRequest;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;

class TeacherController extends Controller
{
    use ApiResponseTrait;

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

        return $this->createdResponse([
            'id'        => $teacher->id,
            'full_name' => $teacher->full_name,
            'username'  => $teacher->username,
            'email'     => $teacher->email,
        ], 'Teacher account created successfully.');
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

        return $this->successResponse($teachers, 'Teachers retrieved successfully.');
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

        return $this->successResponse($teacher, 'Teacher retrieved successfully.');
    }

    public function update(UpdateTeacherRequest $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $data = $request->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $teacher->update($data);

        return $this->successResponse(null, 'Teacher profile updated successfully.');
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();

        return $this->successResponse(null, 'Teacher account deleted successfully.');
    }
}
