<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with([
            'enrollments.section.gradeLevel',
            'enrollments.academicYear',
        ])->get();

        return response()->json([
            'students' => $students,
        ], 200);
    }

    public function show($id)
    {
        $student = Student::with([
            'enrollments.section.gradeLevel',
            'enrollments.academicYear',
            'enrollments.gradeSummaries.subject',
        ])->findOrFail($id);

        return response()->json([
            'student' => $student,
        ], 200);
    }

    public function update(UpdateStudentRequest $request, $id)
    {
        $student = Student::findOrFail($id);
        $student->update($request->validated());

        return response()->json([
            'message' => 'Student profile updated successfully.',
        ], 200);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'message' => 'Student record deleted successfully.',
        ], 200);
    }
}
