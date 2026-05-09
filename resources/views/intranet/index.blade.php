@extends('layouts.app')

@section('title', 'Mini Site Métier')
@section('page-title', 'Mini site métier')
@section('page-subtitle', 'Zone applicative sécurisée utilisée pendant la soutenance pour montrer connexion protégée, actions auditées et remontée d’événements vers CyberGuard.')

@section('content')
    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <span class="dashboard-chip">Parcours démonstration</span>
            <h2>Un mini site simple, crédible et directement relié au SOC CyberGuard.</h2>
            <p>
                L’idée n’est pas de multiplier les modules, mais de montrer un flux clair :
                connexion sécurisée, action métier, audit, détection éventuelle, puis analyse dans le dashboard.
            </p>
            <div class="dashboard-actions">
                <a href="{{ route('intranet.students.index') }}" class="btn btn-primary">Ouvrir les usagers</a>
                @if(auth()->user()?->hasRole('admin'))
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary-outline">Retour au dashboard</a>
                @endif
            </div>
        </div>

        <div class="dashboard-health dashboard-health--low">
            <div class="dashboard-health-label">Zone sécurisée</div>
            <div class="dashboard-health-value">Journalisation active</div>
            <div class="dashboard-health-meta">
                Les actions sensibles effectuées ici peuvent alimenter l’audit et, si besoin, des alertes corrélées dans CyberGuard.
            </div>
            <div class="dashboard-health-stats">
                <div>
                    <strong>OTP</strong>
                    <span>entrée sécurisée</span>
                </div>
                <div>
                    <strong>Audit</strong>
                    <span>suivi des actions</span>
                </div>
            </div>
        </div>
    </section>

    <section class="intranet-collection-grid">
        <article class="intranet-collection-card">
            <span>Module 01</span>
            <strong>Usagers</strong>
            <p>Profils, coordonnées et états exploitables pour illustrer les habilitations et l’audit métier.</p>
            <div class="intranet-page-actions">
                <a href="{{ route('intranet.students.index') }}" class="btn btn-primary btn-sm">Ouvrir</a>
            </div>
        </article>

        <article class="intranet-collection-card intranet-collection-card--green">
            <span>Module 02</span>
            <strong>Services</strong>
            <p>Catalogue métier réduit servant de support aux scénarios SQL Injection et à la traçabilité des modifications.</p>
            <div class="intranet-page-actions">
                <a href="{{ route('intranet.courses.index') }}" class="btn btn-primary btn-sm">Ouvrir</a>
            </div>
        </article>

        <article class="intranet-collection-card intranet-collection-card--amber">
            <span>Module 03</span>
            <strong>Messages</strong>
            <p>Contenus internes et échanges libres utiles pour montrer la détection XSS et la remontée d’alertes contextualisées.</p>
            <div class="intranet-page-actions">
                <a href="{{ route('intranet.messages.index') }}" class="btn btn-primary btn-sm">Ouvrir</a>
            </div>
        </article>
    </section>

    <section class="intranet-summary-grid">
        <div class="intranet-metric">
            <span>Étape 1</span>
            <strong>Connexion sécurisée</strong>
            <p class="intranet-empty-text">Authentification OTP puis ouverture d’une session protégée.</p>
        </div>
        <div class="intranet-metric">
            <span>Étape 2</span>
            <strong>Action métier</strong>
            <p class="intranet-empty-text">Consultation, création ou modification d’une donnée sur le mini site.</p>
        </div>
        <div class="intranet-metric">
            <span>Étape 3</span>
            <strong>Analyse CyberGuard</strong>
            <p class="intranet-empty-text">Audit, détection, création d’alerte et suivi sur le dashboard ou la liste d’incidents.</p>
        </div>
    </section>
@endsection
