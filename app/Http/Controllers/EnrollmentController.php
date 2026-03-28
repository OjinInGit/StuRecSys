<?php

namespace App\Http\Controllers;

use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Http\Requests\Enrollment\ConfirmEnrollmentRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\AcademicYear;

class EnrollmentController extends Controller
{
    public function store(StoreEnrollmentRequest $request)
    {
        $activeYear = AcademicYear::where('is_active', 1)->first();

        if (!$activeYear) {
            return response()->json([
                'message' => 'No active academic year found. Please configure one first.',
            ], 422);
        }

        $availableSections = Section::where('grade_level_id', $request->grade_level_id)
            ->get()
            ->filter(function ($section) use ($activeYear) {
                $currentCount = Enrollment::where('section_id', $section->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->count();
                return $currentCount < $section->capacity;
            });

        if ($availableSections->isEmpty()) {
            return response()->json([
                'message' => 'All sections for this grade level are at full capacity.',
            ], 422);
        }

        $assignedSection = $availableSections->random();

        DB::beginTransaction();

        try {
            $student = Student::create([
                'id_number'                => $request->id_number,
                'surname'                  => $request->surname,
                'given_name'               => $request->given_name,
                'middle_initial'           => $request->middle_initial,
                'birthdate'                => $request->birthdate,
                'age'                      => $request->age,
                'gender'                   => $request->gender,
                'emergency_contact_number' => $request->emergency_contact_number,
            ]);

            $enrollment = Enrollment::create([
                'student_id'       => $student->id,
                'section_id'       => $assignedSection->id,
                'academic_year_id' => $activeYear->id,
                'enrolled_by'      => $request->user()->id,
                'status'           => 'active',
            ]);

            DB::commit();

            return response()->json([
                'message'    => 'Student enrolled successfully.',
                'student'    => $student,
                'enrollment' => [
                    'id'            => $enrollment->id,
                    'section'       => $assignedSection->name,
                    'grade_level'   => $assignedSection->gradeLevel->level,
                    'academic_year' => $activeYear->year_start . '–' . $activeYear->year_end,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Enrollment failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $enrollments = Enrollment::with([
            'student',
            'section.gradeLevel',
            'academicYear',
            'enrolledBy',
        ])->get();

        return response()->json([
            'enrollments' => $enrollments,
        ], 200);
    }

    public function show($id)
    {
        $enrollment = Enrollment::with([
            'student',
            'section.gradeLevel',
            'academicYear',
            'gradeSummaries.subject',
            'scoreRecords.subject',
        ])->findOrFail($id);

        return response()->json([
            'enrollment' => $enrollment,
        ], 200);
    }

    public function confirm(ConfirmEnrollmentRequest $request, $id)
    {
        $enrollment = Enrollment::findOrFail($id);

        if ($enrollment->is_confirmed) {
            return response()->json([
                'message' => 'This enrollment has already been confirmed.',
            ], 422);
        }

        if (is_null($enrollment->gpa)) {
            return response()->json([
                'message' => 'GPA has not been computed yet. Ensure all grades are recorded.',
            ], 422);
        }

        $enrollment->update([
            'status'       => $request->status,
            'is_confirmed' => true,
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'message'    => 'Enrollment confirmed. Student marked as ' . $request->status . '.',
            'enrollment' => $enrollment,
        ], 200);
    }
}
