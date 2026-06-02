@extends('layouts.app')

@section('title', 'Modifier l\'usager - Module de démonstration')
@section('page-title', 'Modifier un usager')
@section('page-subtitle', 'Mise à jour d’une fiche métier dans un parcours qui conserve la trace des changements et de leur contexte.')

@section('content')
    <section class="intranet-form-layout">
        <div class="intranet-form-card">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('intranet.students.update', $student) }}" method="POST" class="intranet-form-grid">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="student_id">Identifiant usager</label>
                    <input type="text" name="student_id" id="student_id" value="{{ old('student_id', $student->student_id) }}" required>
                </div>
                <div class="form-group">
                    <label for="first_name">Prénom</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $student->first_name) }}" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Nom</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $student->last_name) }}" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $student->email) }}" required>
                </div>
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone) }}">
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date de naissance</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="address">Adresse</label>
                    <textarea name="address" id="address">{{ old('address', $student->address) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select name="status" id="status">
                        <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ old('status', $student->status) === 'inactive' ? 'selected' : '' }}>Inactif</option>
                        <option value="graduated" {{ old('status', $student->status) === 'graduated' ? 'selected' : '' }}>Archive</option>
                    </select>
                </div>

                <div class="intranet-page-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('intranet.students.show', $student) }}" class="btn btn-secondary-outline">Retour</a>
                </div>
            </form>
        </div>

        <aside class="intranet-side-card">
            <div class="section-title">Point de démonstration</div>
            <p>Modifier le statut, l’adresse ou une donnée libre permet d’expliquer la chaîne : action métier, audit, puis investigation si le contenu devient suspect.</p>
        </aside>
    </section>
@endsection
