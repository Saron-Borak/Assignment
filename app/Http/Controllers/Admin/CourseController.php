<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseRequest;
use App\Models\Course;
use App\Models\Faculty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::with('faculty')
            ->withCount('classSections')
            ->search($request->string('q')->toString() ?: null)
            ->when($request->integer('faculty_id'), fn ($q, $id) => $q->where('faculty_id', $id))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.courses.index', [
            'courses' => $courses,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.courses.create', [
            'course' => new Course(['credit_hours' => 3]),
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        Course::create($request->validated());

        return redirect()->route('admin.courses.index')->with('success', 'Course created.');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', [
            'course' => $course,
            'faculties' => Faculty::orderBy('name')->get(),
        ]);
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $course->update($request->validated());

        return redirect()->route('admin.courses.index')->with('success', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->classSections()->exists()) {
            return back()->with('error', 'This course still has class sections. Remove them first.');
        }

        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted.');
    }
}
