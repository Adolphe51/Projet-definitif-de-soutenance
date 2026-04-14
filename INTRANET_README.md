# Intranet Académique - Système de Test CyberGuard

## Vue d'ensemble

L'intranet académique est un système fictif intégré à CyberGuard pour créer un environnement de test réaliste permettant de simuler et détecter des attaques informatiques dans un contexte éducatif.

## Architecture

### Modèles de données

- **Student** : Étudiants avec informations personnelles
- **Course** : Cours académiques avec métadonnées
- **Enrollment** : Inscriptions des étudiants aux cours
- **Attendance** : Suivi des présences aux cours
- **Message** : Communications internes entre étudiants
- **Resource** : Matériels pédagogiques et ressources

### Isolation et sécurité

- Namespace dédié `App\Models\Intranet\` pour éviter les conflits
- UUID comme clés primaires pour la scalabilité
- Middleware de sécurité CyberGuard appliqué à toutes les routes
- Audit logging automatique de toutes les actions

## Installation et configuration

### 1. Exécuter les migrations

```bash
php artisan migrate
```

### 2. Générer les données fictives

```bash
php artisan db:seed --class=IntranetSeeder
```

### 3. Injecter des vulnérabilités de test

```bash
# Injecter toutes les vulnérabilités
php artisan intranet:vulnerabilities inject

# Ou injecter spécifiquement
php artisan intranet:vulnerabilities sql
php artisan intranet:vulnerabilities xss
php artisan intranet:vulnerabilities enumeration
php artisan intranet:vulnerabilities bruteforce
php artisan intranet:vulnerabilities dos
```

### 4. Nettoyer les données de test

```bash
php artisan intranet:vulnerabilities clean
```

## Utilisation

### Accès à l'intranet

L'intranet est accessible via `/intranet` une fois authentifié dans CyberGuard.

### Routes disponibles

- `GET /intranet` - Page d'accueil
- `GET /intranet/students` - Liste des étudiants
- `GET /intranet/courses` - Liste des cours
- `GET /intranet/messages` - Messages internes
- `GET /intranet/enrollments` - Inscriptions
- `GET /intranet/attendances` - Présences

## Scénarios de test

### 1. Injection SQL

- Données injectées avec des payloads SQL malicieux
- Détection automatique via `AttackDetectionService`

### 2. Cross-Site Scripting (XSS)

- Contenu HTML/JavaScript malicieux dans les descriptions de cours
- Test de sanitisation des entrées

### 3. Énumération d'utilisateurs

- Comptes avec noms prédictibles (ADMIN001, ADMIN002, etc.)
- Détection des tentatives de reconnaissance

### 4. Attaque par force brute

- Messages contenant des mots de passe potentiels
- Patterns de tentatives répétées

### 5. Déni de service (DoS)

- Simulation d'accès massifs aux ressources
- Test des limites de débit

## Intégration avec CyberGuard

### Événements

L'intranet déclenche automatiquement des événements `IntranetDataChanged` pour chaque modification :

```php
event(new IntranetDataChanged('student', 'create', $studentData));
```

### Listener

Le `ProcessIntranetDataChange` listener :
- Enregistre les actions dans les logs d'audit
- Analyse les patterns d'attaque
- Déclenche les alertes appropriées

### Service de détection

Le `AttackDetectionService` analyse :
- Patterns d'injection SQL
- Modifications massives de données
- Accès non autorisés
- Tentatives d'énumération

## Données de test

### Volume

- 100 étudiants fictifs
- 20 cours académiques
- 3-6 inscriptions par étudiant
- Présences sur 4 mois
- 200 messages internes
- 2-5 ressources par cours

### Réalisme

- Noms et adresses français (via Faker)
- Âges étudiants : 18-25 ans
- Départements académiques variés
- Notes et présences aléatoires

## Sécurité et bonnes pratiques

### Isolation

- Données intranet séparées des données CyberGuard
- Pas de croisement entre les systèmes de production et de test

### Audit

- Toutes les actions loggées automatiquement
- Traçabilité complète des modifications

### Nettoyage

- Commandes dédiées pour nettoyer les données de test
- Possibilité de reset complet de l'environnement

## Développement et extension

### Ajouter de nouveaux modèles

1. Créer le modèle dans `app/Models/Intranet/`
2. Créer la migration correspondante
3. Ajouter les relations Eloquent
4. Mettre à jour le seeder
5. Ajouter des routes et contrôleurs si nécessaire

### Créer de nouvelles vulnérabilités

1. Étendre `IntranetVulnerabilityService`
2. Ajouter la logique dans la commande Artisan
3. Documenter le scénario de test

## Métriques et monitoring

### Logs d'audit

Toutes les actions sont tracées dans `audit_logs` avec :
- Type d'entité modifiée
- Action effectuée
- Données avant/après
- Adresse IP et User-Agent

### Alertes CyberGuard

Les attaques détectées génèrent des alertes avec :
- Type d'attaque
- Gravité
- Contexte de l'attaque
- Mesures recommandées

## Documentation pour thèse

Ce système fournit un environnement contrôlé pour :
- Tester les capacités de détection de CyberGuard
- Valider les algorithmes d'analyse comportementale
- Mesurer les performances sous charge
- Évaluer l'efficacité des contre-mesures

### Scénarios pédagogiques

1. **Détection d'intrusion** : Injection de payloads malicieux
2. **Analyse comportementale** : Patterns d'accès anormaux
3. **Réponse aux incidents** : Automatisation des alertes
4. **Forensique** : Traçabilité des attaques

---

*Ce système est destiné à un usage éducatif et de recherche uniquement.*