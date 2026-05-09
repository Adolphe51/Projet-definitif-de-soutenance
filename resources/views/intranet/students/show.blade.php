@extends('layouts.app')

@section('title', 'Détails de l\'usager - Mini site')
@section('page-title', 'Fiche usager')
@section('page-subtitle', 'Vue détaillée d’un profil métier utilisé pour démontrer traçabilité, rattachements et corrélation avec le SOC.')

@section('content')
    @php
        $statusBadge = match ($student->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'graduated' => 'info',
            default => 'info',
        };
    @endphp

    <section class="intranet-detail-grid">
        <div class="intranet-detail-card">
            <div class="intranet-note">
                <strong>Surface métier surveillée</strong>
                <p>Les champs texte, les changements de statut et les rattachements enregistrés ici peuvent être audités puis analysés par CyberGuard.</p>
            </div>

            <div class="section-header" style="margin-top: 1rem;">
                <div class="section-title">Informations principales</div>
            </div>

            <dl class="intranet-kv">
                <div>
                    <dt>Identifiant</dt>
                    <dd class="mono">{{ $student->student_id }}</dd>
                </div>
                <div>
                    <dt>Statut</dt>
                    <dd>
                        <span class="badge badge-{{ $statusBadge }}">
                            {{ match ($student->status) {
                                'active' => 'ACTIF',
                                'inactive' => 'INACTIF',
                                'graduated' => 'ARCHIVE',
                                default => strtoupper($student->status),
                            } }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>Nom complet</dt>
                    <dd>{{ $student->first_name }} {{ $student->last_name }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $student->email }}</dd>
                </div>
                <div>
                    <dt>Téléphone</dt>
                    <dd>{{ $student->phone ?: 'Non renseigné' }}</dd>
                </div>
                <div>
                    <dt>Date de naissance</dt>
                    <dd>{{ optional($student->date_of_birth)->format('d/m/Y') ?: 'Non renseignée' }}</dd>
                </div>
                <div style="grid-column: 1 / -1;">
                    <dt>Adresse</dt>
                    <dd>{{ $student->address ?: 'Aucune adresse renseignée.' }}</dd>
                </div>
            </dl>

            <div class="section-header" style="margin-top: 1.25rem;">
                <div class="section-title">Rattachements métier</div>
            </div>

            @if($student->enrollments->isNotEmpty())
                <ul class="intranet-rich-list">
                    @foreach($student->enrollments as $enrollment)
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
                            <strong>{{ $enrollment->course->title ?? 'Service supprimé' }}</strong>
                            <span class="intranet-table-subtle">
                                {{ $relationLabel }} · {{ $assignmentLabel }}
                                @if($enrollment->enrollment_date)
                                    · enregistré le {{ $enrollment->enrollment_date->format('d/m/Y') }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="intranet-empty-text">Aucun rattachement n’est encore enregistré pour cet usager.</p>
            @endif
        </div>

        <aside class="intranet-side-card">
            <div class="section-title">Lecture soutenance</div>
            <div class="intranet-summary-list">
                <div class="intranet-summary-row">
                    <span>Accès</span>
                    <strong>Authentifié</strong>
                </div>
                <div class="intranet-summary-row">
                    <span>Traçabilité</span>
                    <strong>Audit activé</strong>
                </div>
                <div class="intranet-summary-row">
                    <span>Détection associée</span>
                    <strong>SQLi / XSS</strong>
                </div>
            </div>

            <div class="intranet-page-actions">
                <a href="{{ route('intranet.students.edit', $student) }}" class="btn btn-primary">Éditer</a>
                <a href="{{ route('intranet.students.index') }}" class="btn btn-secondary-outline">Retour</a>
            </div>
        </aside>
    </section>
@endsection
