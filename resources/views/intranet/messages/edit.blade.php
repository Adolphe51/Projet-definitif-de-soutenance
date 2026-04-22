@extends('layouts.app')

@section('title', 'Modifier le message - Intranet')

@section('content')
    <div class="container">
        <h1>Modifier le message</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('intranet.messages.update', $message) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="sender_id">Expéditeur</label>
                <select name="sender_id" id="sender_id" required>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('sender_id', $message->sender_id) === $student->id ? 'selected' : '' }}>{{ $student->first_name }} {{ $student->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="recipient_id">Destinataire</label>
                <select name="recipient_id" id="recipient_id" required>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('recipient_id', $message->recipient_id) === $student->id ? 'selected' : '' }}>{{ $student->first_name }} {{ $student->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="subject">Sujet</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject', $message->subject) }}" required>
            </div>
            <div class="form-group">
                <label for="body">Contenu</label>
                <textarea name="body" id="body" required>{{ old('body', $message->body) }}</textarea>
            </div>
            <div class="form-group">
                <label for="is_read">Lu</label>
                <input type="checkbox" name="is_read" id="is_read" value="1" {{ old('is_read', $message->is_read) ? 'checked' : '' }}>
            </div>
            <div class="form-group" style="flex-direction: row; gap: 0.75rem;">
                <button type="submit" class="button primary">Enregistrer</button>
                <a href="{{ route('intranet.messages.index') }}" class="button secondary">Retour</a>
            </div>
        </form>
    </div>
@endsection