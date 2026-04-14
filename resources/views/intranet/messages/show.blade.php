@extends('layouts.app')

@section('title', 'Voir le message - Intranet')

@section('content')
    <div class="container">
        <h1>Détails du message</h1>

        <p><strong>Expéditeur :</strong> {{ $message->sender->first_name ?? 'N/A' }} {{ $message->sender->last_name ?? '' }}
        </p>
        <p><strong>Destinataire :</strong> {{ $message->recipient->first_name ?? 'N/A' }}
            {{ $message->recipient->last_name ?? '' }}</p>
        <p><strong>Sujet :</strong> {{ $message->subject }}</p>
        <p><strong>Contenu :</strong></p>
        <p>{{ $message->body }}</p>
        <p><strong>Lu :</strong> {{ $message->is_read ? 'Oui' : 'Non' }}</p>

        <div>
            <a href="{{ route('intranet.messages.edit', $message) }}">Éditer</a>
            <a href="{{ route('intranet.messages.index') }}">Retour</a>
        </div>
    </div>
@endsection