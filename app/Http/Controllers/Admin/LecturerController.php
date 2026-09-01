<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LecturerRequest;
use App\Models\Faculty;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LecturerController extends Controller
{
    public function index(Request $request): View
    {
        $lecturers = Lecturer::with(['user', 'faculty'])
            ->withCount('classSections')
            ->search($request->string('q')->toString() ?: null)
            ->when($request->integer('faculty_id'), fn ($q, $id) => $q->where('faculty_id', $id))
            ->orderBy(User::select('name')->whereColumn('users.id', 'lecturers.user_id'))
            ->paginate(15)
            ->withQueryString();

        return view('admin.lecturers.index', [
            'lecturers' => $lecturers,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.lecturers.create', [
            'lecturer' => new Lecturer,
            'faculties' => Faculty::orderBy('name')->get(),
            'suggestedStaffNo' => $this->nextStaffNo(),
        ]);
    }

    public function store(LecturerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => UserRole::Lecturer,
                'is_active' => $data['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            $user->lecturer()->create([
                'faculty_id' => $data['faculty_id'],
                'staff_no' => $data['staff_no'],
                'title' => $data['title'] ?? null,
            ]);
        });

        return redirect()->route('admin.lecturers.index')->with('success', 'Lecturer account created.');
    }

    public function show(Lecturer $lecturer): View
    {
        $lecturer->load([
            'user',
            'faculty',
            'classSections.course',
            'classSections.semester',
            'classSections.schedules',
        ])->loadCount('classSections');

        return view('admin.lecturers.show', compact('lecturer'));
    }

    public function edit(Lecturer $lecturer): View
    {
        $lecturer->load('user');

        return view('admin.lecturers.edit', [
            'lecturer' => $lecturer,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function update(LecturerRequest $request, Lecturer $lecturer): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $lecturer) {
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

            $lecturer->user->update($account);

            $lecturer->update([
                'faculty_id' => $data['faculty_id'],
                'staff_no' => $data['staff_no'],
                'title' => $data['title'] ?? null,
            ]);
        });

        return redirect()->route('admin.lecturers.index')->with('success', 'Lecturer updated.');
    }

    public function destroy(Lecturer $lecturer): RedirectResponse
    {
        if ($lecturer->classSections()->exists()) {
            return back()->with('error', 'Reassign this lecturer\'s class sections before deleting them.');
        }

        // Removing the user cascades to the lecturer profile.
        $lecturer->user->delete();

        return redirect()->route('admin.lecturers.index')->with('success', 'Lecturer deleted.');
    }

    protected function nextStaffNo(): string
    {
        $count = Lecturer::count() + 1;

        return 'EAMU-L-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
