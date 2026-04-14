<?php

namespace App\Http\Controllers\Intranet;

use App\Events\IntranetDataChanged;
use App\Http\Controllers\Controller;
use App\Models\Intranet\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::orderBy('last_name')->paginate(15);

        return view('intranet.students.index', compact('students'));
    }

    public function create()
    {
        return view('intranet.students.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|string|max:20|unique:intranet_students,student_id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:intranet_students,email',
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,graduated',
        ]);

        $data['id'] = Str::uuid()->toString();

        $student = Student::create($data);

        event(new IntranetDataChanged('student', 'create', $student->toArray()));

        return Redirect::route('intranet.students.index')->with('success', 'Étudiant créé avec succès.');
    }

    public function show(string $id)
    {
        $student = Student::with(['enrollments.course', 'messages'])->findOrFail($id);

        return view('intranet.students.show', compact('student'));
    }

    public function edit(string $id)
    {
        $student = Student::findOrFail($id);

        return view('intranet.students.edit', compact('student'));
    }

    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $data = $request->validate([
            'student_id' => 'required|string|max:20|unique:intranet_students,student_id,' . $student->id . ',id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:intranet_students,email,' . $student->id . ',id',
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,graduated',
        ]);

        $student->update($data);

        event(new IntranetDataChanged('student', 'update', $student->toArray()));

        return Redirect::route('intranet.students.index')->with('success', 'Étudiant mis à jour.');
    }

    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);

        $student->delete();

        event(new IntranetDataChanged('student', 'delete', ['id' => $id]));

        return Redirect::route('intranet.students.index')->with('success', 'Étudiant supprimé.');
    }
}
