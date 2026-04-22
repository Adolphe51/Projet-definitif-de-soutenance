@extends('layouts.app')

@section('title', 'Créer un étudiant - Intranet')

@section('content')
    <div class="container">
        <h1>Créer un étudiant</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('intranet.students.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="student_id">Identifiant étudiant</label>
                <input type="text" name="student_id" id="student_id" value="{{ old('student_id') }}" required>
            </div>
            <div class="form-group">
                <label for="first_name">Prénom</label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nom</label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}">
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date de naissance</label>
                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}">
            </div>
            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea name="address" id="address">{{ old('address') }}</textarea>
            </div>
            <div class="form-group">
                <label for="status">Statut</label>
                <select name="status" id="status">
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    <option value="graduated" {{ old('status') === 'graduated' ? 'selected' : '' }}>Diplômé</option>
                </select>
            </div>
            <div class="form-group" style="flex-direction: row; gap: 0.75rem;">
                <button type="submit" class="button primary">Créer</button>
                <a href="{{ route('intranet.students.index') }}" class="button secondary">Retour</a>
            </div>
        </form>
    </div>
@endsection