@extends('layouts.app')

@section('title', 'Présences - Intranet')

@section('content')
    <div class="container">
        <h1>Présences</h1>

        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Cours</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->enrollment->student->first_name ?? 'N/A' }}
                            {{ $attendance->enrollment->student->last_name ?? '' }}</td>
                        <td>{{ $attendance->enrollment->course->name ?? 'N/A' }}</td>
                        <td>{{ optional($attendance->lecture_date)->format('Y-m-d') }}</td>
                        <td>{{ $attendance->status }}</td>
                        <td>{{ $attendance->notes ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $attendances->links() }}
    </div>
@endsection