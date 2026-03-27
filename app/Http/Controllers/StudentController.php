<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    // -------------------------------------------------------
    // GET ALL STUDENTS
    // -------------------------------------------------------

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

    // -------------------------------------------------------
    // GET A SINGLE STUDENT
    // -------------------------------------------------------

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

    // -------------------------------------------------------
    // UPDATE STUDENT PROFILE (Admin only)
    // -------------------------------------------------------

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'surname'                  => 'sometimes|string|max:100',
            'given_name'               => 'sometimes|string|max:100',
            'middle_initial'           => 'nullable|string|max:5',
            'birthdate'                => 'sometimes|date',
            'age'                      => 'sometimes|integer|min:1|max:20',
            'gender'                   => 'sometimes|in:Male,Female',
            'emergency_contact_number' => 'sometimes|string|max:20',
        ]);

        $student->update($request->all());

        return response()->json([
            'message' => 'Student profile updated successfully.',
        ], 200);
    }

    // -------------------------------------------------------
    // DELETE A STUDENT (Admin only)
    // -------------------------------------------------------

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'message' => 'Student record deleted successfully.',
        ], 200);
    }
}
