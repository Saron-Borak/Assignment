<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProgramRequest;
use App\Models\Faculty;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $programs = Program::with('faculty')
            ->withCount('students')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('admin.programs.create', [
            'program' => new Program,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        Program::create($request->validated());

        return redirect()->route('admin.programs.index')->with('success', 'Program created.');
    }

    public function edit(Program $program): View
    {
        return view('admin.programs.edit', [
            'program' => $program,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        $program->update($request->validated());

        return redirect()->route('admin.programs.index')->with('success', 'Program updated.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        if ($program->students()->exists()) {
            return back()->with('error', 'This program still has students enrolled.');
        }

        $program->delete();

        return redirect()->route('admin.programs.index')->with('success', 'Program deleted.');
    }
}
