@extends('layouts.app')

@section('title', 'Inscriptions - Intranet')

@section('content')
    <div class="container">
        <h1>Inscriptions</h1>

        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Cours</th>
                    <th>Semestre</th>
                    <th>Note</th>
                    <th>Score final</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrollments as $enrollment)
                    <tr>
                        <td>{{ $enrollment->student->first_name ?? 'N/A' }} {{ $enrollment->student->last_name ?? '' }}</td>
                        <td>{{ $enrollment->course->name ?? 'N/A' }}</td>
                        <td>{{ $enrollment->semester }}</td>
                        <td>{{ $enrollment->grade ?? 'N/A' }}</td>
                        <td>{{ $enrollment->final_score !== null ? number_format($enrollment->final_score, 2) : 'N/A' }}</td>
                        <td>{{ $enrollment->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $enrollments->links() }}
    </div>
@endsection