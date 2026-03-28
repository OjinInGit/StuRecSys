<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ClassAdvisoryController;
use App\Http\Controllers\ScoreRecordController;
use App\Http\Controllers\GradeSummaryController;

// ============================================================
// PUBLIC ROUTES — No authentication required
// ============================================================

// Admin login
Route::post('/auth/admin/login', [AuthController::class, 'adminLogin'])
    ->name('auth.admin.login');

// Teacher login
Route::post('/auth/teacher/login', [AuthController::class, 'teacherLogin'])
    ->name('auth.teacher.login');

// Admin self-registration (enforces max 6 accounts inside controller)
Route::post('/auth/admin/register', [AdminController::class, 'register'])
    ->name('auth.admin.register');

// Student record lookup by ID number (no password required)
Route::post('/auth/student/lookup', [AuthController::class, 'studentLookup'])
    ->name('auth.student.lookup');


// ============================================================
// PROTECTED ROUTES — Requires valid Sanctum token
// ============================================================

Route::middleware('auth:sanctum')->group(function () {

    // ----------------------------------------------------------
    // SHARED — Both Admin and Teacher can access
    // ----------------------------------------------------------

    // Logout (Admin or Teacher)
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    // View grade summaries
    Route::get('/grade-summaries', [GradeSummaryController::class, 'index'])
        ->name('grade-summaries.index');

    Route::get('/grade-summaries/show', [GradeSummaryController::class, 'show'])
        ->name('grade-summaries.show');


    // ----------------------------------------------------------
    // ADMIN ONLY ROUTES
    // ----------------------------------------------------------

    Route::middleware('is_admin')->group(function () {

        // Admin profile management
        Route::get('/admins', [AdminController::class, 'index'])
            ->name('admins.index');

        Route::get('/admins/{id}', [AdminController::class, 'show'])
            ->name('admins.show');

        Route::put('/admins/{id}', [AdminController::class, 'update'])
            ->name('admins.update');

        // Teacher management
        Route::post('/teachers', [TeacherController::class, 'store'])
            ->name('teachers.store');

        Route::get('/teachers', [TeacherController::class, 'index'])
            ->name('teachers.index');

        Route::get('/teachers/{id}', [TeacherController::class, 'show'])
            ->name('teachers.show');

        Route::put('/teachers/{id}', [TeacherController::class, 'update'])
            ->name('teachers.update');

        Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])
            ->name('teachers.destroy');

        // Student management
        Route::get('/students', [StudentController::class, 'index'])
            ->name('students.index');

        Route::get('/students/{id}', [StudentController::class, 'show'])
            ->name('students.show');

        Route::put('/students/{id}', [StudentController::class, 'update'])
            ->name('students.update');

        Route::delete('/students/{id}', [StudentController::class, 'destroy'])
            ->name('students.destroy');

        // Enrollment management
        Route::post('/enrollments', [EnrollmentController::class, 'store'])
            ->name('enrollments.store');

        Route::get('/enrollments', [EnrollmentController::class, 'index'])
            ->name('enrollments.index');

        Route::get('/enrollments/{id}', [EnrollmentController::class, 'show'])
            ->name('enrollments.show');

        Route::patch('/enrollments/{id}/confirm', [EnrollmentController::class, 'confirm'])
            ->name('enrollments.confirm');

        // Class advisory management
        Route::post('/class-advisories', [ClassAdvisoryController::class, 'store'])
            ->name('class-advisories.store');

        Route::get('/class-advisories', [ClassAdvisoryController::class, 'index'])
            ->name('class-advisories.index');

        Route::get('/class-advisories/{id}', [ClassAdvisoryController::class, 'show'])
            ->name('class-advisories.show');

        Route::delete('/class-advisories/{id}', [ClassAdvisoryController::class, 'destroy'])
            ->name('class-advisories.destroy');
    });


    // ----------------------------------------------------------
    // TEACHER ONLY ROUTES
    // ----------------------------------------------------------

    Route::middleware('is_teacher')->group(function () {

        // View class list (enrollments under teacher's advisory section)
        Route::get('/teacher/class-list', function (\Illuminate\Http\Request $request) {
            $teacher = $request->user();

            $activeAdvisory = \App\Models\ClassAdvisory::where('teacher_id', $teacher->id)
                ->whereHas('academicYear', fn($q) => $q->where('is_active', 1))
                ->with([
                    'section.gradeLevel',
                    'section.enrollments.student',
                    'academicYear',
                ])
                ->first();

            if (!$activeAdvisory) {
                return response()->json([
                    'message' => 'No active class advisory found for this teacher.',
                ], 404);
            }

            return response()->json([
                'advisory' => [
                    'grade_level'   => $activeAdvisory->section->gradeLevel->level,
                    'section'       => $activeAdvisory->section->name,
                    'academic_year' => $activeAdvisory->academicYear->year_start
                                        . '–'
                                        . $activeAdvisory->academicYear->year_end,
                    'students'      => $activeAdvisory->section->enrollments
                        ->map(fn($enrollment) => [
                            'enrollment_id' => $enrollment->id,
                            'id_number'     => $enrollment->student->id_number,
                            'full_name'     => $enrollment->student->full_name,
                            'gender'        => $enrollment->student->gender,
                        ]),
                ],
            ], 200);
        })->name('teacher.class-list');

        // Score recording
        Route::post('/score-records', [ScoreRecordController::class, 'store'])
            ->name('score-records.store');

        Route::put('/score-records/{id}', [ScoreRecordController::class, 'update'])
            ->name('score-records.update');

        Route::get('/score-records', [ScoreRecordController::class, 'index'])
            ->name('score-records.index');
    });
});
