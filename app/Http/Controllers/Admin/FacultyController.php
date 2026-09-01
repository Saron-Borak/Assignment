<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacultyRequest;
use App\Models\Faculty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacultyController extends Controller
{
    public function index(Request $request): View
    {
        $faculties = Faculty::withCount(['programs', 'courses', 'lecturers'])
            ->when($request->string('q')->toString(), fn ($q, $term) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%"))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.faculties.index', compact('faculties'));
    }

    public function create(): View
    {
        return view('admin.faculties.create', ['faculty' => new Faculty]);
    }

    public function store(FacultyRequest $request): RedirectResponse
    {
        Faculty::create($request->validated());

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty created.');
    }

    public function edit(Faculty $faculty): View
    {
        return view('admin.faculties.edit', compact('faculty'));
    }

    public function update(FacultyRequest $request, Faculty $faculty): RedirectResponse
    {
        $faculty->update($request->validated());

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty updated.');
    }

    public function destroy(Faculty $faculty): RedirectResponse
    {
        if ($faculty->programs()->exists() || $faculty->courses()->exists() || $faculty->lecturers()->exists()) {
            return back()->with('error', 'This faculty still has programs, courses or lecturers attached.');
        }

        $faculty->delete();

        return redirect()->route('admin.faculties.index')->with('success', 'Faculty deleted.');
    }
}
