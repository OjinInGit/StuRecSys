<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassAdvisory\StoreClassAdvisoryRequest;
use App\Traits\ApiResponseTrait;
use App\Models\ClassAdvisory;
use App\Models\AcademicYear;

class ClassAdvisoryController extends Controller
{
    use ApiResponseTrait;

    public function store(StoreClassAdvisoryRequest $request)
    {
        $activeYear = AcademicYear::where('is_active', 1)->first();

        if (!$activeYear) {
            return $this->validationErrorResponse('No active academic year found.');
        }

        $sectionTaken = ClassAdvisory::where('section_id', $request->section_id)
            ->where('academic_year_id', $activeYear->id)
            ->exists();

        if ($sectionTaken) {
            return $this->validationErrorResponse(
                'This section already has a class adviser for the current academic year.'
            );
        }

        $teacherTaken = ClassAdvisory::where('teacher_id', $request->teacher_id)
            ->where('academic_year_id', $activeYear->id)
            ->exists();

        if ($teacherTaken) {
            return $this->validationErrorResponse(
                'This teacher is already assigned to a section for the current academic year.'
            );
        }

        $advisory = ClassAdvisory::create([
            'teacher_id'       => $request->teacher_id,
            'section_id'       => $request->section_id,
            'academic_year_id' => $activeYear->id,
            'assigned_by'      => $request->user()->id,
        ]);

        return $this->createdResponse(
            $advisory->load([
                'teacher',
                'section.gradeLevel',
                'academicYear',
            ]),
            'Teacher assigned to section successfully.'
        );
    }

    public function index()
    {
        $advisories = ClassAdvisory::with([
            'teacher',
            'section.gradeLevel',
            'academicYear',
            'assignedBy',
        ])->get();

        return $this->successResponse($advisories, 'Class advisories retrieved successfully.');
    }

    public function show($id)
    {
        $advisory = ClassAdvisory::with([
            'teacher',
            'section.gradeLevel',
            'academicYear',
            'assignedBy',
        ])->findOrFail($id);

        return $this->successResponse($advisory, 'Class advisory retrieved successfully.');
    }

    public function destroy($id)
    {
        $advisory = ClassAdvisory::findOrFail($id);
        $advisory->delete();

        return $this->successResponse(null, 'Class advisory removed successfully.');
    }
}
