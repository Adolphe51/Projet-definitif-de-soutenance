# Analyse Rapide Du Projet CyberGuard

Date d'analyse mise a jour: 2026-04-21

## 1. Vue d'ensemble

CyberGuard est une application Laravel 12 orientee cybersécurité. Le projet combine:

- un tableau de bord de supervision securite
- une authentification renforcee par OTP
- un moteur de generation/detection d'attaques
- un systeme de honeypot avec pieges web
- un mini intranet academique servant de surface fonctionnelle et de terrain de test
- un journal d'alertes, d'audit et de sessions de securite

L'intention du projet est claire: fournir une plateforme pedagogique ou de demonstration capable de simuler, detecter et visualiser des incidents tout en montrant des mecanismes de defense.

## 2. Stack et environnement

### Backend

- PHP `8.3.6` detecte localement
- Laravel `12.54.1`
- Composer `2.8.9`
- package OTP: `pragmarx/google2fa`
- HTTP client: `guzzlehttp/guzzle`

### Frontend

- Blade
- JavaScript vanilla + Alpine.js
- Vite 5
- Tailwind CSS 3
- CSS custom dans `resources/css`

### Runtime local observe

- environnement: `local`
- debug: `enabled`
- langue: `fr`
- timezone app: `Africa/Porto-Novo`
- base active: `sqlite`
- cache: `database`
- queue: `database`
- session: `database`
- mailer: `log`

Sources: `composer.json`, `package.json`, `.env.example`, `php artisan about`.

## 3. Modules fonctionnels identifies

### 3.1 Authentification securisee

Le flux d'authentification actif est en 3 etapes:

1. saisie email + mot de passe
2. creation d'un etat temporaire `pending_auth` puis envoi d'un code OTP par email
3. verification OTP puis creation d'une session securisee

Comportements notables:

- OTP a 8 chiffres
- duree de vie OTP: `3 minutes`
- etat `pending_auth` limite dans le temps avant verification OTP
- limitation des tentatives OTP
- journalisation des echecs et succes d'authentification
- creation de session securisee avec token d'acces + refresh token
- empreinte navigateur pour detecter le vol de session
- limite de 5 sessions actives par utilisateur
- cookie `access_token` en `httpOnly` et `sameSite=strict`
- cookie `secure` adapte a `config('session.secure')`
- validation dediee via `SendOtpRequest` et `VerifyOtpRequest`

Fichiers clefs:

- `app/Http/Controllers/Auth/LoginController.php`
- `app/Services/Auth/OTPService.php`
- `app/Services/Auth/SecuritySessionService.php`
- `app/Http/Middleware/Auth/*`

### 3.2 Tableau de bord securite

Le dashboard agrege:

- nombre total d'attaques
- attaques critiques
- IP bloquees
- alertes non lues
- simulations executees
- pays detectes
- type d'attaque dominant
- activite honeypot recente

Le dashboard expose aussi un endpoint JSON de stats avec rafraichissement court et generation aleatoire d'attaques de demonstration.

Fichier clef:

- `app/Http/Controllers/DashboardController.php`

### 3.3 Detection et simulation d'attaques

Le service de detection produit des attaques fictives ou semi-simulees avec:

- IP source aleatoire
- geolocalisation
- type d'attaque
- severite
- metadonnees reseau
- creation automatique d'alerte

Types declares:

- DDoS
- SQL Injection
- XSS
- Brute Force
- Port Scan
- Ransomware
- Phishing
- MITM
- Buffer Overflow
- DNS Spoofing
- ARP Poisoning
- Zero Day

Fichiers clefs:

- `app/Services/AttackDetectionService.php`
- `app/Models/Attack.php`
- `app/Http/Controllers/AttackController.php`
- `app/Http/Controllers/SimulationController.php`

### 3.4 Honeypot

Le projet implemente des pieges web et des donnees appats:

- faux portail admin
- faux phpMyAdmin
- fausse API REST
- faux WordPress login
- document canary

Le honeypot journalise:

- IP source
- pays / ville / ISP
- user agent
- payload
- identifiants saisis
- score de risque

Fonctions prevues:

- initialisation des pieges
- simulation d'interactions
- visualisation des interactions
- alertes critiques lors de capture d'identifiants
- auto-blocage si risque tres eleve

Fichiers clefs:

- `app/Services/HoneypotService.php`
- `app/Http/Controllers/HoneypotController.php`
- `app/Http/Middleware/HoneypotMiddleware.php`

### 3.5 Intranet academique

Le module intranet sert de domaine metier de demonstration avec:

- etudiants
- cours
- inscriptions
- presences
- messagerie
- ressources

Le module est complete en CRUD sur plusieurs entites et alimente par un seeder volumineux.

Fichiers clefs:

- `app/Http/Controllers/Intranet/*`
- `app/Models/Intranet/*`
- `resources/views/intranet/*`

## 4. Specifications techniques et securite

### Middlewares de securite declares

Ordre voulu dans `bootstrap/app.php`:

1. `csrf`
2. `honeypot`
3. `blocked.ip`
4. `session.security`
5. `ip.authorized`
6. `audit`

Ce point montre une intention architecturale explicite sur l'ordre de controle des requetes.

### Authentification et durcissement

La configuration `config/cyberguard.php` contient maintenant une section `auth` centralisee:

- OTP:
  - longueur: `8`
  - validite: `3 minutes`
  - tentatives max: `3`
  - delai de renvoi: `180 secondes`
  - validite de `pending_auth`: `10 minutes`
- sessions:
  - sessions actives max: `5`
  - duree de vie: `1 heure`

Le flux actuel est plus robuste qu'auparavant:

- le mot de passe est verifie avant emission OTP
- `resendOtp` et `verifyOtp` exigent un etat `pending_auth` valide
- la session Laravel est regeneree avant et apres le flux sensible
- le cookie d'acces est emis en mode `httpOnly` avec `sameSite=strict`

### CSRF renforce

Le middleware `EnhancedCsrfProtection` ajoute:

- journalisation des CSRF manquants ou invalides
- acceptation du token via bearer token si necessaire
- verification supplementaire du `User-Agent` pour durcir la coherence de requete

### Rate limiting

Regles definies dans `config/cyberguard.php`:

- `login`: 5 tentatives / 15 min
- `otp.send`: 3 tentatives / 15 min
- `otp.resend`: 3 tentatives / 15 min
- `otp.verify`: 5 tentatives / 5 min

Dans l'etat actuel du routage:

- `GET /login` n'est plus throttle
- le throttle est applique sur `POST /otp/send`, `POST /otp/resend` et `POST /otp/verify`

Le middleware incremente avant de laisser passer la requete, bloque temporairement l'IP et renvoie:

- du JSON `429` pour les clients API
- une redirection avec message d'erreur pour les clients web

### Sessions de securite

- tokens hashes en base
- expiration reduite a 1 heure
- rotation du refresh token
- revocation auto de la plus ancienne session apres 5 sessions actives
- verification du fingerprint navigateur

### Honeypot

Configuration `config/cyberguard.php`:

- honeypot active par defaut
- journalisation active par defaut
- chemins declares:
  - `/wp-admin`
  - `/phpmyadmin`
  - `/admin`
  - `/api/v1`
  - `/internal/confidential.pdf`
- whitelist locale:
  - `127.0.0.1`
  - `::1`

### Detection / alarmes

- seuil d'alarme par defaut: `high`
- mode demo active par defaut
- declencheurs d'alarme sonore: `high`, `critical`
- provider geo par defaut: `local`

## 5. Donnees, base et seeders

### Base de donnees

Le projet supporte:

- SQLite pour dev / demarrage rapide
- MySQL en option

### Migrations principales observees

- utilisateurs
- sessions de securite
- roles utilisateur
- permissions
- journaux d'audit
- codes OTP
- attaques
- alertes
- simulations
- IP bloquees
- interactions honeypot
- tables intranet
- index de performance ajoutes en avril 2026

### Seeders principaux

- `PermissionSeeder`
- `SystemSeeder`
- `CyberGuardSeeder`
- `IntranetSeeder`

### Comptes de demonstration seedes

Crees par `database/seeders/SystemSeeder.php`:

- `admin@gmail.com` / `Admin@123`
- `analyst@univ.dz` / `Secret@123`

Remarque: ces identifiants sont utiles pour la demo locale, mais ne doivent pas survivre tels quels en environnement reel.

## 6. Commandes Artisan et automatisation

### Commandes metier presentes

- `cyberguard:detect`
- `cyberguard:honeypot`
- `cyberguard:autoblock`
- `cyberguard:cleanup`
- `inject intranet vulnerabilities` via `InjectIntranetVulnerabilities`

### Scheduler

Observe dans `app/Console/Kernel.php`:

- generation d'attaques de demo chaque minute
- auto-blocage toutes les 5 minutes
- simulation honeypot toutes les 2 minutes
- nettoyage quotidien a `03:00`
- rapport honeypot quotidien a `08:00`

## 7. Routage actif constate

`php artisan route:list` remonte actuellement `34` routes.

Routes effectivement actives et visibles:

- `/`
- `/login`
- `/otp/send`
- `/otp/resend`
- `/otp/verify`
- `/auth/logout`
- `/admin/dashboard`
- `/intranet/...` avec CRUD et pages associees

### Ecart important

Les controleurs suivants existent dans le code:

- `AttackController`
- `AlertController`
- `GeoController`
- `SimulationController`
- `HoneypotController`
- `FaceLoginController`

Mais leurs routes ne sont pas actuellement exposees dans `routes/web.php`.

Consequence:

- la documentation `README.md` annonce plus de pages que celles reellement branchées
- une partie importante du projet est implementee cote controleurs / vues / services, mais pas encore raccordee au routage actif

Exemples de pages annoncees dans le README mais non visibles dans le `route:list` observe:

- `/attacks`
- `/attacks/live`
- `/geo/attackers`
- `/simulations`
- `/alerts`
- `/honeypot`

## 8. Qualite, tests et etat actuel

### Couverture de tests

Des tests existent dans:

- `tests/Feature/Auth/*`
- `tests/Feature/CyberGuard/*`
- `tests/Feature/ProfileTest.php`
- `tests/TestCase.php`
- `tests/CreatesApplication.php`

### Etat d'execution

La chaine de tests Laravel est de nouveau fonctionnelle.

Resultat observe sur `composer test`:

- `22` tests executes
- `78` assertions
- `1` erreur restante

Conclusion:

- le socle de tests est restaure
- la majorite des tests passe
- l'erreur restante est localisee dans `tests/Feature/CyberGuard/DashboardStatsTest.php`
- le probleme vient du test lui-meme, qui appelle `assertStatus()` sur un `JsonResponse` obtenu directement depuis le controleur au lieu de passer par une vraie requete HTTP

### Portee des tests actuellement visibles

Les tests couvrent notamment:

- affichage de la page login
- demarrage du flux OTP
- rejet des mauvais mots de passe
- throttling OTP
- logout avec session securisee
- cookie de session secure / non secure selon configuration
- refus d'acces avec session expiree
- coherence RBAC avec le schema actuel

## 9. Documentation et artefacts utiles

### Documentation existante

- `README.md`
- `docs/REVUE_FINALE_AUTH_SOUTENANCE.md`
- `public/UML/diagram-cas-utilisation.puml`
- `public/UML/diagram-class.puml`
- `public/UML/diagram-deployment.puml`
- `public/UML/sequence/sequence-authentification.puml`
- `public/UML/sequence/sequence-generale.puml`
- `public/UML/sequence/sequence-RBAC-ABAC.puml`

Ces fichiers sont utiles pour une soutenance, car ils couvrent deja:

- cas d'utilisation
- diagramme de classes
- deploiement
- sequences d'authentification et de controle d'acces

## 10. Resume executif

### Forces du projet

- theme et positionnement clairs
- architecture Laravel proprement decoupee en controllers / services / models / middleware
- vrai effort sur la securite applicative
- intranet pedagogique riche pour contextualiser les attaques
- seeders de demo bien fournis
- documentation UML deja presente

### Points d'attention

- ecart entre README et routage actif reel
- fonctionnalites cybersécurité presentes dans le code mais non exposees publiquement
- suite de tests globalement restauree mais encore non totalement verte
- un test dashboard reste a corriger
- depot local actuellement tres modifie, donc prudence avant refactor large

### Lecture synthese

Si tu dois presenter vite:

- CyberGuard est une plateforme Laravel 12 de supervision et simulation cyber
- le coeur actif aujourd'hui est l'auth OTP durcie, le dashboard admin et l'intranet
- les modules attaques, alertes, geo, simulations et honeypot sont largement developpes mais semblent en phase d'integration de routage
- l'environnement local est configure en SQLite avec cache, queue et sessions en base
- la couche d'authentification est maintenant bien mieux testee et plus defendable en soutenance
