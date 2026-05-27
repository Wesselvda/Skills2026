<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::withCount('chapters')
            ->orderBy('title')
            ->get();

        $deleteCourse = null;

        if ($request->filled('delete')) {
            $deleteCourse = Course::withCount('chapters')->find($request->integer('delete'));
        }

        return view('courses.index', [
            'title' => 'Course Management',
            'courses' => $courses,
            'deleteCourse' => $deleteCourse,
        ]);
    }

    public function create()
    {
        return view('courses.form', [
            'title' => 'Add course',
            'pageTitle' => 'Add course',
            'course' => new Course(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'difficulty_level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
        ]);

        Course::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'difficulty_level' => $data['difficulty_level'],
            'status' => 'draft',
            'created_date' => now(),
        ]);

        return redirect()->route('courses.index')->with('status', 'Course created.');
    }

    public function edit(Course $course)
    {
        return view('courses.form', [
            'title' => 'Update course',
            'pageTitle' => 'Update course',
            'course' => $course,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'difficulty_level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
        ]);

        $course->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'difficulty_level' => $data['difficulty_level'],
            'status' => $request->input('action') === 'archive' ? 'archived' : $course->status,
        ]);

        return redirect()->route('courses.index')->with('status', $request->input('action') === 'archive' ? 'Course deactivated.' : 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Course deleted.');
    }
}
