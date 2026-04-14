# Plan de Correction pour le Système CyberGuard

**Date de création :** 4 avril 2026  
**Projet :** Plateforme CyberGuard (détection d'attaques, honeypots, audit)  
**Basé sur :** Analyse complète (analyse_complete.md)  
**Objectif :** Corriger les 4 vulnérabilités critiques, 8 problèmes majeurs et 12 améliorations pour atteindre la production.

## Principes généraux du plan
- **Priorisation** : Corrections critiques (sécurité) → Moyennes (performance) → Faibles (conformité).
- **Approche** : Itérative avec validation à chaque étape.
- **Durée estimée** : 4-6 heures par étape critique.
- **Risques** : Impact sur la sécurité si non corrigé.

## Statut des étapes
- [x] **Étape 1** : Corrections critiques de sécurité (Rate limiting, OTP, sessions) ✅ **TERMINÉ**
- [ ] **Étape 2** : Améliorations middleware et authentification
- [ ] **Étape 3** : Optimisations performance (DB, pagination)
- [ ] **Étape 4** : Conformité et finalisation (RGPD, HTTPS)

---

## Étape 1 : Corrections critiques de sécurité (Priorité haute) ✅ **TERMINÉ**
**Objectif :** Résoudre les 4 vulnérabilités critiques identifiées.

**Tâches réalisées :**
- ✅ Implémenter rate limiting réel (RateLimitMiddleware) pour tentatives OTP et login.
  - Configuration ajoutée dans `config/cyberguard.php` avec limites par route
  - Middleware corrigé pour incrémenter AVANT de passer la requête
  - Routes `/login`, `/otp/send`, `/otp/resend`, `/otp/verify` protégées
- ✅ Renforcer OTP : Codes 8 chiffres, expiration 3 min, limiter à 3 tentatives.
  - OTP passé de 6 à 8 chiffres dans `OTPService.php`
  - Expiration réduite de 5 à 3 minutes
  - Limite de tentatives réduite de 5 à 3 dans `AuthCode.php`
- ✅ Sécuriser tokens de session : Chiffrement AES-256, rotation automatique.
  - Génération de tokens cryptographiquement sûre avec `random_bytes()`
  - Durée de vie réduite de 24h à 1h pour access token
  - Rotation automatique des tokens lors du refresh
  - Chiffrement AES-256 avec `Crypt::encryptString()`
- ✅ Corriger ordre middlewares : csrf, honeypot, blocked.ip, session.security, ip.authorized, audit.
  - Ordre corrigé dans `bootstrap/app.php`
  - Routes mises à jour avec le bon ordre de middlewares

**Risques :** Attaques par force brute persistantes.  
**Validation :** Tester force brute simulée, vérifier blocage après limites.

**Fichiers modifiés :**
- `config/cyberguard.php` - Ajout section rate_limits
- `app/Http/Middleware/RateLimitMiddleware.php` - Correction logique
- `app/Services/Auth/OTPService.php` - OTP 8 chiffres, 3 min
- `app/Models/AuthCode.php` - Limite à 3 tentatives
- `app/Services/Auth/SecuritySessionService.php` - Tokens sécurisés
- `bootstrap/app.php` - Ordre middlewares corrigé
- `routes/web.php` - Routes protégées et ordre corrigé

---

## Étape 2 : Améliorations middleware et authentification (Priorité haute)
**Objectif :** Renforcer l'authentification et les contrôles d'accès.

**Tâches :**
- Ajouter protection CSRF complète.
- Implémenter vraie détection d'intrusion (remplacer génération aléatoire).
- Améliorer journalisation échecs d'authentification.
- Sécuriser stockage API keys (chiffrement).

**Risques :** Contournement des protections.  
**Validation :** Tests d'intrusion, vérification logs détaillés.

---

## Étape 3 : Optimisations performance (Priorité moyenne)
**Objectif :** Améliorer les performances et l'expérience utilisateur.

**Tâches :**
- Résoudre requêtes N+1 avec eager loading.
- Implémenter pagination sur toutes les listes.
- Ajouter index DB sur champs fréquents (source_ip, timestamps).
- Remplacer polling par WebSockets.

**Risques :** Dégradation performances sous charge.  
**Validation :** Tests de charge, mesurer temps de réponse.

---

## Étape 4 : Conformité et finalisation (Priorité moyenne)
**Objectif :** Atteindre conformité RGPD et bonnes pratiques.

**Tâches :**
- Implémenter nettoyage automatique données anciennes (OTP, logs).
- Forcer HTTPS dans toute l'application.
- Ajouter mécanisme suppression données utilisateur.
- Désactiver mode démo par défaut.

**Risques :** Non-conformité légale.  
**Validation :** Audit RGPD simulé, vérification HTTPS forcé.

---

## Historique des validations
- **Étape 0** : Analyse complète réalisée. [Validé le 4 avril 2026]