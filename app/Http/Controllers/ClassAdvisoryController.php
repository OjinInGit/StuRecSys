<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassAdvisory;
use App\Models\AcademicYear;

class ClassAdvisoryController extends Controller
{
    // -------------------------------------------------------
    // ASSIGN A TEACHER TO A SECTION (Admin only)
    // -------------------------------------------------------

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,id',
            'section_id' => 'required|integer|exists:sections,id',
        ]);

        $activeYear = AcademicYear::where('is_active', 1)->first();

        if (!$activeYear) {
            return response()->json([
                'message' => 'No active academic year found.',
            ], 422);
        }

        // Check if section already has an adviser this year
        $sectionTaken = ClassAdvisory::where('section_id', $request->section_id)
            ->where('academic_year_id', $activeYear->id)
            ->exists();

        if ($sectionTaken) {
            return response()->json([
                'message' => 'This section already has a class adviser for the current academic year.',
            ], 422);
        }

        // Check if teacher is already assigned to another section this year
        $teacherTaken = ClassAdvisory::where('teacher_id', $request->teacher_id)
            ->where('academic_year_id', $activeYear->id)
            ->exists();

        if ($teacherTaken) {
            return response()->json([
                'message' => 'This teacher is already assigned to a section for the current academic year.',
            ], 422);
        }

        $advisory = ClassAdvisory::create([
            'teacher_id'       => $request->teacher_id,
            'section_id'       => $request->section_id,
            'academic_year_id' => $activeYear->id,
            'assigned_by'      => $request->user()->id,
        ]);

        return response()->json([
            'message'  => 'Teacher assigned to section successfully.',
            'advisory' => $advisory->load([
                'teacher',
                'section.gradeLevel',
                'academicYear',
            ]),
        ], 201);
    }

    // -------------------------------------------------------
    // GET ALL CLASS ADVISORIES
    // -------------------------------------------------------

    public function index()
    {
        $advisories = ClassAdvisory::with([
            'teacher',
            'section.gradeLevel',
            'academicYear',
            'assignedBy',
        ])->get();

        return response()->json([
            'advisories' => $advisories,
        ], 200);
    }

    // -------------------------------------------------------
    // GET A SINGLE CLASS ADVISORY
    // -------------------------------------------------------

    public function show($id)
    {
        $advisory = ClassAdvisory::with([
            'teacher',
            'section.gradeLevel',
            'academicYear',
            'assignedBy',
        ])->findOrFail($id);

        return response()->json([
            'advisory' => $advisory,
        ], 200);
    }

    // -------------------------------------------------------
    // UNASSIGN A TEACHER FROM A SECTION (Admin only)
    // -------------------------------------------------------

    public function destroy($id)
    {
        $advisory = ClassAdvisory::findOrFail($id);
        $advisory->delete();

        return response()->json([
            'message' => 'Class advisory removed successfully.',
        ], 200);
    }
}
