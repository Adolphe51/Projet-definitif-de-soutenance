@extends('layouts.app')

@section('title', 'Créer un cours - Intranet')

@section('content')
    <div class="container">
        <h1>Créer un cours</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('intranet.courses.store') }}" method="POST">
            @csrf
            <div>
                <label for="course_code">Code du cours</label>
                <input type="text" name="course_code" id="course_code" value="{{ old('course_code') }}" required>
            </div>
            <div>
                <label for="title">Titre</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required>
            </div>
            <div>
                <label for="description">Description</label>
                <textarea name="description" id="description">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="department">Département</label>
                <input type="text" name="department" id="department" value="{{ old('department') }}" required>
            </div>
            <div>
                <label for="credits">Crédits</label>
                <input type="number" name="credits" id="credits" value="{{ old('credits', 3) }}" min="1" required>
            </div>
            <div>
                <label for="semester">Semestre</label>
                <input type="text" name="semester" id="semester" value="{{ old('semester') }}" required>
            </div>
            <div>
                <label for="max_students">Capacité maximale</label>
                <input type="number" name="max_students" id="max_students" value="{{ old('max_students', 30) }}" min="1"
                    required>
            </div>
            <div>
                <label for="status">Statut</label>
                <select name="status" id="status">
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div>
                <button type="submit">Créer</button>
                <a href="{{ route('intranet.courses.index') }}">Retour</a>
            </div>
        </form>
    </div>
@endsection