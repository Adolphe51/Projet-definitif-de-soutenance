# Guide d'utilisation des composants Intranet

## Composants disponibles

### 1. Card (Carte)
Conteneur stylisé avec en-tête et contenu.

```blade
<x-card title="Titre de la carte" :action="$actionView">
    Contenu de la carte
</x-card>
```

### 2. Badge
Étiquettes colorées compact.

```blade
<x-badge type="primary">Label</x-badge>
<x-badge type="success">Success</x-badge>
<x-badge type="danger">Danger</x-badge>
<x-badge type="warning">Warning</x-badge>
```

### 3. Status Badge
Statut avec indicateur visuel.

```blade
<x-status-badge status="active" label="Actif" title="L'objet est actif"/>
<x-status-badge status="inactive"/>
<x-status-badge status="pending" label="En attente"/>
```

### 4. Breadcrumb
Navigation en chaîne de miettes.

```blade
<x-breadcrumb :items="[
    ['label' => 'Accueil', 'url' => route('intranet.index')],
    ['label' => 'Étudiants', 'url' => route('intranet.students.index')],
    ['label' => 'Jean Dupont']
]"/>
```

### 5. Tooltip
Info-bulle au survol.

```blade
<x-tooltip trigger="Infos">
    C'est un texte d'aide qui apparaît au survol
</x-tooltip>
```

### 6. Alert
Boîte de notification.

```blade
<x-alert type="success" title="Succès">
    L'opération a été complétée avec succès.
</x-alert>

<x-alert type="danger">
    Une erreur s'est produite.
</x-alert>

<x-alert>
    Message d'information
</x-alert>
```

### 7. Empty State
Affichage quand aucune donnée disponible.

```blade
<x-empty-state 
    title="Aucun étudiant" 
    icon="👨‍🎓"
    :action="$createButton"
>
    Il n'y a pas encore d'étudiants. Commencez par en créer un.
</x-empty-state>
```

### 8. Progress Bar
Barre de progression.

```blade
<x-progress-bar 
    :percentage="75" 
    label="Progression"
/>
```

### 9. Info Box
Boîte d'information.

```blade
<x-info-box>
    Ceci est une boîte d'information utile pour l'utilisateur.
</x-info-box>
```

### 10. Form Field
Champ de formulaire avec gestion d'erreurs.

```blade
<x-form-field 
    type="text"
    name="first_name"
    label="Prénom"
    placeholder="Entrez le prénom"
    required
    hint="Le prénom de l'étudiant"
/>

<x-form-field 
    type="email"
    name="email"
    label="Email"
    required
/>

<x-form-field 
    type="select"
    name="course_id"
    label="Cours"
    required
    :options="['1' => 'Mathématiques', '2' => 'Français']"
/>

<x-form-field 
    type="textarea"
    name="description"
    label="Description"
    rows="5"
    hint="Décrivez le cours en détail"
/>
```

## Utilisation dans les vues

### Example simple avec Card

```blade
@extends('layouts.app')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Accueil', 'url' => route('intranet.index')],
        ['label' => 'Étudiants']
    ]"/>

    <h1>Gestion des étudiants</h1>

    @if($students->isEmpty())
        <x-empty-state 
            title="Aucun étudiant"
            icon="👨‍🎓"
        >
            Commencez par ajouter des étudiants.
        </x-empty-state>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    <tr>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>
                            <x-status-badge 
                                status="{{ $student->is_active ? 'active' : 'inactive' }}"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
```

### Example avec formulaire

```blade
@extends('layouts.app')

@section('content')
    <h1>Créer un nouvel étudiant</h1>

    <x-card title="Informations de l'étudiant">
        <form method="POST" action="{{ route('intranet.students.store') }}">
            @csrf

            <x-form-field 
                type="text"
                name="first_name"
                label="Prénom"
                required
            />

            <x-form-field 
                type="text"
                name="last_name"
                label="Nom de famille"
                required
            />

            <x-form-field 
                type="email"
                name="email"
                label="Email"
                required
            />

            <x-form-field 
                type="date"
                name="birth_date"
                label="Date de naissance"
            />

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="button primary">Créer l'étudiant</button>
                <a href="{{ route('intranet.students.index') }}" class="button secondary">
                    Annuler
                </a>
            </div>
        </form>
    </x-card>

    @if($errors->any())
        <x-alert type="danger" title="Erreurs de validation">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif
@endsection
```

## Styles et classes utilitaires

### Classes de texte
- `.text-center` - Centrer le texte
- `.text-right` - Aligner à droite
- `.text-muted` - Texte grisé
- `.text-success` - Texte vert
- `.text-danger` - Texte rouge
- `.text-warning` - Texte orange

### Marges
- `.mt-1`, `.mt-2`, `.mt-3`, `.mt-4` - Marges supérieures
- `.mb-1`, `.mb-2`, `.mb-3`, `.mb-4` - Marges inférieures

### Thème sombre
Le thème sombre s'active automatiquement selon la préférence du système, ou manuellement via le bouton dans la navigation.

Tout est géré automatiquement via CSS variables dans `:root`.

## Comportements JavaScript

### Recherche en temps réel
Tous les tableaux ont automatiquement un champ de recherche qui filtre les lignes.

### Thème clair/sombre
Un bouton de bascule de thème est ajouté automatiquement à la navigation intranet.

### Confirmations modales
Les éléments avec `data-confirm` ouvrent une modal de confirmation élégante au lieu du `confirm()` du navigateur.

### Lazy loading
Les images avec `data-src` seront chargées paresseusement lorsqu'elles apparaissent dans la vue.

## Bonnes pratiques

1. Utilisez les composants pour une cohérence visuelle
2. Exploitez la recherche en temps réel dans les listes longues
3. Utilisez les empty states pour améliorer l'UX
4. Les tooltips pour les éléments pédagogiques
5. Les badges pour les statuts et catégories
6. Le thème sombre est accessible via le bouton lune/soleil
