<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\UpdateStudentRequest;
use App\Traits\ApiResponseTrait;
use App\Models\Student;

class StudentController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $students = Student::with([
            'enrollments.section.gradeLevel',
            'enrollments.academicYear',
        ])->get();

        return $this->successResponse($students, 'Students retrieved successfully.');
    }

    public function show($id)
    {
        $student = Student::with([
            'enrollments.section.gradeLevel',
            'enrollments.academicYear',
            'enrollments.gradeSummaries.subject',
        ])->findOrFail($id);

        return $this->successResponse($student, 'Student retrieved successfully.');
    }

    public function update(UpdateStudentRequest $request, $id)
    {
        $student = Student::findOrFail($id);
        $student->update($request->validated());

        return $this->successResponse(null, 'Student profile updated successfully.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return $this->successResponse(null, 'Student record deleted successfully.');
    }
}
