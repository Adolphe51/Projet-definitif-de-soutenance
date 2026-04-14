@extends('layouts.app')

@section('title', 'Messages - Intranet')

@section('content')
    <div class="container">
        <h1>Messages</h1>
        <p><a class="button primary" href="{{ route('intranet.messages.create') }}">Créer un message</a></p>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Expéditeur</th>
                    <th>Destinataire</th>
                    <th>Sujet</th>
                    <th>Lu</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $message)
                    <tr>
                        <td>{{ $message->sender->first_name ?? 'N/A' }} {{ $message->sender->last_name ?? '' }}</td>
                        <td>{{ $message->recipient->first_name ?? 'N/A' }} {{ $message->recipient->last_name ?? '' }}</td>
                        <td>{{ $message->subject }}</td>
                        <td>{{ $message->is_read ? 'Oui' : 'Non' }}</td>
                        <td>
                            <a class="button secondary" href="{{ route('intranet.messages.show', $message) }}">Voir</a>
                            <a class="button secondary" href="{{ route('intranet.messages.edit', $message) }}">Éditer</a>
                            <form action="{{ route('intranet.messages.destroy', $message) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button secondary"
                                    data-confirm="Supprimer ce message ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $messages->links() }}
    </div>
@endsection