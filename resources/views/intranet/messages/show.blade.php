@extends('layouts.app')

@section('title', 'Voir le message - Intranet')

@section('content')
    <div class="container">
        <h1>Détails du message</h1>

        <dl>
            <dt>Expéditeur</dt>
            <dd>{{ $message->sender->first_name ?? 'N/A' }} {{ $message->sender->last_name ?? '' }}</dd>

            <dt>Destinataire</dt>
            <dd>{{ $message->recipient->first_name ?? 'N/A' }} {{ $message->recipient->last_name ?? '' }}</dd>

            <dt>Sujet</dt>
            <dd>{{ $message->subject }}</dd>

            <dt>Contenu</dt>
            <dd>{{ $message->body }}</dd>

            <dt>Lu</dt>
            <dd>{{ $message->is_read ? 'Oui' : 'Non' }}</dd>
        </dl>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
            <a href="{{ route('intranet.messages.edit', $message) }}" class="button primary">Éditer</a>
            <a href="{{ route('intranet.messages.index') }}" class="button secondary">Retour</a>
        </div>
    </div>
@endsection