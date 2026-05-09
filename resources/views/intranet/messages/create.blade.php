@extends('layouts.app')

@section('title', 'Créer un message - Mini site')
@section('page-title', 'Créer un message')
@section('page-subtitle', 'Rédaction d’un échange interne dans un espace sécurisé, utile pour démontrer la journalisation et la détection de contenu suspect.')

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

            <form action="{{ route('intranet.messages.store') }}" method="POST" class="intranet-form-grid">
                @csrf
                <div class="form-group">
                    <label for="sender_id">Expéditeur</label>
                    <select name="sender_id" id="sender_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('sender_id') === $student->id ? 'selected' : '' }}>
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="recipient_id">Destinataire</label>
                    <select name="recipient_id" id="recipient_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('recipient_id') === $student->id ? 'selected' : '' }}>
                                {{ $student->first_name }} {{ $student->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject">Sujet</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required>
                </div>
                <div class="form-group">
                    <label for="body">Contenu</label>
                    <textarea name="body" id="body" required>{{ old('body') }}</textarea>
                </div>
                <label class="intranet-inline-checkbox" for="is_read">
                    <input type="checkbox" name="is_read" id="is_read" value="1" {{ old('is_read') ? 'checked' : '' }}>
                    <span>Marquer comme lu dès la création</span>
                </label>

                <div class="intranet-page-actions">
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                    <a href="{{ route('intranet.messages.index') }}" class="btn btn-secondary-outline">Retour</a>
                </div>
            </form>
        </div>

        <aside class="intranet-side-card">
            <div class="section-title">Angle démonstration</div>
            <p>La zone de contenu libre est idéale pour provoquer une détection XSS maîtrisée puis montrer immédiatement l’alerte correspondante dans CyberGuard.</p>
        </aside>
    </section>
@endsection
