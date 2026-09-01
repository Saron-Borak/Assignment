<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SemesterRequest;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(): View
    {
        $semesters = Semester::withCount('classSections')
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('admin.semesters.index', compact('semesters'));
    }

    public function create(): View
    {
        return view('admin.semesters.create', ['semester' => new Semester]);
    }

    public function store(SemesterRequest $request): RedirectResponse
    {
        $this->persist(new Semester, $request->validated());

        return redirect()->route('admin.semesters.index')->with('success', 'Semester created.');
    }

    public function edit(Semester $semester): View
    {
        return view('admin.semesters.edit', compact('semester'));
    }

    public function update(SemesterRequest $request, Semester $semester): RedirectResponse
    {
        $this->persist($semester, $request->validated());

        return redirect()->route('admin.semesters.index')->with('success', 'Semester updated.');
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        if ($semester->classSections()->exists()) {
            return back()->with('error', 'This semester still has class sections attached.');
        }

        $semester->delete();

        return redirect()->route('admin.semesters.index')->with('success', 'Semester deleted.');
    }

    /**
     * Exactly one semester may be active at a time, so activating one stands the
     * others down in the same transaction.
     */
    protected function persist(Semester $semester, array $data): void
    {
        DB::transaction(function () use ($semester, $data) {
            $semester->fill($data)->save();

            if ($semester->is_active) {
                Semester::where('id', '!=', $semester->id)->update(['is_active' => false]);
            }
        });
    }
}
