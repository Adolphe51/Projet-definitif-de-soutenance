@extends('layouts.app')

@section('title', 'Usagers - Mini site')
@section('page-title', 'Usagers')
@section('page-subtitle', 'Référentiel métier compact pour illustrer consultation sécurisée, modifications auditées et corrélation avec CyberGuard.')

@section('content')
    @php
        $activeCount = $students->getCollection()->where('status', 'active')->count();
        $inactiveCount = $students->getCollection()->where('status', 'inactive')->count();
    @endphp

    <section class="intranet-summary-grid">
        <div class="intranet-metric">
            <span>En vue</span>
            <strong>{{ $students->total() }}</strong>
            <p class="intranet-empty-text">usager(s) recensé(s) dans ce jeu de démonstration.</p>
        </div>
        <div class="intranet-metric">
            <span>Actifs</span>
            <strong>{{ $activeCount }}</strong>
            <p class="intranet-empty-text">profils immédiatement exploitables pour la démonstration.</p>
        </div>
        <div class="intranet-metric">
            <span>À surveiller</span>
            <strong>{{ $inactiveCount }}</strong>
            <p class="intranet-empty-text">statuts plus parlants pour expliquer les changements audités.</p>
        </div>
    </section>

    <section class="card intranet-panel intranet-table-shell">
        <div class="intranet-toolbar">
            <div class="intranet-toolbar-copy">
                <div class="section-title">Répertoire des usagers</div>
                <p>Chaque action sur ces fiches peut être auditée puis corrélée à une alerte si un comportement suspect est détecté.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('intranet.students.create') }}">Créer un usager</a>
        </div>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        <table class="intranet-table">
            <thead>
                <tr>
                    <th>Identifiant</th>
                    <th>Usager</th>
                    <th>Contact</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php
                        $statusBadge = match ($student->status) {
                            'active' => 'success',
                            'inactive' => 'warning',
                            'graduated' => 'info',
                            default => 'info',
                        };
                    @endphp
                    <tr>
                        <td class="mono">{{ $student->student_id }}</td>
                        <td>
                            <div class="intranet-table-main">
                                <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                <small>{{ $student->address }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="intranet-table-main">
                                <strong>{{ $student->email }}</strong>
                                <small>{{ $student->phone ?: 'Téléphone non renseigné' }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $statusBadge }}">
                                {{ match ($student->status) {
                                    'active' => 'ACTIF',
                                    'inactive' => 'INACTIF',
                                    'graduated' => 'ARCHIVE',
                                    default => strtoupper($student->status),
                                } }}
                            </span>
                        </td>
                        <td>
                            <div class="intranet-actions">
                                <a class="btn btn-secondary-outline btn-sm" href="{{ route('intranet.students.show', $student) }}">Voir</a>
                                <a class="btn btn-secondary-outline btn-sm" href="{{ route('intranet.students.edit', $student) }}">Éditer</a>
                                <form action="{{ route('intranet.students.destroy', $student) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Supprimer cet usager ?">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">👤</div>
                                <p class="empty-state-title">Aucun usager</p>
                                <p class="empty-state-text">Ajoute une première fiche pour alimenter le mini site.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="intranet-pagination">
            {{ $students->links() }}
        </div>
    </section>
@endsection
