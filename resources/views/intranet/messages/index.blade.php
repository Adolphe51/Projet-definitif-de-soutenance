@extends('layouts.app')

@section('title', 'Messages - Module de démonstration')
@section('page-title', 'Messages')
@section('page-subtitle', 'Flux de communication interne utilisé pour montrer contenu libre, lecture métier et détection contextualisée dans CyberGuard.')

@section('content')
    @php
        $unreadCount = $messages->getCollection()->where('is_read', false)->count();
    @endphp

    <section class="intranet-summary-grid">
        <div class="intranet-metric">
            <span>Messages</span>
            <strong>{{ $messages->total() }}</strong>
            <p class="intranet-empty-text">éléments disponibles dans la boîte de démonstration.</p>
        </div>
        <div class="intranet-metric">
            <span>Non lus</span>
            <strong>{{ $unreadCount }}</strong>
            <p class="intranet-empty-text">contenus utiles pour expliquer le traitement prioritaire.</p>
        </div>
        <div class="intranet-metric">
            <span>Lecture sécurité</span>
            <strong>XSS / SQLi</strong>
            <p class="intranet-empty-text">messages et HTML libre comme supports de détection.</p>
        </div>
    </section>

    <section class="card intranet-panel intranet-table-shell">
        <div class="intranet-toolbar">
            <div class="intranet-toolbar-copy">
                <div class="section-title">Messagerie interne</div>
                <p>Le contenu libre saisi ici peut être analysé puis lié à une alerte dans CyberGuard si un motif suspect est détecté.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('intranet.messages.create') }}">Créer un message</a>
        </div>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        <table class="intranet-table">
            <thead>
                <tr>
                    <th>Expéditeur</th>
                    <th>Destinataire</th>
                    <th>Sujet</th>
                    <th>État</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>{{ $message->sender->first_name ?? 'N/A' }} {{ $message->sender->last_name ?? '' }}</td>
                        <td>{{ $message->recipient->first_name ?? 'N/A' }} {{ $message->recipient->last_name ?? '' }}</td>
                        <td>
                            <div class="intranet-table-main">
                                <strong>{{ $message->subject }}</strong>
                                <small>{{ \Illuminate\Support\Str::limit($message->body, 90) }}</small>
                            </div>
                        </td>
                        <td><span class="badge badge-{{ $message->is_read ? 'success' : 'warning' }}">{{ $message->is_read ? 'LU' : 'NON LU' }}</span></td>
                        <td>
                            <div class="intranet-actions">
                                <a class="btn btn-secondary-outline btn-sm" href="{{ route('intranet.messages.show', $message) }}">Voir</a>
                                <a class="btn btn-secondary-outline btn-sm" href="{{ route('intranet.messages.edit', $message) }}">Éditer</a>
                                <form action="{{ route('intranet.messages.destroy', $message) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Supprimer ce message ?">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">✉️</div>
                                <p class="empty-state-title">Aucun message</p>
                                <p class="empty-state-text">Les échanges internes de démonstration apparaîtront ici.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="intranet-pagination">
            {{ $messages->links() }}
        </div>
    </section>
@endsection
