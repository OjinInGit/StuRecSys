<?php

namespace App\Http\Controllers;

use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Http\Requests\Enrollment\ConfirmEnrollmentRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\AcademicYear;

class EnrollmentController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreEnrollmentRequest $request)
    {
        $activeYear = AcademicYear::where('is_active', 1)->first();

        if (!$activeYear) {
            return $this->validationErrorResponse(
                'No active academic year found. Please configure one first.'
            );
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
            return $this->validationErrorResponse(
                'All sections for this grade level are at full capacity.'
            );
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

            return $this->createdResponse([
                'student'    => $student,
                'enrollment' => [
                    'id'            => $enrollment->id,
                    'section'       => $assignedSection->name,
                    'grade_level'   => $assignedSection->gradeLevel->level,
                    'academic_year' => $activeYear->year_start
                                        . '–'
                                        . $activeYear->year_end,
                ],
            ], 'Student enrolled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(
                'Enrollment failed. Please try again.',
                $e->getMessage()
            );
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

        return $this->successResponse($enrollments, 'Enrollments retrieved successfully.');
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

        return $this->successResponse($enrollment, 'Enrollment retrieved successfully.');
    }

    public function confirm(ConfirmEnrollmentRequest $request, $id)
    {
        $enrollment = Enrollment::findOrFail($id);

        if ($enrollment->is_confirmed) {
            return $this->validationErrorResponse(
                'This enrollment has already been confirmed.'
            );
        }

        if (is_null($enrollment->gpa)) {
            return $this->validationErrorResponse(
                'GPA has not been computed yet. Ensure all grades are recorded.'
            );
        }

        $enrollment->update([
            'status'       => $request->status,
            'is_confirmed' => true,
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return $this->successResponse(
            $enrollment,
            'Enrollment confirmed. Student marked as ' . $request->status . '.'
        );
    }
}
