@extends('layouts.app')

@section('title', 'Créer un message - Intranet')

@section('content')
    <div class="container">
        <h1>Créer un message</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('intranet.messages.store') }}" method="POST">
            @csrf
            <div>
                <label for="sender_id">Expéditeur</label>
                <select name="sender_id" id="sender_id" required>
                    <option value="">Sélectionner</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('sender_id') === $student->id ? 'selected' : '' }}>
                            {{ $student->first_name }} {{ $student->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="recipient_id">Destinataire</label>
                <select name="recipient_id" id="recipient_id" required>
                    <option value="">Sélectionner</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('recipient_id') === $student->id ? 'selected' : '' }}>
                            {{ $student->first_name }} {{ $student->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="subject">Sujet</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required>
            </div>
            <div>
                <label for="body">Contenu</label>
                <textarea name="body" id="body" required>{{ old('body') }}</textarea>
            </div>
            <div>
                <label for="is_read">Lu</label>
                <input type="checkbox" name="is_read" id="is_read" value="1" {{ old('is_read') ? 'checked' : '' }}>
            </div>
            <div>
                <button type="submit">Envoyer</button>
                <a href="{{ route('intranet.messages.index') }}">Retour</a>
            </div>
        </form>
    </div>
@endsection