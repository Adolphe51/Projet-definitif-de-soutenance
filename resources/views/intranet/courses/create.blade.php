@extends('layouts.app')

@section('title', 'Créer un service - Module de démonstration')
@section('page-title', 'Créer un service')
@section('page-subtitle', 'Ajout d’un nouveau point d’entrée métier dans un périmètre volontairement compact pour la soutenance.')

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

            <form action="{{ route('intranet.courses.store') }}" method="POST" class="intranet-form-grid">
                @csrf
                <div class="form-group">
                    <label for="course_code">Code de référence</label>
                    <input type="text" name="course_code" id="course_code" value="{{ old('course_code') }}" required>
                </div>
                <div class="form-group">
                    <label for="title">Titre</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="department">Domaine</label>
                    <input type="text" name="department" id="department" value="{{ old('department') }}" required>
                </div>
                <div class="form-group">
                    <label for="credits">Criticite</label>
                    <input type="number" name="credits" id="credits" value="{{ old('credits', 3) }}" min="1" required>
                </div>
                <div class="form-group">
                    <label for="semester">Cycle</label>
                    <input type="text" name="semester" id="semester" value="{{ old('semester') }}" required>
                </div>
                <div class="form-group">
                    <label for="max_students">Capacité cible</label>
                    <input type="number" name="max_students" id="max_students" value="{{ old('max_students', 30) }}" min="1" required>
                </div>
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select name="status" id="status">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>

                <div class="intranet-page-actions">
                    <button type="submit" class="btn btn-primary">Créer</button>
                    <a href="{{ route('intranet.courses.index') }}" class="btn btn-secondary-outline">Retour</a>
                </div>
            </form>
        </div>

        <aside class="intranet-side-card">
            <div class="section-title">Ce que le jury doit comprendre</div>
            <p>Un service métier est un bon support pour expliquer le lien entre donnée fonctionnelle, contenu libre, audit d’action et détection d’un comportement anormal.</p>
        </aside>
    </section>
@endsection
