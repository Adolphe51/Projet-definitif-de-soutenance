@extends('layouts.app')

@section('title', 'Modifier le message - Mini site')
@section('page-title', 'Modifier un message')
@section('page-subtitle', 'Mise à jour d’un contenu interne avec conservation de la trace et possibilité de corrélation vers les événements de sécurité.')

@section('content')
    <section class="intranet-form-layout">
        <div class="intranet-form-card">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('intranet.messages.update', $message) }}" method="POST" class="intranet-form-grid">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="sender_id">Expéditeur</label>
                    <select name="sender_id" id="sender_id" required>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('sender_id', $message->sender_id) === $student->id ? 'selected' : '' }}>
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="recipient_id">Destinataire</label>
                    <select name="recipient_id" id="recipient_id" required>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('recipient_id', $message->recipient_id) === $student->id ? 'selected' : '' }}>
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
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
                <label class="intranet-inline-checkbox" for="is_read">
                    <input type="checkbox" name="is_read" id="is_read" value="1" {{ old('is_read', $message->is_read) ? 'checked' : '' }}>
                    <span>Message déjà lu</span>
                </label>

                <div class="intranet-page-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('intranet.messages.show', $message) }}" class="btn btn-secondary-outline">Retour</a>
                </div>
            </form>
        </div>

        <aside class="intranet-side-card">
            <div class="section-title">Point clé</div>
            <p>Modifier un message permet de montrer qu’un contenu normal peut devenir un événement à surveiller dès qu’il contient un motif ou une charge malveillante.</p>
        </aside>
    </section>
@endsection
