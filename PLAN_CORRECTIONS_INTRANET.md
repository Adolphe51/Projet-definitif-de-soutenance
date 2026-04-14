# Plan de Correction et Amélioration de l'Intranet Académique CyberGuard

**Date de création :** 4 avril 2026  
**Projet :** Intégration d'un intranet académique fictif pour tests de détection d'attaques  
**Objectif :** Corriger les problèmes identifiés, implémenter les fonctionnalités manquantes et améliorer le design front-end de manière classique (CSS/JS vanilla).

## Principes généraux du plan
- **Approche itérative** : Chaque étape est indépendante, testable et réversible. Validation obligatoire avant progression.
- **Priorisation** : Corrections critiques (sécurité, modèles) → Fonctionnalités (contrôleurs, vues) → Améliorations (design).
- **Front-end classique** :
  - CSS : Fichiers `.css` séparés, sélecteurs classiques, flexbox/grid, sans frameworks (pas de Bootstrap).
  - JS : Vanilla JavaScript (ES6+), manipulation DOM native, événements classiques, sans bibliothèques.
  - Pas de styles inline dans les vues Blade ; tout dans des fichiers dédiés.
- **Tests** : Exécuter `php artisan test` et vérifications manuelles via `/intranet`.
- **Durée estimée** : 2-3 heures par étape.
- **Validation** : Après chaque étape, confirmer (ex. : "OK pour Étape X") ou demander ajustements.

## Statut des étapes
- [x] **Étape 1** : Corrections des modèles et migrations ✅ **TERMINÉ**
- [x] **Étape 2** : Implémentation des contrôleurs ✅ **TERMINÉ**
- [ ] **Étape 3** : Création des vues de base avec CSS/JS classiques
- [ ] **Étape 4** : Amélioration du design et styles front-end
- [ ] **Étape 5** : Tests finaux, documentation et intégration CyberGuard

---

## Étape 1 : Corrections des modèles et migrations (Priorité haute - Sécurité et stabilité) ✅ **TERMINÉ**
**Objectif :** Résoudre les incohérences identifiées (fillable, UUID, relations) pour éviter les erreurs de base de données et renforcer l'isolation.

**Tâches réalisées :**
- ✅ Vérifier et corriger tous les `$fillable` dans les modèles Intranet (ajouter 'id' partout, synchroniser avec les migrations).
  - `Attendance.php` : Ajout de `'id'` dans `$fillable`
  - `Enrollment.php` : Ajout de `'id'` dans `$fillable`
  - `Resource.php` : Ajout de `'id'` dans `$fillable`
  - `Student.php` : Déjà correct (contient `'id'`)
  - `Message.php` : Déjà correct (contient `'id'`)
  - `Course.php` : Déjà correct (contient `'id'`)
- ✅ Uniformiser les noms de colonnes (ex. : `lecture_date` vs `date` dans Attendance).
  - Vérifié : `lecture_date` est cohérent entre modèle et migration
- ✅ Corriger incohérence dans Course.php : `is_active` → `status` dans le scope `active()`
- ℹ️ Ajouter des contraintes manquantes (ex. : validation des emails, longueurs max).
  - Sera fait au niveau des contrôleurs (Étape 2)

**Risques :** Erreurs de seeding si non corrigé.  
**Validation :** Exécuter `php artisan db:seed --class=IntranetSeeder` sans erreurs, puis `php artisan tinker` pour vérifier les relations (ex. : `$student->enrollments`).

**Fichiers modifiés :**
- `app/Models/Intranet/Course.php` - Correction scope active() (is_active → status)
- `app/Models/Intranet/Attendance.php` - Ajout 'id' dans fillable
- `app/Models/Intranet/Enrollment.php` - Ajout 'id' dans fillable
- `app/Models/Intranet/Resource.php` - Ajout 'id' dans fillable

**Notes :** Tous les modèles Intranet ont maintenant 'id' dans $fillable pour être cohérents avec les migrations UUID.

---

## Étape 2 : Implémentation des contrôleurs (Priorité haute - Fonctionnalités de base)
**Objectif :** Rendre les routes fonctionnelles avec logique métier basique, tout en intégrant les événements CyberGuard.

**Tâches :**
- Implémenter les méthodes CRUD dans `StudentController`, `CourseController`, etc. (index, show, create, store, etc.).
- Ajouter la logique d'authentification et d'autorisation (middleware existants).
- Intégrer les événements `IntranetDataChanged` dans chaque action (ex. : `event(new IntranetDataChanged('student', 'create', $data))`).
- Gérer les erreurs et redirections.

**Risques :** Routes non fonctionnelles si les contrôleurs sont vides.  
**Validation :** Accéder à `/intranet/students` et vérifier que la liste s'affiche (même basique), puis créer un étudiant et vérifier les logs d'audit.

**Notes :** ✅ Contrôleurs implémentés et routes Intranet vérifiées. Les vues de base ont aussi été partiellement créées pour les messages, inscriptions et présences.

---

## Étape 3 : Création des vues de base avec CSS/JS classiques (Priorité moyenne - Interface utilisateur)
**Objectif :** Créer des vues Blade simples, avec CSS et JS séparés pour l'intranet, sans styles complexes.

**Tâches :**
- Créer des vues pour chaque contrôleur (ex. : `resources/views/intranet/students/index.blade.php` avec tableaux HTML classiques).
- Structurer le layout : Header/footer simple, navigation avec `<nav>`, contenu principal avec `<main>`.
- CSS : Fichier `resources/css/intranet.css` avec styles de base (couleurs neutres, typographie simple, responsive avec media queries).
- JS : Fichier `resources/js/intranet.js` pour interactions (ex. : confirmation de suppression, tri de tableaux).
- Pas de Bootstrap : Utiliser `display: flex` pour layouts, `box-shadow` pour effets.

**Risques :** Interface non utilisable si les vues sont absentes.  
**Validation :** Naviguer dans l'intranet et vérifier que les pages se chargent correctement, avec styles appliqués (ex. : boutons fonctionnels).

**Notes :** [Ajouter ici vos observations après validation]

---

## Étape 4 : Amélioration du design et styles front-end (Priorité moyenne - Expérience utilisateur)
**Objectif :** Polir l'interface pour une meilleure UX, tout en restant classique.

**Tâches :**
- Améliorer le CSS : Ajouter des thèmes (clair/sombre via classes), animations subtiles (transitions CSS), icônes SVG inline.
- JS avancé : Ajout de fonctionnalités comme recherche en temps réel, pagination côté client, modales pour confirmations.
- Responsive design : Adapter pour mobile avec flexbox et grid.
- Intégrer des éléments pédagogiques (ex. : tooltips pour expliquer les vulnérabilités).
- Optimisation : Minifier CSS/JS, lazy loading pour les ressources.

**Risques :** Performance si JS est trop lourd.  
**Validation :** Tester sur différents navigateurs et tailles d'écran ; vérifier que les interactions (ex. : recherche) fonctionnent sans erreurs.

**Notes :** [Ajouter ici vos observations après validation]

---

## Étape 5 : Tests finaux, documentation et intégration CyberGuard (Priorité basse - Finalisation)
**Objectif :** Assurer la robustesse et documenter pour la thèse.

**Tâches :**
- Écrire des tests unitaires/fonctionnels pour les contrôleurs et services.
- Tester les vulnérabilités injectées (ex. : vérifier que CyberGuard détecte les injections SQL).
- Mettre à jour `INTRANET_README.md` avec captures d'écran et scénarios de test.
- Optimiser les performances (cache, requêtes N+1).

**Risques :** Bugs cachés dans l'intégration.  
**Validation :** Exécuter `php artisan intranet:vulnerabilities inject` et vérifier les alertes CyberGuard ; simuler une attaque et confirmer la détection.

**Notes :** [Ajouter ici vos observations après validation]

---

## Ressources et outils nécessaires
- **Outils** : VS Code pour l'édition, navigateur pour tests, Tinker pour debug.
- **Fichiers clés** : Modèles dans `app/Models/Intranet/`, contrôleurs dans `app/Http/Controllers/Intranet/`, vues dans `resources/views/intranet/`.
- **Dépendances** : Aucune nouvelle ; utiliser Laravel existant.

## Historique des validations
- **Étape 0 (Initiale)** : Intranet de base implémenté avec modèles, migrations, seeder et vulnérabilités. [Validé le 4 avril 2026]
- [Ajouter ici les validations futures, ex. : "Étape 1 validée le XX/XX/XXXX - Commentaires : ..."]

**Prochaine action :** Poursuivre avec l'Étape 3 pour finaliser les vues de base et valider l'interface Intranet.