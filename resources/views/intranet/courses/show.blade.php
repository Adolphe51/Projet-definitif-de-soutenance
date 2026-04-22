@extends('layouts.app')

@section('title', 'Détails du cours - Intranet')

@section('content')
    <div class="container">
        <h1>Détails du cours</h1>

        <dl>
            <dt>Code</dt>
            <dd>{{ $course->course_code }}</dd>

            <dt>Titre</dt>
            <dd>{{ $course->title }}</dd>

            <dt>Description</dt>
            <dd>{{ $course->description }}</dd>

            <dt>Département</dt>
            <dd>{{ $course->department }}</dd>

            <dt>Crédits</dt>
            <dd>{{ $course->credits }}</dd>

            <dt>Semestre</dt>
            <dd>{{ $course->semester }}</dd>

            <dt>Capacité maximale</dt>
            <dd>{{ $course->max_students }}</dd>

            <dt>Statut</dt>
            <dd>{{ ucfirst($course->status) }}</dd>
        </dl>

        <h2>Inscriptions</h2>
        <ul>
            @foreach($course->enrollments as $enrollment)
                <li>{{ $enrollment->student->first_name ?? 'Étudiant supprimé' }} {{ $enrollment->student->last_name ?? '' }} -
                    {{ ucfirst($enrollment->status) }}
                </li>
            @endforeach
        </ul>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
            <a href="{{ route('intranet.courses.edit', $course) }}" class="button primary">Éditer</a>
            <a href="{{ route('intranet.courses.index') }}" class="button secondary">Retour</a>
        </div>
    </div>
@endsection