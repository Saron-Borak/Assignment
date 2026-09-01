<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::with(['user', 'program.faculty'])
            ->withCount(['enrollments' => fn ($q) => $q->active()])
            ->search($request->string('q')->toString() ?: null)
            ->when($request->integer('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->orderBy(User::select('name')->whereColumn('users.id', 'students.user_id'))
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'programs' => Program::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', [
            'student' => new Student(['intake_year' => (int) date('Y')]),
            'programs' => Program::with('faculty')->orderBy('name')->get(),
        ]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => UserRole::Student,
                'is_active' => $data['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            $user->student()->create([
                'program_id' => $data['program_id'],
                'student_no' => $data['student_no'],
                'intake_year' => $data['intake_year'],
                'status' => $data['status'],
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student account created.');
    }

    public function show(Student $student, AttendanceReportService $reports): View
    {
        $student->load(['user', 'program.faculty']);

        return view('admin.students.show', [
            'student' => $student,
            'stats' => $reports->studentOverall($student),
        ]);
    }

    public function edit(Student $student): View
    {
        $student->load('user');

        return view('admin.students.edit', [
            'student' => $student,
            'programs' => Program::with('faculty')->orderBy('name')->get(),
        ]);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $student) {
            $account = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            // A blank password field means "leave the current one alone".
            if (filled($data['password'] ?? null)) {
                $account['password'] = $data['password'];
            }

            $student->user->update($account);

            $student->update([
                'program_id' => $data['program_id'],
                'student_no' => $data['student_no'],
                'intake_year' => $data['intake_year'],
                'status' => $data['status'],
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        // Enrollments and attendance records cascade from the user row.
        $student->user->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted.');
    }
}
