@extends('layouts.app')

@section('title', 'Détails du service - Module de démonstration')
@section('page-title', 'Fiche service')
@section('page-subtitle', 'Vue détaillée d’un service métier utilisé pour illustrer contenus sensibles, rattachements et détection contextualisée.')

@section('content')
    <section class="intranet-detail-grid">
        <div class="intranet-detail-card">
            <div class="intranet-note">
                <strong>Support de démonstration</strong>
                <p>Les descriptions, contenus affichés et rattachements présents ici peuvent servir de base à une détection SQL Injection ou XSS expliquée au jury.</p>
            </div>

            <div class="section-header" style="margin-top: 1rem;">
                <div class="section-title">Informations du service</div>
            </div>

            <dl class="intranet-kv">
                <div>
                    <dt>Référence</dt>
                    <dd class="mono">{{ $course->course_code }}</dd>
                </div>
                <div>
                    <dt>Statut</dt>
                    <dd><span class="badge badge-{{ $course->status === 'active' ? 'success' : 'warning' }}">{{ strtoupper($course->status) }}</span></dd>
                </div>
                <div>
                    <dt>Titre</dt>
                    <dd>{{ $course->title }}</dd>
                </div>
                <div>
                    <dt>Domaine</dt>
                    <dd>{{ $course->department }}</dd>
                </div>
                <div>
                    <dt>Criticite</dt>
                    <dd>{{ $course->credits }}/5</dd>
                </div>
                <div>
                    <dt>Cycle</dt>
                    <dd>{{ $course->semester }}</dd>
                </div>
                <div>
                    <dt>Capacité cible</dt>
                    <dd>{{ $course->max_students }}</dd>
                </div>
                <div style="grid-column: 1 / -1;">
                    <dt>Description</dt>
                    <dd>{{ $course->description ?: 'Aucune description disponible.' }}</dd>
                </div>
            </dl>

            <div class="section-header" style="margin-top: 1.25rem;">
                <div class="section-title">Rattachements enregistrés</div>
            </div>

            @if($course->enrollments->isNotEmpty())
                <ul class="intranet-rich-list">
                    @foreach($course->enrollments as $enrollment)
                        @php
                            $relationLabel = match ($enrollment->grade) {
                                'PO' => 'Proprietaire fonctionnel',
                                'VA' => 'Validateur',
                                'OP' => 'Operateur',
                                'RE' => 'Referent',
                                default => 'Rattachement',
                            };

                            $assignmentLabel = match ($enrollment->status) {
                                'completed' => 'rattachement valide',
                                'enrolled' => 'acces en cours',
                                'dropped' => 'acces retire',
                                default => $enrollment->status,
                            };
                        @endphp
                        <li>
                            <strong>{{ $enrollment->student->first_name ?? 'Usager supprimé' }} {{ $enrollment->student->last_name ?? '' }}</strong>
                            <span class="intranet-table-subtle">{{ $relationLabel }} · {{ $assignmentLabel }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="intranet-empty-text">Aucun rattachement n’est encore enregistré pour ce service.</p>
            @endif
        </div>

        <aside class="intranet-side-card">
            <div class="section-title">Lecture soutenance</div>
            <p>Ce module permet de montrer comment un contenu métier apparemment banal peut devenir une source d’alerte dès qu’une saisie ou une publication devient suspecte.</p>
            <div class="intranet-page-actions">
                <a href="{{ route('intranet.courses.edit', $course) }}" class="btn btn-primary">Éditer</a>
                <a href="{{ route('intranet.courses.index') }}" class="btn btn-secondary-outline">Retour</a>
            </div>
        </aside>
    </section>
@endsection
