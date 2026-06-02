@extends('layouts.app')

@section('title', 'Modifier le service - Module de démonstration')
@section('page-title', 'Modifier un service')
@section('page-subtitle', 'Mise à jour d’un élément du catalogue métier avec conservation de la trace et du contexte de modification.')

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

            <form action="{{ route('intranet.courses.update', $course) }}" method="POST" class="intranet-form-grid">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="course_code">Code de référence</label>
                    <input type="text" name="course_code" id="course_code" value="{{ old('course_code', $course->course_code) }}" required>
                </div>
                <div class="form-group">
                    <label for="title">Titre</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description">{{ old('description', $course->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="department">Domaine</label>
                    <input type="text" name="department" id="department" value="{{ old('department', $course->department) }}" required>
                </div>
                <div class="form-group">
                    <label for="credits">Criticite</label>
                    <input type="number" name="credits" id="credits" value="{{ old('credits', $course->credits) }}" min="1" required>
                </div>
                <div class="form-group">
                    <label for="semester">Cycle</label>
                    <input type="text" name="semester" id="semester" value="{{ old('semester', $course->semester) }}" required>
                </div>
                <div class="form-group">
                    <label for="max_students">Capacité cible</label>
                    <input type="number" name="max_students" id="max_students" value="{{ old('max_students', $course->max_students) }}" min="1" required>
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select name="status" id="status">
                        <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ old('status', $course->status) === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <div class="intranet-page-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('intranet.courses.show', $course) }}" class="btn btn-secondary-outline">Retour</a>
                </div>
            </form>
        </div>

        <aside class="intranet-side-card">
            <div class="section-title">Point d’attention</div>
            <p>Les modifications textuelles sur un service sont très utiles pour illustrer comment CyberGuard collecte, filtre et analyse un changement potentiellement risqué.</p>
        </aside>
    </section>
@endsection
