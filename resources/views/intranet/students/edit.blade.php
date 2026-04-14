@extends('layouts.app')

@section('title', 'Modifier l\'étudiant - Intranet')

@section('content')
    <div class="container">
        <h1>Modifier l'étudiant</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('intranet.students.update', $student) }}" method="POST">
            @csrf
            @method('PUT')
            <div>
                <label for="student_id">Identifiant étudiant</label>
                <input type="text" name="student_id" id="student_id" value="{{ old('student_id', $student->student_id) }}"
                    required>
            </div>
            <div>
                <label for="first_name">Prénom</label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $student->first_name) }}"
                    required>
            </div>
            <div>
                <label for="last_name">Nom</label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $student->last_name) }}"
                    required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $student->email) }}" required>
            </div>
            <div>
                <label for="phone">Téléphone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone) }}">
            </div>
            <div>
                <label for="date_of_birth">Date de naissance</label>
                <input type="date" name="date_of_birth" id="date_of_birth"
                    value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}">
            </div>
            <div>
                <label for="address">Adresse</label>
                <textarea name="address" id="address">{{ old('address', $student->address) }}</textarea>
            </div>
            <div>
                <label for="status">Statut</label>
                <select name="status" id="status">
                    <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>Inactif
                    </option>
                    <option value="graduated" {{ old('status', $student->status) === 'graduated' ? 'selected' : '' }}>Diplômé
                    </option>
                </select>
            </div>
            <div>
                <button type="submit">Enregistrer</button>
                <a href="{{ route('intranet.students.index') }}">Retour</a>
            </div>
        </form>
    </div>
@endsection