# Mini-Site Métier CyberGuard

## Rôle du module

Le module `intranet` est la surface applicative protégée qui alimente CyberGuard en actions métier auditables.

Il sert à :

- manipuler des données métier crédibles
- produire des événements applicatifs traçables
- illustrer directement la détection `SQL Injection` et `XSS`
- servir de point d'entrée métier avant lecture des incidents côté SOC

## Périmètre utile

Le mini site couvre les éléments réellement utilisés par le système :

- `Usagers`
- `Services métier`
- `Rattachements`
- `Messages`

## Accès

Toutes les routes `/intranet/*` sont protégées par :

- `csrf`
- `blocked.ip`
- `session.security`
- `ip.authorized`
- `audit`

En pratique, aucune action du mini-site n'est accessible sans session authentifiée valide.

## Routes principales

- `GET /intranet`
- `GET /intranet/students`
- `GET /intranet/courses`
- `GET /intranet/messages`

Les routes sont définies dans [routes/web.php](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/routes/web.php).

## Intégration avec CyberGuard

Les changements métier peuvent déclencher l'événement `IntranetDataChanged`, puis :

- la journalisation d'audit
- l'analyse de contenu
- la création d'une attaque détectée
- la génération d'une alerte SOC

## Commandes de test

```bash
php artisan intranet:vulnerabilities inject
php artisan intranet:vulnerabilities sql
php artisan intranet:vulnerabilities xss
php artisan intranet:vulnerabilities bruteforce
php artisan intranet:vulnerabilities clean
```

## Guide de démonstration

Pour un déroulé de soutenance prêt à l'emploi, voir [docs/DEMO_MINI_SITE_METIER.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/docs/DEMO_MINI_SITE_METIER.md).

## Référence

Pour la vue système complète, voir [docs/ARCHITECTURE_SYSTEME.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/docs/ARCHITECTURE_SYSTEME.md).
