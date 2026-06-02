@extends('layouts.app')

@section('title', 'Voir le message - Module de démonstration')
@section('page-title', 'Détail du message')
@section('page-subtitle', 'Lecture métier d’un message interne pour expliquer comment un contenu libre peut être audité puis signalé.')

@section('content')
    <section class="intranet-detail-grid">
        <div class="intranet-detail-card">
            <div class="intranet-note">
                <strong>Message corrélable</strong>
                <p>Ce flux est utile pour montrer comment un contenu HTML ou une charge suspecte peut être repéré puis remonté vers CyberGuard.</p>
            </div>

            <div class="section-header" style="margin-top: 1rem;">
                <div class="section-title">Contenu du message</div>
            </div>

            <dl class="intranet-kv">
                <div>
                    <dt>Expéditeur</dt>
                    <dd>{{ $message->sender->first_name ?? 'N/A' }} {{ $message->sender->last_name ?? '' }}</dd>
                </div>
                <div>
                    <dt>Destinataire</dt>
                    <dd>{{ $message->recipient->first_name ?? 'N/A' }} {{ $message->recipient->last_name ?? '' }}</dd>
                </div>
                <div>
                    <dt>Sujet</dt>
                    <dd>{{ $message->subject }}</dd>
                </div>
                <div>
                    <dt>État</dt>
                    <dd><span class="badge badge-{{ $message->is_read ? 'success' : 'warning' }}">{{ $message->is_read ? 'LU' : 'NON LU' }}</span></dd>
                </div>
                <div style="grid-column: 1 / -1;">
                    <dt>Contenu</dt>
                    <dd>{{ $message->body }}</dd>
                </div>
            </dl>
        </div>

        <aside class="intranet-side-card">
            <div class="section-title">Lecture soutenance</div>
            <p>Un message contenant du HTML, du JavaScript ou des motifs suspects permet de déclencher une démonstration parlante de détection puis d’alerte.</p>
            <div class="intranet-page-actions">
                <a href="{{ route('intranet.messages.edit', $message) }}" class="btn btn-primary">Éditer</a>
                <a href="{{ route('intranet.messages.index') }}" class="btn btn-secondary-outline">Retour</a>
            </div>
        </aside>
    </section>
@endsection
