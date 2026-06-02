@extends('layouts.app')

@section('title', 'Services - Module de démonstration')
@section('page-title', 'Services')
@section('page-subtitle', 'Catalogue métier réduit servant de support aux scénarios démontrables et aux traces d’activité utiles à CyberGuard.')

@section('content')
    @php
        $activeCount = $courses->getCollection()->where('status', 'active')->count();
        $domainsCount = $courses->getCollection()->pluck('department')->unique()->count();
    @endphp

    <section class="intranet-summary-grid">
        <div class="intranet-metric">
            <span>Catalogue</span>
            <strong>{{ $courses->total() }}</strong>
            <p class="intranet-empty-text">service(s) visibles dans l'application métier.</p>
        </div>
        <div class="intranet-metric">
            <span>Actifs</span>
            <strong>{{ $activeCount }}</strong>
            <p class="intranet-empty-text">éléments prêts pour une démonstration fluide.</p>
        </div>
        <div class="intranet-metric">
            <span>Domaines</span>
            <strong>{{ $domainsCount }}</strong>
            <p class="intranet-empty-text">contextes métier distincts pour enrichir le discours.</p>
        </div>
    </section>

    <section class="card intranet-panel intranet-table-shell">
        <div class="intranet-toolbar">
            <div class="intranet-toolbar-copy">
                <div class="section-title">Catalogue des services</div>
                <p>Les descriptions et contenus publiés ici servent de support aux cas SQL Injection, XSS et à la narration de la supervision.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('intranet.courses.create') }}">Créer un service</a>
        </div>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        <table class="intranet-table">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Service</th>
                    <th>Domaine</th>
                    <th>Cycle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr>
                        <td class="mono">{{ $course->course_code }}</td>
                        <td>
                            <div class="intranet-table-main">
                                <strong>{{ $course->title }}</strong>
                                <small>{{ $course->description }}</small>
                            </div>
                        </td>
                        <td>{{ $course->department }}</td>
                        <td>{{ $course->semester }}</td>
                        <td><span class="badge badge-{{ $course->status === 'active' ? 'success' : 'warning' }}">{{ strtoupper($course->status) }}</span></td>
                        <td>
                            <div class="intranet-actions">
                                <a class="btn btn-secondary-outline btn-sm" href="{{ route('intranet.courses.show', $course) }}">Voir</a>
                                <a class="btn btn-secondary-outline btn-sm" href="{{ route('intranet.courses.edit', $course) }}">Éditer</a>
                                <form action="{{ route('intranet.courses.destroy', $course) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Supprimer ce service ?">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">🧩</div>
                                <p class="empty-state-title">Aucun service</p>
                                <p class="empty-state-text">Le catalogue métier s’affichera ici une fois alimenté.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="intranet-pagination">
            {{ $courses->links() }}
        </div>
    </section>
@endsection
