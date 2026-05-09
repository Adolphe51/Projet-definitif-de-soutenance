# CyberGuard

CyberGuard est une plateforme Laravel qui combine un accès sécurisé par OTP, un mini-site métier protégé, un moteur de détection ciblé et une interface SOC d'analyse d'incidents.

## Vue d'ensemble

Le système s'articule autour de quatre blocs :

- `Authentification sécurisée` : email, mot de passe, OTP, session signée, contrôle IP, audit.
- `Mini-site métier` : surface applicative sur `/intranet` pour produire des actions auditables.
- `SOC CyberGuard` : dashboard, alertes, incidents, blocage d'IP, géolocalisation, commentaires.
- `Simulation et honeypot` : laboratoire de démonstration volontaire et pièges publics secondaires.

## Point de cohérence important

Le code conserve encore des noms techniques hérités dans le mini-site :

- `Student` correspond au rôle métier de `Usager`
- `Course` correspond à un `Service métier`
- `Message` reste `Message`
- `Enrollment`, `Attendance` et `Resource` sont des entités de support

Autrement dit, si tu vois encore `students` ou `courses` dans les modèles ou les UML, ce n'est pas une incohérence cachée : c'est bien l'implémentation actuelle du système.

## Contrôle d'accès réel

Les accès sont séparés comme suit :

- `/dashboard`, `/alerts`, `/attacks`, `/geo`, `/simulations`, `/honeypot` : session sécurisée obligatoire + rôle `admin`
- `/intranet/*` : session sécurisée obligatoire pour toute action
- `/admin` et `/phpmyadmin` : routes publiques honeypot, volontairement exposées comme pièges

Le mini-site n'est donc pas public. Toute action y dépend d'une authentification réussie.

## Installation locale

Prérequis :

- PHP 8.2+
- Composer
- Node.js + npm
- SQLite ou MySQL

Installation rapide :

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Application locale :

`http://127.0.0.1:8000`

## Comptes de démonstration

Créés par [database/seeders/SystemSeeder.php](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/database/seeders/SystemSeeder.php) :

- `admin@gmail.com` / `Admin@123`
- `metier@gmail.com` / `Metier@123`

Le compte `admin` est redirigé vers le dashboard SOC après OTP. Le compte `metier` est redirigé vers `/intranet`.

## Commandes utiles

Maintenance :

```bash
php artisan cyberguard:autoblock
php artisan cyberguard:cleanup --days=30
php artisan schedule:work
```

Simulation :

```bash
php artisan cyberguard:detect --count=1
php artisan cyberguard:honeypot init
php artisan cyberguard:honeypot status
```

Mini-site et scénarios de test :

```bash
php artisan intranet:vulnerabilities inject
php artisan intranet:vulnerabilities sql
php artisan intranet:vulnerabilities xss
php artisan intranet:vulnerabilities bruteforce
php artisan intranet:vulnerabilities clean
```

Sans argument, `php artisan intranet:vulnerabilities` affiche désormais les actions disponibles.

## Déploiement VPS

Pour un déploiement propre sur VPS :

- utiliser `APP_ENV=production`
- désactiver `APP_DEBUG`
- servir l'application derrière `Nginx` + `PHP-FPM`
- activer HTTPS
- configurer `SESSION_SECURE_COOKIE=true`
- définir `CYBERGUARD_TRUSTED_ADMIN_IPS`
- préférer MySQL en production
- lancer le scheduler via cron

Le guide détaillé est dans [docs/DEPLOIEMENT_VPS.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/docs/DEPLOIEMENT_VPS.md).

## Documentation utile

- [docs/ARCHITECTURE_SYSTEME.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/docs/ARCHITECTURE_SYSTEME.md)
- [docs/DEMO_MINI_SITE_METIER.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/docs/DEMO_MINI_SITE_METIER.md)
- [docs/DEPLOIEMENT_VPS.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/docs/DEPLOIEMENT_VPS.md)
- [docs/GUIDE_PRESENTATION_UML_SOUTENANCE.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/docs/GUIDE_PRESENTATION_UML_SOUTENANCE.md)
- [INTRANET_README.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/INTRANET_README.md)

Sources UML :

- [public/UML/diagram-class.puml](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/public/UML/diagram-class.puml)
- [public/UML/diagram-deployment.puml](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/public/UML/diagram-deployment.puml)
- [public/UML/diagram-cas-utilisation/diagram-admin-systeme.puml](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/public/UML/diagram-cas-utilisation/diagram-admin-systeme.puml)
- [public/UML/diagram-cas-utilisation/diagram-user-intranet.puml](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/public/UML/diagram-cas-utilisation/diagram-user-intranet.puml)

## Tests

```bash
composer test
```

Ou :

```bash
vendor/bin/phpunit --configuration phpunit.xml.dist
```

## Licence

Projet sous licence MIT.
