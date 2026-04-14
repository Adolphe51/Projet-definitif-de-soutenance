@extends('layouts.app')

@section('title', 'Cours - Intranet')

@section('content')
    <div class="container">
        <h1>Cours</h1>
        <p><a class="button primary" href="{{ route('intranet.courses.create') }}">Créer un cours</a></p>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Titre</th>
                    <th>Département</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                    <tr>
                        <td>{{ $course->course_code }}</td>
                        <td>{{ $course->title }}</td>
                        <td>{{ $course->department }}</td>
                        <td>{{ ucfirst($course->status) }}</td>
                        <td>
                            <a class="button secondary" href="{{ route('intranet.courses.show', $course) }}">Voir</a>
                            <a class="button secondary" href="{{ route('intranet.courses.edit', $course) }}">Éditer</a>
                            <form action="{{ route('intranet.courses.destroy', $course) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button secondary"
                                    data-confirm="Supprimer ce cours ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $courses->links() }}
    </div>
@endsection