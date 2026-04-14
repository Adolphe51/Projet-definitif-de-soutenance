# Plan de Correction Final - CyberGuard + Intranet Académique

**Date de création :** 4 avril 2026  
**Projet :** CyberGuard avec Intranet Académique intégré  
**Objectif :** Plan unifié pour corriger et finaliser les deux systèmes avant production.

## Vue d'ensemble
- **CyberGuard** : Système principal de cybersécurité (score actuel : 6.5/10).
- **Intranet** : Système de test académique fictif intégré.
- **Approche** : Corriger CyberGuard d'abord (sécurité critique), puis Intranet (fonctionnalités).

## Plan unifié par phases

### Phase 1 : Sécurité CyberGuard (Critique - 1-2 jours)
**Étape 1 CyberGuard** : Rate limiting, OTP renforcé, tokens sécurisés, ordre middlewares.

### Phase 2 : Fonctionnalités de base (Haute - 2-3 jours)
**Étape 1 Intranet** : Corrections modèles/migrations.  
**Étape 2 Intranet** : Implémentation contrôleurs avec événements CyberGuard.

### Phase 3 : Interfaces utilisateur (Moyenne - 2-3 jours)
**Étape 3 Intranet** : Vues de base avec CSS/JS classiques.  
**Étape 2 CyberGuard** : Améliorations middleware/authentification.

### Phase 4 : Optimisations et conformité (Moyenne - 2-3 jours)
**Étape 4 Intranet** : Amélioration design/styles front-end.  
**Étape 3 CyberGuard** : Optimisations performance (DB, pagination, WebSockets).

### Phase 5 : Finalisation et tests (Basse - 1-2 jours)
**Étape 5 Intranet** : Tests finaux, documentation.  
**Étape 4 CyberGuard** : Conformité RGPD, HTTPS, nettoyage données.

## Métriques de succès
- **Sécurité** : Score CyberGuard ≥ 9/10.
- **Fonctionnalités** : Intranet opérationnel avec détection attaques.
- **Performance** : Temps réponse < 500ms, pas de requêtes N+1.
- **Conformité** : RGPD respecté, HTTPS forcé.

## Ressources nécessaires
- **Équipe** : 1 développeur full-stack.
- **Outils** : VS Code, Postman, JMeter pour tests.
- **Temps total estimé** : 8-13 jours.

## Suivi de progression
- [x] Phase 1 : Sécurité CyberGuard ✅ **TERMINÉ**
- [x] Étape 1 Intranet : Corrections modèles/migrations ✅ **TERMINÉ**
- [x] Étape 2 Intranet : Implémentation contrôleurs ✅ **TERMINÉ**
- [ ] Phase 3 : Interfaces utilisateur
- [ ] Phase 4 : Optimisations et conformité
- [ ] Phase 5 : Finalisation et tests

**Prochaine action** : Commencer la Phase 3 (Étape 3 Intranet - Création des vues de base avec CSS/JS classiques).

---

## Résumé Étape 1 Intranet - ✅ TERMINÉE

### Corrections des modèles et migrations :

1. **Course.php** :
   - Correction du scope `active()` : utilisation de `status` au lieu de `is_active`
   - Suppression de `is_active` des `$casts` (inexistant en base)

2. **Attendance.php** :
   - Ajout de `'id'` dans `$fillable` (requis pour UUID)

3. **Enrollment.php** :
   - Ajout de `'id'` dans `$fillable` (requis pour UUID)

4. **Resource.php** :
   - Ajout de `'id'` dans `$fillable` (requis pour UUID)

Tous les modèles Intranet sont maintenant cohérents avec leurs migrations respectives.

---

## Résumé Phase 1 (Étape 1 CyberGuard) - ✅ TERMINÉE

### Corrections critiques de sécurité implémentées :

1. **Rate Limiting** :
   - Configuration dans `config/cyberguard.php`
   - Middleware corrigé (incrémente avant requête)
   - Routes OTP/login protégées

2. **OTP Renforcé** :
   - 8 chiffres (au lieu de 6)
   - Expiration 3 min (au lieu de 5)
   - 3 tentatives max (au lieu de 5)

3. **Tokens Sécurisés** :
   - Génération cryptographiquement sûre
   - Durée de vie 1h (au lieu de 24h)
   - Rotation automatique
   - Chiffrement AES-256

4. **Ordre Middlewares Corrigé** :
   - csrf → honeypot → blocked.ip → session.security → ip.authorized → audit
</thinking>
<attempt_completion>
<result>
L'**Étape 1 du plan de correction** a été **complétée avec succès** ✅

### Résumé des corrections critiques de sécurité implémentées :

#### 1. Rate Limiting Réel
- **`config/cyberguard.php`** : Ajout de la section `rate_limits` avec configurations par route
- **`app/Http/Middleware/RateLimitMiddleware.php`** : Correction de la logique (incrémente AVANT la requête)
- **`routes/web.php`** : Routes `/login`, `/otp/send`, `/otp/resend`, `/otp/verify` protégées avec `middleware('throttle')`

#### 2. Renforcement OTP
- **`app/Services/Auth/OTPService.php`** : 
  - OTP passé de 6 à **8 chiffres**
  - Expiration réduite de 5 à **3 minutes**
- **`app/Models/AuthCode.php`** : Limite de tentatives réduite de 5 à **3 tentatives**

#### 3. Sécurisation des Tokens de Session
- **`app/Services/Auth/SecuritySessionService.php`** :
  - Génération de tokens avec `random_bytes()` (cryptographiquement sûr)
  - Durée de vie réduite de 24h à **1 heure**
  - Rotation automatique des tokens lors du refresh
  - Chiffrement AES-256 avec `Crypt::encryptString()`

#### 4. Correction de l'Ordre des Middlewares
- **`bootstrap/app.php`** : Ordre corrigé et groupe `secure` créé
- **`routes/web.php`** : Toutes les routes protégées utilisent le bon ordre :
  `csrf → honeypot → blocked.ip → session.security → ip.authorized → audit`

### Fichiers modifiés :
1. `config/cyberguard.php`
2. `app/Http/Middleware/RateLimitMiddleware.php`
3. `app/Services/Auth/OTPService.php`
4. `app/Models/AuthCode.php`
5. `app/Services/Auth/SecuritySessionService.php`
6. `bootstrap/app.php`
7. `routes/web.php`
8. `PLAN_CORRECTIONS_CYBERGUARD.md` (mis à jour)
9. `PLAN_CORRECTIONS_FINAL.md` (mis à jour)

Les 4 vulnérabilités critiques ont été résolues. Le système CyberGuard est maintenant protégé contre les attaques par force brute et les tokens sont sécurisés.
</result>
<task_progress>
- [x] Étape 1 : Corrections critiques de sécurité
  - [x] 1.1 Rate limiting réel (middleware + routes + config)
  - [x] 1.2 Renforcement OTP (8 chiffres, 3 min, 3 tentatives)
  - [x] 1.3 Sécurisation tokens (AES-256, rotation automatique)
  - [x] 1.4 Correction ordre middlewares
</task_progress>
