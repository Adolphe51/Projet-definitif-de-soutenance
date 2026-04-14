@extends('layouts.app')

@section('title', 'Détails de l\'étudiant - Intranet')

@section('content')
    <div class="container">
        <h1>Détails de l'étudiant</h1>

        <dl>
            <dt>Identifiant</dt>
            <dd>{{ $student->student_id }}</dd>

            <dt>Nom</dt>
            <dd>{{ $student->first_name }} {{ $student->last_name }}</dd>

            <dt>Email</dt>
            <dd>{{ $student->email }}</dd>

            <dt>Téléphone</dt>
            <dd>{{ $student->phone }}</dd>

            <dt>Date de naissance</dt>
            <dd>{{ optional($student->date_of_birth)->format('Y-m-d') }}</dd>

            <dt>Adresse</dt>
            <dd>{{ $student->address }}</dd>

            <dt>Statut</dt>
            <dd>{{ ucfirst($student->status) }}</dd>
        </dl>

        <h2>Inscriptions</h2>
        <ul>
            @foreach($student->enrollments as $enrollment)
                <li>{{ $enrollment->course->title ?? 'Cours supprimé' }} - {{ ucfirst($enrollment->status) }}</li>
            @endforeach
        </ul>

        <p><a href="{{ route('intranet.students.index') }}">Retour à la liste</a></p>
    </div>
@endsection