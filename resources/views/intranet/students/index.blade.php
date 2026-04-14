@extends('layouts.app')

@section('title', 'Étudiants - Intranet')

@section('content')
    <div class="container">
        <h1>Étudiants</h1>
        <p><a class="button primary" href="{{ route('intranet.students.create') }}">Créer un étudiant</a></p>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    <tr>
                        <td>{{ $student->student_id }}</td>
                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ ucfirst($student->status) }}</td>
                        <td>
                            <a class="button secondary" href="{{ route('intranet.students.show', $student) }}">Voir</a>
                            <a class="button secondary" href="{{ route('intranet.students.edit', $student) }}">Éditer</a>
                            <form action="{{ route('intranet.students.destroy', $student) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button secondary"
                                    data-confirm="Supprimer cet étudiant ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $students->links() }}
    </div>
@endsection