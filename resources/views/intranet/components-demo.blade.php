@extends('layouts.app')

@section('title', 'Composants Intranet')

@section('content')
    <x-breadcrumb :items="[
            ['label' => 'Accueil', 'url' => route('intranet.index')],
            ['label' => 'Composants']
        ]" />

    <h1>Galerie de Composants Intranet</h1>

    <x-info-box>
        Cette page démontre tous les composants disponibles de l'Intranet. Chaque composant peut être utilisé dans vos vues.
    </x-info-box>

    <!-- Cartes -->
    <h2>Cartes (Cards)</h2>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <x-card title="Carte simple">
            Ceci est une carte simple avec du contenu.
        </x-card>

        <x-card title="Carte avec moyenne">
            <p>Cette carte affiche des informations importantes.</p>
            <div style="background: blue; padding: 1rem; border-radius: 0.5rem; color: #2563eb;">
                📊 Donnée importante
            </div>
        </x-card>
    </div>

    <!-- Badges -->
    <h2>Badges</h2>

    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <x-badge type="primary">Badge Primaire</x-badge>
        <x-badge type="success">Badge Succès</x-badge>
        <x-badge type="danger">Badge Danger</x-badge>
        <x-badge type="warning">Badge Avertissement</x-badge>
    </div>

    <!-- Status Badges -->
    <h2>Badges de Statut</h2>

    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <x-status-badge status="active" label="Actif" />
        <x-status-badge status="inactive" label="Inactif" />
        <x-status-badge status="pending" label="En attente" />
    </div>

    <!-- Alerts -->
    <h2>Alertes</h2>

    <div style="margin-bottom: 2rem;">
        <x-alert type="success" title="Succès">
            L'opération s'est déroulée sans problème.
        </x-alert>

        <x-alert type="danger" title="Erreur">
            Une erreur est survenue, veuillez réessayer.
        </x-alert>

        <x-alert>
            Ceci est un message d'information générale.
        </x-alert>
    </div>

    <!-- Empty State -->
    <h2>État Vide (Empty State)</h2>

    <x-empty-state title="Aucune donnée disponible" icon="📭">
        Cette page affiche une démonstration d'un état vide. Généralement affiché quand aucune ressource n'existe.
    </x-empty-state>

    <!-- Progress Bar -->
    <h2>Barres de Progression</h2>

    <div style="margin-bottom: 2rem;">
        <p>Progression: 45%</p>
        <x-progress-bar :percentage="45" label="Progression globale" />

        <p style="margin-top: 1.5rem;">Progression: 100%</p>
        <x-progress-bar :percentage="100" label="Tâche terminée" />
    </div>

    <!-- Tooltips -->
    <h2>Infobulles (Tooltips)</h2>

    <div style="margin-bottom: 2rem;">
        <p>
            Passez votre souris sur le texte ci-dessous pour voir une infobulle:
        </p>
        <x-tooltip trigger="❓ Cliquez pour l'aide">
            Ceci est une infobulle contenant des informations d'aide.
        </x-tooltip>
    </div>

    <!-- Formulaire d'exemple -->
    <h2>Formulaire Dynamique</h2>

    <x-card title="Exemple de Formulaire">
        <form method="POST" style="display: grid; gap: 1rem;">
            @csrf

            <x-form-field type="text" name="username" label="Nom d'utilisateur" placeholder="Entrez votre nom d'utilisateur"
                required hint="3-20 caractères" />

            <x-form-field type="email" name="email" label="Adresse Email" required />

            <x-form-field type="select" name="course" label="Cours" :options="[
            '1' => 'Mathématiques',
            '2' => 'Français',
            '3' => 'Informatique',
            '4' => 'Histoire'
        ]" required />

            <x-form-field type="textarea" name="comments" label="Commentaires" rows="4"
                placeholder="Écrivez vos commentaires ici..." hint="Optionnel" />

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="button primary">Envoyer</button>
                <button type="reset" class="button secondary">Réinitialiser</button>
            </div>
        </form>
    </x-card>

    <!-- Breadcrumb -->
    <h2>Chaîne de Navigation (Breadcrumb)</h2>

    <x-breadcrumb :items="[
            ['label' => 'Accueil', 'url' => '#'],
            ['label' => 'Gestion', 'url' => '#'],
            ['label' => 'Étudiants', 'url' => '#'],
            ['label' => 'Jean Dupont']
        ]" />

    <!-- Tableau avec recherche -->
    <h2>Tableau avec Recherche en Temps Réel</h2>

    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Statut</th>
                <th>Inscrit le</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Jean Dupont</td>
                <td>jean@example.com</td>
                <td><x-status-badge status="active" /></td>
                <td>2024-01-15</td>
            </tr>
            <tr>
                <td>Marie Martin</td>
                <td>marie@example.com</td>
                <td><x-status-badge status="active" /></td>
                <td>2024-02-20</td>
            </tr>
            <tr>
                <td>Pierre Lefevre</td>
                <td>pierre@example.com</td>
                <td><x-status-badge status="inactive" /></td>
                <td>2024-01-10</td>
            </tr>
            <tr>
                <td>Sophie Bernard</td>
                <td>sophie@example.com</td>
                <td><x-status-badge status="pending" /></td>
                <td>2024-03-01</td>
            </tr>
            <tr>
                <td>Luc Gautier</td>
                <td>luc@example.com</td>
                <td><x-status-badge status="active" /></td>
                <td>2024-02-28</td>
            </tr>
        </tbody>
    </table>

    <p class="text-muted" style="margin-top: 0.5rem;">
        💡 Essayez de taper dans le champ de recherche pour filtrer les résultats.
    </p>

    <!-- Utilités -->
    <h2>Classes Utilitaires</h2>

    <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
        <div class="text-center alert">Texte aligné au centre</div>
        <div class="text-right alert">Texte aligné à droite</div>
        <div class="text-success">Texte vert (succès)</div>
        <div class="text-danger">Texte rouge (danger)</div>
        <div class="text-warning">Texte orange (avertissement)</div>
        <div class="text-muted">Texte grisé (basique)</div>
    </div>

    <!-- Thème sombre -->
    <h2>Thème Sombre</h2>

    <x-info-box>
        Cliquez sur le bouton lune/soleil (🌙/☀️) en haut à droite de la navigation pour basculer entre le thème clair et le
        thème sombre. La préférence est sauvegardée localement.
    </x-info-box>

    <div
        style="text-align: center; margin-top: 2rem; padding: 2rem; background: var(--color-bg-secondary); border-radius: 0.75rem;">
        <p>
            Tous les styles, animations et thèmes s'adaptent automatiquement au thème sombre. Aucune code spécial
            nécessaire!
        </p>
    </div>

@endsection