<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function edit(Request $request, ClassSection $section): View
    {
        $section->load(['course', 'semester', 'lecturer.user']);

        $enrolled = $section->enrollments()
            ->with(['student.user', 'student.program'])
            ->get()
            ->sortBy('student.user.name');

        // Candidates are everyone not already on this roster, so the picker
        // cannot create a duplicate enrollment.
        $candidates = Student::with(['user', 'program'])
            ->whereNotIn('students.id', $enrolled->pluck('student_id'))
            ->search($request->string('q')->toString() ?: null)
            ->when($request->integer('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->orderBy(User::select('name')->whereColumn('users.id', 'students.user_id'))
            ->limit(100)
            ->get();

        return view('admin.enrollments.edit', [
            'section' => $section,
            'enrolled' => $enrolled,
            'candidates' => $candidates,
            'programs' => Program::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ClassSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $existing = $section->enrollments()->pluck('student_id')->flip();
        $now = now();

        $rows = collect($validated['student_ids'])
            ->unique()
            ->reject(fn (int $id) => $existing->has($id))
            ->map(fn (int $id) => [
                'class_section_id' => $section->id,
                'student_id' => $id,
                'status' => EnrollmentStatus::Enrolled->value,
                'enrolled_at' => $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values();

        if ($rows->isEmpty()) {
            return back()->with('warning', 'Those students are already on this roster.');
        }

        DB::transaction(fn () => Enrollment::insert($rows->all()));

        return back()->with('success', "Enrolled {$rows->count()} student(s).");
    }

    public function destroy(ClassSection $section, Enrollment $enrollment): RedirectResponse
    {
        // Guard against an id from another section being posted in.
        abort_unless($enrollment->class_section_id === $section->id, 404);

        if ($enrollment->student->attendanceRecords()
            ->whereHas('session', fn ($q) => $q->where('class_section_id', $section->id))
            ->exists()
        ) {
            // Attendance history must survive, so the student is marked dropped
            // rather than deleted.
            $enrollment->update(['status' => EnrollmentStatus::Dropped]);

            return back()->with('warning', 'This student has attendance history, so they were marked as dropped instead of removed.');
        }

        $enrollment->delete();

        return back()->with('success', 'Student removed from the roster.');
    }
}
