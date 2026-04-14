<?php

namespace App\Http\Controllers\Intranet;

use App\Events\IntranetDataChanged;
use App\Http\Controllers\Controller;
use App\Models\Intranet\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('course_code')->paginate(15);

        return view('intranet.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('intranet.courses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_code' => 'required|string|max:20|unique:intranet_courses,course_code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department' => 'required|string|max:100',
            'credits' => 'required|integer|min:1|max:10',
            'semester' => 'required|string|max:10',
            'max_students' => 'required|integer|min:1|max:200',
            'status' => 'required|in:active,inactive',
        ]);

        $data['id'] = Str::uuid()->toString();

        $course = Course::create($data);

        event(new IntranetDataChanged('course', 'create', $course->toArray()));

        return Redirect::route('intranet.courses.index')->with('success', 'Cours créé avec succès.');
    }

    public function show(string $id)
    {
        $course = Course::with('enrollments.student')->findOrFail($id);

        return view('intranet.courses.show', compact('course'));
    }

    public function edit(string $id)
    {
        $course = Course::findOrFail($id);

        return view('intranet.courses.edit', compact('course'));
    }

    public function update(Request $request, string $id)
    {
        $course = Course::findOrFail($id);

        $data = $request->validate([
            'course_code' => 'required|string|max:20|unique:intranet_courses,course_code,' . $course->id . ',id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department' => 'required|string|max:100',
            'credits' => 'required|integer|min:1|max:10',
            'semester' => 'required|string|max:10',
            'max_students' => 'required|integer|min:1|max:200',
            'status' => 'required|in:active,inactive',
        ]);

        $course->update($data);

        event(new IntranetDataChanged('course', 'update', $course->toArray()));

        return Redirect::route('intranet.courses.index')->with('success', 'Cours mis à jour.');
    }

    public function destroy(string $id)
    {
        $course = Course::findOrFail($id);

        $course->delete();

        event(new IntranetDataChanged('course', 'delete', ['id' => $id]));

        return Redirect::route('intranet.courses.index')->with('success', 'Cours supprimé.');
    }
}
