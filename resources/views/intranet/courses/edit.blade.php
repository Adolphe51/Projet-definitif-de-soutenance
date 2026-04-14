@extends('layouts.app')

@section('title', 'Modifier le cours - Intranet')

@section('content')
    <div class="container">
        <h1>Modifier le cours</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('intranet.courses.update', $course) }}" method="POST">
            @csrf
            @method('PUT')
            <div>
                <label for="course_code">Code du cours</label>
                <input type="text" name="course_code" id="course_code"
                    value="{{ old('course_code', $course->course_code) }}" required>
            </div>
            <div>
                <label for="title">Titre</label>
                <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}" required>
            </div>
            <div>
                <label for="description">Description</label>
                <textarea name="description" id="description">{{ old('description', $course->description) }}</textarea>
            </div>
            <div>
                <label for="department">Département</label>
                <input type="text" name="department" id="department" value="{{ old('department', $course->department) }}"
                    required>
            </div>
            <div>
                <label for="credits">Crédits</label>
                <input type="number" name="credits" id="credits" value="{{ old('credits', $course->credits) }}" min="1"
                    required>
            </div>
            <div>
                <label for="semester">Semestre</label>
                <input type="text" name="semester" id="semester" value="{{ old('semester', $course->semester) }}" required>
            </div>
            <div>
                <label for="max_students">Capacité maximale</label>
                <input type="number" name="max_students" id="max_students"
                    value="{{ old('max_students', $course->max_students) }}" min="1" required>
            </div>
            <div>
                <label for="status">Statut</label>
                <select name="status" id="status">
                    <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ old('status', $course->status) === 'inactive' ? 'selected' : '' }}>Inactif
                    </option>
                </select>
            </div>
            <div>
                <button type="submit">Enregistrer</button>
                <a href="{{ route('intranet.courses.index') }}">Retour</a>
            </div>
        </form>
    </div>
@endsection